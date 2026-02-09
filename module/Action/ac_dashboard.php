<?php
define('AJAX_MODE', true);

// Set error reporting to catch all errors for debugging, but prevent direct output
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (isset($_POST['action']) && $_POST['action'] === 'get_summary') {
    // Clean any previous output buffers to avoid stray characters only during AJAX
    while (ob_get_level() > 0) ob_end_clean();
    ob_start();
    
    try {
        require_once __DIR__ . '/../../query/query.php';

        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

        $location_data = get_location_summary($start_date, $end_date);
        $item_data = get_inventory_summary($start_date, $end_date);
        $chartData = get_daily_movement_stats($start_date, $end_date);
        $insights = get_inventory_insights($start_date, $end_date);
        $occupancy = get_location_occupancy();

        $low_stock = get_low_stock_items();
        $recent_activities = get_recent_activities();

        // Recursively convert DateTime objects to strings for JSON serialization
        $convert_dates = function (&$item) use (&$convert_dates) {
            if (!is_array($item)) return;
            foreach ($item as $key => &$value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                } elseif (is_array($value)) {
                    $convert_dates($value);
                }
            }
        };

        $convert_dates($location_data);
        $convert_dates($item_data);
        $convert_dates($recent_activities);

        // Check for any stray output (warnings/notices)
        $stray_output = ob_get_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'location_data' => $location_data,
            'item_data' => $item_data,
            'chartData' => $chartData,
            'insights' => $insights,
            'occupancy' => $occupancy,
            'low_stock' => $low_stock,
            'recent_activities' => $recent_activities,
            'debug_info' => [
                'stray_output' => $stray_output,
                'php_error' => error_get_last()
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    } catch (Throwable $e) {
        $stray_output = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug_info' => [
                'stray_output' => $stray_output,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
        exit;
    }
}