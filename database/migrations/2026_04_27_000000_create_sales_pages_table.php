<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_pages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('product_name');
            $table->text('description');
            $table->json('features')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('price')->nullable();
            $table->text('unique_selling_points')->nullable();

            $table->json('generation')->nullable();
            $table->string('llm_provider')->nullable();
            $table->string('llm_model')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_pages');
    }
};

