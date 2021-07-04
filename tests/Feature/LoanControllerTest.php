<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use Database\Seeders\LoanTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanControllerTest extends TestCase
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
            ->assertStatus(201);

        // valid data test
        $formData = $this->validLoginData();
        $response = $this->postJson('/api/login',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(201);

        $this->token = $response->json('token');
    }
    /**
     * case to test loan apply api
     *
     * Cases :
     * test missing required field
     * test invalid data type
     * test valid data
     * test non unique data (existing loan test)
     * @return void
     */
    public function test_loanApply()
    {
        // invalid data test , missing required field "secondName"
        list($formData, $expectedOutput) = $this->missingFieldLoanApplyData();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // invalid data test , invalid data type "secondName"
        list($formData, $expectedOutput) = $this->invalidDataTypeLoanApplyData();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // insufficient income test
        list($formData, $expectedOutput) = $this->insufficientIncomeLoanApplyData();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(403)
            ->assertJson($expectedOutput);

        // valid data test
        list($formData, $expectedOutput) = $this->validLoanApplyData();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(401)
            ->assertJson($expectedOutput);

        // non unique data (existing loan test)
        $expectedOutput = $this->nonUniqueLoanApplyDataOutput();
        $this->json('POST','/api/loanApply', $formData, [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $this->token"])
            ->assertStatus(403)
            ->assertJson($expectedOutput);
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

    /**
     * Method to return insufficient income loan apply data with expected output.
     * @return array
     */
    public function insufficientIncomeLoanApplyData()
    {
        $formData = [
            'loanAmount' => '1000',
            'loanTerm' => '7',
            'monthlyIncome' => '500',
            'loanType' => '1'
        ];

        $expectedOutput = [
            'status' => 'Rejected',
            'message' => 'Monthly income is insufficient for weekly repayments',
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return missing field "loanTerm" loan apply data with expected output.
     * @return array
     */
    public function missingFieldLoanApplyData()
    {
        $formData = [
            'loanAmount' => '1000',
            'monthlyIncome' => '500',
            'loanType' => '1'
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'loanTerm' => [
                    'The loan term field is required.'
                ]
            ],
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return invalid data type "loanTerm" data with expected output.
     * @return array
     */
    public function invalidDataTypeLoanApplyData()
    {
        $formData = [
            'loanAmount' => '1000',
            'monthlyIncome' => '500',
            'loanTerm' => 'aa',
            'loanType' => '1'
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'loanTerm' => [
                    'The loan term must be a number.'
                ]
            ],
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return non unique data output.
     * @return string[]
     */
    public function nonUniqueLoanApplyDataOutput()
    {
        return [
            'status' => 'Rejected',
            'message' => 'Already have a active loan'
        ];
    }
}
