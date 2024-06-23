<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dmhuyen', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string("matinh", 32);
            $table->string("mahuyen", 255)->nullable();
            $table->string("tenhuyen", 255);
            $table->string("tenhuyen_en", 255)->nullable();
            $table->string("nam_quan_ly", 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dmhuyen');
    }
};
