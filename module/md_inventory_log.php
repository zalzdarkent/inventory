<?php
require_once __DIR__ . '/Action/ac_inventory_log.php';

$items = inventory_index();
$recent_logs = get_inventory_logs(null, 20);
$locations = get_all_locations_inv();
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Inventory Management</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Inventory</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <!-- Stock Cards -->
    <div class="row mb-4">
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-12 fw-normal text-muted mb-1">Total Items</div>
                            <h4 class="fw-bold mb-0"><?= count($items) ?></h4>
                        </div>
                        <div class="avatar-text avatar-lg bg-primary text-white">
                            <i class="feather-package"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-12 fw-normal text-muted mb-1">Low Stock</div>
                            <h4 class="fw-bold mb-0 text-warning">
                                <?= count(array_filter($items, fn($i) => $i['current_stock'] < 10)) ?>
                            </h4>
                        </div>
                        <div class="avatar-text avatar-lg bg-warning text-white">
                            <i class="feather-alert-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-12 fw-normal text-muted mb-1">Out of Stock</div>
                            <h4 class="fw-bold mb-0 text-danger">
                                <?= count(array_filter($items, fn($i) => $i['current_stock'] <= 0)) ?>
                            </h4>
                        </div>
                        <div class="avatar-text avatar-lg bg-danger text-white">
                            <i class="feather-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-12 fw-normal text-muted mb-1">Total Stock</div>
                            <h4 class="fw-bold mb-0 text-success">
                                <?= array_sum(array_column($items, 'current_stock')) ?>
                            </h4>
                        </div>
                        <div class="avatar-text avatar-lg bg-success text-white">
                            <i class="feather-trending-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Current Stock</h5>
                    <div class="card-header-action">
                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#inputDataModal">
                            <i class="feather-plus me-1"></i>Input Data
                        </button>
                        <a href="index.php?page=adjustment_history" class="btn btn-sm btn-light-brand me-1">
                            <i class="feather-clock me-1"></i>History Adjustment
                        </a>
                        <button class="btn btn-sm btn-light-brand" onclick="refreshTable()">
                            <i class="feather-refresh-cw me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="stockTable">
                            <thead>
                                <tr>
                                    <th>Picture</th>
                                    <th>Item Code</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Stock</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="assets/uploads/items/<?= htmlspecialchars($item['picture']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 onerror="this.src='assets/images/general/placeholder.svg'">
                                        </td>
                                        <td><?= htmlspecialchars($item['item_code']) ?></td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['location_name'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $item['current_stock'] <= 0 ? 'danger' : ($item['current_stock'] < 10 ? 'warning' : 'success') ?>">
                                                <?= $item['current_stock'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="javascript:void(0);" 
                                                   class="avatar-text avatar-md text-success" 
                                                   onclick="openTransactionModal(<?= $item['id'] ?>, 'IN', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['location_id'] ?? 'null' ?>)"
                                                   title="Stock In">
                                                    <i class="feather feather-arrow-down-circle"></i>
                                                </a>
                                                <a href="javascript:void(0);" 
                                                   class="avatar-text avatar-md text-danger" 
                                                   onclick="openTransactionModal(<?= $item['id'] ?>, 'OUT', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['location_id'] ?? 'null' ?>)"
                                                   title="Stock Out">
                                                    <i class="feather feather-arrow-up-circle"></i>
                                                </a>
                                                <a href="javascript:void(0);" 
                                                   class="avatar-text avatar-md text-primary" 
                                                   onclick="openMoveModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['location_id'] ?? 'null' ?>, '<?= htmlspecialchars($item['location_name'] ?? '', ENT_QUOTES) ?>')"
                                                   title="Move">
                                                    <i class="feather feather-refresh-ccw"></i>
                                                </a>
                                                <a href="javascript:void(0);" 
                                                   class="avatar-text avatar-md text-warning" 
                                                   onclick="openTransactionModal(<?= $item['id'] ?>, 'ADJUST', '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['location_id'] ?? 'null' ?>)"
                                                   title="Adjustment (STO)">
                                                    <i class="feather feather-check-square"></i>
                                                </a>
                                                <a href="index.php?page=in_out_history&item_id=<?= $item['id'] ?>" 
                                                   class="avatar-text avatar-md" 
                                                   title="View History">
                                                    <i class="feather feather-clock"></i>
                                                </a>
                                            </div>
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

    <!-- Recent Transactions -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="logsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Notes</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                    <tr>
                                        <td><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($log['item_code']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($log['item_name']) ?></small>
                                            </div>
                                        </td>
                                         <td>
                                             <?php 
                                             $type = $log['transaction_type'];
                                             $notes = $log['notes'] ?? '';
                                             
                                             if ($type === 'IN' || ($type === 'MOVE' && strpos($notes, 'Transfer from') !== false)): ?>
                                                 <span class="badge bg-success">
                                                     <i class="feather-arrow-down"></i> IN
                                                 </span>
                                             <?php elseif ($type === 'ADJUST'): ?>
                                                 <span class="badge bg-warning">
                                                     <i class="feather-edit"></i> ADJUST
                                                 </span>
                                             <?php else: // OUT or MOVE (Transfer to) ?>
                                                 <span class="badge bg-danger">
                                                     <i class="feather-arrow-up"></i> OUT
                                                 </span>
                                             <?php endif; ?>
                                         </td>
                                        <td>
                                            <strong>
                                                <?php 
                                                if ($log['transaction_type'] === 'ADJUST') {
                                                    echo ($log['qty_mutation'] >= 0 ? '+' : '-') . abs($log['qty_mutation']);
                                                } else {
                                                    echo ($log['qty_mutation'] > 0 ? '+' : '') . $log['qty_mutation'];
                                                }
                                                ?>
                                            </strong>
                                        </td>
                                        <td><?= htmlspecialchars($log['notes'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-soft-secondary text-secondary">
                                                <i class="feather-user me-1"></i><?= htmlspecialchars($log['created_by'] ?? 'System') ?>
                                            </span>
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

<?php include __DIR__ . '/modal/modal_inventory_in_out.php'; ?>
<?php include __DIR__ . '/modal/modal_inventory_move.php'; ?>

<script>
let stockTable, logsTable;

document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        stockTable = $('#stockTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 5] }
            ]
        });
        
        logsTable = $('#logsTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }
});



function refreshTable() {
    location.reload();
}
</script>
<?php include __DIR__ . '/modal/modal_input_data.php'; ?>