<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MenuBadge
{
    public static function count(string $tablePrefix, string $dateColumn): int
    {
        $year = date('Y');
        $month = date('m');

        $table = "{$tablePrefix}_{$year}";

        if (!Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)
            ->whereMonth($dateColumn, $month)
            ->where('status','processing')
            ->count();
    }
}