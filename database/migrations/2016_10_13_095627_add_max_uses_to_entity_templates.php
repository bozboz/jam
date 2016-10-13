<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxUsesToEntityTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_templates', function (Blueprint $table) {
            $table->unsignedInteger('max_uses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entity_templates', function (Blueprint $table) {
            $table->dropColumn('max_uses');
        });
    }
}
