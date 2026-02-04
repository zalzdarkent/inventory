<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

require_once __DIR__ . '/../../query/query.php';

function location_index() {
    return get_all_locations();
}

function location_show($id) {
    if (empty($id) || !is_numeric($id)) {
        return [
            'status' => 'error',
            'message' => 'Invalid location ID'
        ];
    }
    
    $location = get_location_by_id($id);
    if (!$location) {
        return [
            'status' => 'error',
            'message' => 'Location not found'
        ];
    }
    
    return [
        'status' => 'success',
        'data' => $location
    ];
}

function location_save($id, $location_name, $item_ids = []) {
    if (empty($location_name)) {
        return [
            'status' => 'error',
            'message' => 'Location name is required.'
        ];
    }
    
    $id = (int)$id;
    
    if ($id > 0) {
        // Update existing location (only name, don't change items)
        if (update_location($id, $location_name)) {
            return [
                'status' => 'success',
                'message' => 'Location updated successfully.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to update location.'
            ];
        }
    } else {
        // Create new location with items
        $location_id = insert_location_with_items($location_name, $item_ids);
        if ($location_id) {
            return [
                'status' => 'success',
                'message' => 'Location created successfully.',
                'location_id' => $location_id
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Failed to create location.'
            ];
        }
    }
}

function location_toggle_status($id) {
    if (empty($id) || !is_numeric($id)) {
        return [
            'status' => 'error',
            'message' => 'Invalid location ID'
        ];
    }
    
    if (toggle_location_status($id)) {
        return [
            'status' => 'success',
            'message' => 'Status updated successfully.'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Failed to toggle status.'
        ];
    }
}

// AJAX Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save':
            $id = $_POST['id'] ?? 0;
            $location_name = trim($_POST['location_name'] ?? '');
            $item_ids = !empty($_POST['item_ids']) ? (array)$_POST['item_ids'] : [];
            $result = location_save($id, $location_name, $item_ids);
            echo json_encode($result);
            exit;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            $result = location_show($id);
            echo json_encode($result);
            exit;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            $result = location_toggle_status($id);
            echo json_encode($result);
            exit;
            
        case 'get_items':
            $location_id = $_POST['location_id'] ?? '';
            if (empty($location_id) || !is_numeric($location_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid location ID']);
                exit;
            }
            $items = get_location_items($location_id);
            echo json_encode(['status' => 'success', 'data' => $items]);
            exit;
            
        case 'add_items':
            $location_id = $_POST['location_id'] ?? '';
            $item_ids = !empty($_POST['item_ids']) ? (array)$_POST['item_ids'] : [];
            
            if (empty($location_id) || !is_numeric($location_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid location ID']);
                exit;
            }
            
            if (add_items_to_location($location_id, $item_ids)) {
                echo json_encode(['status' => 'success', 'message' => 'Items added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add items']);
            }
            exit;
            
        case 'remove_item':
            $junction_id = $_POST['junction_id'] ?? '';
            if (empty($junction_id) || !is_numeric($junction_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid junction ID']);
                exit;
            }
            
            if (remove_item_from_location($junction_id)) {
                echo json_encode(['status' => 'success', 'message' => 'Item removed successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove item']);
            }
            exit;
    }
}
