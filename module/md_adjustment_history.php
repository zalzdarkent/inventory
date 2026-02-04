<?php
require_once __DIR__ . '/Action/ac_inventory_log.php';

$history = get_adjustment_history();
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Adjustment History (STO)</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="in_out">Inventory</a></li>
            <li class="breadcrumb-item">Adjustment History</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Stock Take History</h5>
                    <div class="card-header-action">
                        <button class="btn btn-sm btn-light-brand" onclick="location.reload()">
                            <i class="feather-refresh-cw me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="adjustmentTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th>Prev Stock</th>
                                    <th>Adjustment</th>
                                    <th>New Stock</th>
                                    <th>Notes</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $adj): ?>
                                    <tr>
                                        <td><?= date('d M Y H:i', strtotime($adj['created_at'])) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($adj['item_code']) ?></strong><br>
                                            <small><?= htmlspecialchars($adj['item_name']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($adj['location_name'] ?? '-') ?></td>
                                        <td><?= $adj['previous_stock'] ?></td>
                                        <td>
                                            <span class="fw-bold text-<?= $adj['adj_type'] == 1 ? 'success' : 'danger' ?>">
                                                <?= ($adj['adj_type'] == 1 ? '+' : '-') . abs($adj['adjusted_qty']) ?>
                                            </span>
                                        </td>
                                        <td><?= $adj['new_stock'] ?></td>
                                        <td><?= htmlspecialchars($adj['notes'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-soft-secondary text-secondary">
                                                <i class="feather-user me-1"></i><?= htmlspecialchars($adj['created_by'] ?? 'System') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($adj['status'] === 'ACTIVE'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Rolled Back</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($adj['status'] === 'ACTIVE'): ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="rollbackAdjustment(<?= $adj['id'] ?>)"
                                                        title="Rollback Adjustment">
                                                    <i class="feather-rotate-ccw me-1"></i>Rollback
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">N/A</span>
                                            <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#adjustmentTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    }
});

function rollbackAdjustment(id) {
    if (typeof Swal === 'undefined') {
        if (!confirm('Are you sure you want to rollback this adjustment?')) return;
    } else {
        Swal.fire({
            title: 'Rollback Adjustment?',
            text: "This will reverse the stock change.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, rollback!'
        }).then((result) => {
            if (result.isConfirmed) {
                processRollback(id);
            }
        });
        return;
    }
    processRollback(id);
}

function processRollback(id) {
    const formData = new FormData();
    formData.append('action', 'rollback');
    formData.append('adj_id', id);

    fetch('module/Action/ac_inventory_log.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Success', data.message, 'success').then(() => location.reload());
            } else {
                alert(data.message);
                location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during rollback');
    });
}
</script>
