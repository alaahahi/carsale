<?php

namespace App\Http\Controllers;

use App\Helpers\TenantDataHelper;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class SupplierPurchasesController extends Controller
{
    protected $userSeles;
    protected $url;

    public function __construct()
    {
        $this->url = env('FRONTEND_URL');

        $this->middleware(function ($request, $next) {
            $userTypeIds = TenantDataHelper::getUserTypeIds();
            $this->userSeles = $userTypeIds['seles'];
            return $next($request);
        });
    }

    public function index()
    {
        return Inertia::render('Suppliers/Purchases', [
            'url' => $this->url,
        ]);
    }

    /**
     * ملخص الموردين: مجموع الشراء/المدفوع/الدَين
     */
    public function summary(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $q = DB::table('car')
            ->leftJoin('users', 'users.id', '=', 'car.user_purchase_id')
            ->whereNotNull('car.user_purchase_id');

        if ($from && $to) {
            $q->whereBetween('car.purchase_data', [$from, $to]);
        }

        $rows = $q->selectRaw("
                car.user_purchase_id as supplier_id,
                users.name as supplier_name,
                COUNT(*) as cars_count,
                SUM(COALESCE(car.purchase_price, 0)) as total_purchase,
                SUM(COALESCE(car.paid_amount, 0)) as total_paid,
                SUM(COALESCE(car.purchase_price, 0) - COALESCE(car.paid_amount, 0)) as debt
            ")
            ->groupBy('car.user_purchase_id', 'users.name')
            ->orderByDesc('debt')
            ->get();

        // إضافة دفعات "على الحساب" (car_id = NULL) إلى إجمالي المدفوعات وحساب الدين
        $supplierIds = $rows->pluck('supplier_id')->filter()->unique()->values();
        $advanceMap = collect();
        if ($supplierIds->isNotEmpty()) {
            $advanceQ = DB::table('supplier_payments')
                ->selectRaw('supplier_id, SUM(amount) as advance_paid')
                ->whereIn('supplier_id', $supplierIds)
                ->whereNull('car_id');
            if ($from && $to) {
                $advanceQ->whereBetween('paid_at', [$from, $to]);
            }
            $advanceMap = $advanceQ->groupBy('supplier_id')->pluck('advance_paid', 'supplier_id');
        }

        $rows = $rows->map(function ($r) use ($advanceMap) {
            $adv = (float) ($advanceMap[$r->supplier_id] ?? 0);
            $r->advance_paid = $adv;
            $r->total_paid = (float) ($r->total_paid ?? 0) + $adv;
            $r->debt = (float) ($r->total_purchase ?? 0) - (float) ($r->total_paid ?? 0);
            return $r;
        })->sortByDesc('debt')->values();

        $totals = [
            'cars_count' => (int) $rows->sum('cars_count'),
            'total_purchase' => (float) $rows->sum('total_purchase'),
            'total_paid' => (float) $rows->sum('total_paid'),
            'debt' => (float) $rows->sum('debt'),
        ];

        return Response::json([
            'success' => true,
            'data' => $rows,
            'totals' => $totals,
        ], 200);
    }

    /**
     * تفاصيل مورد محدد: سياراته + تجميعة حسب (موديل/لون)
     */
    public function details(Request $request, $supplierId)
    {
        $supplierId = (int) $supplierId;
        $supplier = User::select('id', 'name', 'phone')->findOrFail($supplierId);

        $from = $request->get('from');
        $to = $request->get('to');

        // إصلاح تلقائي للحالات القديمة: paid_amount = NULL مع وجود دفعات
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

        $carsQuery = Car::with(['purchaseSupplier:id,name'])
            ->where('user_purchase_id', $supplierId)
            ->orderByDesc('purchase_data')
            ->orderByDesc(DB::raw('CAST(no AS UNSIGNED)'));

        if ($from && $to) {
            $carsQuery->whereBetween('purchase_data', [$from, $to]);
        }

        $cars = $carsQuery->paginate(100);

        $totalsQ = Car::where('user_purchase_id', $supplierId);
        if ($from && $to) {
            $totalsQ->whereBetween('purchase_data', [$from, $to]);
        }
        $totals = $totalsQ->selectRaw('
                COUNT(*) as cars_count,
                SUM(COALESCE(purchase_price, 0)) as total_purchase,
                SUM(COALESCE(paid_amount, 0)) as total_paid,
                SUM(COALESCE(purchase_price, 0) - COALESCE(paid_amount, 0)) as debt
            ')
            ->first();

        // إضافة دفعات "على الحساب" (car_id = NULL) لتفاصيل المورد
        $advanceQ = DB::table('supplier_payments')
            ->where('supplier_id', $supplierId)
            ->whereNull('car_id');
        if ($from && $to) {
            $advanceQ->whereBetween('paid_at', [$from, $to]);
        }
        $advancePaid = (float) ($advanceQ->sum('amount') ?? 0);
        if ($totals) {
            $totals->advance_paid = $advancePaid;
            $totals->total_paid = (float) ($totals->total_paid ?? 0) + $advancePaid;
            $totals->debt = (float) ($totals->total_purchase ?? 0) - (float) ($totals->total_paid ?? 0);
        }

        $aggQ = DB::table('car')
            ->where('user_purchase_id', $supplierId);
        if ($from && $to) {
            $aggQ->whereBetween('purchase_data', [$from, $to]);
        }

        $aggregate = $aggQ->selectRaw('
                model,
                color,
                COUNT(*) as cars_count,
                SUM(COALESCE(purchase_price, 0)) as total_purchase,
                SUM(COALESCE(paid_amount, 0)) as total_paid,
                SUM(COALESCE(purchase_price, 0) - COALESCE(paid_amount, 0)) as debt
            ')
            ->groupBy('model', 'color')
            ->orderByDesc('cars_count')
            ->get();

        // أرباح المورد (ربح محقق من السيارات المباعة فقط)
        // profit = pay_price - (purchase_price + expenses)
        $costExpr = '(COALESCE(purchase_price,0) + COALESCE(erbil_exp,0) + COALESCE(erbil_shipping,0) + COALESCE(dubai_exp,0) + COALESCE(dubai_shipping,0))';
        $profitExpr = '(COALESCE(pay_price,0) - ' . $costExpr . ')';

        $soldBaseQ = DB::table('car')
            ->where('user_purchase_id', $supplierId)
            ->where('results', 2)
            ->whereRaw('COALESCE(pay_price,0) > 0');
        if ($from && $to) {
            // نفس فلترة الصفحة (حسب تاريخ الشراء)
            $soldBaseQ->whereBetween('purchase_data', [$from, $to]);
        }

        $profitsTotals = (clone $soldBaseQ)->selectRaw("
                COUNT(*) as sold_count,
                SUM(COALESCE(pay_price,0)) as total_sales,
                SUM($costExpr) as total_cost,
                SUM($profitExpr) as total_profit,
                AVG($profitExpr) as avg_profit,
                SUM(CASE WHEN $profitExpr > 0 THEN 1 ELSE 0 END) as profitable_count,
                SUM(CASE WHEN $profitExpr < 0 THEN 1 ELSE 0 END) as loss_count,
                MAX($profitExpr) as max_profit,
                MIN($profitExpr) as min_profit
            ")->first();

        $topProfitCars = (clone $soldBaseQ)
            ->selectRaw("
                id, no, pin, name, model, color,
                COALESCE(pay_price,0) as sale_price,
                $costExpr as total_cost,
                $profitExpr as profit
            ")
            ->orderByDesc(DB::raw($profitExpr))
            ->limit(10)
            ->get();

        $topLossCars = (clone $soldBaseQ)
            ->selectRaw("
                id, no, pin, name, model, color,
                COALESCE(pay_price,0) as sale_price,
                $costExpr as total_cost,
                $profitExpr as profit
            ")
            ->orderBy(DB::raw($profitExpr))
            ->limit(10)
            ->get();

        return Response::json([
            'success' => true,
            'supplier' => $supplier,
            'cars' => $cars,
            'totals' => $totals,
            'aggregate' => $aggregate,
            'profits' => [
                'totals' => $profitsTotals,
                'top_profit_cars' => $topProfitCars,
                'top_loss_cars' => $topLossCars,
            ],
        ], 200);
    }

    /**
     * مجمعة عامة: عدد/موديل/لون/اسم المورد
     */
    public function carsAggregate(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $supplierId = $request->get('supplier_id');

        $q = DB::table('car')
            ->leftJoin('users', 'users.id', '=', 'car.user_purchase_id')
            ->whereNotNull('car.user_purchase_id');

        if ($supplierId) {
            $q->where('car.user_purchase_id', (int) $supplierId);
        }

        if ($from && $to) {
            $q->whereBetween('car.purchase_data', [$from, $to]);
        }

        $rows = $q->selectRaw('
                car.user_purchase_id as supplier_id,
                users.name as supplier_name,
                car.model,
                car.color,
                COUNT(*) as cars_count,
                SUM(COALESCE(car.purchase_price, 0)) as total_purchase,
                SUM(COALESCE(car.paid_amount, 0)) as total_paid,
                SUM(COALESCE(car.purchase_price, 0) - COALESCE(car.paid_amount, 0)) as debt
            ')
            ->groupBy('car.user_purchase_id', 'users.name', 'car.model', 'car.color')
            ->orderByDesc('cars_count')
            ->get();

        return Response::json([
            'success' => true,
            'data' => $rows,
        ], 200);
    }
}

