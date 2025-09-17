<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;



use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\FormRegistrationController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\UserWalletController;

use App\Models\SystemConfig;


// Central API routes
Route::group(['middleware' => ['central'], 'prefix' => 'admin'], function () {
    // Admin API routes here
    Route::get('tenants/by-subdomain', function(\Illuminate\Http\Request $request) {
        $subdomain = $request->get('subdomain');
        
        if (!$subdomain) {
            return response()->json(['error' => 'Subdomain is required'], 400);
        }
        
        $tenant = \App\Helpers\SubdomainHelper::getTenantBySubdomain($subdomain);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }
        
        return response()->json([
            'tenant' => $tenant,
            'domains' => $tenant->domains,
        ]);
    })->name('api.tenants.by-subdomain');
    
    // Subdomain API routes
    Route::group(['prefix' => 'subdomain'], function () {
        Route::get('tenant/by-subdomain', [App\Http\Controllers\Api\SubdomainApiController::class, 'getTenantBySubdomain']);
        Route::get('tenant/by-domain', [App\Http\Controllers\Api\SubdomainApiController::class, 'getTenantByDomain']);
        Route::post('generate-url', [App\Http\Controllers\Api\SubdomainApiController::class, 'generateSubdomainUrl']);
        Route::post('validate', [App\Http\Controllers\Api\SubdomainApiController::class, 'validateSubdomain']);
        Route::get('domains', [App\Http\Controllers\Api\SubdomainApiController::class, 'getAllTenantDomains']);
        Route::post('clear-cache', [App\Http\Controllers\Api\SubdomainApiController::class, 'clearTenantCache']);
        Route::post('clear-all-cache', [App\Http\Controllers\Api\SubdomainApiController::class, 'clearAllTenantCache']);
    });
});

// Tenant API routes
Route::group(['middleware' => ['tenant']], function () {
    Route::apiResource('upload', UploadController::class);
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {return $request->user();});
    Route::get('/user/{id}', function (Request $request) { return  User::find($request->id)->massage;});
    Route::get('/user/{id}',[UserController::class, 'getMassages']);
    Route::get('/getUserMassages/{id}/{user}',[UserController::class, 'getUserMassages']);
    Route::middleware('auth:api')->get('/user', function (Request $request) { return $request->user();});
    Route::post('login',[UserController::class, 'login']);
    Route::get('getcontact/{id}',[UserController::class, 'getcontact']);
    Route::get('receiveCard',[UserController::class, 'receiveCard']);

    Route::get('ackUserMassages/{sender}/{receiver}/{date}',[UserController::class, 'ackUserMassages']);
    Route::get('getSaler',[UserController::class, 'getSaler']);

    Route::get('addTransfers',[TransfersController::class, 'addTransfers'])->name('addTransfers');
    Route::get('transfers',[TransfersController::class, 'index'])->name('transfers');
    Route::get('transfers/transactions',[TransfersController::class, 'getTransactions'])->name('transfers.transactions');
    Route::delete('transfers/transaction/{transactionId}',[TransfersController::class, 'deleteTransaction'])->name('transfers.delete');
    Route::get('getIndexAccountsSelas',[TransfersController::class, 'getIndexAccountsSelas'])->name('getIndexAccountsSelas');
    
    // Investment routes
    Route::post('investments/add',[TransfersController::class, 'addCarInvestment'])->name('investments.add');
    Route::post('investments/{id}/withdraw',[TransfersController::class, 'withdrawInvestment'])->name('investments.withdraw');
    Route::post('investments/direct-car-investment', [UserWalletController::class, 'addDirectCarInvestment'])->name('investments.direct-car-investment');
    Route::post('cars/{carId}/calculate-profit', [UserWalletController::class, 'calculateProfitOnCarSale'])->name('cars.calculate-profit');
    Route::get('investors/{userId}/profit-report', [UserWalletController::class, 'getInvestorProfitReport'])->name('investors.profit-report');
    Route::post('cars/{carId}/distribute-profit', [DashboardController::class, 'calculateProfitOnCarSale'])->name('cars.distribute-profit');

    // Route::get('carConfig',[CarConfigController::class, 'index'])->name('carConfig');
    // Route::get('addCompany',[CarConfigController::class, 'create'])->name('addCompany');
    // Route::post('addCompany',[CarConfigController::class, 'store']);
    // Route::get('addName',[CarConfigController::class, 'storeName'])->name('addName');
    // Route::get('addCarModel',[CarConfigController::class, 'storeCarModel'])->name('addCarModel');
    // Route::get('addColor',[CarConfigController::class, 'storeColor'])->name('addColor');
    Route::post('addCar',[DashboardController::class, 'addCar'])->name('addCar');

    Route::post('payCar',[DashboardController::class, 'payCar'])->name('payCar');
    Route::post('DelCar',[DashboardController::class, 'DelCar'])->name('DelCar');

    Route::get('client',[DashboardController::class, 'client'])->name('client');
    Route::get('getIndexCar',[DashboardController::class, 'getIndexCar'])->name('getIndexCar');
    Route::get('getIndexCarSearch',[DashboardController::class, 'getIndexCarSearch'])->name('getIndexCarSearch');
    Route::get('totalInfo',[DashboardController::class, 'totalInfo'])->name('totalInfo');

    Route::get('getIndexExpenses',[DashboardController::class, 'getIndexExpenses'])->name('getIndexExpenses');
    Route::get('GenExpenses',[DashboardController::class, 'GenExpenses'])->name('GenExpenses');
    Route::post('addGenExpenses',[DashboardController::class, 'addGenExpenses']);
    // Route::get('showCar',[CarConfigController::class, 'showCar']);

    Route::get('addExpenses',[DashboardController::class, 'addExpenses'])->name('addExpenses');
    Route::get('addPaymentCar',[DashboardController::class, 'addPaymentCar'])->name('addPaymentCar');

    Route::get('addToBox',[DashboardController::class, 'addToBox'])->name('addToBox');
    Route::get('withDrawFromBox',[DashboardController::class, 'withDrawFromBox'])->name('withDrawFromBox');

    // Route::get('getIndexCompany',[CarConfigController::class, 'getIndex'])->name('getIndexCompany');
    // Route::get('getIndexName',[CarConfigController::class, 'getIndexName'])->name('getIndexName');
    // Route::get('getIndexModel',[CarConfigController::class, 'getIndexModel'])->name('getIndexModel');
    // Route::get('getIndexColor',[CarConfigController::class, 'getIndexColor'])->name('getIndexColor');

    // Route::get('companyEdit/{id}',[CarConfigController::class, 'companyEdit'])->name('companyEdit');
    // Route::get('delCompany/{id}',[CarConfigController::class, 'companyDel'])->name('delCompany');
    // Route::get('delName/{id}',[CarConfigController::class, 'delName'])->name('delName');
    // Route::get('delModel/{id}',[CarConfigController::class, 'delModel'])->name('delModel');
    // Route::get('delColor/{id}',[CarConfigController::class, 'delColor'])->name('delColor');
    // Route::get('companyStoreEdit',[CarConfigController::class, 'index'])->name('companyStoreEdit');
    // Route::post('companyStoreEdit',[CarConfigController::class, 'storeEdit'])->name('companyStoreEdit');

    Route::get('car/{carId}/history', [DashboardController::class, 'getCarHistory']);
    Route::post('car/history/{historyId}/restore', [DashboardController::class, 'restoreCarHistory']);
    
    Route::get('clients', [UserController::class, 'getIndexClients'])->name('clients.index');
    Route::put('clients/{id}', [UserController::class, 'updateClient'])->name('clients.update');
    Route::post('clients/store', [UserController::class, 'storeClient'])->name('clients.store');
    Route::delete('clients/{id}', [UserController::class, 'destroyClient'])->name('clients.destroy');
    
    // Client investment profit management
    Route::get('clients/{clientId}/investments', [UserController::class, 'getClientInvestments'])->name('clients.investments');
    Route::post('clients/{clientId}/update-profit-shares', [UserController::class, 'updateClientProfitShares'])->name('clients.update-profit-shares');
    Route::get('car-payments', [DashboardController::class, 'getCarPayments']);
    Route::post('editSalePrice', [DashboardController::class, 'editSalePrice']);
    Route::post('editPaidAmount', [DashboardController::class, 'editPaidAmount']);
    Route::get('main-fund-profit-from-investors', [DashboardController::class, 'getMainFundProfitFromInvestors'])->name('main-fund.profit-from-investors');
    Route::post('user-wallet/add', [UserWalletController::class, 'addToWallet'])->name('user-wallet.add');
    Route::post('user-wallet/direct-car-investment', [UserWalletController::class, 'addDirectCarInvestment'])->name('user-wallet.direct-car-investment');
    Route::get('user-wallet/cars-needing-completion', [UserWalletController::class, 'getCarsNeedingCompletionForUser'])->name('user-wallet.cars-needing-completion');
    Route::post('user-wallet/complete-car-investment', [UserWalletController::class, 'completeCarInvestment'])->name('user-wallet.complete-car-investment');
// Car Investment Routes
Route::get('cars/available-for-investment', [UserWalletController::class, 'getAvailableCarsForInvestment'])->name('cars.available-for-investment');
Route::post('investments/car-investment', [UserWalletController::class, 'createCarInvestment'])->name('investments.car-investment');
Route::get('investments/{id}/details', [UserWalletController::class, 'getInvestmentDetails'])->name('investments.details');
    Route::post('user-wallet/withdraw', [UserWalletController::class, 'withdrawFromWallet'])->name('user-wallet.withdraw');
    Route::delete('user-wallet/transactions/{transactionId}', [UserWalletController::class, 'deleteTransaction'])->name('user-wallet.delete-transaction');
    Route::get('user-wallet/stats', [UserWalletController::class, 'getUserStats'])->name('user-wallet.stats');
});
