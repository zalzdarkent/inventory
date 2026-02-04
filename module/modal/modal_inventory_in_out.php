<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalTitle">Stock Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm">
                <div class="modal-body">
                    <input type="hidden" id="item_id" name="item_id">
                    <input type="hidden" id="transaction_type" name="transaction_type">
                    
                    <div class="mb-3">
                        <label class="form-label" id="locationLabel">Location</label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['location']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="item_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" id="qtyLabel">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="qty" name="qty" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #transactionModal {
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
    const modal = document.getElementById('transactionModal');
    if (modal) {
        document.body.appendChild(modal);
    }
});

function openTransactionModal(itemId, type, itemName, defaultLocationId = null) {
    console.log('Open Modal:', { itemId, type, itemName, defaultLocationId });

    document.getElementById('item_id').value = itemId;
    document.getElementById('transaction_type').value = type;
    document.getElementById('item_name').value = itemName;
    document.getElementById('qty').value = '';
    document.getElementById('notes').value = '';
    
    const locIdStr = defaultLocationId !== null ? String(defaultLocationId) : '';
    document.getElementById('location_id').value = locIdStr;
    
    const modalTitle = type === 'IN' ? 'Stock In' : (type === 'OUT' ? 'Stock Out' : 'Stock Adjustment');
    document.getElementById('transactionModalTitle').textContent = modalTitle;
    
    const submitBtn = document.getElementById('submitBtn');
    const qtyLabel = document.getElementById('qtyLabel');

    if (type === 'IN') {
        submitBtn.className = 'btn btn-success';
        submitBtn.textContent = 'Add Stock';
        qtyLabel.innerHTML = 'Quantity <span class="text-danger">*</span>';
        document.getElementById('qty').min = "1";
    } else if (type === 'OUT') {
        submitBtn.className = 'btn btn-danger';
        submitBtn.textContent = 'Remove Stock';
        qtyLabel.innerHTML = 'Quantity <span class="text-danger">*</span>';
        document.getElementById('qty').min = "1";
    } else {
        submitBtn.className = 'btn btn-warning';
        submitBtn.textContent = 'Adjust Stock';
        qtyLabel.innerHTML = 'Physical Count (Hasil STO) <span class="text-danger">*</span>';
        document.getElementById('qty').removeAttribute('min');
    }
    
    new bootstrap.Modal(document.getElementById('transactionModal')).show();
}

document.getElementById('transactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const type = document.getElementById('transaction_type').value;
    formData.append('action', type === 'ADJUST' ? 'adjustment' : 'transaction');
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Processing...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }
    
    fetch('module/Action/ac_inventory_log', {
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
                text: 'An error occurred'
            });
        } else {
            alert('An error occurred');
        }
    });
});
</script>
