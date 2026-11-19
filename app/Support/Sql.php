<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Ekspresi SQL lintas driver (MySQL produksi, SQLite test). */
class Sql
{
    public static function hour(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }

    public static function date(string $column): string
    {
        return "DATE({$column})";
    }
}
