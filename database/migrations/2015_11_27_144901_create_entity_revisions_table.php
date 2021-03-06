<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntityRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_revisions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('user_id')->index()->nullable();
            $table->datetime('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entity_id')
                  ->references('id')->on('entities')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });

        Schema::table('entities', function (Blueprint $table) {
            $table->foreign('revision_id')
                  ->references('id')->on('entity_revisions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropForeign('entities_revision_id_foreign');
        });

        Schema::drop('entity_revisions');
    }
}
