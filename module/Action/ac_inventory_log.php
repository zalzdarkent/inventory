<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../query/query.php';

function inventory_index() {
    return get_items_with_stock();
}

function inventory_logs($item_id = null) {
    return get_inventory_logs($item_id);
}

function inventory_transaction($item_id, $transaction_type, $qty, $notes, $location_id = null, $is_transfer = false, $target_location_id = null, $user_id = null, $junction_id = null) {
    // Support both junction_id (new) and item_id+location_id (legacy)
    if ($junction_id === null && (empty($item_id) || !is_numeric($item_id))) {
        return [
            'status' => 'error',
            'message' => 'Invalid item ID'
        ];
    }
    
    if (!in_array($transaction_type, ['IN', 'OUT', 'MOVE', 'ADJUST'])) {
        return [
            'status' => 'error',
            'message' => 'Invalid transaction type'
        ];
    }
    
    if ($transaction_type !== 'ADJUST' && (!is_numeric($qty) || $qty <= 0)) {
        return [
            'status' => 'error',
            'message' => 'Quantity must be greater than 0'
        ];
    }

    if ($transaction_type === 'ADJUST' && !is_numeric($qty)) {
        return [
            'status' => 'error',
            'message' => 'Invalid quantity for adjustment'
        ];
    }

    if ($is_transfer && $target_location_id) {
        // Get source junction_id if not provided
        $source_junction_id = $junction_id;
        if ($source_junction_id === null && $item_id && $location_id) {
            $source_junction_id = get_junction_id($item_id, $location_id);
        }
        
        // Get target junction_id
        $target_junction_id = get_junction_id($item_id, $target_location_id);
        
        if (!$source_junction_id || !$target_junction_id) {
            return [
                'status' => 'error',
                'message' => 'Invalid location or item assignment'
            ];
        }
        
        $current_stock_source = get_item_stock($item_id, $location_id);
        if ($current_stock_source < $qty) {
            return [
                'status' => 'error',
                'message' => "Insufficient stock at source location. Current: {$current_stock_source}"
            ];
        }

        $source_location = get_location_by_id($location_id);
        $target_location = get_location_by_id($target_location_id);
        
        $source_name = $source_location ? $source_location['location'] : 'Unknown';
        $target_name = $target_location ? $target_location['location'] : 'Unknown';

        $out_notes = $notes . " (Transfer to: $target_name)";
        // qty_mutation is negative for OUT, qty (absolute) is positive
        if (!insert_inventory_log($item_id, 'MOVE', -$qty, $out_notes, $location_id, $user_id, $qty, $source_junction_id)) {
             return [
                'status' => 'error',
                'message' => 'Failed to record transfer (Source deduction)'
            ];
        }

        $in_notes = $notes . " (Transfer from: $source_name)";
        // qty_mutation is positive for IN, qty (absolute) is positive
        if (!insert_inventory_log($item_id, 'MOVE', $qty, $in_notes, $target_location_id, $user_id, $qty, $target_junction_id)) {
             return [
                'status' => 'warning',
                'message' => 'Transfer partially completed. Deducted from source but failed to add to target.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Stock transferred successfully'
        ];
    }
    
    // Calculate qty_mutation based on transaction type
    $qty_mutation = $qty;
    if ($transaction_type === 'OUT') {
        $qty_mutation = -$qty;
    } else if ($transaction_type === 'ADJUST') {
        // For ADJUST, qty from input is the physical count, qty_mutation is already computed
        // This will be handled below in adjustment handler
    }
    
    if ($transaction_type !== 'ADJUST') {
        // For IN, OUT, MOVE: qty_mutation is signed, qty (absolute) is always positive
        if (insert_inventory_log($item_id, $transaction_type, $qty_mutation, $notes, $location_id, $user_id, $qty, $junction_id)) {
            $new_stock = $junction_id ? 0 : get_item_stock($item_id);
            return [
                'status' => 'success',
                'message' => 'Transaction recorded successfully',
                'new_stock' => $new_stock
            ];
        }
    }
    
    return [
        'status' => 'error',
        'message' => 'Failed to record transaction'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'transaction':
            $item_id = $_POST['item_id'] ?? 0;
            $transaction_type = strtoupper($_POST['transaction_type'] ?? '');
            $qty = $_POST['qty'] ?? 0;
            $notes = trim($_POST['notes'] ?? '');
            $location_id = !empty($_POST['location_id']) ? $_POST['location_id'] : null;
            $is_transfer = isset($_POST['is_transfer']) && $_POST['is_transfer'] === 'true';
            $target_location_id = !empty($_POST['target_location_id']) ? $_POST['target_location_id'] : null;
            
            if ($is_transfer) {
                $transaction_type = 'MOVE'; 
            }

            $user_id = $_SESSION['user_id'] ?? null;
            $result = inventory_transaction($item_id, $transaction_type, $qty, $notes, $location_id, $is_transfer, $target_location_id, $user_id);
            echo json_encode($result);
            exit;
            
        case 'adjustment':
            $item_id = $_POST['item_id'] ?? 0;
            $physical_count = $_POST['qty'] ?? 0; 
            $notes = trim($_POST['notes'] ?? '');
            $location_id = !empty($_POST['location_id']) ? $_POST['location_id'] : null;
            $junction_id = !empty($_POST['junction_id']) ? $_POST['junction_id'] : null;

            $previous_stock = get_item_stock($item_id, $location_id);
            
            $delta = $physical_count - $previous_stock;
            $adj_type = $delta >= 0 ? 1 : 0;
            $abs_delta = abs($delta);

            $user_id = $_SESSION['user_id'] ?? null;
            // qty_mutation is delta (signed), qty is absolute delta
            if (insert_inventory_log($item_id, 'ADJUST', $delta, $notes, $location_id, $user_id, $abs_delta, $junction_id)) {
                if (insert_adjustment($item_id, $location_id, $previous_stock, $abs_delta, $adj_type, $physical_count, $notes, $user_id, $junction_id)) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Adjustment recorded successfully. Delta: ' . ($delta > 0 ? '+' : '') . $delta,
                        'new_stock' => $physical_count
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'warning',
                        'message' => 'Log recorded but history entry failed'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to record adjustment'
                ]);
            }
            exit;

        case 'rollback':
            $adj_id = $_POST['adj_id'] ?? 0;
            $adj_data = get_adjustment_by_id($adj_id);

            if (!$adj_data) {
                echo json_encode(['status' => 'error', 'message' => 'Adjustment data not found']);
                exit;
            }

            if ($adj_data['status'] === 'ROLLED_BACK') {
                echo json_encode(['status' => 'error', 'message' => 'Already rolled back']);
                exit;
            }

            $rollback_qty_mutation = -$adj_data['adjusted_qty'] * ($adj_data['adj_type'] == 1 ? 1 : -1);
            $rollback_qty = $adj_data['adjusted_qty'];
            $rollback_notes = "Rollback STO ID: #{$adj_id} - Original: {$adj_data['notes']}";

            $user_id = $_SESSION['user_id'] ?? null;
            // qty_mutation is negated, qty is absolute
            if (insert_inventory_log($adj_data['item_id'], 'ADJUST', $rollback_qty_mutation, $rollback_notes, $adj_data['location_id'], $user_id, $rollback_qty)) {
                if (update_adjustment_status($adj_id, 'ROLLED_BACK')) {
                    echo json_encode(['status' => 'success', 'message' => 'Rolled back successfully']);
                } else {
                    echo json_encode(['status' => 'warning', 'message' => 'Stock reversed but status update failed']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to reverse stock']);
            }
            exit;

        case 'get_logs':
            $item_id = $_POST['item_id'] ?? null;
            $logs = inventory_logs($item_id);
            echo json_encode([
                'status' => 'success',
                'data' => $logs
            ]);
            exit;
            
        case 'get_active_items':
            $items = get_active_items();
            echo json_encode([
                'status' => 'success',
                'data' => $items
            ]);
            exit;
    }
}
