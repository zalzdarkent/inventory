<?php
require_once __DIR__ . '/Action/ac_inventory_log.php';

$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($item_id <= 0) {
    header('Location: in_out');
    exit;
}

$item = get_item_by_id($item_id);
if (!$item) {
    header('Location: in_out');
    exit;
}

$logs = inventory_logs($item_id, 1000);
$current_stock = get_item_stock($item_id);
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Transaction History</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="in_out">Inventory</a></li>
            <li class="breadcrumb-item">History</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="in_out" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <img src="assets/uploads/items/<?= htmlspecialchars($item['picture']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="img-thumbnail" 
                                 style="width: 80px; height: 80px; object-fit: cover;"
                                 onerror="this.src='assets/images/general/placeholder.svg'">
                        </div>
                        <div class="col">
                            <h4 class="mb-1"><?= htmlspecialchars($item['name']) ?></h4>
                            <div class="text-muted mb-2">
                                <strong>Code:</strong> <?= htmlspecialchars($item['item_code']) ?> | 
                                <strong>Location:</strong> <?= htmlspecialchars($item['location_name'] ?? '-') ?>
                            </div>
                            <div>
                                <span class="badge bg-<?= $current_stock <= 0 ? 'danger' : ($current_stock < 10 ? 'warning' : 'success') ?> fs-5">
                                    Current Stock: <?= $current_stock ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">All Transactions</h5>
                    <div class="card-header-action">
                        <span class="badge bg-primary"><?= count($logs) ?> transactions</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-5">
                            <i class="feather-inbox fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No transaction history found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date & Time</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Running Stock</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $running_stock = 0;
                                    $reversed_logs = array_reverse($logs);
                                    foreach ($reversed_logs as $index => $log): 
                                        $running_stock += $log['qty_mutation'];
                                    endforeach;
                                    
                                    foreach ($logs as $index => $log): 
                                    ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= date('d M Y', strtotime($log['created_at'])) ?></strong><br>
                                                    <small class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $type = $log['transaction_type'];
                                                $notes = $log['notes'] ?? '';
                                                $is_in = ($type === 'IN' || ($type === 'MOVE' && strpos($notes, 'Transfer from') !== false));
                                                $is_adj = ($type === 'ADJUST');
                                                
                                                if ($is_in): ?>
                                                    <span class="badge bg-success">
                                                        <i class="feather-arrow-down"></i> STOCK IN
                                                    </span>
                                                <?php elseif ($is_adj): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="feather-edit"></i> STOCK ADJUST
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="feather-arrow-up"></i> STOCK OUT
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($is_adj): ?>
                                                    <strong class="fs-5 text-warning">
                                                        <?= ($log['qty_mutation'] >= 0 ? '+' : '') . $log['qty_mutation'] ?>
                                                    </strong>
                                                <?php else: ?>
                                                    <strong class="fs-5 <?= $is_in ? 'text-success' : 'text-danger' ?>">
                                                        <?= ($log['qty_mutation'] > 0 ? '+' : '') . $log['qty_mutation'] ?>
                                                    </strong>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stock_at_time = $current_stock;
                                                for ($i = 0; $i < $index; $i++) {
                                                    $stock_at_time -= $logs[$i]['qty_mutation'];
                                                }
                                                ?>
                                                <span class="badge bg-info"><?= $stock_at_time ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($log['notes'] ?: '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#historyTable').DataTable({
            pageLength: 25,
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: [5] }
            ]
        });
    }
});
</script>
