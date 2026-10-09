<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/** Escape output HTML (pencegahan XSS) — dipakai untuk SEMUA data ke HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Format angka ke Rupiah: 15000000 -> Rp 15.000.000 */
function rupiah(float|int|string $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/** Redirect lalu hentikan script (Post/Redirect/Get pattern). */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------------------- Flash message ---------------------- */

/** Simpan pesan satu kali tampil. $type: success | danger */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil lalu hapus pesan flash. */
function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ------------------------- CSRF ---------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

/* ----------------------- Validasi -------------------------- */

/**
 * Validasi & bersihkan input form produk.
 * @return array{0: array, 1: array} [data bersih, daftar error]
 */
function validate_product(array $input): array
{
    $errors = [];

    $name = trim((string) ($input['name'] ?? ''));
    if ($name === '') {
        $errors['name'] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($name) > 150) {
        $errors['name'] = 'Nama produk maksimal 150 karakter.';
    }

    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($categoryId === false) {
        $errors['category_id'] = 'Pilih kategori.';
    }

    $supplierId = filter_var($input['supplier_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($supplierId === false) {
        $errors['supplier_id'] = 'Pilih supplier.';
    }

    $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($price === false || $price < 0) {
        $errors['price'] = 'Harga harus berupa angka 0 atau lebih.';
    } elseif ($price > 9999999999.99) {
        $errors['price'] = 'Harga terlalu besar.';
    }

    $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($stock === false) {
        $errors['stock'] = 'Stok harus berupa bilangan bulat 0 atau lebih.';
    }

    $data = [
        'name'        => $name,
        'category_id' => (int) $categoryId,
        'supplier_id' => (int) $supplierId,
        'price'       => (float) $price,
        'stock'       => (int) $stock,
    ];

    return [$data, $errors];
}

/** Ambil daftar kategori & supplier untuk dropdown. */
function fetch_lookups(PDO $pdo): array
{
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
    return [$categories, $suppliers];
}
