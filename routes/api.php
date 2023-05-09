<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StringhandlingController;
use App\Http\Controllers\MoviesWebpageController;
use App\Http\Controllers\AuthController;

# Global endpoints
# Authenticate endpoints (recommended for users that know that will be admins) #
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
##########-----------------------------------------------------------###########

# Shows all the movies stored in DB
Route::get('/movies', [MoviesWebpageController::class, 'readMovies']);

# Rate a movie
Route::post('/movies/rate', [MoviesWebpageController::class, 'rateMovie']);
# Extra point
Route::post('/string-reduce/{string}', [StringhandlingController::class, 'reduceString']);


# Admin only, endpoints
Route::group([
    'middleware' => ['api', 'role:admin'],
    'prefix' => 'admin'
], function($router){
    # Create a new movie/series
    Route::post('movies', [MoviesWebpageController::class, 'createMovie']);
    # Edit an existing item
    Route::put('movies', [MoviesWebpageController::class, 'updateMovie']);
    # Delete the existing item
    Route::delete('movies/{title}', [MoviesWebpageController::class, 'deleteMovie']);
});