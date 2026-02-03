<!-- Move Stock Modal -->
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Move Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moveForm">
                <div class="modal-body">
                    <input type="hidden" id="move_item_id" name="item_id">
                    <input type="hidden" name="is_transfer" value="true">
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="move_item_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Source Location</label>
                        <input type="hidden" id="move_location_id" name="location_id">
                        <input type="text" class="form-control" id="move_location_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target Location <span class="text-danger">*</span></label>
                        <select class="form-select" id="move_target_location_id" name="target_location_id" required>
                            <option value="">Select Target Location</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="move_qty" name="qty" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="move_notes" name="notes" rows="3" placeholder="Reason for moving..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="moveSubmitBtn">Move Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #moveModal {
        z-index: 1060 !important;
        backdrop-filter: none !important;
        filter: none !important;
    }
    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('moveModal');
    if (modal) {
        document.body.appendChild(modal);
    }
});

function openMoveModal(itemId, itemName, locationId, locationName) {
    document.getElementById('move_item_id').value = itemId;
    document.getElementById('move_item_name').value = itemName;
    document.getElementById('move_location_id').value = locationId;
    document.getElementById('move_location_name').value = locationName || 'No Location';
    document.getElementById('move_qty').value = '';
    document.getElementById('move_notes').value = '';
    document.getElementById('move_target_location_id').value = '';
    
    new bootstrap.Modal(document.getElementById('moveModal')).show();
}

document.getElementById('moveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const sourceLoc = document.getElementById('move_location_id').value;
    const targetLoc = document.getElementById('move_target_location_id').value;
    
    if (sourceLoc === targetLoc) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Target location must be different from source location.' });
        } else {
            alert('Target location must be different from source location.');
        }
        return;
    }

    const formData = new FormData(this);
    formData.append('action', 'transaction');
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Moving Stock...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }
    
    fetch('module/Action/ac_inventory_log.php', {
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
                    location.reload();
                });
            } else {
                alert(data.message);
                location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during move.'
            });
        } else {
            alert('An error occurred during move.');
        }
    });
});
</script>
