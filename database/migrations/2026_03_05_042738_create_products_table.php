<?php
// database/migrations/2024_01_01_000001_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category')->default('T-Shirt');
            $table->enum('status', [
                'Draft',
                'Downloaded', 
                'In Progress',
                'Complete',
                'Sample Ordered',
                'Ready for Catalog',
                'Active',
                'Discontinued'
            ])->default('Draft');
            $table->json('images'); // Store array of image paths
            $table->string('genre');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};