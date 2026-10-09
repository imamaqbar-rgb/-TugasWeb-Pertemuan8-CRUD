<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();

$product = [];
$errors  = [];

try {
    [$categories, $suppliers] = fetch_lookups($pdo);
} catch (PDOException $e) {
    error_log('DB Error (create lookups): ' . $e->getMessage());
    flash('danger', 'Gagal memuat data kategori/supplier.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash('danger', 'Sesi form tidak valid. Silakan ulangi.');
        redirect('create.php');
    }

    [$clean, $errors] = validate_product($_POST);
    $product = $_POST;                      // tampilkan input mentah jika ada error

    if (!$errors) {
        try {
            // CREATE — prepared statement: nilai diikat sebagai DATA, bukan kode SQL
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category_id, supplier_id, price, stock)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $clean['name'],
                $clean['category_id'],
                $clean['supplier_id'],
                $clean['price'],
                $clean['stock'],
            ]);

            flash('success', 'Produk “' . $clean['name'] . '” berhasil ditambahkan.');
            redirect('index.php');          // Post/Redirect/Get
        } catch (PDOException $e) {
            error_log('DB Error (create): ' . $e->getMessage());
            $errors['form'] = $e->getCode() === '23000'
                ? 'Kategori atau supplier yang dipilih tidak valid.'
                : 'Gagal menyimpan produk. Silakan coba lagi.';
        }
    }
}

$pageTitle   = 'Tambah Produk';
$action      = 'create.php';
$submitLabel = 'Simpan Produk';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div>
    <h1>Tambah Produk</h1>
    <p class="muted">Isi data produk baru lalu simpan.</p>
  </div>
</div>

<?php if (isset($errors['form'])): ?>
  <div class="alert alert--danger"><?= e($errors['form']) ?></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
