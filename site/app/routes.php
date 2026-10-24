<?php
declare(strict_types=1);

/** @var App\Core\Router $router */

use App\Controllers\AccountController;
use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AdminOrderController;
use App\Controllers\Admin\AdminProductController;
use App\Controllers\Admin\AdminReviewController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\HomeController;
use App\Controllers\OrderController;
use App\Controllers\PageController;
use App\Controllers\PaymentController;
use App\Controllers\ProductController;
use App\Controllers\ReviewController;

// Storefront
$router->get('/', [HomeController::class, 'index']);
$router->get('/shop', [ProductController::class, 'index']);
$router->get('/search', [ProductController::class, 'index']);
$router->get('/product/{slug}', [ProductController::class, 'show']);

// Cart (AJAX + form fallback)
$router->get('/cart', [CartController::class, 'page']);
$router->get('/cart/state', [CartController::class, 'state']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/buy-now', [CartController::class, 'buyNow']);

// Checkout + payments
$router->get('/checkout', [CheckoutController::class, 'show']);
$router->post('/checkout', [CheckoutController::class, 'place']);
$router->post('/payment/razorpay/verify', [PaymentController::class, 'verify']);
$router->post('/payment/razorpay/failed', [PaymentController::class, 'failed']);
$router->post('/payment/razorpay/webhook', [PaymentController::class, 'webhook'], ['middleware' => ['nocsrf']]);
$router->get('/payment/retry/{number}', [PaymentController::class, 'retry']);

// Orders
$router->get('/order/confirmation/{number}', [OrderController::class, 'confirmation']);
$router->get('/track', [OrderController::class, 'trackForm']);
$router->post('/track', [OrderController::class, 'trackRequest']);
$router->get('/track/{number}/{token}', [OrderController::class, 'trackView']);

// Auth
$router->get('/login', [AuthController::class, 'loginForm'], ['middleware' => ['guest']]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm'], ['middleware' => ['guest']]);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotForm']);
$router->post('/forgot-password', [AuthController::class, 'forgot']);
$router->get('/reset-password/{token}', [AuthController::class, 'resetForm']);
$router->post('/reset-password', [AuthController::class, 'reset']);

// Account
$router->get('/account', [AccountController::class, 'index'], ['middleware' => ['auth']]);
$router->post('/account/profile', [AccountController::class, 'updateProfile'], ['middleware' => ['auth']]);
$router->post('/account/password', [AccountController::class, 'updatePassword'], ['middleware' => ['auth']]);
$router->get('/account/addresses', [AccountController::class, 'addresses'], ['middleware' => ['auth']]);
$router->post('/account/addresses', [AccountController::class, 'addAddress'], ['middleware' => ['auth']]);
$router->post('/account/addresses/{id}/delete', [AccountController::class, 'deleteAddress'], ['middleware' => ['auth']]);
$router->get('/account/orders', [AccountController::class, 'orders'], ['middleware' => ['auth']]);
$router->get('/account/orders/{number}', [AccountController::class, 'order'], ['middleware' => ['auth']]);

// Reviews
$router->post('/reviews', [ReviewController::class, 'store'], ['middleware' => ['auth']]);

// Informational pages
$router->get('/about', [PageController::class, 'about']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [PageController::class, 'contactSubmit']);
$router->get('/faq', [PageController::class, 'faq']);
$router->get('/shipping-policy', [PageController::class, 'shipping']);
$router->get('/return-policy', [PageController::class, 'returns']);
$router->get('/privacy-policy', [PageController::class, 'privacy']);
$router->get('/terms', [PageController::class, 'terms']);
$router->get('/disclaimer', [PageController::class, 'disclaimer']);
$router->get('/sitemap.xml', [PageController::class, 'sitemap']);
$router->get('/health', [PageController::class, 'health']);

// Admin
$router->get('/admin/setup', [AdminAuthController::class, 'setupForm']);
$router->post('/admin/setup', [AdminAuthController::class, 'setup']);
$router->get('/admin/login', [AdminAuthController::class, 'loginForm']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->post('/admin/logout', [AdminAuthController::class, 'logout'], ['middleware' => ['admin']]);
$router->get('/admin', [DashboardController::class, 'index'], ['middleware' => ['admin']]);
$router->get('/admin/products', [AdminProductController::class, 'index'], ['middleware' => ['admin']]);
$router->get('/admin/products/new', [AdminProductController::class, 'create'], ['middleware' => ['admin']]);
$router->post('/admin/products/new', [AdminProductController::class, 'store'], ['middleware' => ['admin']]);
$router->get('/admin/products/{id}', [AdminProductController::class, 'edit'], ['middleware' => ['admin']]);
$router->post('/admin/products/{id}', [AdminProductController::class, 'update'], ['middleware' => ['admin']]);
$router->post('/admin/products/{id}/images', [AdminProductController::class, 'uploadImage'], ['middleware' => ['admin']]);
$router->post('/admin/products/{id}/images/{imageId}/delete', [AdminProductController::class, 'deleteImage'], ['middleware' => ['admin']]);
$router->get('/admin/orders', [AdminOrderController::class, 'index'], ['middleware' => ['admin']]);
$router->get('/admin/orders/export', [AdminOrderController::class, 'export'], ['middleware' => ['admin']]);
$router->get('/admin/orders/{id}', [AdminOrderController::class, 'show'], ['middleware' => ['admin']]);
$router->post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'], ['middleware' => ['admin']]);
$router->post('/admin/orders/{id}/tracking', [AdminOrderController::class, 'updateTracking'], ['middleware' => ['admin']]);
$router->get('/admin/reviews', [AdminReviewController::class, 'index'], ['middleware' => ['admin']]);
$router->post('/admin/reviews/{id}/status', [AdminReviewController::class, 'updateStatus'], ['middleware' => ['admin']]);
$router->get('/admin/messages', [AdminSettingsController::class, 'messages'], ['middleware' => ['admin']]);
$router->post('/admin/messages/{id}/read', [AdminSettingsController::class, 'markRead'], ['middleware' => ['admin']]);
$router->get('/admin/settings', [AdminSettingsController::class, 'index'], ['middleware' => ['admin']]);
$router->post('/admin/settings', [AdminSettingsController::class, 'save'], ['middleware' => ['admin']]);
$router->post('/admin/settings/logo', [AdminSettingsController::class, 'uploadLogo'], ['middleware' => ['admin']]);
