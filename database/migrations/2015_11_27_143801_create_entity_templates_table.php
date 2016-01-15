<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('view')->nullable();
            $table->unsignedInteger('type_id');

            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft');
            $table->unsignedInteger('_rgt');

            $table->timestamps();

            $table->index([ '_lft', '_rgt', 'parent_id' ]);

            $table->foreign('type_id')
                  ->references('id')->on('entity_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entity_templates');
    }
}
