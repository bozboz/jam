<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeAliasToEntityTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_templates', function (Blueprint $table) {
            $table->string('type_alias')->after('view')->index();
        });

        DB::table('entity_templates')->update([
            'type_alias' => DB::raw("(
                SELECT alias
                FROM entity_types
                WHERE entity_types.id = entity_templates.type_id
            )")
        ]);

        Schema::table('entity_templates', function (Blueprint $table) {
            $table->dropForeign('entity_templates_type_id_foreign');
        });

        Schema::table('entity_templates', function (Blueprint $table) {
            $table->dropColumn('type_id');
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
            $table->dropColumn('type_alias');
            $table->unsignedInteger('type_id');
        });
    }
}
