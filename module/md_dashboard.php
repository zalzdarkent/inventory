<?php
require_once __DIR__ . '/Action/ac_dashboard.php';
// Periodik per hari - gunakan date picker untuk fleksibilitas
// Periodik per bulan sebagai default untuk trend yang lebih baik
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$summary_location = get_location_summary($start_date, $end_date);
$summary_item = get_inventory_summary($start_date, $end_date);
$total_stock = 0;
$total_in = 0;
$total_out = 0;
$total_adj = 0;
foreach ($summary_location as $row) {
    $total_stock += $row['end_balance'];
    $total_in += $row['total_in'];
    $total_out += $row['total_out'];
    $total_adj += $row['total_adj'];
}
$chartData = get_daily_movement_stats($start_date, $end_date);
$insights = get_inventory_insights($start_date, $end_date);
$occupancy = get_location_occupancy();
$low_stock = get_low_stock_items();
$recent_activities = get_recent_activities();
?>

<style>
    .dashboard-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .metric-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .scroll-list {
        max-height: 350px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .scroll-list::-webkit-scrollbar {
        width: 5px;
    }

    .scroll-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .scroll-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .scroll-list::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
        padding: 5px;
    }

    .insight-tile {
        padding: 15px;
        border-radius: 12px;
        background: #f8f9fa;
        text-align: center;
        transition: all 0.2s;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .insight-tile:hover {
        background: #fff;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .fast-moving-tile {
        border-bottom: 3px solid #28a745;
    }

    .dead-stock-tile {
        border-bottom: 3px solid #dc3545;
    }

    .tile-value {
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .tile-label {
        font-size: 0.75rem;
        color: #919da9;
        font-weight: 500;
    }

    .tile-name {
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 8px;
        color: #172b4c;
    }

    .glass-header {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
    }

    .activity-feed {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 12px;
        border-left: 3px solid #e9ecef;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 0 6px 6px 0;
        position: relative;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
    }

    .activity-item.IN { border-left-color: #28a745; }
    .activity-item.IN::before { border-color: #28a745; }
    
    .activity-item.OUT { border-left-color: #dc3545; }
    .activity-item.OUT::before { border-color: #dc3545; }
    
    .activity-item.ADJUST { border-left-color: #ffc107; }
    .activity-item.ADJUST::before { border-color: #ffc107; }

    .activity-date {
        font-size: 0.75rem;
        color: #919da9;
    }

    .list-item-hover:hover {
        background: #f8f9fa;
        cursor: default;
    }

    .scroll-list {
        max-height: 350px;
        overflow-y: auto;
    }
</style>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Inventory Dashboard</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Dashboard</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card border-0">
                <div class="card-body">
                    <form id="filterForm" class="row align-items-end g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date"
                                value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date"
                                value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="feather-filter me-2"></i>Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4" id="statsGrid">
        <div class="col-xxl-3 col-md-6 mb-4 mb-xxl-0">
            <div class="card metric-card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted fs-12 mb-1">TOTAL STOCK</div>
                            <h3 class="fw-bold mb-0 text-primary" id="stat_total_stock">
                                <?= number_format($total_stock) ?></h3>
                        </div>
                        <div class="metric-icon bg-soft-primary text-primary">
                            <i class="feather-package"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6 mb-4 mb-xxl-0">
            <div class="card metric-card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted fs-12 mb-1">TOTAL IN</div>
                            <h3 class="fw-bold mb-0 text-success" id="stat_total_in">+<?= number_format($total_in) ?>
                            </h3>
                        </div>
                        <div class="metric-icon bg-soft-success text-success">
                            <i class="feather-arrow-down-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6 mb-4 mb-md-0">
            <div class="card metric-card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted fs-12 mb-1">TOTAL OUT</div>
                            <h3 class="fw-bold mb-0 text-danger" id="stat_total_out">-<?= number_format($total_out) ?>
                            </h3>
                        </div>
                        <div class="metric-icon bg-soft-danger text-danger">
                            <i class="feather-arrow-up-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card metric-card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted fs-12 mb-1">TOTAL ADJUST</div>
                            <h3 class="fw-bold mb-0 text-warning" id="stat_total_adj">
                                <?= ($total_adj >= 0 ? '+' : '') . number_format($total_adj) ?></h3>
                        </div>
                        <div class="metric-icon bg-soft-warning text-warning">
                            <i class="feather-sliders"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Group -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card dashboard-card h-100">
                <div class="card-header glass-header">
                    <h5 class="card-title mb-0">Activity Trend</h5>
                </div>
                <div class="card-body">
                    <div id="activityChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dashboard-card h-100">
                <div class="card-header glass-header">
                    <h5 class="card-title mb-0">Location Occupancy</h5>
                </div>
                <div class="card-body">
                    <div id="occupancyChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Alerts & Recent Activity -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header glass-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-danger"><i class="feather-alert-triangle me-2"></i>Urgent Stock Alerts</h5>
                    <span class="badge bg-soft-danger text-danger"><?= count($low_stock) ?> Items</span>
                </div>
                <div class="card-body">
                    <div id="lowStockList" class="scroll-list">
                        <?php if (empty($low_stock)): ?>
                            <div class="text-center py-5">
                                <i class="feather-check-circle fs-40 text-success mb-3 d-block"></i>
                                <span class="text-muted">All stock levels are healthy!</span>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($low_stock as $item): ?>
                                    <div class="list-group-item px-0 list-item-hover border-0 mb-2 rounded-3 p-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($item['item_code']) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-danger"><?= number_format($item['current_stock']) ?> / <?= number_format($item['stock_min']) ?></div>
                                                <small class="text-muted">Stock / Min</small>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 4px;">
                                            <?php 
                                                $percent = ($item['stock_min'] > 0) ? ($item['current_stock'] / $item['stock_min']) * 100 : 100;
                                                $percent = min(100, $percent);
                                            ?>
                                            <div class="progress-bar bg-danger" style="width: <?= $percent ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header glass-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary"><i class="feather-activity me-2"></i>Recent Activity Feed</h5>
                </div>
                <div class="card-body">
                    <div id="activityFeedList" class="activity-feed">
                        <?php if (empty($recent_activities)): ?>
                            <div class="text-center py-5">
                                <span class="text-muted">No recent activities found</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item <?= $activity['transaction_type'] ?>">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($activity['item_name']) ?></span>
                                        <span class="activity-date">
                                            <?= ($activity['created_at'] instanceof DateTime) ? $activity['created_at']->format('H:i, d M') : date('H:i, d M', strtotime($activity['created_at'] ?? 'now')) ?>
                                        </span>
                                    </div>
                                    <div class="mt-1 d-flex align-items-center justify-content-between">
                                        <span class="fs-12">
                                            <span class="badge bg-soft-<?= $activity['transaction_type'] == 'IN' ? 'success' : ($activity['transaction_type'] == 'OUT' ? 'danger' : 'warning') ?> text-<?= $activity['transaction_type'] == 'IN' ? 'success' : ($activity['transaction_type'] == 'OUT' ? 'danger' : 'warning') ?> fs-10 me-1">
                                                <?= $activity['transaction_type'] ?>
                                            </span>
                                            <?= number_format(abs($activity['qty_mutation'])) ?> units by <strong><?= htmlspecialchars($activity['created_by'] ?? 'System') ?></strong>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header glass-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Inventory Summary</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportSummary()">
                        <i class="feather-download me-1"></i>Export
                    </button>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="summaryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab" aria-controls="location" aria-selected="true">By Location</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="item-tab" data-bs-toggle="tab" data-bs-target="#item" type="button" role="tab" aria-controls="item" aria-selected="false">By Item</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="summaryTabsContent">
                        <div class="tab-pane fade show active" id="location" role="tabpanel" aria-labelledby="location-tab">
                            <div class="table-responsive">
                                <table class="table table-hover" id="locationTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th>Location</th>
                                            <th class="text-center">Begin</th>
                                            <th class="text-center">IN</th>
                                            <th class="text-center">OUT</th>
                                            <th class="text-center">ADJ</th>
                                            <th class="text-center">End / Pcs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($summary_location as $index => $row): ?>
                                            <tr>
                                                <td class="text-center"><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['location_display']) ?></div>
                                                </td>
                                                <td class="text-center fw-semibold text-muted">
                                                    <?= number_format($row['begin_balance']) ?></td>
                                                <td class="text-center">
                                                    <span
                                                        class="text-success fw-bold"><?= $row['total_in'] > 0 ? '+' . number_format($row['total_in']) : '0' ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="text-danger fw-bold"><?= $row['total_out'] > 0 ? '-' . number_format($row['total_out']) : '0' ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['total_adj'] > 0): ?>
                                                        <span
                                                            class="text-success fw-bold">+<?= number_format($row['total_adj']) ?></span>
                                                    <?php elseif ($row['total_adj'] < 0): ?>
                                                        <span class="text-danger fw-bold"><?= number_format($row['total_adj']) ?></span>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-muted">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <h6 class="text-primary fw-bold mb-0"><?= number_format($row['end_balance']) ?>
                                                    </h6>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="item" role="tabpanel" aria-labelledby="item-tab">
                            <div class="table-responsive">
                                <table class="table table-hover" id="itemTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th>Item Details</th>
                                            <th class="text-center">Begin</th>
                                            <th class="text-center">IN</th>
                                            <th class="text-center">OUT</th>
                                            <th class="text-center">ADJ</th>
                                            <th class="text-center">End / Pcs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($summary_item as $index => $row): ?>
                                            <tr>
                                                <td class="text-center"><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['item_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($row['item_code']) ?> | <span
                                                            class="badge bg-soft-info text-info"><?= htmlspecialchars($row['location_display']) ?></span></small>
                                                </td>
                                                <td class="text-center fw-semibold text-muted">
                                                    <?= number_format($row['begin_balance']) ?></td>
                                                <td class="text-center">
                                                    <span
                                                        class="text-success fw-bold"><?= $row['total_in'] > 0 ? '+' . number_format($row['total_in']) : '0' ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="text-danger fw-bold"><?= $row['total_out'] > 0 ? '-' . number_format($row['total_out']) : '0' ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['total_adj'] > 0): ?>
                                                        <span
                                                            class="text-success fw-bold">+<?= number_format($row['total_adj']) ?></span>
                                                    <?php elseif ($row['total_adj'] < 0): ?>
                                                        <span class="text-danger fw-bold"><?= number_format($row['total_adj']) ?></span>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-muted">0</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <h6 class="text-primary fw-bold mb-0"><?= number_format($row['end_balance']) ?>
                                                    </h6>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let locationTable, itemTable;

        if (typeof $.fn.DataTable !== 'undefined') {
            locationTable = $('#locationTable').DataTable({
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0] }
                ]
            });
            itemTable = $('#itemTable').DataTable({
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0] }
                ]
            });
        }

        let activityChart;
        const chartOptions = {
            series: [{
                name: 'IN',
                data: <?= json_encode($chartData['in']) ?>
            }, {
                name: 'OUT',
                data: <?= json_encode($chartData['out']) ?>
            }, {
                name: 'ADJUST',
                data: <?= json_encode($chartData['adj']) ?>
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'inherit'
            },
            colors: ['#28a745', '#dc3545', '#ffc107'],
            dataLabels: { enabled: false },
            stroke: { 
                curve: 'smooth', 
                width: 3.5,
                lineCap: 'round'
            },
            shadow: {
                enabled: true,
                color: '#000',
                top: 18,
                left: 7,
                blur: 10,
                opacity: 1
            },
            markers: {
                size: 5,
                strokeWidth: 3,
                hover: { size: 7 }
            },
            xaxis: {
                categories: <?= json_encode($chartData['labels']) ?>,
                labels: {
                    style: { colors: '#919da9' },
                    formatter: function (val) {
                        if (!val) return '';
                        const d = new Date(val);
                        return d.getDate() + ' ' + d.toLocaleString('en-us', { month: 'short' });
                    }
                }
            },
            yaxis: {
                labels: { style: { colors: '#919da9' } }
            },
            legend: { position: 'top', horizontalAlign: 'right' },
            tooltip: {
                x: { format: 'dd MMM yyyy' }
            },
            grid: {
                borderColor: '#f1f1f1',
                padding: { top: 0, right: 0, bottom: 0, left: 10 }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            }
        };

        if (document.querySelector("#activityChart")) {
            activityChart = new ApexCharts(document.querySelector("#activityChart"), chartOptions);
            activityChart.render();
        }

        // Occupancy Chart Initialization
        let occupancyChart;
        const occupancyOptions = {
            series: <?= json_encode(array_column($occupancy, 'value')) ?>,
            labels: <?= json_encode(array_column($occupancy, 'name')) ?>,
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'inherit'
            },
            legend: { position: 'bottom' },
            dataLabels: {
                enabled: true,
                dropShadow: { enabled: false },
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex].toLocaleString();
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString() + " Items";
                    }
                }
            },
            colors: ['#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#1abc9c', '#34495e'],
            stroke: { width: 0 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Stock',
                                fontSize: '14px',
                                fontWeight: 600,
                                color: '#919da9',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            },
                            value: {
                                fontSize: '24px',
                                fontWeight: 700,
                                color: '#172b4c',
                                offsetY: 5
                            }
                        }
                    }
                }
            }
        };

        if (document.querySelector("#occupancyChart")) {
            occupancyChart = new ApexCharts(document.querySelector("#occupancyChart"), occupancyOptions);
            occupancyChart.render();
        }

        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'get_summary');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Updating Dashboard...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            // Using full path if possible or at least ensuring leading slash if routed
            fetch('module/Action/ac_dashboard.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update Stats Grid
                        let t_stock = 0, t_in = 0, t_out = 0, t_adj = 0;

                        // Update Tables
                        if (locationTable && data.location_data) {
                            locationTable.clear();
                            data.location_data.forEach((row, index) => {
                                t_stock += parseInt(row.end_balance);
                                t_in += parseInt(row.total_in);
                                t_out += parseInt(row.total_out);
                                t_adj += parseInt(row.total_adj);

                                let adjDisplay = `<span class="fw-bold text-muted">0</span>`;
                                if (row.total_adj > 0) adjDisplay = `<span class="text-success fw-bold">+${row.total_adj.toLocaleString()}</span>`;
                                else if (row.total_adj < 0) adjDisplay = `<span class="text-danger fw-bold">${row.total_adj.toLocaleString()}</span>`;

                                locationTable.row.add([
                                    `<div class="text-center">${index + 1}</div>`,
                                    `<div class="fw-bold text-dark">${row.location_display}</div>`,
                                    `<div class="text-center fw-semibold text-muted">${row.begin_balance.toLocaleString()}</div>`,
                                    `<div class="text-center text-success fw-bold">${parseInt(row.total_in) > 0 ? '+' + parseInt(row.total_in).toLocaleString() : '0'}</div>`,
                                    `<div class="text-center text-danger fw-bold">${parseInt(row.total_out) > 0 ? '-' + parseInt(row.total_out).toLocaleString() : '0'}</div>`,
                                    `<div class="text-center">${adjDisplay}</div>`,
                                    `<div class="text-center"><h6 class="text-primary fw-bold mb-0">${row.end_balance.toLocaleString()}</h6></div>`
                                ]);
                            });
                            locationTable.draw();
                        }

                        if (itemTable && data.item_data) {
                            itemTable.clear();
                            data.item_data.forEach((row, index) => {
                                let adjDisplay = `<span class="fw-bold text-muted">0</span>`;
                                if (row.total_adj > 0) adjDisplay = `<span class="text-success fw-bold">+${row.total_adj.toLocaleString()}</span>`;
                                else if (row.total_adj < 0) adjDisplay = `<span class="text-danger fw-bold">${row.total_adj.toLocaleString()}</span>`;

                                itemTable.row.add([
                                    `<div class="text-center">${index + 1}</div>`,
                                    `<div>
                                        <div class="fw-bold text-dark">${row.item_name}</div>
                                        <small class="text-muted">${row.item_code} | <span class="badge bg-soft-info text-info">${row.location_display}</span></small>
                                    </div>`,
                                    `<div class="text-center fw-semibold text-muted">${row.begin_balance.toLocaleString()}</div>`,
                                    `<div class="text-center text-success fw-bold">${parseInt(row.total_in) > 0 ? '+' + parseInt(row.total_in).toLocaleString() : '0'}</div>`,
                                    `<div class="text-center text-danger fw-bold">${parseInt(row.total_out) > 0 ? '-' + parseInt(row.total_out).toLocaleString() : '0'}</div>`,
                                    `<div class="text-center">${adjDisplay}</div>`,
                                    `<div class="text-center"><h6 class="text-primary fw-bold mb-0">${row.end_balance.toLocaleString()}</h6></div>`
                                ]);
                            });
                            itemTable.draw();
                        }

                        // Update Metric Labels
                        document.getElementById('stat_total_stock').innerText = t_stock.toLocaleString();
                        document.getElementById('stat_total_in').innerText = '+' + t_in.toLocaleString();
                        document.getElementById('stat_total_out').innerText = '-' + t_out.toLocaleString();
                        document.getElementById('stat_total_adj').innerText = (t_adj >= 0 ? '+' : '') + t_adj.toLocaleString();

                        if (activityChart && data.chartData) {
                            activityChart.updateOptions({ xaxis: { categories: data.chartData.labels } });
                            activityChart.updateSeries([
                                { name: 'IN', data: data.chartData.in },
                                { name: 'OUT', data: data.chartData.out },
                                { name: 'ADJUST', data: data.chartData.adj }
                            ]);
                        }

                        if (occupancyChart && data.occupancy) {
                            occupancyChart.updateOptions({
                                series: data.occupancy.map(i => i.value),
                                labels: data.occupancy.map(i => i.name)
                            });
                        }

                        if (data.low_stock) {
                            const lowStockList = document.getElementById('lowStockList');
                            if (data.low_stock.length > 0) {
                                let html = '<div class="list-group list-group-flush">';
                                data.low_stock.forEach(item => {
                                    let percent = (item.stock_min > 0) ? (item.current_stock / item.stock_min) * 100 : 100;
                                    percent = Math.min(100, percent);
                                    html += `
                                        <div class="list-group-item px-0 list-item-hover border-0 mb-2 rounded-3 p-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">${item.name}</h6>
                                                    <small class="text-muted">${item.item_code}</small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-danger">${parseInt(item.current_stock).toLocaleString()} / ${parseInt(item.stock_min).toLocaleString()}</div>
                                                    <small class="text-muted">Stock / Min</small>
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 4px;">
                                                <div class="progress-bar bg-danger" style="width: ${percent}%"></div>
                                            </div>
                                        </div>`;
                                });
                                html += '</div>';
                                lowStockList.innerHTML = html;
                            } else {
                                lowStockList.innerHTML = '<div class="text-center py-5"><i class="feather-check-circle fs-40 text-success mb-3 d-block"></i><span class="text-muted">All stock levels are healthy!</span></div>';
                            }
                        }

                        if (data.recent_activities) {
                            const activityList = document.getElementById('activityFeedList');
                            if (data.recent_activities.length > 0) {
                                activityList.innerHTML = data.recent_activities.map(activity => {
                                    const date = new Date(activity.created_at);
                                    const timeStr = date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0') + ', ' + date.getDate() + ' ' + date.toLocaleString('en-us', { month: 'short' });
                                    const typeClass = activity.transaction_type;
                                    const badgeClass = activity.transaction_type === 'IN' ? 'success' : (activity.transaction_type === 'OUT' ? 'danger' : 'warning');
                                    
                                    return `
                                        <div class="activity-item ${typeClass}">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold text-dark">${activity.item_name}</span>
                                                <span class="activity-date">${timeStr}</span>
                                            </div>
                                            <div class="mt-1 d-flex align-items-center justify-content-between">
                                                <span class="fs-12">
                                                    <span class="badge bg-soft-${badgeClass} text-${badgeClass} fs-10 me-1">${activity.transaction_type}</span>
                                                    ${Math.abs(activity.qty_mutation).toLocaleString()} units by <strong>${activity.created_by || 'System'}</strong>
                                                </span>
                                            </div>
                                        </div>`;
                                }).join('');
                            } else {
                                activityList.innerHTML = '<div class="text-center py-5"><span class="text-muted">No recent activities found</span></div>';
                            }
                        }

                        if (typeof Swal !== 'undefined') Swal.close();
                    } else {
                        throw new Error(data.message || 'Server returned an error');
                    }
                })
                .catch(error => {
                    console.error('Dashboard Update Error:', error);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Update Failed', 
                            text: error.message + (error.debug_info ? '\nCheck console for details.' : '')
                        });
                    }
                });
        });
    });

    function exportSummary() {
        alert('Export function coming soon!');
    }
</script>