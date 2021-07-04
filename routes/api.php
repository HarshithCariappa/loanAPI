<?php

use App\Http\Controllers\LoanRepaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// login route
Route::post('/login', [AuthController::class, 'login']);

// Route to register a user.
Route::post('/register', [AuthController::class, 'register']);

/**
 * Protected Routes
 * Put all the routes which requires login within this group.
 */
Route::group(['middleware' => ['auth:sanctum']], function () {
    // Route to apply for a loan.
    Route::post('/loanApply', [LoanController::class, 'processLoan']);

    // Route to repay a loan.
    Route::post('/loanRepay', [LoanRepaymentController::class, 'processLoanRepayment']);

    // Route to logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
