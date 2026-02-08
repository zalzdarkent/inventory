<?php
define('AJAX_MODE', true);

if (isset($_POST['action']) && $_POST['action'] === 'get_summary') {
    try {
        require_once __DIR__ . '/../../query/query.php';

        $start_date = $_POST['start_date'] ?? date('Y-m-01');
        $end_date = $_POST['end_date'] ?? date('Y-m-t');

        $location_data = get_location_summary($start_date, $end_date);
        $item_data = get_inventory_summary($start_date, $end_date);
        $chartData = get_daily_movement_stats($start_date, $end_date);
        $insights = get_inventory_insights($start_date, $end_date);
        $occupancy = get_location_occupancy();

        $low_stock = get_low_stock_items();
        $recent_activities = get_recent_activities();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'location_data' => $location_data,
            'item_data' => $item_data,
            'chartData' => $chartData,
            'insights' => $insights,
            'occupancy' => $occupancy,
            'low_stock' => $low_stock,
            'recent_activities' => $recent_activities
        ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    } catch (Throwable $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        exit;
    }
}