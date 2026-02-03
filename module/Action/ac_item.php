<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

require_once __DIR__ . '/../../query/query.php';

function item_index() {
    return get_all_items();
}

function item_search($keyword) {
    if (empty($keyword)) {
        return ['status' => 'error', 'message' => 'Keyword required'];
    }
    
    $items = search_items($keyword);
    return [
        'status' => 'success',
        'data' => $items
    ];
}

function item_show($id) {
    if (empty($id) || !is_numeric($id)) {
        return [
            'status' => 'error',
            'message' => 'Invalid item ID'
        ];
    }
    
    $item = get_item_by_id($id);
    if (!$item) {
        return [
            'status' => 'error',
            'message' => 'Item not found'
        ];
    }
    
    return [
        'status' => 'success',
        'data' => $item
    ];
}

function item_save($id, $name, $picture_data = null, $description = null) {
    if (empty($name)) {
        return [
            'status' => 'error',
            'message' => 'Item name is required.'
        ];
    }
    
    $id = (int)$id;
    
    if ($id > 0) {
        // Fetch old item data to get the current picture filename
        $item = get_item_by_id($id);
        $old_pic = $item ? $item['picture'] : null;
        $picture = null;
        
        if ($picture_data) {
            $upload_result = handle_picture_upload($picture_data);
            if ($upload_result['status'] === 'error') {
                return $upload_result;
            }
            $picture = $upload_result['filename'];
        }
        
        if (update_item($id, $name, $picture, $description)) {
            // Delete old picture file if a new one was uploaded
            if ($picture && $old_pic) {
                $old_path = __DIR__ . '/../../assets/uploads/items/' . $old_pic;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            return [
                'status' => 'success',
                'message' => 'Item updated successfully.'
            ];
        } else {
            // Cleanup the newly uploaded picture if the database update failed
            if ($picture) {
                $new_path = __DIR__ . '/../../assets/uploads/items/' . $picture;
                if (file_exists($new_path)) {
                    unlink($new_path);
                }
            }
            return [
                'status' => 'error',
                'message' => 'Failed to update item.'
            ];
        }
    } else {
        if (empty($picture_data)) {
            return [
                'status' => 'error',
                'message' => 'Picture is required for new item.'
            ];
        }        
        $upload_result = handle_picture_upload($picture_data);
        if ($upload_result['status'] === 'error') {
            return $upload_result;
        }
        
        $picture = $upload_result['filename'];
        $item_code = generate_item_code();
        
        if (insert_item($item_code, $name, $picture, $description)) {
            return [
                'status' => 'success',
                'message' => 'Item created successfully.',
                'item_code' => $item_code
            ];
        } else {
            if (file_exists(__DIR__ . '/../../assets/uploads/items/' . $picture)) {
                unlink(__DIR__ . '/../../assets/uploads/items/' . $picture);
            }
            return [
                'status' => 'error',
                'message' => 'Failed to create item.'
            ];
        }
    }
}

function handle_picture_upload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'status' => 'error',
            'message' => 'Upload error occurred.'
        ];
    }
    
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return [
            'status' => 'error',
            'message' => 'File size exceeds 5MB limit.'
        ];
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return [
            'status' => 'error',
            'message' => 'Only JPG, JPEG, PNG, and WEBP files are allowed.'
        ];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'item_' . time() . '_' . uniqid() . '.' . $extension;
    
    $uploadDir = __DIR__ . '/../../assets/uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destination = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'status' => 'success',
            'filename' => $filename
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Failed to save uploaded file.'
        ];
    }
}

function item_toggle_status($id) {
    if (empty($id) || !is_numeric($id)) {
        return [
            'status' => 'error',
            'message' => 'Invalid item ID'
        ];
    }
    
    if (toggle_item_status($id)) {
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

function item_filter_by_location($location_id) {
    // If location_id is empty, return all active items
    if (empty($location_id)) {
        $items = get_active_items();
        return [
            'status' => 'success',
            'data' => $items
        ];
    }
    
    if (!is_numeric($location_id)) {
        return [
            'status' => 'error',
            'message' => 'Invalid location ID'
        ];
    }
    
    $items = get_active_items();
    return [
        'status' => 'success',
        'data' => $items
    ];
}

// AJAX Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save':
            $id = $_POST['id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            $picture = null;
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                $picture = $_FILES['picture'];
            }
            
            $result = item_save($id, $name, $picture, $description);
            echo json_encode($result);
            exit;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            $result = item_show($id);
            echo json_encode($result);
            exit;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            $result = item_toggle_status($id);
            echo json_encode($result);
            exit;
            
        case 'search':
            $keyword = trim($_POST['keyword'] ?? '');
            $result = item_search($keyword);
            echo json_encode($result);
            exit;
            
        case 'filter':
            $location_id = $_POST['location_id'] ?? '';
            $result = item_filter_by_location($location_id);
            echo json_encode($result);
            exit;
    }
}
