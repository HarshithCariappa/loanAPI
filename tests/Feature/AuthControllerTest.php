<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test case to test user register
     * AuthController register method
     *
     * Cases :
     * pass invalid data : missing required field test, incorrect data type test, password miss match test
     * pass valid data
     * pass non unique data
     * @return void
     */
    public function test_register()
    {
        // invalid data test , missing required field "secondName"
        list($formData, $expectedOutput) = $this->missingFieldRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // invalid data test , incorrect datatype for phone number field
        list($formData, $expectedOutput) = $this->incorrectDataTypeRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // invalid data test , password miss match
        list($formData, $expectedOutput) = $this->passwordMissMatchRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJson($expectedOutput);

        // valid data test
        $formData = $this->validRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(200);

        // non unique data test
        $expectedOutput = $this->nonUniqueRegistrationOutput();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(422)
            ->assertJson($expectedOutput);
    }

    /**
     * Case to test login api
     *
     * Cases :
     * Bad credentials
     * valid credentials
     */
    public function test_login()
    {
        // register a user before login
        $formData = $this->validRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(200);

        // valid data test
        $formData = $this->validLoginData();
        $this->json('POST','/api/login',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(201);

        // Bad credentials test
        list($formData, $expectedOutput) = $this->invalidLoginData();
        $this->json('POST','/api/login',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(401)
            ->assertJson($expectedOutput);
    }

    /**
     * Method  to test logout
     */
    public function test_logout()
    {
        $expectedOutput = $this->unauthenticatedOutput();
        $this->json('POST','/api/logout', [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(401)
            ->assertJson($expectedOutput);

        // register a user before login
        $formData = $this->validRegistrationData();
        $this->json('POST','/api/register',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(200);

        // valid data test
        $formData = $this->validLoginData();
        $response = $this->postJson('/api/login',$formData, ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(201);

        $token = $response->json('token');

        $this->post('/api/logout', [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_AUTHORIZATION' => "Bearer $token"
        ])
            ->assertStatus(200);
    }

    /**
     * Method to return non unique registration output
     * @return array
     */
    public function nonUniqueRegistrationOutput()
    {
        return [
            'message' => 'The given data was invalid.',
            'errors' => [
                'phoneNumber' => [
                    'The phone number has already been taken.'
                ]
            ]
        ];
    }

    /**
     * Method to return missing required field registration data with expected output.
     * @return array
     */
    public function missingFieldRegistrationData()
    {
        $formData = [
            'firstName' => 'Harshith',
            'password' => 'password',
            'phoneNumber' => '9945564586',
            'password_confirmation' => 'password'
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'lastName' => [
                    'The last name field is required.'
                ]
            ]
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return incorrect Data Type registration data with expected output.
     * @return array
     */
    public function incorrectDataTypeRegistrationData()
    {
        $formData = [
            'firstName' => 'Harshith',
            'secondName' => 'Cariappa',
            'password' => 'password',
            'phoneNumber' => '994556458h',
            'password_confirmation' => 'password'
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'phoneNumber' => [
                    'The phone number must be 10 digits.',
                    'The phone number must be a number.'
                ]
            ]
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return password Miss Match registration data with expected output.
     * @return array
     */
    public function passwordMissMatchRegistrationData()
    {
        $formData = [
            'firstName' => 'Harshith',
            'secondName' => 'Cariappa',
            'password' => 'password',
            'phoneNumber' => '9945564586',
            'password_confirmation' => 'passwor'
        ];

        $expectedOutput = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'password' => [
                    'The password confirmation does not match.'
                ]
            ]
        ];

        return array($formData, $expectedOutput);
    }

    /**
     * Method to return invalid login data with expected output
     * @return array
     */
    public function invalidLoginData()
    {
        $formData = [
            'phoneNumber' => '994556458',
            'password' => 'password'
        ];

        $expectedOutput = [
            'message' => 'Bad Credentials'
        ];

        return array($formData, $expectedOutput);
    }
}
