<?php
require_once __DIR__ . '/../../query/query.php';

if (isset($_POST['action']) && $_POST['action'] === 'get_summary') {
    $start_date = $_POST['start_date'] ?? date('Y-m-01');
    $end_date = $_POST['end_date'] ?? date('Y-m-t');

    $data = get_inventory_summary($start_date, $end_date);
    $chartData = get_daily_movement_stats($start_date, $end_date);
    $insights = get_inventory_insights($start_date, $end_date);
    $occupancy = get_location_occupancy();

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'chartData' => $chartData,
        'insights' => $insights,
        'occupancy' => $occupancy
    ]);
    exit;
}