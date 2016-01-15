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
            $table->unsignedInteger('template_id')->index();
            $table->string('name');
            $table->string('type_alias');
            $table->text('validation')->nullable();
            $table->unsignedInteger('sorting');
            $table->timestamps();

            $table->foreign('template_id')
                  ->references('id')->on('entity_templates')
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
