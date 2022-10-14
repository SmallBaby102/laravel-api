<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_users', function (Blueprint $table) {
            $table->id();
            $table->string("userid");
            $table->string("username");
            $table->string("refid");
            $table->string("parentuserid")->nullable();
            $table->string("firstname")->nullable();
            $table->string("lastname")->nullable();
            $table->string("dateinserted");
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
        Schema::dropIfExists('affiliate_user');
    }
}
