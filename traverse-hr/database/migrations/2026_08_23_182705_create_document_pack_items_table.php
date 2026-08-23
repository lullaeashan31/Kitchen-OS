<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_pack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_pack_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('required')->default(true);
            $table->timestamps();

            $table->unique(['document_pack_id', 'document_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_pack_items');
    }
};
