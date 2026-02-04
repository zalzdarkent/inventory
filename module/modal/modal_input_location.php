<!-- Transaction Modal -->
<div class="modal fade" id="inputLocationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inputLocationModalTitle">Create New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="locationForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="location_id" name="id" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Items <span class="text-danger">*</span></label>
                        <select id="itemSelect" name="item_ids[]" class="form-control" data-select2-selector="tag" multiple required style="width: 100%;">
                            <!-- Options will be loaded here -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Create Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #inputLocationModal {
        z-index: 1060 !important;
        backdrop-filter: none !important;
        filter: none !important;
    }

    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>

<script>
    let locationModal;
    let itemSelect;

    document.addEventListener('DOMContentLoaded', function () {
        const modalElem = document.getElementById('inputLocationModal');
        if (modalElem) {
            document.body.appendChild(modalElem);
            locationModal = new bootstrap.Modal(modalElem);

            // Re-initialize Select2 when modal is shown to fix positioning/focus issues
            modalElem.addEventListener('shown.bs.modal', function () {
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    itemSelect = $('#itemSelect').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#inputLocationModal'),
                        placeholder: 'Search and select items...',
                        allowClear: true,
                        width: '100%',
                        templateResult: typeof bgformat !== 'undefined' ? bgformat : null,
                        templateSelection: typeof bgformat !== 'undefined' ? bgformat : null,
                        escapeMarkup: function(m) { return m; }
                    });
                }
            });
        }
        
        loadActiveItems();
    });

    function loadActiveItems() {
        fetch('module/Action/ac_inventory_log', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_active_items'
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const selectElem = $('#itemSelect');
                    selectElem.empty();
                    
                    data.data.forEach(item => {
                        const option = new Option(
                            `${item.item_code} - ${item.name}`,
                            item.id,
                            false,
                            false
                        );
                        // Add data-bg for the project's 'tag' selector if needed
                        $(option).attr('data-bg', 'bg-primary');
                        selectElem.append(option);
                    });
                    
                    if (itemSelect) {
                        itemSelect.trigger('change');
                    }
                }
            })
            .catch(error => console.error('Error loading items:', error));
    }

    function openinputLocationModal() {
        const form = document.getElementById('locationForm');
        form.reset();
        document.getElementById('location_id').value = '0';
        
        if (itemSelect) {
            itemSelect.val(null).trigger('change');
        }
        
        if (locationModal) {
            locationModal.show();
        }
    }

    document.getElementById('locationForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }

        fetch('module/Action/ac_location', {
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
