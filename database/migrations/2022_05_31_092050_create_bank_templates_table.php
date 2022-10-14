<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_templates', function (Blueprint $table) {
            $table->id();
            $table->string("email");
            $table->string("template_name");
            $table->string("beneficiary_name")->nullable();;
            $table->string("bank_name")->nullable();;
            $table->string("bank_account_number")->nullable();;
            $table->string("bank_country")->nullable();;
            $table->string("swift_bic_code")->nullable();;
            $table->string("reference_code")->nullable();
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
        Schema::dropIfExists('bank_templates');
    }
}
