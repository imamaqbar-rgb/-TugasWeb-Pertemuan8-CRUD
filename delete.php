<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

// DELETE hanya boleh lewat POST (aman dari penghapusan via link/crawler)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!csrf_valid()) {
    flash('danger', 'Sesi form tidak valid. Penghapusan dibatalkan.');
    redirect('index.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    flash('danger', 'ID produk tidak valid.');
    redirect('index.php');
}

$pdo = Database::getInstance()->getConnection();

try {
    // DELETE — prepared statement
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        flash('danger', 'Produk tidak ditemukan atau sudah dihapus.');
    } else {
        flash('success', 'Produk berhasil dihapus.');
    }
} catch (PDOException $e) {
    error_log('DB Error (delete): ' . $e->getMessage());
    flash('danger', 'Gagal menghapus produk. Silakan coba lagi.');
}

redirect('index.php');
