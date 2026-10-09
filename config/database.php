<?php
declare(strict_types=1);

/**
 * Database — koneksi PDO dengan Singleton pattern.
 *
 * Hanya ada SATU koneksi per request:
 *   $pdo = Database::getInstance()->getConnection();
 *
 * Kredensial dibaca dari file .env (lihat .env.example) atau variabel
 * environment, dengan nilai default untuk Laragon/XAMPP lokal.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    // Private: tidak bisa `new Database()` dari luar kelas
    private function __construct()
    {
        $this->loadEnv(__DIR__ . '/../.env');

        $host = $this->env('DB_HOST', 'localhost');
        $port = $this->env('DB_PORT', '3306');
        $name = $this->env('DB_NAME', 'inventaris_db');
        $user = $this->env('DB_USER', 'root');
        $pass = $this->env('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $options = [
            // 1. Lempar exception saat error (bisa ditangkap try-catch)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // 2. Hasil query berupa associative array
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // 3. Pakai real prepared statement (bukan emulasi)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Detail error hanya ke log — jangan bocor ke pengguna
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Tidak dapat terhubung ke database. Periksa konfigurasi server.');
        }
    }

    // Singleton: cegah duplikasi lewat clone / unserialize
    private function __clone() {}

    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize a singleton.');
    }

    /** Satu-satunya cara mengambil instance. */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }

    /** Baca variabel environment dengan nilai default. */
    private function env(string $key, string $default): string
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    /** Loader .env sederhana: baris KEY=VALUE, komentar diawali #. */
    private function loadEnv(string $file): void
    {
        if (!is_readable($file)) {
            return;
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (getenv($key) === false) {
                putenv($key . '=' . trim($value, " \t\"'"));
            }
        }
    }
}
