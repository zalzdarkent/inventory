<div class="modal fade" id="editLocationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLocation">
                <div class="modal-body">
                    <input type="hidden" id="edit_id">

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Nama Lokasi</label>
                        <input type="text" class="form-control" id="edit_location_name" placeholder="Contoh: Gudang A"
                            required>
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
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editLocationModal');
        if (modal) {
            document.body.appendChild(modal);
        }
    });

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
                    // Set nilai ke input modal
                    document.getElementById('edit_id').value = result.data.id;
                    document.getElementById('edit_location_name').value = result.data.location;

                    // Tampilkan modal
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

        const id = document.getElementById('edit_id').value;
        const locationName = document.getElementById('edit_location_name').value;

        const formData = new URLSearchParams();
        formData.append('action', 'save');
        formData.append('id', id);
        formData.append('location_name', locationName);

        fetch('module/Action/ac_location', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Terjadi kesalahan pada sistem', 'error');
            });
    });
</script>