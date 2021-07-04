<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id('loan_id');
            $table->foreignId('uid')->constrained('users', 'uid');
            $table->decimal('loan_amount', 9, 3);
            $table->integer('loan_term');
            $table->decimal('interest_rate', 9, 3);
            $table->foreignId('loan_type_id')->constrained('loan_types', 'loan_type_id');
            $table->decimal('monthly_income', 9, 3);
            $table->decimal('loan_balance', 9, 3);
            $table->decimal('weekly_repayment_amount', 9, 3);
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
        Schema::dropIfExists('loans');
    }
}
