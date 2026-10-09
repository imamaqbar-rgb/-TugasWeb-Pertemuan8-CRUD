<?php
/**
 * Form produk (dipakai create.php & edit.php).
 *
 * @var array  $product     nilai field saat ini (kosong untuk create)
 * @var array  $errors      pesan error per field
 * @var array  $categories  dropdown kategori
 * @var array  $suppliers   dropdown supplier
 * @var string $action      URL tujuan form
 * @var string $submitLabel teks tombol
 */
?>
<form method="POST" action="<?= e($action) ?>" class="card form" novalidate>
  <?= csrf_field() ?>

  <div class="field <?= isset($errors['name']) ? 'field--error' : '' ?>">
    <label for="name">Nama Produk</label>
    <input type="text" id="name" name="name" maxlength="150" required
           value="<?= e($product['name'] ?? '') ?>" placeholder="mis. Mouse Wireless">
    <?php if (isset($errors['name'])): ?><p class="field__error"><?= e($errors['name']) ?></p><?php endif; ?>
  </div>

  <div class="grid-2">
    <div class="field <?= isset($errors['category_id']) ? 'field--error' : '' ?>">
      <label for="category_id">Kategori</label>
      <select id="category_id" name="category_id" required>
        <option value="">— Pilih kategori —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['id']) ?>"
            <?= (int) ($product['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['category_id'])): ?><p class="field__error"><?= e($errors['category_id']) ?></p><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['supplier_id']) ? 'field--error' : '' ?>">
      <label for="supplier_id">Supplier</label>
      <select id="supplier_id" name="supplier_id" required>
        <option value="">— Pilih supplier —</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= e($s['id']) ?>"
            <?= (int) ($product['supplier_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['supplier_id'])): ?><p class="field__error"><?= e($errors['supplier_id']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="grid-2">
    <div class="field <?= isset($errors['price']) ? 'field--error' : '' ?>">
      <label for="price">Harga (Rp)</label>
      <input type="number" id="price" name="price" min="0" step="0.01" required
             value="<?= e($product['price'] ?? '') ?>" placeholder="0">
      <?php if (isset($errors['price'])): ?><p class="field__error"><?= e($errors['price']) ?></p><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['stock']) ? 'field--error' : '' ?>">
      <label for="stock">Stok</label>
      <input type="number" id="stock" name="stock" min="0" step="1" required
             value="<?= e($product['stock'] ?? '0') ?>">
      <?php if (isset($errors['stock'])): ?><p class="field__error"><?= e($errors['stock']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="form__actions">
    <a class="btn btn--ghost" href="index.php">Batal</a>
    <button type="submit" class="btn btn--primary"><?= e($submitLabel) ?></button>
  </div>
</form>
