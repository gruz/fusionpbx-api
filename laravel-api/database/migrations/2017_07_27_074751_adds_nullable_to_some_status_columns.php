<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsNullableToSomeStatusColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('statuses', function($table)
      {
          $table->string('os')->nullable()->change();
          $table->string('services')->nullable()->change();
      });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('statuses', function($table)
      {
          $table->string('os')->change();
          $table->string('services')->change();
      });
    }
}
