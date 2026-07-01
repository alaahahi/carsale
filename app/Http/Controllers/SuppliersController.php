<?php

namespace App\Http\Controllers;

use App\Helpers\TenantDataHelper;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SuppliersController extends Controller
{
    private function ensureSupplierTypeId(): int
    {
        $supplierTypeId = TenantDataHelper::getUserTypeId('supplier');

        if ($supplierTypeId) {
            return (int) $supplierTypeId;
        }

        return (int) DB::transaction(function () {
            // إنشاء نوع "supplier" إذا لم يكن موجوداً (متوافق مع قواعد بيانات قديمة)
            $existing = DB::table('user_type')->where('name', 'supplier')->lockForUpdate()->first();
            if ($existing && isset($existing->id)) {
                return (int) $existing->id;
            }

            $idColumn = null;
            try {
                $idColumn = DB::selectOne("SHOW COLUMNS FROM `user_type` WHERE Field = 'id'");
            } catch (\Throwable $e) {
                $idColumn = null;
            }

            $extra = strtolower((string) ($idColumn->Extra ?? ''));
            $isAutoIncrement = str_contains($extra, 'auto_increment');

            if ($isAutoIncrement) {
                return (int) DB::table('user_type')->insertGetId([
                    'name' => 'supplier',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $nextId = ((int) DB::table('user_type')->max('id')) + 1;
            if ($nextId <= 0) {
                $nextId = 1;
            }

            DB::table('user_type')->insert([
                'id' => $nextId,
                'name' => 'supplier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $nextId;
        });
    }

    /**
     * إنشاء تاجر/مورد جديد لاستخدامه كمصدر شراء.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $supplierTypeId = $this->ensureSupplierTypeId();

        $password = Str::random(16);

        $data = [
            'name' => $request->name,
            'email' => null,
            'password' => bcrypt($password),
            'type_id' => $supplierTypeId,
            'phone' => $request->phone,
        ];
        if (Schema::hasColumn('users', 'show_wallet')) {
            $data['show_wallet'] = false;
        }

        $user = User::create($data);

        // إنشاء محفظة للتاجر (اختياري لكن مفيد للتوافق)
        try {
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );
        } catch (\Throwable $e) {
            // ignore
        }

        return Response::json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ], 201);
    }
}

