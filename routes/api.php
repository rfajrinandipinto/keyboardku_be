<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\AdminMiddleware;

header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

Route::get('/available-brands', [ProductController::class, 'getAvailableBrands']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/whoami', [AuthController::class, 'whoAmI']);

    Route::post('categories', [CategoryController::class, 'store'])->middleware(AdminMiddleware::class);
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware(AdminMiddleware::class);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware(AdminMiddleware::class);

    Route::put('products/{product}/activate', [ProductController::class, 'activate'])->middleware(AdminMiddleware::class);
    Route::put('products/{product}/deactivate', [ProductController::class, 'deactivate'])->middleware(AdminMiddleware::class);
    Route::post('products', [ProductController::class, 'store'])->middleware(AdminMiddleware::class);
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware(AdminMiddleware::class);
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware(AdminMiddleware::class);
    Route::post('products/{product}/images', [ProductController::class, 'addImage'])->middleware(AdminMiddleware::class);
    Route::put('products/{product}/images/{image}', [ProductController::class, 'updateImage'])->middleware(AdminMiddleware::class);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->middleware(AdminMiddleware::class);

    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add/{product}', [CartController::class, 'addToCart']);
    Route::put('cart/update/{cartItem}', [CartController::class, 'updateCartItemQuantity']);
    Route::delete('cart/remove/{cartItem}', [CartController::class, 'removeFromCart']);
    Route::delete('cart/clear', [CartController::class, 'clearCart']);
    Route::post('cart/checkout', [CartController::class, 'checkout']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders/place', [OrderController::class, 'placeOrder']);

    Route::post('payments/callback', [PaymentController::class, 'callback']);
    Route::post('orders/{orderId}/payments', [PaymentController::class, 'createPayment']);
});
