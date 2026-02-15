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
        // Add indexes for family_events table to improve query performance
        if (Schema::hasTable('family_events')) {
            Schema::table('family_events', function (Blueprint $table) {
                if (Schema::hasColumn('family_events', 'family_id')) {
                    $table->index(['family_id', 'title']);
                }
            });
        }
        
        // Add index for people.gid for faster lookups during import
        if (Schema::hasTable('people')) {
            Schema::table('people', function (Blueprint $table) {
                if (Schema::hasColumn('people', 'gid')) {
                    $table->index('gid');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('family_events')) {
            Schema::table('family_events', function (Blueprint $table) {
                if (Schema::hasColumn('family_events', 'family_id')) {
                    $table->dropIndex(['family_id', 'title']);
                }
            });
        }
        
        if (Schema::hasTable('people')) {
            Schema::table('people', function (Blueprint $table) {
                if (Schema::hasColumn('people', 'gid')) {
                    $table->dropIndex(['gid']);
                }
            });
        }
    }
};
