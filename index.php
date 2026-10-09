<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

$pdo = Database::getInstance()->getConnection();

$q        = trim((string) ($_GET['q'] ?? ''));
$products = [];
$error    = null;

try {
    // READ — JOIN 3 tabel (products, categories, suppliers)
    $sql = 'SELECT p.id, p.name, p.price, p.stock,
                   c.name AS category,
                   s.name AS supplier
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers  s ON p.supplier_id = s.id';
    $params = [];

    // Bonus: pencarian — tetap pakai prepared statement
    if ($q !== '') {
        $sql .= ' WHERE p.name LIKE :q1 OR c.name LIKE :q2 OR s.name LIKE :q3';
        $like   = '%' . addcslashes($q, '%_\\') . '%';
        $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
    }
    $sql .= ' ORDER BY p.name';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (index): ' . $e->getMessage());
    $error = 'Terjadi kesalahan database. Silakan coba lagi.';
}

$totalItems = count($products);
$totalStock = array_sum(array_column($products, 'stock'));
$totalValue = 0.0;
foreach ($products as $p) {
    $totalValue += (float) $p['price'] * (int) $p['stock'];
}

$pageTitle = 'Daftar Produk';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div>
    <h1>Daftar Produk</h1>
    <p class="muted">Kelola stok barang beserta kategori dan supplier-nya.</p>
  </div>

  <form method="GET" action="index.php" class="search" role="search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari produk, kategori, supplier…" aria-label="Cari">
    <button type="submit" class="btn">Cari</button>
    <?php if ($q !== ''): ?><a class="btn btn--ghost" href="index.php">Reset</a><?php endif; ?>
  </form>
</div>

<?php if ($error): ?>
  <div class="alert alert--danger"><?= e($error) ?></div>
<?php else: ?>

  <section class="stats" aria-label="Ringkasan">
    <div class="stat"><span class="stat__label">Jumlah Produk</span><strong><?= e($totalItems) ?></strong></div>
    <div class="stat"><span class="stat__label">Total Stok</span><strong><?= e(number_format($totalStock, 0, ',', '.')) ?></strong></div>
    <div class="stat"><span class="stat__label">Nilai Persediaan</span><strong><?= e(rupiah($totalValue)) ?></strong></div>
  </section>

  <div class="card table-wrap">
    <table>
      <thead>
        <tr>
          <th class="num">#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Supplier</th>
          <th class="num">Harga</th>
          <th class="num">Stok</th>
          <th class="actions">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$products): ?>
        <tr>
          <td colspan="7" class="empty">
            <?= $q !== '' ? 'Tidak ada produk yang cocok dengan “' . e($q) . '”.' : 'Belum ada produk. Tambahkan produk pertama Anda.' ?>
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($products as $i => $p): ?>
        <tr>
          <td class="num"><?= e($i + 1) ?></td>
          <td class="strong"><?= e($p['name']) ?></td>
          <td><span class="tag"><?= e($p['category']) ?></span></td>
          <td><?= e($p['supplier']) ?></td>
          <td class="num"><?= e(rupiah($p['price'])) ?></td>
          <td class="num">
            <span class="stock <?= (int) $p['stock'] <= 5 ? 'stock--low' : '' ?>"><?= e($p['stock']) ?></span>
          </td>
          <td class="actions">
            <a class="btn btn--sm" href="edit.php?id=<?= e($p['id']) ?>">Edit</a>

            <!-- DELETE lewat POST + konfirmasi, bukan link GET -->
            <form method="POST" action="delete.php" class="inline"
                  data-confirm="Hapus produk “<?= e($p['name']) ?>”?"
                  onsubmit="return confirm(this.dataset.confirm)">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= e($p['id']) ?>">
              <button type="submit" class="btn btn--sm btn--danger">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
