<?php

namespace Database;

class Migrator
{
    protected $koneksi;
    protected $migrationsPath;

    public function __construct($koneksi, $migrationsPath)
    {
        $this->koneksi = $koneksi;
        $this->migrationsPath = $migrationsPath;
    }

    /**
     * Run all pending migrations
     */
    public function migrate()
    {
        echo "Starting migration...\n";
        echo str_repeat("=", 60) . "\n\n";

        // Create migrations table if not exists
        $this->createMigrationsTable();

        // Get all migration files
        $migrations = $this->getMigrationFiles();

        if (empty($migrations)) {
            echo "No migration files found.\n";
            return;
        }

        // Get already migrated
        $migrated = $this->getMigratedList();

        $count = 0;
        foreach ($migrations as $file) {
            $migrationName = str_replace('.php', '', basename($file));

            if (in_array($migrationName, $migrated)) {
                echo "⊘ SKIPPED  | {$migrationName}\n";
                continue;
            }

            try {
                // Include and instantiate migration class
                $class = $this->getMigrationClass($file);
                $migration = new $class($this->koneksi);

                echo "⟳ RUNNING  | {$migrationName}...";

                // Execute up() method
                $migration->up();

                // Record migration
                $this->recordMigration($migrationName);

                echo " ✓ DONE\n";
                $count++;
            } catch (\Exception $e) {
                echo " ✗ FAILED\n";
                echo "Error: " . $e->getMessage() . "\n\n";
                return;
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Migration completed! {$count} new migration(s) executed.\n";
    }

    /**
     * Rollback the last migration batch
     */
    public function rollback()
    {
        echo "Starting rollback...\n";
        echo str_repeat("=", 60) . "\n\n";

        // Get last batch migrations
        $sql = "SELECT TOP 1 batch FROM migrations ORDER BY batch DESC";
        $stmt = sqlsrv_query($this->koneksi, $sql);

        if ($stmt === false) {
            echo "Error: Could not retrieve migration batch.\n";
            return;
        }

        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if (!$result) {
            echo "No migrations to rollback.\n";
            return;
        }

        $batch = $result['batch'];

        $sql = "SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC";
        $params = array($batch);
        $stmt = sqlsrv_query($this->koneksi, $sql, $params);

        if ($stmt === false) {
            echo "Error: " . print_r(sqlsrv_errors(), true) . "\n";
            return;
        }

        $count = 0;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $migrationName = $row['migration'];
            $file = $this->migrationsPath . '/' . $migrationName . '.php';

            if (!file_exists($file)) {
                echo "✗ SKIP   | {$migrationName} (file not found)\n";
                continue;
            }

            try {
                echo "⟳ ROLLING | {$migrationName}...";

                $class = $this->getMigrationClass($file);
                $migration = new $class($this->koneksi);
                $migration->down();

                $this->deleteMigration($migrationName);

                echo " ✓ DONE\n";
                $count++;
            } catch (\Exception $e) {
                echo " ✗ FAILED\n";
                echo "Error: " . $e->getMessage() . "\n\n";
                return;
            }
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Rollback completed! {$count} migration(s) reversed.\n";
    }

    /**
     * Reset database (rollback all, then migrate)
     */
    public function refresh()
    {
        echo "Refreshing migrations...\n";
        echo str_repeat("=", 60) . "\n\n";

        // Rollback all
        $sql = "SELECT COUNT(*) as count FROM migrations";
        $stmt = sqlsrv_query($this->koneksi, $sql);
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($result['count'] > 0) {
            echo "Rolling back all migrations...\n\n";
            while (true) {
                $sql = "SELECT TOP 1 batch FROM migrations ORDER BY batch DESC";
                $stmt = sqlsrv_query($this->koneksi, $sql);
                $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

                if (!$result) break;

                ob_start();
                $this->rollback();
                ob_end_clean();
            }
        }

        echo "\nRunning all migrations...\n\n";
        $this->migrate();
    }

    /**
     * Get list of migration files
     */
    protected function getMigrationFiles()
    {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    /**
     * Get list of already migrated
     */
    protected function getMigratedList()
    {
        $sql = "SELECT migration FROM migrations ORDER BY batch, id";
        $stmt = sqlsrv_query($this->koneksi, $sql);

        if ($stmt === false) {
            return array();
        }

        $migrated = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $migrated[] = $row['migration'];
        }

        return $migrated;
    }

    /**
     * Get migration class from file
     */
    protected function getMigrationClass($file)
    {
        $filename = basename($file, '.php');
        
        // Include the file first
        require_once $file;
        
        // Convert file name from snake_case to CamelCase
        // Skip all leading numeric parts (timestamp) and extract class name
        // Example: 2026_02_02_100001_create_users_table -> CreateUsersTable
        $parts = explode('_', $filename);
        
        // Find where numeric parts end
        $startIndex = 0;
        foreach ($parts as $i => $part) {
            if (!is_numeric($part)) {
                $startIndex = $i;
                break;
            }
        }
        
        // Get remaining parts and build class name
        $classNameParts = array_slice($parts, $startIndex);
        
        $className = '';
        foreach ($classNameParts as $part) {
            if (!empty($part)) {
                $className .= ucfirst($part);
            }
        }

        // Try both with and without namespace
        $namespaces = array('Database\\Migrations\\', '');
        
        foreach ($namespaces as $ns) {
            $fullClassName = $ns . $className;
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }

        throw new \Exception("Migration class not found for file: {$filename}. Expected class: {$className}");
    }

    /**
     * Create migrations tracking table
     */
    protected function createMigrationsTable()
    {
        $sql = "
            IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'migrations')
            BEGIN
                CREATE TABLE migrations (
                    id INT PRIMARY KEY IDENTITY(1,1),
                    migration NVARCHAR(255) NOT NULL UNIQUE,
                    batch INT NOT NULL,
                    executed_at DATETIME DEFAULT GETDATE()
                )
            END
        ";

        sqlsrv_query($this->koneksi, $sql);
    }

    /**
     * Record migration as executed
     */
    protected function recordMigration($migrationName)
    {
        $sql = "SELECT MAX(batch) as max_batch FROM migrations";
        $stmt = sqlsrv_query($this->koneksi, $sql);
        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $batch = ($result['max_batch'] ?? 0) + 1;

        $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
        $params = array($migrationName, $batch);

        sqlsrv_query($this->koneksi, $sql, $params);
    }

    /**
     * Delete migration record
     */
    protected function deleteMigration($migrationName)
    {
        $sql = "DELETE FROM migrations WHERE migration = ?";
        $params = array($migrationName);

        sqlsrv_query($this->koneksi, $sql, $params);
    }

    /**
     * Show migrations status
     */
    public function status()
    {
        echo "Migration Status\n";
        echo str_repeat("=", 60) . "\n";
        echo str_pad("Migration", 45) . " | Batch | Time\n";
        echo str_repeat("-", 60) . "\n";

        $sql = "SELECT * FROM migrations ORDER BY batch, id";
        $stmt = sqlsrv_query($this->koneksi, $sql);

        if ($stmt === false) {
            echo "No migrations found.\n";
            return;
        }

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $name = str_pad($row['migration'], 45);
            $batch = str_pad($row['batch'], 5);
            $time = $row['executed_at']->format('Y-m-d H:i:s');
            echo "{$name} | {$batch} | {$time}\n";
        }

        echo str_repeat("=", 60) . "\n";
    }
}
