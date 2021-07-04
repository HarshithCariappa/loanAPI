<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Method to return valid registration data
     * @return string[]
     */
    public function validRegistrationData()
    {
        return [
            'firstName' => 'Harshith',
            'lastName' => 'Cariappa',
            'password' => 'password',
            'phoneNumber' => '9945564586',
            'password_confirmation' => 'password'
        ];
    }

    /**
     * Method to return valid login data
     * @return string[]
     */
    public function validLoginData()
    {
        return [
            'phoneNumber' => '9945564586',
            'password' => 'password'
        ];
    }
}
