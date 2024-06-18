<?php

use App\Http\Controllers\DestinationCategoryController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DestinationOrderController;
use App\Http\Controllers\DestinationUserCartController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductOrderController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductUserCartController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\TouristTypeController;
use App\Http\Controllers\TourTypesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DestinationMaintenanceController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\VariantController;
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

Route::middleware(['auth.root'])->group(function () {
    Route::prefix('products-order')->group(function () {
        Route::post('/', [ProductOrderController::class, 'index']); //done
        Route::post('/store', [ProductOrderController::class, 'store']); //done
        Route::post('/update', [ProductOrderController::class, 'update']); //done
    });
    Route::prefix('product-user-cart')->group(function () {
        Route::post('/', [ProductUserCartController::class, 'index']);
        Route::post('/store', [ProductUserCartController::class, 'store']);
        Route::post('/destroy', [ProductUserCartController::class, 'destroy']);
    });

    Route::prefix('destination-user-cart')->group(function () {
        Route::post('/', [DestinationUserCartController::class, 'index']);
        Route::post('/store', [DestinationUserCartController::class, 'store']);
        Route::post('/destroy', [DestinationUserCartController::class, 'destroy']);
    });


    Route::prefix('destination-order')->group(function () {
        Route::post('/', [DestinationOrderController::class, 'index']);
        Route::post('/store', [DestinationOrderController::class, 'store']);
        Route::post('/update', [DestinationOrderController::class, 'update']);
    });
});



Route::prefix('kiosk')->group(function () {
    Route::post('/', [KioskController::class, 'index']);
    Route::post('/store', [KioskController::class, 'store']);
    Route::patch('/update/{id}', [KioskController::class, 'update']);
    Route::delete('/{id}', [KioskController::class, 'delete']);
    Route::get('/{id}', [KioskController::class, 'get']);
    Route::post('/redirectUser', [LoginController::class, 'kioskRedirect']);
});

Route::prefix('destination-rentals')->group(function () {
    Route::post('/', [RentalController::class, 'index']);
    Route::get('/{id}', [RentalController::class, 'get']);
});

Route::prefix('destination-rules')->group(function () {
    Route::post('/', [RulesController::class, 'index']);
    Route::post('/store', [RulesController::class, 'store']);
    Route::patch('/{id}', [RulesController::class, 'update']);
    Route::delete('/{id}', [RulesController::class, 'delete']);
    Route::get('/{id}', [RulesController::class, 'get']);
});

Route::get('/get-product/{id}', [ProductsController::class, 'get']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/get-refno', [PaymentMethodController::class, 'getRefnoNumer']);
Route::post('/products', [ProductsController::class, 'index']);
Route::post('/destination', [DestinationController::class, 'index']);
Route::post('/tour-types', [TourTypesController::class, 'index']);
Route::post('/tourist-types', [TouristTypeController::class, 'index']);
Route::post('/destination-maintenance', [DestinationMaintenanceController::class, 'index']);
Route::post('/products-categories', [ProductsController::class, 'showProductCategories']);
Route::post('/products-condition', [ProductsController::class, 'showProductConditions']);
Route::post('/destination-categories', [DestinationController::class, 'showDestinationCategories']);
Route::post('/destination-rules', [DestinationController::class, 'showRules']);
Route::get('/destination/{id}', [DestinationController::class, 'get']);
Route::get('/products/{id}', [ProductCategoryController::class, 'get']);
Route::get('/getDestination/{id}', [DestinationController::class, 'get']);
Route::post('/roles', [UserController::class, 'roleIndex']);
Route::post('/destination/get-tour-type-prices', [DestinationController::class, 'getTourTypes']);
Route::post('/destination/get-rentals', [DestinationController::class, 'getRentals']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/generate-report', [PdfController::class, 'generateAndDownloadPDF']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/destination-order/store-without-payment', [DestinationOrderController::class, 'storeWithoutPayment']);

    Route::prefix('users')->group(function () {
        Route::post('/', [UserController::class, 'index']);
        Route::post('/store', [UserController::class, 'store']);
        Route::patch('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'delete']);
        Route::get('/{id}', [UserController::class, 'get']);
    });

    Route::prefix('destination')->group(function () {
        // Route::post('/', [DestinationController::class, 'index']);
        Route::post('/store', [DestinationController::class, 'store']);
        Route::patch('/{id}', [DestinationController::class, 'update']);
        Route::delete('/delete/{id}', [DestinationController::class, 'delete']);
        Route::post('/upload/{id}', [DestinationController::class, 'destinationImages']);
        Route::delete('/delete-image/{id}', [DestinationController::class, 'destinationImagesDelete']);
        Route::post('/tour-types', [DestinationController::class, 'destinationTourType']);
        Route::post('/tour-types/store', [DestinationController::class, 'destinationTourTypeStore']);
        Route::post('/upload-video/{id}', [DestinationController::class, 'destinationVideo']);
        Route::post('/tourist-type', [DestinationController::class, 'destinationTouristTypeIndex']);
        Route::delete('/tour-type/{id}', [DestinationController::class, 'destinationTourTypeDelete']);
        Route::post('/tourist-type/store', [DestinationController::class, 'destinationTouristTypeStore']);
        Route::get('/tourist-type/{id}', [DestinationController::class, 'destinationTouristTypeGet']);
        Route::patch('/tourist-type/{id}', [DestinationController::class, 'destinationTouristTypeUpdate']);
        Route::delete('/tourist-type-delete/{id}', [DestinationController::class, 'touristTypeDelete']);
        Route::delete('/tourist-type/{id}', [DestinationController::class, 'destinationTouristTypeDelete']);
        Route::post('/user-orders/{id}', [UserController::class, 'showDestinationOrders']);
        Route::post('checkin', [DestinationController::class, 'checkIn']);
        Route::post('get-individual', [DestinationController::class, 'getIndividualsDetail']);
    });

    Route::prefix('destination-order')->group(function () {
        Route::post('/admin', [DestinationOrderController::class, 'adminIndex']);
        Route::post('/find', [DestinationOrderController::class, 'findOrder']);
        Route::post('/ticket-details', [DestinationOrderController::class, 'getTicketDetails']);
        Route::post('/qr-code', [DestinationOrderController::class, 'getUserDetailsQr']);
        Route::post('getIndividualsQrCode', [DestinationOrderController::class, 'getIndividualsQrCode']);
    });
    Route::prefix('products-order')->group(function () {
        Route::post('/admin', [ProductOrderController::class, 'adminIndex']);
    });

    Route::prefix('destination-category')->group(function () {
        Route::post('/', [DestinationCategoryController::class, 'index']);
        Route::post('/store', [DestinationCategoryController::class, 'store']);
        Route::get('/{id}', [DestinationCategoryController::class, 'get']);
        Route::patch('/{id}', [DestinationCategoryController::class, 'update']);
        Route::delete('/{id}', [DestinationCategoryController::class, 'delete']);
    });

    Route::prefix('destination-sales-information')->group(function () {
        Route::post('/store/{id}', [DestinationController::class, 'storeSalesInformation']);
    });

    Route::prefix('destination-rentals')->group(function () {
        Route::post('/', [RentalController::class, 'index']);
        Route::post('/store', [RentalController::class, 'store']);
        Route::patch('/{id}', [RentalController::class, 'update']);
        Route::delete('/{id}', [RentalController::class, 'delete']);
    });

    Route::prefix('tour-types')->group(function () {
        Route::post('/store', [TourTypesController::class, 'store']);
        Route::get('/{id}', [TourTypesController::class, 'get']);
        Route::patch('/{id}', [TourTypesController::class, 'update']);
        Route::delete('/{id}', [TourTypesController::class, 'delete']);
    });

    Route::prefix('tourist-types')->group(function () {
        Route::post('/store', [TouristTypeController::class, 'store']);
        Route::get('/{id}', [TouristTypeController::class, 'get']);
        Route::patch('/{id}', [TouristTypeController::class, 'update']);
        Route::delete('/{id}', [TouristTypeController::class, 'delete']);
    });

    Route::prefix('products')->group(function () {
        Route::post('/store', [ProductsController::class, 'store']);
        Route::patch('/{id}', [ProductsController::class, 'update']);
        Route::delete('/{id}', [ProductsController::class, 'delete']);
        Route::post('/upload/{id}', [ProductsController::class, 'productImages']);
        Route::delete('/delete-image/{id}', [ProductsController::class, 'productImagesDelete']);
        Route::post('/upload-video/{id}', [ProductsController::class, 'productvideo']);
        Route::delete('/delete-video/{id}', [ProductsController::class, 'deleteProductVideo']);
        Route::post('/user-orders/{id}', [UserController::class, 'showProductOrders']);
        Route::post('/merchant', [ProductsController::class, 'merchantProducts']);
    });

    Route::prefix('products-category')->group(function () {
        Route::post('/', [ProductCategoryController::class, 'index']);
        Route::post('/store', [ProductCategoryController::class, 'store']);
        Route::patch('/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('/{id}', [ProductCategoryController::class, 'delete']);
        Route::get('/{id}', [ProductCategoryController::class, 'get']);
    });

    Route::prefix('destination-maintenance')->group(function () {
        // Route::post('/', [DestinationMaintenanceController::class, 'index']);
        Route::post('/store', [DestinationMaintenanceController::class, 'store']);
        Route::delete('/{id}', [DestinationMaintenanceController::class, 'delete']);
    });

    Route::prefix('merchant')->group(function () {
        Route::post('/upload-image/{id}', [UserController::class, 'merchantImage']);
        Route::delete('delete-image/{id}', [UserController::class, 'deleteMerchantImage']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
    Route::prefix('variant')->group(function () {
        Route::post('/', [VariantController::class, 'index']);
        Route::post('/store', [VariantController::class, 'store']);
        Route::patch('/{id}', [VariantController::class, 'update']);
        Route::delete('/{id}', [VariantController::class, 'delete']);
        Route::get('/{id}', [VariantController::class, 'get']);
    });
});