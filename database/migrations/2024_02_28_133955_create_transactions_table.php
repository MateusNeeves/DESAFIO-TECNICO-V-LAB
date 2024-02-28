<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->float('value', 8, 2, 0);
            $table->string('type');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_category');


            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_category')->references('id')->on('categories');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
