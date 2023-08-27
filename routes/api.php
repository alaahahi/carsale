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
use App\Http\Controllers\CarConfigController;

use App\Models\SystemConfig;


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
Route::get('getIndexAccountsSelas',[TransfersController::class, 'getIndexAccountsSelas'])->name('getIndexAccountsSelas');

Route::get('carConfig',[CarConfigController::class, 'index'])->name('carConfig');
Route::get('addCompany',[CarConfigController::class, 'create'])->name('addCompany');
Route::post('addCompany',[CarConfigController::class, 'store']);
Route::get('addName',[CarConfigController::class, 'storeName'])->name('addName');
Route::get('addCarModel',[CarConfigController::class, 'storeCarModel'])->name('addCarModel');
Route::get('addColor',[CarConfigController::class, 'storeColor'])->name('addColor');
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
Route::get('showCar',[CarConfigController::class, 'showCar']);

Route::get('addExpenses',[DashboardController::class, 'addExpenses'])->name('addExpenses');
Route::get('addPaymentCar',[DashboardController::class, 'addPaymentCar'])->name('addPaymentCar');


Route::get('addToBox',[DashboardController::class, 'addToBox'])->name('addToBox');
Route::get('withDrawFromBox',[DashboardController::class, 'withDrawFromBox'])->name('withDrawFromBox');




Route::get('getIndexCompany',[CarConfigController::class, 'getIndex'])->name('getIndexCompany');
Route::get('getIndexName',[CarConfigController::class, 'getIndexName'])->name('getIndexName');
Route::get('getIndexModel',[CarConfigController::class, 'getIndexModel'])->name('getIndexModel');
Route::get('getIndexColor',[CarConfigController::class, 'getIndexColor'])->name('getIndexColor');


Route::get('companyEdit/{id}',[CarConfigController::class, 'companyEdit'])->name('companyEdit');
Route::get('delCompany/{id}',[CarConfigController::class, 'companyDel'])->name('delCompany');
Route::get('delName/{id}',[CarConfigController::class, 'delName'])->name('delName');
Route::get('delModel/{id}',[CarConfigController::class, 'delModel'])->name('delModel');
Route::get('delColor/{id}',[CarConfigController::class, 'delColor'])->name('delColor');
Route::get('companyStoreEdit',[CarConfigController::class, 'index'])->name('companyStoreEdit');
Route::post('companyStoreEdit',[CarConfigController::class, 'storeEdit'])->name('companyStoreEdit');


