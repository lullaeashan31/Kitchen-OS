<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We need to modify the ENUM column to include 'rejected'
        // Since Doctrine DBAL doesn't support changing ENUMs easily in some versions, 
        // using raw SQL is often safer for ENUM modifications in MySQL.

        DB::statement("ALTER TABLE recipes MODIFY COLUMN status ENUM('draft', 'permanent', 'rejected') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM (Warning: this might fail if there are 'rejected' records)
        // ideally we would move rejected to draft before reverting, but for now we technically just revert schema
        DB::statement("UPDATE recipes SET status = 'draft' WHERE status = 'rejected'");
        DB::statement("ALTER TABLE recipes MODIFY COLUMN status ENUM('draft', 'permanent') NOT NULL DEFAULT 'draft'");
    }
};
