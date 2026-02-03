<!-- Modal for Input Inventory Data -->
<div class="modal fade" id="inputDataModal" tabindex="-1" aria-labelledby="inputDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inputDataModalLabel">Input Inventory Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="inputDataForm">
                    <div class="mb-3">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <select class="form-select" name="location_id" id="inputLocation" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['id'] ?>">
                                    <?= htmlspecialchars($loc['location']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select class="form-select" name="item_id" id="inputItem" required>
                            <option value="">Select Item</option>
                            <?php 
                            $active_items = get_active_items();
                            foreach ($active_items as $item): 
                            ?>
                                <option value="<?= $item['id'] ?>" data-item-code="<?= htmlspecialchars($item['item_code']) ?>">
                                    <?= htmlspecialchars($item['item_code']) ?> - <?= htmlspecialchars($item['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="qty" id="inputQty" placeholder="Enter quantity" 
                               min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="inputNotes" placeholder="Enter notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitInputData()">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
#inputDataModal .modal-content {
    background-color: #fff;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

#inputDataModal {
    z-index: 1060 !important;
    backdrop-filter: none !important;
    filter: none !important;
}

#inputDataModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#inputDataModal .modal-header .btn-close {
    filter: none;
}

.modal-backdrop {
    z-index: 1050 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('inputDataModal');
    if (modal) {
        document.body.appendChild(modal);
    }
});

// Get active items (for dropdowns in forms)
let allItems = [];
async function loadAllItems() {
    // Items already loaded via PHP, no need for async fetch
    console.log('Items pre-loaded via server-side PHP');
}

// Load items when location is selected (optional - for future filtering by location)
document.getElementById('inputLocation')?.addEventListener('change', function() {
    // Items are already loaded from server, no filtering needed for now
    // In future, can add filtering logic here if needed
});

// Submit Input Data Form
function submitInputData() {
    const form = document.getElementById('inputDataForm');
    const locationId = document.getElementById('inputLocation').value;
    const itemId = document.getElementById('inputItem').value;
    const qty = document.getElementById('inputQty').value;
    const notes = document.getElementById('inputNotes').value;

    if (!locationId || !itemId || !qty) {
        alert('Please fill in all required fields');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'transaction');
    formData.append('transaction_type', 'IN');
    formData.append('location_id', locationId);
    formData.append('item_id', itemId);
    formData.append('qty', qty);
    formData.append('notes', notes);

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Saving...',
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
                    // Reset form
                    form.reset();
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('inputDataModal'));
                    modal.hide();
                    // Reload page
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
                    text: data.message || 'Failed to save data'
                });
            } else {
                alert(data.message || 'Failed to save data');
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadAllItems();
});
</script>
