<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('family_events', function (Blueprint $table) {
            // Add missing columns to match person_events structure
            if (!Schema::hasColumn('family_events', 'type')) {
                $table->string('type')->nullable()->after('title');
            }
            if (!Schema::hasColumn('family_events', 'plac')) {
                $table->string('plac')->nullable()->after('converted_date');
            }
            if (!Schema::hasColumn('family_events', 'addr_id')) {
                $table->integer('addr_id')->nullable()->after('plac');
            }
            if (!Schema::hasColumn('family_events', 'phon')) {
                $table->string('phon')->nullable()->after('addr_id');
            }
            if (!Schema::hasColumn('family_events', 'caus')) {
                $table->text('caus')->nullable()->after('phon');
            }
            if (!Schema::hasColumn('family_events', 'age')) {
                $table->string('age')->nullable()->after('caus');
            }
            if (!Schema::hasColumn('family_events', 'agnc')) {
                $table->string('agnc')->nullable()->after('age');
            }
            if (!Schema::hasColumn('family_events', 'husb')) {
                $table->unsignedBigInteger('husb')->nullable()->after('agnc');
            }
            if (!Schema::hasColumn('family_events', 'wife')) {
                $table->unsignedBigInteger('wife')->nullable()->after('husb');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('family_events', function (Blueprint $table) {
            $columnsToRemove = ['type', 'plac', 'addr_id', 'phon', 'caus', 'age', 'agnc', 'husb', 'wife'];
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('family_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
