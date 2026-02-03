<!-- Transaction Modal -->
<div class="modal fade" id="inputLocationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inputLocationModalTitle">Stock Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="locationForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" id="location_id" name="id" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" id="locationLabel">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location_name" required>
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

    document.addEventListener('DOMContentLoaded', function () {
        const modalElem = document.getElementById('inputLocationModal');
        if (modalElem) {
            document.body.appendChild(modalElem);
            locationModal = new bootstrap.Modal(modalElem);
        }
    });

    function openinputLocationModal() {
        const form = document.getElementById('locationForm');
        form.reset();
        const submitBtn = document.getElementById('submitBtn');
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

        fetch('module/Action/ac_location.php', {
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