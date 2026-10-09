-- ============================================================
--  schema.sql — Database inventaris_db (3NF)
--  Tugas Rutin 8 · Pemrograman Web · FMIPA UNIMED
--
--  Cara pakai:
--    phpMyAdmin : tab "Import" -> pilih file ini -> Go
--    Terminal   : mysql -u root -p < schema.sql
--
--  CATATAN: tabel lama (jika ada) akan di-DROP lalu dibuat ulang,
--  sehingga file ini aman di-import berulang kali.
-- ============================================================

CREATE DATABASE IF NOT EXISTS inventaris_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

-- Urutan DROP: tabel anak (products) dulu, baru tabel induk
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;

-- ------------------------------------------------------------
--  DDL — Struktur tabel
-- ------------------------------------------------------------
CREATE TABLE categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suppliers (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL,
  phone VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150)  NOT NULL,
  category_id INT           NOT NULL,
  supplier_id INT           NOT NULL,
  price       DECIMAL(12,2) NOT NULL,
  stock       INT           NOT NULL DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  DML — Data seed (minimal 5 baris per tabel)
-- ------------------------------------------------------------
INSERT INTO categories (name) VALUES
  ('Aksesoris'),
  ('Display'),
  ('Komputer'),
  ('Jaringan'),
  ('Penyimpanan');

INSERT INTO suppliers (name, phone) VALUES
  ('PT ABC Teknologi',     '0812-3456-7801'),
  ('CV Maju Jaya',         '0813-2233-4402'),
  ('Toko Medan Komputer',  '0821-9988-7703'),
  ('PT Sumatera Digital',  '0852-1122-3304'),
  ('UD Nusantara Elektro', '0877-5566-7705');

INSERT INTO products (name, category_id, supplier_id, price, stock) VALUES
  ('Mouse Wireless Logitech M331',   1, 1,   185000.00, 25),
  ('Keyboard Mekanik Rexus',         1, 1,   450000.00, 12),
  ('Monitor LED 24 inci',            2, 2,  1850000.00,  8),
  ('Monitor Gaming 27 inci 144Hz',   2, 2,  3200000.00,  4),
  ('Laptop Asus Vivobook 14',        3, 3, 8500000.00,   6),
  ('PC Rakitan Core i5',             3, 3, 7200000.00,   3),
  ('Router TP-Link Archer C6',       4, 4,   520000.00, 15),
  ('Switch Hub 8 Port Gigabit',      4, 4,   310000.00, 10),
  ('SSD Kingston 480GB',             5, 5,   620000.00, 30),
  ('Flashdisk SanDisk 64GB',         5, 5,    95000.00, 50);
