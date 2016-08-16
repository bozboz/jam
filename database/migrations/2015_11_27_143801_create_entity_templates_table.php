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
            $table->string('type_alias')->index();
            $table->string('alias');
            $table->string('view')->nullable();
            $table->string('listing_view')->nullable();

            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft');
            $table->unsignedInteger('_rgt');

            $table->timestamps();

            $table->index([ '_lft', '_rgt', 'parent_id' ]);
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
