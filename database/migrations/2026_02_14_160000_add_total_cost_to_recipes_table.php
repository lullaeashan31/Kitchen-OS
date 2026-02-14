<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            if (!Schema::hasColumn('recipes', 'total_cost')) {
                $table->decimal('total_cost', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('recipes', 'cost_per_portion')) {
                $table->decimal('cost_per_portion', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            if (Schema::hasColumn('recipes', 'total_cost')) {
                $table->dropColumn('total_cost');
            }
            if (Schema::hasColumn('recipes', 'cost_per_portion')) {
                $table->dropColumn('cost_per_portion');
            }
        });
    }
};
