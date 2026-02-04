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
                        <div class="col-md-8">
                            <label class="form-label">Search Item</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="searchInput" placeholder="Type to search..." autocomplete="off">
                                <div id="autocompleteResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-secondary w-100" id="resetFilter">
                                <i class="feather-refresh-cw me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Picture</th>
                                    <th>Item Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No items found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr data-item-id="<?= $item['id'] ?>">
                                            <td>
                                                <img src="assets/uploads/items/<?= htmlspecialchars($item['picture']) ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     onerror="this.src='assets/images/general/placeholder.svg'">
                                            </td>
                                            <td><?= htmlspecialchars($item['item_code']) ?></td>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td>
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
                                                        <i class="feather feather-edit-3"></i>
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
function toggleStatus(id, actionText) {
    const confirmMsg = actionText === 'activate' 
        ? 'Are you sure you want to activate this item?' 
        : 'Are you sure you want to deactivate this item?';
    
    const successMsg = actionText === 'activate' 
        ? 'Item activated successfully!' 
        : 'Item deactivated successfully!';

    if (typeof Swal === 'undefined') {
        if (!confirm(confirmMsg)) return;
    } else {
        Swal.fire({
            title: 'Confirm Action',
            text: confirmMsg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.value === true || result.isConfirmed === true) {
                performToggle(id, successMsg);
            }
        });
        return;
    }
    
    performToggle(id, successMsg);
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
                }).then(() => {
                    location.reload();
                });
            } else {
                alert(successMsg);
                location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to toggle status'
                });
            } else {
                alert(data.message || 'Failed to toggle status');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.'
            });
        } else {
            alert('An error occurred. Please try again.');
        }
    });
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const keyword = this.value.trim();
    
    if (keyword.length < 2) {
        document.getElementById('autocompleteResults').style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'search');
        formData.append('keyword', keyword);
        
        fetch('module/Action/ac_item.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('autocompleteResults');
            
            if (data.status === 'success' && data.data.length > 0) {
                let html = '';
                data.data.forEach(item => {
                    html += `<a href="#" class="list-group-item list-group-item-action" data-item-id="${item.id}">
                        <strong>${item.item_code}</strong> - ${item.name}
                    </a>`;
                });
                resultsDiv.innerHTML = html;
                resultsDiv.style.display = 'block';
                
                resultsDiv.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const itemId = this.dataset.itemId;
                        highlightItem(itemId);
                        resultsDiv.style.display = 'none';
                        document.getElementById('searchInput').value = '';
                    });
                });
            } else {
                resultsDiv.innerHTML = '<div class="list-group-item">No results found</div>';
                resultsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('#searchInput') && !e.target.closest('#autocompleteResults')) {
        document.getElementById('autocompleteResults').style.display = 'none';
    }
});

function highlightItem(itemId) {
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    rows.forEach(row => row.style.backgroundColor = '');
    
    const targetRow = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (targetRow) {
        targetRow.style.backgroundColor = '#fff3cd';
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
            targetRow.style.backgroundColor = '';
        }, 3000);
    }
}

document.getElementById('filterLocation').addEventListener('change', function() {
    const locationId = this.value;
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    
    if (locationId === '') {
        rows.forEach(row => row.style.display = '');
    } else {
        rows.forEach(row => {
            const rowLocationId = row.dataset.locationId;
            row.style.display = rowLocationId === locationId ? '' : 'none';
        });
    }
});

document.getElementById('resetFilter').addEventListener('click', function() {
    document.getElementById('filterLocation').value = '';
    document.getElementById('searchInput').value = '';
    document.getElementById('autocompleteResults').style.display = 'none';
    
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    rows.forEach(row => {
        row.style.display = '';
        row.style.backgroundColor = '';
    });
});
</script>
