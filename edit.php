<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();

// ID wajib bilangan bulat positif — selain itu ditolak
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    flash('danger', 'ID produk tidak valid.');
    redirect('index.php');
}

$errors = [];

try {
    // Ambil data lama untuk mengisi form (pre-filled)
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        flash('danger', 'Produk tidak ditemukan.');
        redirect('index.php');
    }

    [$categories, $suppliers] = fetch_lookups($pdo);
} catch (PDOException $e) {
    error_log('DB Error (edit load): ' . $e->getMessage());
    flash('danger', 'Terjadi kesalahan database. Silakan coba lagi.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash('danger', 'Sesi form tidak valid. Silakan ulangi.');
        redirect('edit.php?id=' . $id);
    }

    [$input, $errors] = validate_product($_POST);
    $product = array_merge($product, $_POST);   // tampilkan input mentah jika ada error

    if (!$errors) {
        try {
            // UPDATE — prepared statement
            $stmt = $pdo->prepare(
                'UPDATE products
                 SET name = ?, category_id = ?, supplier_id = ?, price = ?, stock = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $input['name'],
                $input['category_id'],
                $input['supplier_id'],
                $input['price'],
                $input['stock'],
                $id,
            ]);

            flash('success', 'Produk “' . $input['name'] . '” berhasil diperbarui.');
            redirect('index.php');
        } catch (PDOException $e) {
            error_log('DB Error (update): ' . $e->getMessage());
            $errors['form'] = $e->getCode() === '23000'
                ? 'Kategori atau supplier yang dipilih tidak valid.'
                : 'Gagal memperbarui produk. Silakan coba lagi.';
        }
    }
}

$pageTitle   = 'Edit Produk';
$action      = 'edit.php?id=' . $id;
$submitLabel = 'Simpan Perubahan';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div>
    <h1>Edit Produk</h1>
    <p class="muted">Ubah data produk #<?= e($id) ?>.</p>
  </div>
</div>

<?php if (isset($errors['form'])): ?>
  <div class="alert alert--danger"><?= e($errors['form']) ?></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
