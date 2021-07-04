<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Method to validate the user data and register the user
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'password' => 'required|string|confirmed',
            'phoneNumber' => 'required|digits:10|numeric|unique:users,phone_number'
        ]);

        $user = User::create([
            'first_name' => $fields['firstName'],
            'last_name' => $fields['lastName'],
            'password' => Hash::make($fields['password']),
            'phone_number' => $fields['phoneNumber'],
        ]);

        $token = $user->createToken('myAppToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 200);
    }

    /**
     * Method to validate the user data and login
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'phoneNumber' => 'required',
            'password' => 'required'
        ]);

        // fetch user by username
        $user = User::where('phone_number', $fields['phoneNumber'])->first();

        // check if the user name exist and the password is matching.
        if(!$user || !Hash::check($fields['password'], $user->password))
        {
            return response([
                'message' => 'Bad Credentials'
            ], 401);
        }

        $token = $user->createToken('myAppToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * Method to logout the delete the token.
     * @param Request $request
     * @return string[]
     */
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged Out'
        ];
    }
}
