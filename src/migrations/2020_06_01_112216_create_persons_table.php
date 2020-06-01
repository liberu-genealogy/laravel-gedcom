<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons',function(Blueprint $table){
            $table->increments("id");
            $table->string("givn");
            $table->text("surn")->nullable();
            $table->char("sex",1)->nullable();
            $table->text("description")->nullable();
            $table->integer("child_in_family_id")->references("id")->on("families")->nullable();
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
        Schema::dropIfExists('persons');
    }
}
