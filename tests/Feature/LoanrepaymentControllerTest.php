<?php

namespace Tests\Feature;

use Database\Seeders\LoanTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanrepaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        // Run loan type seeder.
        $this->seed(LoanTypeSeeder::class);

        // register a user before login
        $formData = $this->validRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(200);

        // valid data test
        $formData = $this->validLoginData();
        $response = $this->postJson('/api/login',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(201);

        $this->token = $response->json('token');
    }

    /**
     * case to test loan repay api
     *
     * Cases :
     * test No active loans
     * test missing required field
     * test invalid data type
     * test valid data
     * test non unique data (existing loan test)
     * unauthenticated
     * @return void
     */
    public function test_loan_repay()
    {
        list($validRepayData, $validDataOutput) = $this->validLoanRepayData();
        $expectedOutput = $this->unauthenticatedOutput();
        $this->json('POST','/api/loanRepay', $validRepayData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(401)
            ->assertJson($expectedOutput);

        // No active loans test
        list($formData, $expectedOutput) = $this->noActiveLoanRepayData();
        $this->json('POST','/api/loanRepay', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(403)
            ->assertJson($expectedOutput);

        // valid data test
        list($formData, $expectedOutput) = $this->validLoanApplyData();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(200)
            ->assertJson($expectedOutput);

        // invalid data test , missing required field
        list($formData, $expectedOutput) = $this->missingFieldLoanRepayData();
        $this->json('POST','/api/loanRepay', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // invalid data test , invalid data type
        list($formData, $expectedOutput) = $this->invalidDataTypeLoanRepayData();
        $this->json('POST','/api/loanRepay', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // insufficient income test
        list($formData, $expectedOutput) = $this->incorrectWeeklyPaymentLoanRepayData();
        $this->json('POST','/api/loanRepay', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(406)
            ->assertJson($expectedOutput);

        // valid data test
        $this->json('POST','/api/loanRepay', $validRepayData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(200)
            ->assertJson($validDataOutput);
    }

    /**
     * Method to return valid loan repay data with expected output.
     * @return array
     */
    public function validLoanRepayData()
    {
        $formData = [
            'repayAmount' => '106.6',
        ];

        $expectedOutput = [
            'status' => 'Successful',
            'message' => 'Loan repayment successful',
            'loanRepayId' => 1,
            'balanceAmount' => 959.4,
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return valid loan repay data with expected output.
     * @return array
     */
    public function noActiveLoanRepayData()
    {
        $formData = [
            'repayAmount' => '106.6',
        ];

        $expectedOutput = [
            'status' => 'Failed',
            'message' => 'No existing loan'
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return insufficient income loan apply data with expected output.
     * @return array
     */
    public function incorrectWeeklyPaymentLoanRepayData()
    {
        $formData = [
            'repayAmount' => '100',
        ];

        $expectedOutput = [
            'status' => 'Failed',
            'message' => 'Incorrect weekly repayment amount',
            'weeklyRepayAmount' => 106.6
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return missing field "loanTerm" loan apply data with expected output.
     * @return array
     */
    public function missingFieldLoanRepayData()
    {
        $formData = [
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'repayAmount' => [
                    'The repay amount field is required.'
                ]
            ],
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return invalid data type "repayAmount" data with expected output.
     * @return array
     */
    public function invalidDataTypeLoanRepayData()
    {
        $formData = [
            'repayAmount' => 'abc',
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'repayAmount' => [
                    'The repay amount must be a number.'
                ]
            ],
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return valid loan apply data with expected output.
     * @return array
     */
    public function validLoanApplyData()
    {
        $formData = [
            'loanAmount' => '1000',
            'loanTerm' => '10',
            'monthlyIncome' => '500',
            'loanType' => '1'
        ];

        $expectedOutput = [
            'status' => 'Approved',
            'message' => 'Your loan request is approved',
            'loanId' => 1,
            'totalRepayAmount' => 1066,
            'weeklyRepaymentAmount' => 106.6
        ];

        return array($formData, $expectedOutput);
    }
}
