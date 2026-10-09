<?php
/** @var string $pageTitle */
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Inventaris') ?> · Inventaris Barang</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="topbar">
    <div class="container topbar__inner">
      <a class="brand" href="index.php">
        <span class="brand__mark" aria-hidden="true">▣</span>
        Inventaris<span class="brand__dim">Barang</span>
      </a>
      <a class="btn btn--primary" href="create.php">+ Tambah Produk</a>
    </div>
  </header>

  <main class="container">
    <?php if ($flash): ?>
      <div class="alert alert--<?= e($flash['type']) ?>" role="status">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
