<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->longText('detail')->nullable();
            $table->integer('required_points');
            $table->datetime('expire_at')->nullable();
            $table->longText('usage_condition')->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['app_id', 'active']);
            $table->index(['category_id']);
            $table->index(['expire_at']);
            $table->index(['required_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
