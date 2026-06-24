<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('app:create-yearly-tables')]
#[Description('Create yearly tables automatically based on current year tables')]
class CreateYearlyTables extends Command
{
    public function handle()
    {
        $year = now()->year;
        $nextYear = $year + 1;

        // ambil semua tabel di database
        $tables = DB::select('SHOW TABLES');

        $dbName = DB::getDatabaseName();
        $key = "Tables_in_{$dbName}";

        foreach ($tables as $row) {

            $tableName = $row->$key;

            // hanya proses tabel yang ada suffix tahun sekarang
            if (! str_ends_with($tableName, "_{$year}")) {
                continue;
            }

            $newTable = str_replace("_{$year}", "_{$nextYear}", $tableName);

            // jika tabel tahun sekarang tidak ada, skip
            if (! Schema::hasTable($tableName)) {
                $this->warn("Skip {$tableName} (not exists)");

                continue;
            }

            // jika tabel baru sudah ada, skip
            if (Schema::hasTable($newTable)) {
                $this->info("Skip {$newTable} (already exists)");

                continue;
            }

            DB::statement("
                CREATE TABLE {$newTable}
                LIKE {$tableName}
            ");

            $this->info("Created: {$newTable}");
        }

        return self::SUCCESS;
    }
}
