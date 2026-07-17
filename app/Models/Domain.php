<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

/**
 * جدول domains مركزي فقط — حتى لو وُجد بالخطأ داخل قاعدة تاجر
 */
class Domain extends BaseDomain
{
    public function getConnectionName()
    {
        return config('tenancy.database.central_connection', 'mysql');
    }
}
