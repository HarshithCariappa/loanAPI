<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // insert the seeding data into the load type table.
        LoanType::create([
            'loan_type_id' => 1,
            'loan_type' => 'Home Loan',
            'interest_rate' => 0.066,
        ]);

        LoanType::create([
            'loan_type_id' => 2,
            'loan_type' => 'Car Loan',
            'interest_rate' => 0.08,
        ]);

        LoanType::create([
            'loan_type_id' => 3,
            'loan_type' => 'Agriculture Loan',
            'interest_rate' => 0.045,
        ]);
    }
}
