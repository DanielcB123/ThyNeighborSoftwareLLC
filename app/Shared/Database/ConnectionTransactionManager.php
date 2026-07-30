<?php

namespace App\Shared\Database;

use Closure;
use Illuminate\Support\Facades\DB;

final class ConnectionTransactionManager
{
    public static function central(Closure $callback): mixed
    {
        return DB::connection('central')->transaction($callback);
    }

    public static function tenant(Closure $callback): mixed
    {
        return DB::connection('tenant')->transaction($callback);
    }
}
