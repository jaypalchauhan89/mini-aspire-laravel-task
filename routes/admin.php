<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\LoanController;
/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['middleware'=>['auth:sanctum','admin'],'prefix'=>'admin/'],function(){
    Route::get('loan',[LoanController::class, 'index'])->name('admin.loan');
    Route::post('loan/{id}/approve',[LoanController::class, 'approveLoan'])->name('admin.approveLoan');
});
