<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_template_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_template_id')->unsigned();
            $table->integer('entity_field_id')->unsigned();
            $table->string('name');
            $table->text('validation');
            $table->timestamps();

            $table->foreign('entity_template_id')
                  ->references('id')->on('entity_templates')
                  ->onDelete('cascade');

            $table->foreign('entity_field_id')
                  ->references('id')->on('fields')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entity_template_fields');
    }
}
