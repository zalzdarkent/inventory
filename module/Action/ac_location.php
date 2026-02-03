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

function location_save($id, $location_name) {
    if (empty($location_name)) {
        return [
            'status' => 'error',
            'message' => 'Location name is required.'
        ];
    }
    
    $id = (int)$id;
    
    if ($id > 0) {
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
        if (insert_location($location_name)) {
            return [
                'status' => 'success',
                'message' => 'Location created successfully.'
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
            $result = location_save($id, $location_name);
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
    }
}
