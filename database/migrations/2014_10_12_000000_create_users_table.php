<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('department')->nullable()->default("Individual");
            // individual relative fields
            $table->string('firstname')->nullable();;
            $table->string('lastname')->nullable();;
            $table->string('email')->unique();
            $table->string('title')->nullable();
            $table->string('gener')->nullable();
            $table->string('password')->nullable();
            $table->string('marriage')->nullable();
            $table->string('occupation')->nullable();
            $table->string('birthday')->nullable();
            // $table->string('id_cardtype')->nullable();
            $table->string('id_number')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('issue_country')->nullable();
            $table->string('exp_date')->nullable();
            // $table->string('id_issuer')->nullable();
            $table->string('address')->nullable();
            // $table->string('city')->nullable();
            $table->string('country')->nullable();
            // $table->string('prefecture')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('cellphone_number')->nullable();
            // corporate relative fields
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('director_name')->nullable();
            // $table->string('company_city')->nullable();
            $table->string('company_country')->nullable();
            // $table->string('company_prefecture')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_country_code')->nullable();
            $table->string('company_cellphone_number')->nullable();
            // 
            $table->string('verification_status')->nullable()->default("0");  //0:Not approved, 1: Pending, 2: Completed
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
        Schema::dropIfExists('users');
    }
}
