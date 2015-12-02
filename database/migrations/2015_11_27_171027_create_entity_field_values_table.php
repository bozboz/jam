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
            $table->integer('field_id')->unsigned();
            $table->integer('revision_id')->unsigned();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('field_id')
                  ->references('id')->on('entity_fields')
                  ->onDelete('cascade');

            $table->foreign('revision_id')
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
