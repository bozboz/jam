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
            $table->string('alias');
            $table->string('name');

            $table->integer('parent_id')->nullable()->index();
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();

            $table->integer('entity_type_id');
            $table->integer('entity_template_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entity_type_id')
                  ->references('id')->on('entity_types')
                  ->onDelete('cascade');
            $table->foreign('entity_template_id')
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
