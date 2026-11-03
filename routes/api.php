<?php

use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoyageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
Route::group([
    'middleware' => 'api',
    'prefix' => 'users'
    ], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refreshToken', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });
    Route::get('users/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');


Route::middleware('api')->group(function () {
    
    Route::resource('matches', VoyageController::class);
    Route::resource('stadiums', DestinationController::class);
});
   

// Public read endpoints for stadiums so clients can list and view without auth
Route::get('stadiums', [DestinationController::class, 'index']);
Route::get('stadiums/{stadium}', [DestinationController::class, 'show']);

// Protected endpoints for creating/updating/deleting stadiums
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('stadiums', DestinationController::class)->only(['store', 'update', 'destroy']);
});
    
    
// Route::get('users', [UserController::class, 'index']);  // Récupérer tous les utilisateurs
// Route::get('users/{id}', [UserController::class, 'show']);  // Afficher un utilisateur spécifique
// Route::post('users', [UserController::class, 'store']);  // Créer un nouvel utilisateur
// Route::put('users/{id}', [UserController::class, 'update']);  // Mettre à jour un utilisateur
// Route::delete('users/{id}', [UserController::class, 'destroy']);
   

Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('users', UserController::class);
    });

// Route::middleware('api')->group(function () {
    //     Route::resource('reservations', ReservationController::class);
    //     });
        // Public read endpoints for tickets
        Route::get('tickets', [ReservationController::class, 'index']);
        Route::get('tickets/{ticket}', [ReservationController::class, 'show']);
        // Summation endpoint for a match's reserved places
        Route::get('tickets/somme-places/{idMatch}', [ReservationController::class, 'sommePlaces']);

        // Protected endpoints for creating/updating/deleting tickets
        Route::group(['middleware' => ['auth:api']], function () {
            Route::resource('tickets', ReservationController::class)->only(['store', 'update', 'destroy']);
        });
            Route::get('/matches/voyagespaginate', [VoyageController::class,'voyagesPaginate']);
            // routes/api.php
            Route::get('stadiums/destinationspaginate', [DestinationController::class, 'destinationsPaginate']);



    