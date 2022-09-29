<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoanController;
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
Route::post('/auth/login', [AuthController::class, 'loginUser'])->name('loginUser');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware'=>['auth:sanctum']],function(){
    Route::get('loan',[LoanController::class, 'index'])->name('loan');
    Route::post('loan/apply',[LoanController::class, 'applyLoan'])->name('applyLoan');
    Route::get('loan/{id}',[LoanController::class, 'detail'])->name('loanDetail');
    Route::post('loan/{id}/repayment',[LoanController::class, 'repayment'])->name('repayment');
    Route::post('loan/{id}/emi/{emiId}/repayment',[LoanController::class, 'repaymentAgainstEMI'])->name('repaymentAgainstEMI');
});

require __DIR__.'/admin.php';
