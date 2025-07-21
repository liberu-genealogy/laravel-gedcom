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
        Schema::create('person_events', function (Blueprint $table) {
            $table->id();
            $table->string('converted_date')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('title')->nullable();
            $table->string('date')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('places_id')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->string('type')->nullable();
            $table->string('attr')->nullable();
            $table->string('plac')->nullable();
            $table->integer('addr_id')->nullable();
            $table->string('phon')->nullable();
            $table->text('caus')->nullable();
            $table->string('age')->nullable();
            $table->string('agnc')->nullable();
            $table->string('adop')->nullable();
            $table->string('adop_famc')->nullable();
            $table->string('birt_famc')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_events');
    }
};
