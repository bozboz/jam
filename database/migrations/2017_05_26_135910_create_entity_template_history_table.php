<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityTemplateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_template_history', function (Blueprint $table) {
            $table->increments('id');

            $table->string('action');
            $table->string('revisionable_type');
            $table->unsignedInteger('revisionable_id');
            $table->text('old');
            $table->text('new');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('template_id');

            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('entity_templates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entity_template_history');
    }
}
