<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddListingFieldsAndListingTemplateToEntityTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_templates', function (Blueprint $table) {
            $table->string('listing_fields')->nullable();
            $table->string('listing_view')->nullable();
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
            $table->dropColumn('listing_fields');
            $table->dropColumn('listing_view');
        });
    }
}
