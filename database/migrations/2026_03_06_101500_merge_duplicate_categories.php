<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Identify duplicates
        $duplicates = DB::table('categories')
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('categories')
                ->where('name', $duplicate->name)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            $primaryId = array_shift($ids); // Keep the first (lowest ID) as primary

            // 2. Reassign ingredients to primary ID
            DB::table('ingredients')
                ->whereIn('category_id', $ids)
                ->update(['category_id' => $primaryId]);

            // 3. Reassign recipes to primary ID
            DB::table('recipes')
                ->whereIn('category_id', $ids)
                ->update(['category_id' => $primaryId]);

            // 4. Delete duplicates
            DB::table('categories')
                ->whereIn('id', $ids)
                ->delete();
        }

        // 5. Add unique constraint to name column
        Schema::table('categories', function (Blueprint $table) {
            // First drop existing index if any (unlikely but safe)
            // Then add unique
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
