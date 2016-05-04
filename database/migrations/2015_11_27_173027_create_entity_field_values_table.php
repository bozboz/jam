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
        Schema::create('entity_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('revision_id');
            $table->string('key');
            $table->unsignedInteger('field_id');
            $table->string('type_alias');
            $table->text('value')->nullable();
            $table->unsignedInteger('foreign_key')->nullable()->index();
            $table->timestamps();

            $table->foreign('revision_id')
                  ->references('id')->on('entity_revisions')
                  ->onDelete('cascade');

            $table->foreign('field_id')
                  ->references('id')->on('entity_template_fields')
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
        Schema::drop('entity_values');
    }
}
