<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name');

            $table->integer('parent_id')->nullable();
            $table->integer('_lft')->nullable();
            $table->integer('_rgt')->nullable();

            $table->integer('template_id')->unsigned()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index([ '_lft', '_rgt', 'parent_id' ]);

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
        Schema::drop('entities');
    }
}
