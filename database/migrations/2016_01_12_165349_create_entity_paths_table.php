<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_paths', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('canonical_id')->nullable();
            $table->string('path');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['entity_id', 'path']);

            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('cascade');
            $table->foreign('canonical_id')->references('id')->on('entities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entity_paths');
    }
}
