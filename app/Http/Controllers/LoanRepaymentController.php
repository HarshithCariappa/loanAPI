<?php

namespace App\Http\Controllers;

use App\Models\Loans;
use Illuminate\Http\Request;
use App\Models\LoansRepaymentTracking;
class LoanRepaymentController extends Controller
{
    /**
     * save the repayment record and update the balance amount in loans table
     * @param $loanId
     * @param $repayAmount
     * @param $repaymentBalance
     * @return LoansRepaymentTracking
     */
    public function store($loanId, $repayAmount, $repaymentBalance)
    {
        $objLoansRepaymentTracking = new LoansRepaymentTracking();
        $objLoansRepaymentTracking->loan_id = $loanId;
        $objLoansRepaymentTracking->repay_amount = (float)$repayAmount;
        $objLoansRepaymentTracking->save();

        // update the loan balance amount
        $objLoanController = new LoanController();
        $objLoanController->update($loanId, $repaymentBalance);

        return $objLoansRepaymentTracking;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function processLoanRepayment(Request $request)
    {
        // validate fields
        $fields = $request->validate([
            'repayAmount' => 'required|numeric',
        ]);

        // check for active loans, and return if any loan is pending.
        $objLoan = Loans::where('uid', auth()->user()->uid)
            ->where('loan_balance', '>', 0)->first();

        if(!$objLoan)
        {
            return response([
                'status' => 'Failed',
                'message' => 'No existing loan',
            ], 403);
        }

        // process the loan repayment
        $repaymentBalance = $this->checkRepaymentConditions((float)$fields['repayAmount'], $objLoan);

        if($repaymentBalance == false)
        {
            return response([
                'status' => 'Failed',
                'message' => 'Incorrect weekly repayment amount',
                'weeklyRepayAmount' => $objLoan->weekly_repayment_amount
            ], 403);
        }

        $objLoanRepaymentTracking = self::store($objLoan->loan_id, (float)$fields['repayAmount'], (float)$repaymentBalance);

        if($objLoanRepaymentTracking)
        {
            return response([
                'status' => 'Successful',
                'message' => 'Loan repayment successful',
                'loanRepayId' => $objLoanRepaymentTracking->loan_repay_id,
                'balanceAmount' => $repaymentBalance,
            ], 401);
        }

        return response([
            'status' => 'Failed',
            'message' => 'Repayment failed please try again later',
            'weeklyRepayAmount' => $objLoan->weekly_repayment_amount
        ], 403);
    }

    /**
     * Method to validate repayment conditions.
     * @param $repayAmount
     * @param $objLoan
     * @return false
     */
    private function checkRepaymentConditions($repayAmount, $objLoan)
    {
        if($repayAmount == $objLoan->weekly_repayment_amount)
        {
            return $objLoan->loan_balance - $repayAmount;
        }
        return false;
    }
}
