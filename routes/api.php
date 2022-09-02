<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderContoller;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\RestaurantFoodController;
use App\Http\Controllers\RestaurantFoodRateController;
use App\Http\Controllers\RestaurantLocationController;
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


//register and login routes
Route::group(['middleware' => ['api']], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});




//endpoints that could be reached only if the user is authenticated and has Admin role
Route::group(['middleware' => ['api', 'auth:sanctum', 'isAdmin']], function () {

    //restaurant resource
    Route::resource('/restaurant', RestaurantController::class, ['except' => ['create', 'edit']]);

    //Food resource
    Route::resource('/food', FoodController::class, ['only' => ['index', 'show']]);

    //Food from the specific restaurant
    Route::resource('/restaurants.food', RestaurantFoodController::class, ['except' => ['create', 'edit']]);

    //Locations of the specific restaurant
    Route::resource('/restaurants.locations', RestaurantLocationController::class, ['except' => ['create', 'edit']]);
});







//authenticated users
Route::group(['middleware' => ['auth:sanctum']], function () {

    //restaurant resource
    Route::resource('/restaurant', RestaurantController::class, ['only' => ['index', 'show']]);

    //logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //Food from the specific restaurant
    Route::resource('/restaurants.food', RestaurantFoodController::class, ['only' => ['index', 'show']]);
    Route::post('/restaurants/{restaurant_id}/food/{food_id}/rate', [RestaurantFoodRateController::class, 'rateFood']);

    //Locations of the specific restaurant
    Route::resource('/restaurants.locations', RestaurantLocationController::class, ['only' => ['index', 'show']]);

    //Ordering routes
    Route::get('/orders', [OrderContoller::class, 'myOrders']);
    Route::post('/restaurants/{restaurantID}/food/{foodID}/order', [OrderContoller::class, 'createOrder']);
});
