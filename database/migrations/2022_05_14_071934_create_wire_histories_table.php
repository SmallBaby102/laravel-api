<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWireHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wire_histories', function (Blueprint $table) {
            $table->id();
            $table->string("wireid");
            $table->string("date");
            $table->string("email");
            $table->string("status");
            $table->string("pending_date");
            $table->string("approved_date");
            $table->string("processing_date");
            $table->string("completed_date");
            $table->string("account_type");
            $table->string("beneficiary_name");
            $table->string("beneficiary_country");
            $table->string("beneficiary_street");
            $table->string("beneficiary_city");
            $table->string("beneficiary_postal_code");
            $table->string("bank_name");
            $table->string("bankaccount_number");
            $table->string("bank_country");
            $table->string("bankstreet_address");
            $table->string("bank_city");
            $table->string("bank_region");
            $table->string("bankpostal_code");
            $table->string("swift_code");
            $table->string("reference_code");
            $table->string("intermediarybank_address");
            $table->string("intermediarybank_city");
            $table->string("intermediarybank_name");
            $table->string("intermediarybank_number");
            $table->string("intermediarybank_country");
            $table->string("intermediarybank_region");
            $table->string("intermediarybank_swiftcode");
            $table->string("amount");
            $table->string("memo");

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
        Schema::dropIfExists('wire_histories');
    }
}
