<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // CREATE INDEX CONCURRENTLY cannot run inside a transaction.
    public $withinTransaction = false;

    private const INDEXES = [
        'listings_title_trgm' => 'title',
        'listings_description_trgm' => 'description',
        'listings_city_trgm' => 'city',
        'listings_country_trgm' => 'country',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        foreach (self::INDEXES as $name => $column) {
            DB::statement("CREATE INDEX CONCURRENTLY IF NOT EXISTS {$name} ON listings USING gin ({$column} gin_trgm_ops)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_keys(self::INDEXES) as $name) {
            DB::statement("DROP INDEX CONCURRENTLY IF EXISTS {$name}");
        }
    }
};
