<?php

namespace App\Http\Controllers;

use App\Models\Loans;
use App\Models\LoanType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoanController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * @param $fields
     * @param $interestRate
     * @param $totalRepayAmount
     * @param $weeklyRepayAmount
     * @return Loans
     */
    public function store($fields, $interestRate, $totalRepayAmount, $weeklyRepayAmount)
    {
        $objLoans = new Loans();
        $objLoans->uid = auth()->user()->uid;
        $objLoans->loan_amount = (float)$fields['loanAmount'];
        $objLoans->loan_term = (int)$fields['loanTerm'];
        $objLoans->interest_rate = (float)$interestRate;
        $objLoans->loan_type_id = (int)$fields['loanType'];
        $objLoans->monthly_income = (float)$fields['monthlyIncome'];
        $objLoans->loan_balance = (float)$totalRepayAmount;
        $objLoans->weekly_repayment_amount = (float)$weeklyRepayAmount;
        $objLoans->save();
        return $objLoans;
    }

    /**
     * Update the loan balance amount.
     * @param $loanId
     * @param $loanBalance
     */
    public function update($loanId, $loanBalance)
    {
        $objLoan = Loans::find($loanId);
        $objLoan->loan_balance = (float)$loanBalance;
        $objLoan->save();
    }

    /**
     * Method to validate the loan and save the loan.
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function processLoan(Request $request)
    {
        // validate fields
        $fields = $request->validate([
            'loanAmount' => 'required|numeric|min:100',
            'loanTerm' => 'required|numeric',
            'monthlyIncome' => 'required|numeric',
            'loanType' => 'required|numeric|exists:loan_types,loan_type_id'
        ]);

        // check for active loans, and return if any loan is pending.
        $objLoan = Loans::where('uid', auth()->user()->uid)
            ->where('loan_balance', '>', 0)->first();

        if($objLoan) {
            return response([
                'status' => 'Rejected',
                'message' => 'Already have a active loan',
            ], 403);
        }

        // check if user can repay the loan
        list($totalRepayAmount, $weeklyRepayAmount, $interestRate, $canRepay) = $this->checkLoanConditions($fields);

        if($canRepay == false)
        {
            return response([
                'status' => 'Rejected',
                'message' => 'Monthly income is insufficient for weekly repayments',
            ], 406);
        }

        $objUserLoan = self::store($fields, $interestRate, $totalRepayAmount, $weeklyRepayAmount);

        return response([
            'status' => 'Approved',
            'message' => 'Your loan request is approved',
            'loanId' => $objUserLoan->loan_id,
            'totalRepayAmount' => $totalRepayAmount,
            'weeklyRepaymentAmount' => $weeklyRepayAmount,
        ], 200);
    }

    /**
     * Method to check the repayment conditions
     * @param $fields
     * @return array
     */
    private function checkLoanConditions($fields)
    {
        // get the interest rate based on the loanType
        $objLoanType = LoanType::where('loan_type_id', $fields['loanType'])->first();

        $canRepay = true; // repayment status
        $loanAmountInterest = (float)$fields['loanAmount'] * (float)$objLoanType->interest_rate; // interest for the loan amount.
        $totalRepayAmount = $loanAmountInterest + (float)$fields['loanAmount']; // total repay amount with interest.
        $weeklyRepayAmount = $totalRepayAmount / (int)$fields['loanTerm']; // weekly repayment amount.
        $weeklyIncome = (float)$fields['monthlyIncome'] / 4; // weekly income of the user.

        if($weeklyRepayAmount > $weeklyIncome)
        {
            $canRepay = false;
        }

        return array($totalRepayAmount, $weeklyRepayAmount, $objLoanType->interest_rate, $canRepay);
    }
}
