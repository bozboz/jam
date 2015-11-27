<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_field_id')->unsigned();
            $table->integer('entity_revision_id')->unsigned();
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->foreign('entity_field_id')
                  ->references('id')->on('fields')
                  ->onDelete('cascade');

            $table->foreign('entity_revision_id')
                  ->references('id')->on('entity_revisions')
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
        Schema::drop('entity_field_values');
    }
}
