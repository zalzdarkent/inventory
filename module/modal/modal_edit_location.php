<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLocation">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" name="action" value="save">

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Nama Lokasi</label>
                        <input type="text" class="form-control" id="edit_location_name" name="location_name" placeholder="Contoh: Gudang A"
                            required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Select Items <span class="text-danger">*</span></label>
                        <select id="editItemSelect" name="item_ids[]" class="form-control" data-select2-selector="tag" multiple required style="width: 100%;">
                            <!-- Options will be loaded via JS -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #editLocationModal {
        z-index: 1060 !important;
        backdrop-filter: none !important;
        filter: none !important;
    }

    .modal-backdrop {
        z-index: 1050 !important;
    }
</style>

<script>
    let editItemSelect;

    document.addEventListener('DOMContentLoaded', function() {
        const modalElem = document.getElementById('editLocationModal');
        if (modalElem) {
            document.body.appendChild(modalElem);
            
            // Initialize Select2 when modal is shown
            modalElem.addEventListener('shown.bs.modal', function () {
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    editItemSelect = $('#editItemSelect').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#editLocationModal'),
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
        
        loadItemsForEditControl();
    });

    function loadItemsForEditControl() {
        fetch('module/Action/ac_inventory_log', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=get_active_items'
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const selectElem = $('#editItemSelect');
                    selectElem.empty();
                    data.data.forEach(item => {
                        const option = new Option(
                            `${item.item_code} - ${item.name}`,
                            item.id,
                            false,
                            false
                        );
                        $(option).attr('data-bg', 'bg-primary');
                        selectElem.append(option);
                    });
                }
            });
    }

    function openeditLocationModal(id) {
        const formData = new URLSearchParams();
        formData.append('action', 'get');
        formData.append('id', id);

        fetch('module/Action/ac_location', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    document.getElementById('edit_id').value = result.data.id;
                    document.getElementById('edit_location_name').value = result.data.location;

                    if (result.data.item_ids) {
                        const itemIds = result.data.item_ids.split(',');
                        $('#editItemSelect').val(itemIds).trigger('change');
                    } else {
                        $('#editItemSelect').val(null).trigger('change');
                    }

                    var editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));
                    editModal.show();
                } else {
                    Swal.fire('Gagal', result.message, 'error');
                }
            })
            .catch(err => {
                console.error('Fetch Error:', err);
                Swal.fire('Error', 'Gagal mengambil data dari server', 'error');
            });
    }

    document.getElementById('formEditLocation').addEventListener('submit', function (e) {
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
                            title: 'Berhasil',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', data.message, 'error');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(err => {
                console.error('Error:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Terjadi kesalahan pada sistem', 'error');
                } else {
                    alert('Terjadi kesalahan pada sistem');
                }
            });
    });
</script>
