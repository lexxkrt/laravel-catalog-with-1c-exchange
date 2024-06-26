<?php

use App\Models\Brand;
use App\Models\Category;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->string('model')->nullable();
            $table->string('image')->nullable();
            $table->foreignIdFor(Brand::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->integer('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->integer('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
