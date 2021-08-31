<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Make it nullable in cases this table will be used natively by Laravel
            // $table->uuid('user_uuid')->index()->nullable();
            $table->string('domain_name')->index()->nullable();
            $table->string('username')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // $table->dropColumn('user_uuid');
            $table->dropColumn('domain_name');
            $table->dropColumn('username');
        });
    }
}
