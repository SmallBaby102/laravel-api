<?php
use App\Http\Controllers\API\UserController as APIUserController;
use App\Http\Controllers\API\ReportController as APIReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware(['cors'])->group(function () {
    // api for crypto wire 
    Route::put('profile', [APIUserController::class, 'updateProfile'])->name('updateProfile');
    Route::get('profile/{email}', [APIUserController::class, 'getProfile'])->name('getProfile');
    Route::get('affiliate_id/{email}', [APIUserController::class, 'getAffiliateId'])->name('getAffiliateId');
    Route::post('signup', [APIUserController::class, 'signup'])->name('signup');
    Route::post('signinAffiliate', [APIUserController::class, 'signinAffiliate'])->name('signinAffiliate');
    Route::post('depositAddress', [APIUserController::class, 'saveDepositAddress'])->name('saveDepositAddress');
    Route::post('profileVerification', [APIUserController::class, 'profileVerification'])->name('profileVerification');
    Route::post('withdraw', [APIUserController::class, 'withdraw'])->name('withdraw');
    Route::put('sell', [APIUserController::class, 'sell'])->name('sell');
    Route::post('sell', [APIUserController::class, 'sell'])->name('sell');
    Route::get('depositAddress/{email}/{product}', [APIUserController::class, 'getDepositAddress'])->name('getDepositAddress');
    Route::get('accounts/{nonce}/{authorization_value}', [APIUserController::class, 'getAccounts'])->name('getAccounts');
    Route::get('commission_report/{email}', [APIUserController::class, 'getCommissionReport'])->name('getCommissionReport');
    Route::get('commission_myuser/{email}', [APIUserController::class, 'getCommissionMyUser'])->name('getCommissionMyUser');
    Route::post('orders', [APIUserController::class, 'orders'])->name('orders');
    Route::post('change_pap_password', [APIUserController::class, 'changePapUserPassword'])->name('changePapUserPassword');
    // 
    Route::get('report/{email}', [APIReportController::class, 'getReport'])->name('getReport');
    Route::get('security/{email}', [APIReportController::class, 'getSecurity'])->name('getSecurity');
    Route::post('security/{email}', [APIReportController::class, 'changeSecurity'])->name('changeSecurity');
    Route::get('wirehistory/{id}/{email}', [APIReportController::class, 'getWireHistory'])->name('getWireHistory');
    Route::get('bank_template/{email}', [APIReportController::class, 'getBankTemplate'])->name('getBankTemplate');
    Route::post('bank_template/{email}', [APIReportController::class, 'saveBankTemplate'])->name('saveBankTemplate');
    Route::delete('bank_template/{id}', [APIReportController::class, 'deleteBankTemplate'])->name('deleteBankTemplate');
    Route::get('news', [APIReportController::class, 'getNews'])->name('getNews');

});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
