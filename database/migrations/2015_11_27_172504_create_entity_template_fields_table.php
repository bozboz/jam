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
            $table->integer('template_id')->unsigned();
            $table->integer('field_id')->unsigned();
            $table->string('name');
            $table->text('validation')->nullable();
            $table->timestamps();

            $table->foreign('template_id')
                  ->references('id')->on('entity_templates')
                  ->onDelete('cascade');

            $table->foreign('field_id')
                  ->references('id')->on('entity_fields')
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
