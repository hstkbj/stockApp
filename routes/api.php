<?php

use App\Http\Controllers\AprovisionnementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceMecefController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProduitVenteStatsController;
use App\Http\Controllers\RayonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\InventaireController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/login',[UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){

    //Profile
    Route::get('/user/profile', [UserController::class, 'show']);
    Route::put('/user/profile', [UserController::class, 'changeProfile']);
    Route::post('/user/profile/change-password', [UserController::class, 'changePassword']);

    //CurrentUser
    Route::get('/user', [UserController::class, 'getUser']);
    //logout
    Route::post('/logout',[UserController::class, 'logout']);

    //Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);


    // Clients
    Route::apiResource('clients', ClientController::class);

    // Catégories
    Route::apiResource('categories', CategoryController::class);

    // Rayon
    Route::apiResource('rayons', RayonController::class);

    //Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Produits
    Route::get('products/boutique', [ProductController::class, 'indexProductBoutique']);
    Route::get('products/magasin',  [ProductController::class, 'indexProductMagasin']);
    Route::post('products/boutiqueStore', [ProductController::class, 'storeProductBoutique']);
    Route::post('products/magasinStore', [ProductController::class,  'storeProductMagasin']);
    Route::put   ('products/boutiquePut/{product}',[ProductController::class, 'updateProductBoutique']);
    Route::put   ('products/magasinPut/{product}', [ProductController::class,  'updateProductMagasin']);
    Route::get('/products/{product}', [ProductController::class, 'showBoutique']);
    Route::get('/products-magasin/{product}', [ProductController::class, 'showMagasin']);
    Route::apiResource('products', ProductController::class)->except('show');

    // Mouvements
    Route::get('mouvements/filter', [MouvementController::class, 'indexByEmplacement']);
    Route::apiResource('mouvements', MouvementController::class)->except(['update']);

    //Transfert
    Route::post('/transferts/vers-magasin', [TransfertController::class, 'versMagasin']);
    Route::post('/transferts/vers-boutique', [TransfertController::class, 'versBoutique']);

    // Ventes
    Route::apiResource('invoices', InvoiceController::class);
    Route::put('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus']);
    Route::put('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    Route::post('invoices/{invoice}/normalize', [InvoiceMecefController::class, 'normalizeInvoice']);
    Route::post('invoices/{invoice}/cancelled', [InvoiceMecefController::class, 'cancelNormalizedInvoice']);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
    Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendByEmail']);

    // Fournisseurs
    Route::apiResource('fournisseurs', FournisseurController::class);

    // Approvisionnements
    Route::apiResource('aprovisionnements', AprovisionnementController::class)->except(['update']);
    Route::put('aprovisionnement/{aprovisionnement}/enAttente', [AprovisionnementController::class, 'enAttente']);
    Route::post('aprovisionnement/{aprovisionnement}/livrer', [AprovisionnementController::class, 'livrer']);

    //Rôles et permissions
    Route::get('/roles/pages', [RoleController::class, 'pages']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    //Inventaire
    Route::get('/inventaire', [InventaireController::class, 'index']);

    //Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);


});
