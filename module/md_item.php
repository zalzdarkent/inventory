<?php
require_once __DIR__ . '/Action/ac_item.php';

$items = item_index();
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Log Data</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Log Data</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="log_data_create" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>New Item</span>
                </a>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-soft-info border-0 mb-0">
                                <i class="feather-info me-2"></i>
                                Listing all registered products. Use the search bar in the table to find specific items.
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="80">Picture</th>
                                    <th>Item Code</th>
                                    <th>Name</th>
                                    <th class="text-center">Min Stock</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr data-item-id="<?= $item['id'] ?>">
                                            <td>
                                                <img src="assets/uploads/items/<?= htmlspecialchars($item['picture']) ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;"
                                                     onerror="this.src='assets/images/general/placeholder.svg'">
                                            </td>
                                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($item['item_code']) ?></span></td>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-soft-secondary text-secondary fw-bold">
                                                    <?= number_format($item['stock_min'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($item['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="hstack gap-2 justify-content-end">
                                                    <a href="log_data_create&id=<?= $item['id'] ?>" 
                                                       class="avatar-text avatar-md" title="Edit">
                                                        <i class="feather-edit-3"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                       class="avatar-text avatar-md <?= $item['is_active'] ? 'text-danger' : 'text-success' ?>"
                                                       onclick="toggleStatus(<?= $item['id'] ?>, '<?= $item['is_active'] ? 'deactivate' : 'activate' ?>')"
                                                       title="<?= $item['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="feather <?= $item['is_active'] ? 'feather-slash' : 'feather-check-circle' ?>"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#itemsTable').DataTable({
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 5] }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products...",
                lengthMenu: "Show _MENU_ entries"
            }
        });
    }
});

function toggleStatus(id, actionText) {
    const confirmMsg = actionText === 'activate' 
        ? 'Are you sure you want to activate this item?' 
        : 'Are you sure you want to deactivate this item?';
    
    const successMsg = actionText === 'activate' 
        ? 'Item activated successfully!' 
        : 'Item deactivated successfully!';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Confirm Action',
            text: confirmMsg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performToggle(id, successMsg);
            }
        });
    } else {
        if (confirm(confirmMsg)) performToggle(id, successMsg);
    }
}

function performToggle(id, successMsg) {
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', id);
    
    fetch('module/Action/ac_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: successMsg,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                location.reload();
            }
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Connection failed' });
    });
}
</script>
