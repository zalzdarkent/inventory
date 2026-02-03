<?php

/**
 * Database Migration Runner
 * 
 * Usage:
 *   php migrate.php              - Run pending migrations
 *   php migrate.php migrate      - Run pending migrations (explicit)
 *   php migrate.php rollback     - Rollback last migration batch
 *   php migrate.php refresh      - Rollback all and re-run migrations
 *   php migrate.php status       - Show migration status
 *   php migrate.php --help       - Show help message
 */

// Load configuration
require_once __DIR__ . '/config/koneksi.php';

// Load migrator class
require_once __DIR__ . '/database/Migration.php';
require_once __DIR__ . '/database/Migrator.php';

use Database\Migrator;

// Check if connection is successful
if (!$koneksi) {
    echo "✗ ERROR: Cannot connect to database\n";
    die;
}

// Initialize migrator
$migrationsPath = __DIR__ . '/database/migrations';
$migrator = new Migrator($koneksi, $migrationsPath);

// Get command from arguments
$command = isset($argv[1]) ? $argv[1] : 'migrate';

// Handle commands
switch ($command) {
    case 'migrate':
        $migrator->migrate();
        break;

    case 'rollback':
        $migrator->rollback();
        break;

    case 'refresh':
        $migrator->refresh();
        break;

    case 'status':
        $migrator->status();
        break;

    case '--help':
    case '-h':
    case 'help':
        showHelp();
        break;

    default:
        echo "✗ Unknown command: {$command}\n\n";
        showHelp();
        exit(1);
}

// Close connection
sqlsrv_close($koneksi);
echo "\nConnection closed.\n";

function showHelp()
{
    echo <<<EOT
╔════════════════════════════════════════════════════════════╗
║         Inventory Database Migration Tool                  ║
╚════════════════════════════════════════════════════════════╝

USAGE:
  php migrate.php [command]

COMMANDS:
  migrate              Run all pending migrations (default)
  rollback             Rollback the last migration batch
  refresh              Rollback all migrations then re-run them
  status               Display current migration status
  --help, -h, help     Show this help message

EXAMPLES:
  php migrate.php                # Run pending migrations
  php migrate.php migrate        # Same as above
  php migrate.php status         # Show status
  php migrate.php rollback       # Rollback last batch
  php migrate.php refresh        # Reset database

MIGRATION FILES:
  Migration files are stored in: database/migrations/
  Format: YYYY_MM_DD_HHmmss_action_name.php
  
  Example migration file:
    2026_02_02_100001_create_users_table.php

═════════════════════════════════════════════════════════════

EOT;
}
