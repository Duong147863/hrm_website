<?php

use App\Http\Controllers\DecisionsController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DiplomasController;
use App\Http\Controllers\EnterprisesController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\RelativesController;
use App\Http\Controllers\SalariesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\WorkingProcessesController;
use App\Http\Controllers\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//
Route::controller(EnterprisesController::class)->group(function () {
    Route::get('/v1/enterprises', 'index');
    Route::put('', '');
    Route::post('', '');
    Route::delete('', '');
});
//
Route::controller(DiplomasController::class)->group(function () {
    Route::get('', '');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(DepartmentsController::class)->group(function () {
    Route::get('/v1/departments/{id}', 'showDepartmentsByEnterpriseID');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(DecisionsController::class)->group(function () {
    Route::get('/v1/decisions/{id}', 'showDecisionsByEnterpriseID');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(ProfilesController::class)->group(function () {
    Route::get('/profile/{id}', 'getUserProfile');
    Route::get('/v1/profiles/{id}', 'showProfilesByEnterpriseID');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(PositionsController::class)->group(function () {
    Route::get('/v1/positions/{id}', 'showPositionsByEnterpriseID');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(ProjectsController::class)->group(function () {
    Route::get('', '');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(RelativesController::class)->group(
    function () {
        Route::get('/relatives/{id}', 'showRelativesOf');
        Route::put('', '');
        Route::post('',);
        Route::delete('', '');
    }
);
//
Route::controller(WorkingProcessesController::class)->group(function () {
    Route::get('', '');
    Route::put('', '');
    Route::post('',);
    Route::delete('', '');
});
//
Route::controller(SalariesController::class)->group(function () {
    Route::get('/v1/salaries/{id}', 'getSalariesByEnterpriseID');
    // Route::get('/salaries/{id}', 'getSalariesByDepartmentID');
    // Route::get('/salary/{id}', 'getSalary');
    Route::put('', '');
    Route::post('','');
    Route::delete('', '');
});
