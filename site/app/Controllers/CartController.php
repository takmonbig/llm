<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Services\CartService;

final class CartController
{
    public function page(): void
    {
        View::show('cart', ['cart' => CartService::summary(), 'meta' => ['title' => 'Your Cart | ' . setting('store_name'), 'canonical' => url('cart')]]);
    }

    public function state(): void
    {
        json_response(['ok' => true, 'cart' => CartService::summary()]);
    }

    public function add(): void
    {
        $this->respond(fn() => CartService::add((int) Request::input('product_id'), (int) Request::input('quantity', 1)), 'Added to your cart.');
    }

    public function update(): void
    {
        $this->respond(fn() => CartService::setQuantity((int) Request::input('item_id'), (int) Request::input('quantity', 1)), 'Cart updated.');
    }

    public function remove(): void
    {
        $this->respond(fn() => CartService::remove((int) Request::input('item_id')), 'Item removed.');
    }

    public function buyNow(): void
    {
        $productId = (int) Request::input('product_id');
        $qty = max(1, min(CartService::MAX_QTY, (int) Request::input('quantity', 1)));
        try {
            $cart = CartService::cart();
            \App\Core\DB::run('DELETE FROM cart_items WHERE cart_id = ?', [$cart['id']]);
            CartService::add($productId, $qty);
        } catch (\InvalidArgumentException $e) {
            if (Request::wantsJson()) {
                json_response(['ok' => false, 'error' => $e->getMessage()], 422);
            }
            Session::flash('error', $e->getMessage());
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
        if (Request::wantsJson()) {
            json_response(['ok' => true, 'redirect' => url('checkout')]);
        }
        redirect('/checkout');
    }

    private function respond(callable $fn, string $message): void
    {
        try {
            $cart = $fn();
        } catch (\InvalidArgumentException $e) {
            if (Request::wantsJson()) {
                json_response(['ok' => false, 'error' => $e->getMessage(), 'cart' => CartService::summary()], 422);
            }
            Session::flash('error', $e->getMessage());
            redirect($_SERVER['HTTP_REFERER'] ?? '/cart');
        }
        if (Request::wantsJson()) {
            json_response(['ok' => true, 'message' => $message, 'cart' => $cart]);
        }
        Session::flash('success', $message);
        redirect('/cart');
    }
}
