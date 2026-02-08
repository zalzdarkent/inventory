<?php
require_once __DIR__ . '/../config/koneksi.php';

// ==================== LOCATIONS ====================

function get_all_locations() {
    global $koneksi;
    $sql = "SELECT id, location, is_active, created_at, updated_at FROM item_location ORDER BY created_at DESC";
    $stmt = sqlsrv_query($koneksi, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $locations = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $locations[] = $row;
    }
    return $locations;
}

function get_all_locations_inv() {
    global $koneksi;
    $sql = "SELECT id, location, is_active, created_at, updated_at FROM item_location WHERE is_active = '1' ORDER BY created_at DESC";
    $stmt = sqlsrv_query($koneksi, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $locations = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $locations[] = $row;
    }
    return $locations;
}

function get_location_by_id($id) {
    global $koneksi;
    $sql = "SELECT id, location, is_active,
            (SELECT STRING_AGG(CAST(item_id AS VARCHAR), ',') FROM item_table_locations WHERE location_id = item_location.id) as item_ids
            FROM item_location WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt === false) {
        return false;
    }
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

function insert_location($location) {
    global $koneksi;
    $sql = "INSERT INTO item_location (location, is_active, created_at) VALUES (?, 1, GETDATE())";
    $params = array($location);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

function update_location($id, $location) {
    global $koneksi;
    $sql = "UPDATE item_location SET location = ?, updated_at = GETDATE() WHERE id = ?";
    $params = array($location, $id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

function toggle_location_status($id) {
    global $koneksi;
    $sql = "UPDATE item_location SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = GETDATE() WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

// ==================== ITEM TABLE LOCATIONS (JUNCTION) ====================

function insert_location_with_items($location_name, $item_ids = []) {
    global $koneksi;
    
    // Insert location
    $sql_location = "INSERT INTO item_location (location, is_active, created_at) VALUES (?, 1, GETDATE())";
    $params_location = array($location_name);
    $stmt_location = sqlsrv_query($koneksi, $sql_location, $params_location);
    
    if ($stmt_location === false) {
        return false;
    }
    
    // Get the location ID that was just created
    $sql_id = "SELECT TOP 1 id FROM item_location ORDER BY id DESC";
    $stmt_id = sqlsrv_query($koneksi, $sql_id);
    $row_id = sqlsrv_fetch_array($stmt_id, SQLSRV_FETCH_ASSOC);
    $location_id = $row_id['id'];
    
    // Insert items to junction table
    if (!empty($item_ids)) {
        foreach ($item_ids as $item_id) {
            $sql_junction = "INSERT INTO item_table_locations (location_id, item_id, created_at) VALUES (?, ?, GETDATE())";
            $params_junction = array($location_id, (int)$item_id);
            $stmt_junction = sqlsrv_query($koneksi, $sql_junction, $params_junction);
            
            if ($stmt_junction === false) {
                return false;
            }
        }
    }
    
    return $location_id;
}

function get_location_items($location_id) {
    global $koneksi;
    $sql = "SELECT 
                itl.id as junction_id,
                itl.location_id,
                itl.item_id,
                i.item_code,
                i.name as item_name,
                i.picture,
                ISNULL(SUM(il.qty_mutation), 0) as current_stock
            FROM item_table_locations itl
            INNER JOIN item_table i ON itl.item_id = i.id
            LEFT JOIN inventory_log il ON il.item_id = i.id 
                AND il.location_id = itl.location_id
            WHERE itl.location_id = ? AND i.is_active = 1
            GROUP BY itl.id, itl.location_id, itl.item_id, i.id, i.item_code, i.name, i.picture
            ORDER BY i.name ASC";
    
    $params = array($location_id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return [];
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

function add_items_to_location($location_id, $item_ids) {
    global $koneksi;
    
    if (empty($item_ids)) {
        return true;
    }
    
    foreach ($item_ids as $item_id) {
        $sql = "INSERT INTO item_table_locations (location_id, item_id, created_at) VALUES (?, ?, GETDATE())";
        $params = array($location_id, (int)$item_id);
        $stmt = sqlsrv_query($koneksi, $sql, $params);
        
        if ($stmt === false) {
            return false;
        }
    }
    
    return true;
}

function remove_item_from_location($junction_id) {
    global $koneksi;
    $sql = "DELETE FROM item_table_locations WHERE id = ?";
    $params = array($junction_id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

// ==================== ITEM TABLE ====================


function generate_item_code() {
    global $koneksi;
    $sql = "SELECT TOP 1 item_code FROM item_table ORDER BY id DESC";
    $stmt = sqlsrv_query($koneksi, $sql);
    
    if ($stmt === false) {
        return 'ITM-001';
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$row) {
        return 'ITM-001';
    }
    
    $lastCode = $row['item_code'];
    $number = (int)substr($lastCode, 4); 
    $newNumber = $number + 1;
    
    return 'ITM-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}

function get_all_items() {
    global $koneksi;
    $sql = "SELECT * FROM item_table ORDER BY created_at ASC";
    $stmt = sqlsrv_query($koneksi, $sql);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

function search_items($keyword) {
    global $koneksi;
    $sql = "SELECT TOP 10 * FROM item_table WHERE item_code LIKE ? OR name LIKE ? ORDER BY name ASC";
    $searchTerm = "%{$keyword}%";
    $params = array($searchTerm, $searchTerm);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return [];
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

function get_item_by_id($id) {
    global $koneksi;
    $sql = "SELECT * FROM item_table WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

function insert_item($item_code, $name, $picture, $description = null, $stock_min = 0) {
    global $koneksi;
    $sql = "INSERT INTO item_table (item_code, name, picture, description, stock_min, is_active, created_at) 
            VALUES (?, ?, ?, ?, ?, 1, GETDATE())";
    $params = array($item_code, $name, $picture, $description, $stock_min);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    return $stmt !== false;
}

function update_item($id, $name, $picture = null, $description = null, $stock_min = 0) {
    global $koneksi;
    
    if ($picture !== null) {
        $sql = "UPDATE item_table 
                SET name = ?, picture = ?, description = ?, stock_min = ?, updated_at = GETDATE() 
                WHERE id = ?";
        $params = array($name, $picture, $description, $stock_min, $id);
    } else {
        $sql = "UPDATE item_table 
                SET name = ?, description = ?, stock_min = ?, updated_at = GETDATE() 
                WHERE id = ?";
        $params = array($name, $description, $stock_min, $id);
    }
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

function toggle_item_status($id) {
    global $koneksi;
    $sql = "UPDATE item_table 
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, 
                updated_at = GETDATE() 
            WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    return $stmt !== false;
}

function get_active_items() {
    global $koneksi;
    $sql = "SELECT id, item_code, name FROM item_table WHERE is_active = 1 ORDER BY name ASC";
    $stmt = sqlsrv_query($koneksi, $sql);
    
    if ($stmt === false) {
        return [];
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

function get_active_locations() {
    global $koneksi;
    $sql = "SELECT id, location as location_name FROM item_location WHERE is_active = 1 ORDER BY location ASC";
    $stmt = sqlsrv_query($koneksi, $sql);
    
    if ($stmt === false) {
        return [];
    }
    
    $locations = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $locations[] = $row;
    }
    return $locations;
}

// ============= INVENTORY LOG FUNCTIONS =============

function get_items_with_stock() {
    global $koneksi;
    $sql = "SELECT 
                i.id, 
                i.item_code, 
                i.name, 
                i.picture,
                itl.id as junction_id,
                itl.location_id,
                l.location as location_name,
                ISNULL(SUM(il.qty_mutation), 0) as current_stock
            FROM item_table_locations itl
            INNER JOIN item_table i ON i.id = itl.item_id
            INNER JOIN item_location l ON itl.location_id = l.id
            LEFT JOIN inventory_log il ON il.item_id = i.id AND il.location_id = itl.location_id
            WHERE i.is_active = 1
            GROUP BY i.id, i.item_code, i.name, i.picture, itl.id, itl.location_id, l.location
            ORDER BY i.item_code ASC";
    
    $stmt = sqlsrv_query($koneksi, $sql);
    
    if ($stmt === false) {
        return [];
    }
    
    $items = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $items[] = $row;
    }
    return $items;
}

function get_inventory_logs($item_id = null, $limit = 50) {
    global $koneksi;
    
    if ($item_id) {
        $sql = "SELECT TOP {$limit}
                    il.*,
                    i.item_code,
                    i.name as item_name,
                    u.username as created_by
                FROM inventory_log il
                JOIN item_table i ON il.item_id = i.id
                LEFT JOIN users u ON il.user_id = u.id
                WHERE il.item_id = ?
                ORDER BY il.created_at DESC";
        $params = array($item_id);
    } else {
        $sql = "SELECT TOP {$limit}
                    il.*,
                    i.item_code,
                    i.name as item_name,
                    u.username as created_by
                FROM inventory_log il
                JOIN item_table i ON il.item_id = i.id
                LEFT JOIN users u ON il.user_id = u.id
                ORDER BY il.created_at DESC";
        $params = array();
    }
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return [];
    }
    
    $logs = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (isset($row['created_at']) && $row['created_at'] instanceof DateTime) {
            $row['created_at'] = $row['created_at']->format('Y-m-d H:i:s');
        }
        $logs[] = $row;
    }
    return $logs;
}

function insert_inventory_log($item_id, $transaction_type, $qty_mutation, $notes, $location_id = null, $user_id = null, $qty = null, $junction_id = null) {
    global $koneksi;
    // If qty (absolute value) not provided, calculate it from qty_mutation
    if ($qty === null) {
        $qty = abs($qty_mutation);
    }
    
    // If junction_id is provided, use it; otherwise use item_id and location_id
    if ($junction_id !== null) {
        $sql = "INSERT INTO inventory_log (item_table_location_id, transaction_type, qty_mutation, qty, notes, user_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, GETDATE())";
        $params = array($junction_id, $transaction_type, $qty_mutation, $qty, $notes, $user_id);
    } else {
        $sql = "INSERT INTO inventory_log (item_id, transaction_type, qty_mutation, qty, notes, location_id, user_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())";
        $params = array($item_id, $transaction_type, $qty_mutation, $qty, $notes, $location_id, $user_id);
    }
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    return $stmt !== false;
}

function get_junction_id($item_id, $location_id) {
    global $koneksi;
    $sql = "SELECT id FROM item_table_locations WHERE item_id = ? AND location_id = ?";
    $params = array($item_id, $location_id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return null;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ? $row['id'] : null;
}

function get_item_stock($item_id, $location_id = null) {
    global $koneksi;
    
    $sql = "SELECT 
                ISNULL(SUM(qty_mutation), 0) as current_stock
            FROM inventory_log
            WHERE item_id = ?";
    
    $params = array($item_id);
    
    if ($location_id !== null) {
        $sql .= " AND location_id = ?";
        $params[] = $location_id;
    }
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return 0;
    }
    
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ? (int)$row['current_stock'] : 0;
}

function insert_adjustment($item_id, $location_id, $previous_stock, $adjusted_qty, $adj_type, $new_stock, $notes, $user_id = null, $junction_id = null) {
    global $koneksi;
    
    // If junction_id is provided, use it; otherwise use item_id and location_id
    if ($junction_id !== null) {
        $sql = "INSERT INTO inventory_adjustment (item_table_location_id, previous_stock, adjusted_qty, adj_type, new_stock, notes, user_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVE', GETDATE())";
        $params = array($junction_id, $previous_stock, $adjusted_qty, $adj_type, $new_stock, $notes, $user_id);
    } else {
        $sql = "INSERT INTO inventory_adjustment (item_id, location_id, previous_stock, adjusted_qty, adj_type, new_stock, notes, user_id, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', GETDATE())";
        $params = array($item_id, $location_id, $previous_stock, $adjusted_qty, $adj_type, $new_stock, $notes, $user_id);
    }
    
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

function get_adjustment_history() {
    global $koneksi;
    $sql = "SELECT adj.*, i.name as item_name, i.item_code, l.location as location_name, u.username as created_by
            FROM inventory_adjustment adj
            JOIN item_table i ON adj.item_id = i.id
            LEFT JOIN item_location l ON adj.location_id = l.id
            LEFT JOIN users u ON adj.user_id = u.id
            ORDER BY adj.created_at DESC";
    $stmt = sqlsrv_query($koneksi, $sql);
    $history = [];
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (isset($row['created_at']) && $row['created_at'] instanceof DateTime) {
                $row['created_at'] = $row['created_at']->format('Y-m-d H:i:s');
            }
            $history[] = $row;
        }
    }
    return $history;
}

function get_adjustment_by_id($id) {
    global $koneksi;
    $sql = "SELECT * FROM inventory_adjustment WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

function update_adjustment_status($id, $status) {
    global $koneksi;
    $sql = "UPDATE inventory_adjustment SET status = ? WHERE id = ?";
    $params = array($status, $id);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    return $stmt !== false;
}

function get_inventory_summary($start_date, $end_date) {
    global $koneksi;
    $start = $start_date . ' 00:00:00';
    $end = $end_date . ' 23:59:59';
    
    $sql = "SELECT 
                i.id as item_id,
                i.item_code,
                i.name as item_name,
                l.location as location_name,
                ISNULL(l.location, 'Unassigned') as location_display,
                ISNULL((
                    SELECT SUM(qty_mutation)
                    FROM inventory_log
                    WHERE item_id = i.id AND location_id = l.id AND created_at < ?
                ), 0) as begin_balance,
                ISNULL((
                    SELECT SUM(qty_mutation)
                    FROM inventory_log
                    WHERE item_id = i.id AND location_id = l.id AND qty_mutation > 0
                    AND created_at >= ? AND created_at <= ?
                ), 0) as total_in,
                ISNULL((
                    SELECT SUM(ABS(qty_mutation))
                    FROM inventory_log
                    WHERE item_id = i.id AND location_id = l.id AND qty_mutation < 0
                    AND (transaction_type = 'OUT' OR (transaction_type = 'MOVE' AND notes LIKE '%Transfer to%'))
                    AND created_at >= ? AND created_at <= ?
                ), 0) as total_out,
                ISNULL((
                    SELECT SUM(qty_mutation)
                    FROM inventory_log
                    WHERE item_id = i.id AND location_id = l.id AND transaction_type = 'ADJUST'
                    AND created_at >= ? AND created_at <= ?
                ), 0) as total_adj
            FROM inventory_log il
            JOIN item_table i ON il.item_id = i.id
            LEFT JOIN item_location l ON il.location_id = l.id
            WHERE i.is_active = 1
            GROUP BY i.id, i.item_code, i.name, l.id, l.location
            ORDER BY i.name ASC, l.location ASC";
            
    $params = array($start, $start, $end, $start, $end, $start, $end);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt === false) {
        return [];
    }
    $summary = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $row['end_balance'] = $row['begin_balance'] + $row['total_in'] - $row['total_out'] + $row['total_adj'];
        $summary[] = $row;
    }
    return $summary;
}

function get_location_summary($start_date, $end_date) {
    global $koneksi;
    $start = $start_date . ' 00:00:00';
    $end = $end_date . ' 23:59:59';
    
    $sql = "SELECT 
                l.id as location_id,
                l.location as location_name,
                ISNULL(l.location, 'Unassigned') as location_display,
                ISNULL(SUM(CASE WHEN il.created_at < ? THEN il.qty_mutation ELSE 0 END), 0) as begin_balance,
                ISNULL(SUM(CASE WHEN il.qty_mutation > 0 AND il.created_at >= ? AND il.created_at <= ? THEN il.qty_mutation ELSE 0 END), 0) as total_in,
                ISNULL(SUM(CASE WHEN il.qty_mutation < 0 AND (il.transaction_type = 'OUT' OR (il.transaction_type = 'MOVE' AND il.notes LIKE '%Transfer to%')) AND il.created_at >= ? AND il.created_at <= ? THEN ABS(il.qty_mutation) ELSE 0 END), 0) as total_out,
                ISNULL(SUM(CASE WHEN il.transaction_type = 'ADJUST' AND il.created_at >= ? AND il.created_at <= ? THEN il.qty_mutation ELSE 0 END), 0) as total_adj
            FROM item_location l
            LEFT JOIN inventory_log il ON il.location_id = l.id
            LEFT JOIN item_table i ON il.item_id = i.id AND i.is_active = 1
            GROUP BY l.id, l.location
            ORDER BY l.location ASC";
            
    $params = array($start, $start, $end, $start, $end, $start, $end);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt === false) {
        return [];
    }
    $summary = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $row['end_balance'] = $row['begin_balance'] + $row['total_in'] - $row['total_out'] + $row['total_adj'];
        $summary[] = $row;
    }
    return $summary;
}

function get_daily_movement_stats($start, $end) {
    global $koneksi;
    
    // Create a list of all dates in the range to ensure no gaps in the chart
    $dates = [];
    $start_dt = new DateTime($start);
    $end_dt = new DateTime($end);
    $end_dt->modify('+1 day'); // Include the end date
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start_dt, $interval, $end_dt);
    
    foreach ($period as $dt) {
        $dates[$dt->format('Y-m-d')] = ['IN' => 0, 'OUT' => 0, 'ADJUST' => 0];
    }

    $sql = "SELECT 
                CAST(created_at AS DATE) as log_date,
                CASE 
                    WHEN transaction_type = 'IN' OR (transaction_type = 'MOVE' AND notes LIKE '%Transfer from%') THEN 'IN'
                    WHEN transaction_type = 'OUT' OR (transaction_type = 'MOVE' AND notes LIKE '%Transfer to%') THEN 'OUT'
                    WHEN transaction_type = 'ADJUST' THEN 'ADJUST'
                    ELSE 'OTHER'
                END as flow_type,
                SUM(ABS(qty_mutation)) as total_qty
            FROM inventory_log
            WHERE created_at >= ? AND created_at <= ?
            GROUP BY CAST(created_at AS DATE), 
                CASE 
                    WHEN transaction_type = 'IN' OR (transaction_type = 'MOVE' AND notes LIKE '%Transfer from%') THEN 'IN'
                    WHEN transaction_type = 'OUT' OR (transaction_type = 'MOVE' AND notes LIKE '%Transfer to%') THEN 'OUT'
                    WHEN transaction_type = 'ADJUST' THEN 'ADJUST'
                    ELSE 'OTHER'
                END
            ORDER BY log_date ASC";
            
    $params = array($start . ' 00:00:00', $end . ' 23:59:59');
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $dateVal = $row['log_date'];
            $date = '';
            if ($dateVal instanceof DateTime) {
                $date = $dateVal->format('Y-m-d');
            } else if (is_string($dateVal)) {
                $date = substr($dateVal, 0, 10);
            }
            
            $type = $row['flow_type'];
            if ($date && isset($dates[$date]) && isset($dates[$date][$type])) {
                $dates[$date][$type] = (int)$row['total_qty'];
            }
        }
    }

    $labels = array_keys($dates);
    $in_data = [];
    $out_data = [];
    $adj_data = [];
    
    foreach ($dates as $data) {
        $in_data[] = $data['IN'];
        $out_data[] = $data['OUT'];
        $adj_data[] = $data['ADJUST'];
    }

    return [
        'labels' => $labels,
        'in' => $in_data,
        'out' => $out_data,
        'adj' => $adj_data
    ];
}

function get_inventory_insights($start, $end) {
    global $koneksi;
    
    // 1. Fast Moving (Based on OUT transactions only - periodik per hari)
    $sql_fast = "SELECT TOP 10 
                    i.name, 
                    SUM(ABS(il.qty_mutation)) as total_qty,
                    COUNT(il.id) as frequency
                FROM inventory_log il
                JOIN item_table i ON il.item_id = i.id
                WHERE il.created_at >= ? AND il.created_at <= ?
                AND il.transaction_type = 'OUT'
                GROUP BY i.id, i.name
                ORDER BY total_qty DESC";
    
    // 2. Dead Stock (Item dengan jumlah OUT paling sedikit - fokus pada OUT transactions only)
    // Termasuk item yang belum pernah OUT sama sekali dalam periode
    $sql_dead = "SELECT TOP 10 
                    i.id,
                    i.name,
                    ISNULL(SUM(CASE WHEN il.transaction_type = 'OUT' THEN ABS(il.qty_mutation) ELSE 0 END), 0) as total_out,
                    ISNULL((SELECT SUM(qty_mutation) FROM inventory_log WHERE item_id = i.id), 0) as current_stock
                FROM item_table i
                LEFT JOIN inventory_log il ON i.id = il.item_id 
                    AND il.created_at >= ? AND il.created_at <= ?
                WHERE i.is_active = 1
                GROUP BY i.id, i.name
                ORDER BY total_out ASC, current_stock DESC";

    $params_fast = array($start . ' 00:00:00', $end . ' 23:59:59');
    $params_dead = array($start . ' 00:00:00', $end . ' 23:59:59');
    
    $fast_moving = [];
    $stmt_fast = sqlsrv_query($koneksi, $sql_fast, $params_fast);
    if ($stmt_fast !== false) {
        while ($row = sqlsrv_fetch_array($stmt_fast, SQLSRV_FETCH_ASSOC)) {
            $fast_moving[] = $row;
        }
    }

    $dead_stock = [];
    $stmt_dead = sqlsrv_query($koneksi, $sql_dead, $params_dead);
    if ($stmt_dead !== false) {
        while ($row = sqlsrv_fetch_array($stmt_dead, SQLSRV_FETCH_ASSOC)) {
            $dead_stock[] = $row;
        }
    }

    return [
        'fast_moving' => $fast_moving,
        'dead_stock' => $dead_stock
    ];
}

function get_location_occupancy() {
    global $koneksi;
    
    $sql = "SELECT 
                ISNULL(l.location, 'Unassigned') as location_name,
                SUM(il.qty_mutation) as total_stock
            FROM inventory_log il
            LEFT JOIN item_location l ON il.location_id = l.id
            GROUP BY l.location, l.id
            HAVING SUM(il.qty_mutation) > 0
            ORDER BY total_stock DESC";

    $stmt = sqlsrv_query($koneksi, $sql);
    $data = [];
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = [
                'name' => $row['location_name'],
                'value' => (int)$row['total_stock']
            ];
        }
    }
    return $data;
}

function get_low_stock_items() {
    global $koneksi;
    $sql = "SELECT i.name, i.item_code, i.stock_min, SUM(il.qty_mutation) as current_stock
            FROM item_table i
            JOIN inventory_log il ON i.id = il.item_id
            WHERE i.is_active = 1
            GROUP BY i.id, i.name, i.item_code, i.stock_min
            HAVING SUM(il.qty_mutation) < i.stock_min
            ORDER BY (SUM(il.qty_mutation) - i.stock_min) ASC";
    $stmt = sqlsrv_query($koneksi, $sql);
    $items = [];
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $items[] = $row;
        }
    }
    return $items;
}

function get_recent_activities($limit = 10) {
    global $koneksi;
    $sql = "SELECT TOP $limit il.created_at, il.transaction_type, il.qty_mutation, i.name as item_name, u.username as created_by
            FROM inventory_log il
            JOIN item_table i ON il.item_id = i.id
            LEFT JOIN users u ON il.user_id = u.id
            ORDER BY il.created_at DESC";
    $stmt = sqlsrv_query($koneksi, $sql);
    $logs = [];
    if ($stmt !== false) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $logs[] = $row;
        }
    }
    return $logs;
}

// ==================== AUTHENTICATION ====================

function login_user_db($email, $password) {
    global $koneksi;
    $sql = "SELECT id, email, username FROM users WHERE (email = ? OR username = ?) AND password = ?";
    $params = array($email, $email, $password);
    $stmt = sqlsrv_query($koneksi, $sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}
