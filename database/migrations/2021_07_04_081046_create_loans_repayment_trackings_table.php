<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansRepaymentTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans_repayment_trackings', function (Blueprint $table) {
            $table->id('loan_repay_id');
            $table->foreignId('loan_id')->constrained('loans', 'loan_id');
            $table->decimal('repay_amount', 9, 3);
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
        Schema::dropIfExists('loans_repayment_trackings');
    }
}
