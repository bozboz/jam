<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeAliasToEntityValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_values', function (Blueprint $table) {
            $table->integer('field_id')->after('id');
            $table->string('type_alias')->after('field_id');
        });

        \DB::statement('
            UPDATE entity_values AS ev
            SET
                type_alias = (
                    SELECT etf.type_alias
                    FROM entity_revisions AS er
                    JOIN entities AS e
                    ON e.id = er.entity_id
                    JOIN entity_templates AS et
                    ON et.id = e.template_id
                    JOIN entity_template_fields AS etf
                    ON etf.template_id = et.id
                    WHERE er.id = ev.revision_id
                    AND etf.name = ev.key
                ),
                field_id = (
                    SELECT etf.id
                    FROM entity_revisions AS er
                    JOIN entities AS e
                    ON e.id = er.entity_id
                    JOIN entity_templates AS et
                    ON et.id = e.template_id
                    JOIN entity_template_fields AS etf
                    ON etf.template_id = et.id
                    WHERE er.id = ev.revision_id
                    AND etf.name = ev.key
                )
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entity_values', function (Blueprint $table) {
            $table->dropColumn('type_alias');
            $table->dropColumn('field_id');
        });
    }
}
