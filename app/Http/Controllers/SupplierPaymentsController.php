<?php

namespace App\Http\Controllers;

use App\Helpers\TenantDataHelper;
use App\Models\Car;
use App\Models\SupplierPayment;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SupplierPaymentsController extends Controller
{
    protected $userAdmin;
    protected $userSeles;
    protected $userAccount;
    protected $outAccount;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $userTypeIds = TenantDataHelper::getUserTypeIds();
            $this->userAdmin = $userTypeIds['admin'];
            $this->userSeles = $userTypeIds['seles'];
            $this->userAccount = $userTypeIds['account'];

            $accounts = TenantDataHelper::getAccountingUsers();
            $this->outAccount = $accounts['out'];
            return $next($request);
        });
    }

    public function index(Request $request, $supplierId)
    {
        $supplierId = (int) $supplierId;
        $from = $request->get('from');
        $to = $request->get('to');

        // إصلاح تلقائي للحالات القديمة: إذا كان paid_amount = NULL
        // والدفعات موجودة، فـ increment سابقاً لم يكن يؤثر (NULL + رقم = NULL).
        try {
            DB::statement("
                UPDATE car c
                JOIN (
                    SELECT car_id, SUM(amount) AS total_paid
                    FROM supplier_payments
                    WHERE supplier_id = ?
                    GROUP BY car_id
                ) sp ON sp.car_id = c.id
                SET c.paid_amount = sp.total_paid
                WHERE c.user_purchase_id = ?
                  AND c.paid_amount IS NULL
            ", [$supplierId, $supplierId]);
        } catch (\Throwable $e) {
            // تجاهل في حال عدم توافق مخطط قاعدة البيانات
        }

        $q = SupplierPayment::with(['creator:id,name', 'car:id,no,pin,name,purchase_price,paid_amount'])
            ->where('supplier_id', $supplierId)
            ->orderByDesc('paid_at')
            ->orderByDesc('id');

        if ($from && $to) {
            $q->whereBetween('paid_at', [$from, $to]);
        }

        $payments = $q->paginate(100);

        $sumQ = SupplierPayment::where('supplier_id', $supplierId);
        if ($from && $to) {
            $sumQ->whereBetween('paid_at', [$from, $to]);
        }

        return Response::json([
            'success' => true,
            'payments' => $payments,
            'summary' => [
                'total' => (float) ($sumQ->sum('amount') ?? 0),
                'count' => (int) ($sumQ->count() ?? 0),
            ],
        ], 200);
    }

    /**
     * إضافة دفعة للمورد وتوزيعها على سياراته غير المسددة.
     * إذا تم تمرير car_id سيتم تخصيصها لسيارة واحدة.
     */
    public function store(Request $request, $supplierId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|date',
            'note' => 'nullable|string',
            'car_id' => 'nullable|integer',
            'on_account' => 'nullable|boolean',
        ]);

        $supplierId = (int) $supplierId;
        $supplier = User::select(['id', 'name'])->findOrFail($supplierId);

        $amount = (float) $request->amount;
        $paidAt = $request->paid_at;
        $note = $request->note ?? null;
        $carId = $request->car_id ? (int) $request->car_id : null;
        $onAccount = (bool) ($request->on_account ?? false);

        $allocations = [];

        DB::transaction(function () use ($supplierId, $supplier, $amount, $paidAt, $note, $carId, $onAccount, &$allocations) {
            $remaining = $amount;

            $carsQuery = Car::query()
                ->where('user_purchase_id', $supplierId)
                ->whereRaw('COALESCE(purchase_price,0) > COALESCE(paid_amount,0)')
                ->orderBy('purchase_data')
                ->orderByRaw('CAST(no AS UNSIGNED)');

            if ($carId) {
                $carsQuery->where('id', $carId);
            }

            $cars = $carsQuery->lockForUpdate()->get();

            // منع تجاوز المبلغ عن المطلوب (دين المورد/السيارة)
            $totalDue = 0.0;
            foreach ($cars as $car) {
                $carPurchase = (float) ($car->purchase_price ?? 0);
                $carPaid = (float) ($car->paid_amount ?? 0);
                $totalDue += max(0, $carPurchase - $carPaid);
            }
            $totalDue = (float) $totalDue;

            if ($cars->isEmpty()) {
                // لا توجد سيارات غير مدفوعة: اسمح فقط إذا اختار المستخدم الدفع على الحساب
                if (!$onAccount) {
                    abort(422, 'لا توجد سيارات غير مدفوعة لهذا المورد. فعّل خيار "دفعة على الحساب" لتسجيل رصيد');
                }

                SupplierPayment::create([
                    'supplier_id' => $supplierId,
                    'car_id' => null,
                    'amount' => $amount,
                    'paid_at' => $paidAt,
                    'note' => $note,
                    'created_by' => auth()->id() ?: null,
                ]);

                // تسجيل حركة مالية (خروج) من حساب الخرج إن وُجد
                $accounts = TenantDataHelper::getAccountingUsers();
                $out = $accounts['out'];
                if ($out) {
                    $wallet = $out->getWalletOrCreate();
                    $desc = "دفعة على الحساب للمورد: {$supplier->name} - مبلغ: {$amount}$";
                    if ($note) {
                        $desc .= " - ملاحظة: {$note}";
                    }

                    Transactions::create([
                        'amount' => $amount,
                        'type' => 'out',
                        'description' => $desc,
                        'wallet_id' => $wallet->id,
                        'morphed_id' => $supplierId,
                        'morphed_type' => 'App\\Models\\User',
                        'user_id' => auth()->id() ?: null,
                    ]);
                }

                // لا توجد allocations لأن الدفع على الحساب
                return;
            }

            if (!$onAccount && $amount > $totalDue + 0.00001) {
                abort(422, 'المبلغ أكبر من المتبقي المطلوب لهذا المورد/السيارة');
            }

            foreach ($cars as $car) {
                if ($remaining <= 0) break;

                $carPurchase = (float) ($car->purchase_price ?? 0);
                $carPaid = (float) ($car->paid_amount ?? 0);
                $carDue = max(0, $carPurchase - $carPaid);
                if ($carDue <= 0) continue;

                $pay = min($remaining, $carDue);

                // paid_amount قد يكون NULL في بعض قواعد البيانات القديمة
                // لذلك نستخدم COALESCE لضمان التحديث بشكل صحيح.
                Car::where('id', (int) $car->id)->update([
                    'paid_amount' => DB::raw('COALESCE(paid_amount,0) + ' . (float) $pay),
                ]);

                $payment = SupplierPayment::create([
                    'supplier_id' => $supplierId,
                    'car_id' => (int) $car->id,
                    'amount' => $pay,
                    'paid_at' => $paidAt,
                    'note' => $note,
                    'created_by' => auth()->id() ?: null,
                ]);

                $allocations[] = [
                    'supplier_payment_id' => $payment->id,
                    'car_id' => (int) $car->id,
                    'car_no' => $car->no,
                    'pin' => $car->pin,
                    'amount' => $pay,
                    'due_before' => $carDue,
                ];

                $remaining -= $pay;
            }

            if (empty($allocations)) {
                abort(422, 'لم يتم توزيع الدفعة (ربما السيارات مدفوعة بالكامل)');
            }

            $allocatedAmount = (float) array_reduce($allocations, function ($carry, $a) {
                return $carry + (float) ($a['amount'] ?? 0);
            }, 0.0);

            // إذا كان مسموح الدفع على الحساب ووجد فرق (overpayment) خزّنه كرصيد للمورد
            $advance = max(0, (float) ($amount - $allocatedAmount));
            if ($onAccount && $advance > 0.00001) {
                SupplierPayment::create([
                    'supplier_id' => $supplierId,
                    'car_id' => null,
                    'amount' => $advance,
                    'paid_at' => $paidAt,
                    'note' => $note ? ($note . ' (على الحساب)') : '(على الحساب)',
                    'created_by' => auth()->id() ?: null,
                ]);
            }

            // تسجيل حركة مالية (خروج) من حساب الخرج إن وُجد
            $accounts = TenantDataHelper::getAccountingUsers();
            $out = $accounts['out'];
            if ($out) {
                $wallet = $out->getWalletOrCreate();
                $desc = "دفعة للمورد: {$supplier->name} - مبلغ: {$amount}$";
                if ($note) {
                    $desc .= " - ملاحظة: {$note}";
                }
                $desc .= " - توزيع على " . count($allocations) . " سيارة";
                if ($advance > 0.00001) {
                    $desc .= " - على الحساب: {$advance}$";
                }

                Transactions::create([
                    'amount' => $amount,
                    'type' => 'out',
                    'description' => $desc,
                    'wallet_id' => $wallet->id,
                    'morphed_id' => $supplierId,
                    'morphed_type' => 'App\\Models\\User',
                    'user_id' => auth()->id() ?: null,
                ]);
            }
        });

        return Response::json([
            'success' => true,
            'message' => 'تمت إضافة الدفعة بنجاح',
            'allocations' => $allocations,
        ], 200);
    }

    /**
     * حذف دفعة لمورد (مع عكس تأثيرها على paid_amount)
     */
    public function destroy(Request $request, $supplierId, $paymentId)
    {
        $supplierId = (int) $supplierId;
        $paymentId = (int) $paymentId;

        return DB::transaction(function () use ($supplierId, $paymentId) {
            $payment = SupplierPayment::where('supplier_id', $supplierId)->where('id', $paymentId)->first();
            if (!$payment) {
                return Response::json(['success' => false, 'message' => 'الدفعة غير موجودة'], 404);
            }

            $carId = $payment->car_id ? (int) $payment->car_id : null;
            $amount = (float) ($payment->amount ?? 0);

            $supplier = User::select(['id', 'name'])->find($supplierId);

            // حذف الدفعة
            $payment->delete();

            // إعادة ضبط paid_amount للسيارة من واقع دفعات المورد المتبقية
            if ($carId) {
                $totalPaidForCar = (float) (SupplierPayment::where('supplier_id', $supplierId)
                    ->where('car_id', $carId)
                    ->sum('amount') ?? 0);

                Car::where('id', $carId)->update([
                    'paid_amount' => $totalPaidForCar,
                ]);
            }

            // معاملة محاسبية معاكسة: إعادة المبلغ إلى حساب الخرج (إن وُجد)
            $accounts = TenantDataHelper::getAccountingUsers();
            $out = $accounts['out'];
            if ($out && $amount > 0) {
                $wallet = $out->getWalletOrCreate();
                if ($wallet) {
                    $desc = 'حذف دفعة للمورد';
                    if ($supplier) {
                        $desc .= ": {$supplier->name}";
                    } else {
                        $desc .= ": #{$supplierId}";
                    }
                    if ($carId) {
                        $desc .= " - car_id: {$carId}";
                    }
                    $desc .= " - مبلغ: {$amount}$";

                    Transactions::create([
                        'amount' => $amount,
                        'type' => 'in',
                        'description' => $desc,
                        'wallet_id' => $wallet->id,
                        'morphed_id' => $supplierId,
                        'morphed_type' => 'App\\Models\\User',
                        'is_pay' => 0,
                    ]);
                }
            }

            return Response::json([
                'success' => true,
                'message' => 'تم حذف الدفعة بنجاح',
            ], 200);
        });
    }
}

