<?php
require_once __DIR__ . '/../query/query.php';
require_once __DIR__ . '/Action/ac_item.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = null;
$pageTitle = 'Create Item';
$itemCode = '';

if ($id > 0) {
    $result = item_show($id);
    if ($result['status'] === 'success') {
        $item = $result['data'];
        $pageTitle = 'Edit Item';
        $itemCode = $item['item_code'];
    } else {
        header('Location: log_data');
        exit;
    }
}
?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"><?= $pageTitle ?></h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="log_data">Log Data</a></li>
            <li class="breadcrumb-item"><?= $pageTitle ?></li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <form id="itemForm" action="javascript:void(0);" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="row">
                            <!-- Form Fields -->
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <label class="form-label">Item Code</label>
                                    <input type="text" class="form-control" value="<?= $itemCode ?: 'Auto-generated' ?>" readonly style="background-color: #f8f9fa;">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="itemName" 
                                           placeholder="Enter item name" 
                                           value="<?= $item ? htmlspecialchars($item['name']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="itemDescription" 
                                              placeholder="Enter item description"
                                              rows="3"><?= $item ? htmlspecialchars($item['description'] ?? '') : '' ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Image Upload & Preview -->
                            <div class="col-md-5 d-flex flex-column align-items-center justify-content-start">
                                <div class="mb-3 w-100">
                                    <label class="form-label">
                                        Upload Picture <?= !$item ? '<span class="text-danger">*</span>' : '(optional)' ?>
                                    </label>
                                    <input type="file" class="form-control" id="pictureInput" name="picture" 
                                           accept="image/jpeg,image/jpg,image/png" 
                                           onchange="previewImage(event)" <?= !$item ? 'required' : '' ?>>
                                    <small class="text-muted">Max 5MB. JPG, JPEG, PNG only.</small>
                                </div>
                                
                                <div class="mb-3 w-100 text-center">
                                    <?php if ($item && $item['picture']): ?>
                                        <img id="imagePreview" 
                                             src="assets/uploads/items/<?= htmlspecialchars($item['picture']) ?>" 
                                             alt="Preview" 
                                             style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #eee; object-fit: cover;">
                                    <?php else: ?>
                                        <img id="imagePreview" src="#" alt="Preview" 
                                             style="display: none; max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #eee; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="w-100 text-center mt-auto">
                                    <button type="submit" class="btn btn-primary w-75">
                                        <i class="feather-save me-2"></i>Save
                                    </button>
                                    <a href="log_data" class="btn btn-secondary w-75 mt-2">
                                        <i class="feather-x me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds 5MB limit!');
            input.value = '';
            preview.src = '#';
            preview.style.display = 'none';
            return;
        }
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG, JPEG, and PNG files are allowed!');
            input.value = '';
            preview.src = '#';
            preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'inline-block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
}

document.getElementById('itemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'save');
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    fetch('module/Action/ac_item', {
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
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'log_data';
                });
            } else {
                alert(data.message);
                window.location.href = 'log_data';
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to save item'
                });
            } else {
                alert(data.message || 'Failed to save item');
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
});
</script>