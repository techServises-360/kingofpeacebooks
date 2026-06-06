<?php
/**
 * Neon PostgreSQL Migration Script
 * Run this script to create all database tables
 */

// Database configuration (Neon) - pulled from environment variables
$host = $_ENV['NEON_DB_HOST'] ?? getenv('NEON_DB_HOST') ?? '';
$port = $_ENV['NEON_DB_PORT'] ?? getenv('NEON_DB_PORT') ?? '5432';
$dbname = $_ENV['NEON_DB_NAME'] ?? getenv('NEON_DB_NAME') ?? '';
$user = $_ENV['NEON_DB_USER'] ?? getenv('NEON_DB_USER') ?? '';
$password = $_ENV['NEON_DB_PASSWORD'] ?? getenv('NEON_DB_PASSWORD') ?? '';

if ($host === '' || $dbname === '' || $user === '' || $password === '') {
    echo "❌ Missing Neon database environment variables.\n";
    echo "Required: NEON_DB_HOST, NEON_DB_NAME, NEON_DB_USER, NEON_DB_PASSWORD (NEON_DB_PORT optional).\n";
    exit(1);
}

try {
    // Connect to Neon PostgreSQL
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connected to Neon PostgreSQL successfully!\n";

    // Get all migration files
    $migrationFiles = [
        '001_create_users_table.sql',
        '002_create_books_table.sql', 
        '003_create_orders_table.sql',
        '004_create_reviews_table.sql',
        '005_add_author_request_fields_to_users.sql',
        '006_add_public_id_to_books.sql'
    ];

    // Run each migration
    foreach ($migrationFiles as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (file_exists($filePath)) {
            echo "Running migration: $file\n";
            
            $sql = file_get_contents($filePath);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        echo "Error in $file: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            echo "Completed: $file\n";
        } else {
            echo "Migration file not found: $file\n";
        }
    }

    // Create migration tracking table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Ensure filename is unique so ON CONFLICT works
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS migrations_filename_unique ON migrations(filename)");

    // Mark migrations as executed
    foreach ($migrationFiles as $file) {
        $pdo->prepare("
            INSERT INTO migrations (filename) 
            VALUES (?) 
            ON CONFLICT (filename) DO NOTHING
        ")->execute([$file]);
    }

    echo "\n🎉 All migrations completed successfully!\n";
    echo "Database is ready for your bookstore!\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your Neon database credentials.\n";
    exit(1);
}
