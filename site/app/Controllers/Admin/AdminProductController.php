<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Product;
use App\Services\UploadService;

final class AdminProductController
{
    private const FIELDS = ['title', 'subtitle', 'short_description', 'description', 'sku', 'ingredients', 'usage_directions', 'warnings', 'storage_instructions', 'manufacturer_info', 'verification_notes', 'seo_title', 'seo_description'];

    public function index(): void
    {
        View::show('admin/products/index', ['products' => Product::all(), 'meta' => ['title' => 'Products']], 'admin');
    }

    public function create(): void
    {
        View::show('admin/products/form', ['product' => null, 'images' => [], 'variants' => [], 'meta' => ['title' => 'New product']], 'admin');
    }

    public function store(): void
    {
        [$data, $errors] = $this->collect();
        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            Session::set('_old', $_POST);
            redirect('/admin/products/new');
        }
        $data['slug'] = $this->uniqueSlug((string) Request::input('slug') ?: $data['title']);
        $id = DB::insert('products', $data);
        Session::flash('success', 'Product created.');
        redirect('/admin/products/' . $id);
    }

    public function edit(string $id): void
    {
        $product = Product::find((int) $id) ?: abort(404);
        View::show('admin/products/form', [
            'product' => $product,
            'images' => Product::images((int) $id),
            'variants' => Product::variants((int) $id),
            'meta' => ['title' => 'Edit: ' . $product['title']],
        ], 'admin');
    }

    public function update(string $id): void
    {
        $product = Product::find((int) $id) ?: abort(404);
        [$data, $errors] = $this->collect();
        if ($errors) {
            Session::flash('error', implode(' ', $errors));
            redirect('/admin/products/' . $id);
        }
        $slug = (string) Request::input('slug', '');
        if ($slug !== '' && $slug !== $product['slug']) {
            $data['slug'] = $this->uniqueSlug($slug, (int) $id);
        }
        DB::update('products', $data, 'id = ?', [(int) $id]);
        foreach ((array) Request::input('variants', []) as $vid => $v) {
            if (!is_array($v)) {
                continue;
            }
            DB::update('product_variants', [
                'name' => mb_substr(trim((string) ($v['name'] ?? '')), 0, 190),
                'tablet_count' => max(0, (int) ($v['tablet_count'] ?? 0)),
                'description' => trim((string) ($v['description'] ?? '')),
                'is_sellable' => empty($v['is_sellable']) ? 0 : 1,
            ], 'id = ? AND product_id = ?', [(int) $vid, (int) $id]);
        }
        foreach ((array) Request::input('image_alt', []) as $iid => $alt) {
            DB::update('product_images', ['alt_text' => mb_substr(trim((string) $alt), 0, 255)], 'id = ? AND product_id = ?', [(int) $iid, (int) $id]);
        }
        foreach ((array) Request::input('image_order', []) as $iid => $ord) {
            DB::update('product_images', ['sort_order' => (int) $ord], 'id = ? AND product_id = ?', [(int) $iid, (int) $id]);
        }
        Session::flash('success', 'Product saved.');
        redirect('/admin/products/' . $id);
    }

    public function uploadImage(string $id): void
    {
        Product::find((int) $id) ?: abort(404);
        try {
            $path = UploadService::storeImage($_FILES['image'] ?? null, 'products');
            $max = (int) DB::value('SELECT COALESCE(MAX(sort_order),0) FROM product_images WHERE product_id = ?', [(int) $id]);
            DB::insert('product_images', ['product_id' => (int) $id, 'path' => $path, 'alt_text' => mb_substr(trim((string) Request::input('alt_text', '')), 0, 255), 'sort_order' => $max + 1]);
            Session::flash('success', 'Image uploaded.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/products/' . $id);
    }

    public function deleteImage(string $id, string $imageId): void
    {
        $img = DB::one('SELECT * FROM product_images WHERE id = ? AND product_id = ?', [(int) $imageId, (int) $id]);
        if ($img) {
            DB::run('DELETE FROM product_images WHERE id = ?', [$img['id']]);
            if (str_starts_with($img['path'], 'uploads/')) {
                @unlink(ROOT_PATH . '/' . $img['path']);
            }
        }
        Session::flash('success', 'Image removed.');
        redirect('/admin/products/' . $id);
    }

    private function collect(): array
    {
        $data = [];
        $errors = [];
        foreach (self::FIELDS as $f) {
            $v = trim((string) Request::input($f, ''));
            $data[$f] = $v === '' ? null : $v;
        }
        if (mb_strlen((string) $data['title']) < 2) {
            $errors[] = 'Title is required.';
        }
        $price = (float) str_replace(',', '', (string) Request::input('price', '0'));
        if ($price <= 0) {
            $errors[] = 'Price must be greater than zero.';
        }
        $data['price'] = round($price, 2);
        $cmp = trim((string) Request::input('compare_at_price', ''));
        $data['compare_at_price'] = $cmp === '' ? null : round((float) str_replace(',', '', $cmp), 2);
        $data['stock'] = max(0, (int) Request::input('stock', 0));
        $data['bottle_count'] = max(1, (int) Request::input('bottle_count', 1));
        $data['tablet_count'] = max(0, (int) Request::input('tablet_count', 0));
        $data['is_active'] = Request::input('is_active') ? 1 : 0;
        $data['is_featured'] = Request::input('is_featured') ? 1 : 0;
        $data['is_combo'] = Request::input('is_combo') ? 1 : 0;
        return [$data, $errors];
    }

    private function uniqueSlug(string $base, int $ignoreId = 0): string
    {
        $slug = slugify($base);
        $candidate = $slug;
        $i = 2;
        while (DB::value('SELECT COUNT(*) FROM products WHERE slug = ? AND id <> ?', [$candidate, $ignoreId]) > 0) {
            $candidate = $slug . '-' . $i++;
        }
        return $candidate;
    }
}
