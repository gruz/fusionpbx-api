<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPushtokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pushtokens', function (Blueprint $table) {
            $table->uuid('pushtoken_uuid')->index()->nullable();
            $table->uuid('user_uuid');
            $table->string('token_type');
            $table->string('token');
            $table->string('token_class');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pushtokens');
    }
}
