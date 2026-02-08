<?php
require_once __DIR__ . '/../../query/query.php';

/**
 * INVENTORY DATA SEEDER
 * Populates database with realistic test data for dashboard visualization.
 */

try {
    // 1. Ensure User exists or get first user
    $sql_user = "SELECT TOP 1 id FROM users";
    $stmt_user = sqlsrv_query($koneksi, $sql_user);
    $user_row = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC);
    $user_id = $user_row ? $user_row['id'] : null;

    if (!$user_id) {
        throw new Exception("No user found in database. Please register/login first.");
    }

    echo "<h3>Seeding Progress:</h3>";
    echo "Using User ID: $user_id<br>";

    // 2. Seed Locations (Targeting few locations as requested)
    $locations = ["Gudang Utama", "Rak A1", "Rak B2", "Pameran Lt.1", "Area Transit"];
    $location_ids = [];
    foreach ($locations as $loc) {
        $sql_check = "SELECT id FROM item_location WHERE location = ?";
        $stmt_check = sqlsrv_query($koneksi, $sql_check, [$loc]);
        $row_check = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);
        
        if ($row_check) {
            $location_ids[] = $row_check['id'];
        } else {
            insert_location($loc);
            $sql_id = "SELECT TOP 1 id FROM item_location ORDER BY id DESC";
            $stmt_id = sqlsrv_query($koneksi, $sql_id);
            $row_id = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);
            $location_ids[] = $row_id['id'];
        }
    }
    echo "✓ Locations seeded (" . count($location_ids) . ")<br>";

    // 3. Seed Items (Targeting many items)
    $item_types = ["CPU", "Monitor", "Keyboard", "Mouse", "Laptop", "Switch", "Router", "Cable", "Webcam", "Headset"];
    $item_brands = ["Dell", "HP", "Asus", "Logitech", "Cisco", "TP-Link", "Xiaomi", "Apple"];
    $item_ids = [];
    
    for ($i = 1; $i <= 50; $i++) {
        $name = $item_brands[array_rand($item_brands)] . " " . $item_types[array_rand($item_types)] . " Gen-" . rand(1, 9);
        $code = "ITM-" . str_pad($i, 3, '0', STR_PAD_LEFT);
        
        $sql_check = "SELECT id FROM item_table WHERE item_code = ?";
        $stmt_check = sqlsrv_query($koneksi, $sql_check, [$code]);
        $row_check = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);
        
        if ($row_check) {
            $item_ids[] = $row_check['id'];
        } else {
            insert_item($code, $name, null, "Auto-generated test data item #$i");
            $sql_id = "SELECT TOP 1 id FROM item_table ORDER BY id DESC";
            $stmt_id = sqlsrv_query($koneksi, $sql_id);
            $row_id = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);
            $item_ids[] = $row_id['id'];
        }
    }
    echo "✓ Items seeded (" . count($item_ids) . ")<br>";

    // 4. Link many items to few locations (Junction Table)
    foreach ($item_ids as $item_id) {
        // Assign each item to 1 or 2 random locations
        $assigned_locs = (array)array_rand($location_ids, rand(1, 2));
        foreach ($assigned_locs as $loc_index) {
            $loc_id = $location_ids[$loc_index];
            $sql_check = "SELECT id FROM item_table_locations WHERE item_id = ? AND location_id = ?";
            $stmt_check = sqlsrv_query($koneksi, $sql_check, [$item_id, $loc_id]);
            if (!sqlsrv_fetch_array($stmt_check)) {
                $sql_ins = "INSERT INTO item_table_locations (item_id, location_id, created_at) VALUES (?, ?, GETDATE())";
                sqlsrv_query($koneksi, $sql_ins, [$item_id, $loc_id]);
            }
        }
    }
    echo "✓ Junction relations seeded (Multiple items per location)<br>";

    // 5. Seed Transactions (Inventory Logs)
    echo "Seeding Transactions (this might take a few seconds)...<br>";
    $transaction_count = 0;
    $now = new DateTime();
    
    foreach ($item_ids as $item_id) {
        // Each item gets random transactions over last 30 days
        $num_txs = rand(5, 15);
        
        // Get locations for this item
        $sql_locs = "SELECT location_id FROM item_table_locations WHERE item_id = ?";
        $stmt_locs = sqlsrv_query($koneksi, $sql_locs, [$item_id]);
        $item_locations = [];
        while ($row = sqlsrv_fetch_array($stmt_locs, SQLSRV_FETCH_ASSOC)) {
            $item_locations[] = $row['location_id'];
        }
        
        if (empty($item_locations)) continue;

        for ($t = 0; $t < $num_txs; $t++) {
            $loc_id = $item_locations[array_rand($item_locations)];
            $days_ago = rand(0, 30);
            $tx_date = (clone $now)->modify("-$days_ago days")->format('Y-m-d H:i:s');
            
            // Randomize Transaction Type
            $types = ['IN', 'IN', 'OUT', 'ADJUST']; // Weighted towards IN
            $type = $types[array_rand($types)];
            $qty_mutation = rand(1, 10);
            
            if ($type === 'OUT') $qty_mutation = -$qty_mutation;
            if ($type === 'ADJUST') $qty_mutation = rand(-5, 5);
            if ($qty_mutation == 0) continue;

            $sql_log = "INSERT INTO inventory_log (item_id, location_id, transaction_type, qty_mutation, qty, notes, user_id, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $item_id, 
                $loc_id, 
                $type, 
                $qty_mutation, 
                abs($qty_mutation), 
                "Automated seeder transaction", 
                $user_id, 
                $tx_date
            ];
            
            if (sqlsrv_query($koneksi, $sql_log, $params)) {
                $transaction_count++;
            }
        }
    }
    echo "✓ Transactions seeded ($transaction_count records)<br>";
    echo "<br><h2 style='color:green'>Done! Database is now rich with data.</h2>";
    echo "<a href='../../index.php'>Go to Dashboard</a>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
}
