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
            $table->string('alias', 64);
            $table->string('name');
            $table->string('view');
            $table->unsignedInteger('type_id');
            $table->timestamps();

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
