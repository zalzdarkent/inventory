<?php
require_once __DIR__ . '/Action/ac_location.php';
$locations = location_index();
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Location</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Location</li>
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
                <a href="javascript:void(0)" onclick="openinputLocationModal()" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>
                    <span>New Location</span>
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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="locationTable">
                            <thead>
                                <tr>
                                    <th class="wd-30">#</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($locations)): ?>
                                    <?php foreach ($locations as $index => $row): ?>
                                        <tr class="single-item">
                                            <td><?= $index + 1 ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($row["location"]) ?></td>
                                            <td>
                                                <?php if ($row["is_active"]): ?>
                                                    <span class="badge bg-soft-success text-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-soft-danger text-danger">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="hstack gap-2 justify-content-end">
                                                    <a href="javascript:void(0);"
                                                        data-id="<?= $row["id"] ?>"
                                                        onclick="openeditLocationModal(<?= $row["id"] ?>)"
                                                        class="avatar-text avatar-md" title="Edit">
                                                        <i class="feather feather-edit-3"></i>
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                        class="avatar-text avatar-md <?= $row["is_active"] ? "text-danger" : "text-success" ?>"
                                                        onclick="toggleStatus(<?= $row["id"] ?>, '<?= $row["is_active"] ? "nonaktif" : "aktif" ?>')"
                                                        title="<?= $row["is_active"] ? "Nonaktifkan" : "Aktifkan" ?>">
                                                        <i
                                                            class="feather <?= $row["is_active"] ? "feather-slash" : "feather-check-circle" ?>"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No locations found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/modal/modal_input_location.php'; ?>
<?php include __DIR__ . '/modal/modal_edit_location.php'; ?>

<script>
    function toggleStatus(id, actionText) {
    if (typeof Swal === 'undefined') {
        if (confirm('Yakin mau ' + actionText + ' lokasi ini?')) {
            const formData = new URLSearchParams();
            formData.append('action', 'toggle_status');
            formData.append('id', id);
            fetch('module/Action/ac_location', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(() => window.location.reload());
        }
        return;
    }

    var textAction = actionText === 'nonaktif' ? 'menonaktifkan' : 'mengaktifkan';

    Swal.fire({
        title: 'Yakin?',
        text: 'Mau ' + textAction + ' lokasi ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Yakin!'
    }).then((result) => {
        if (result.value === true || result.isConfirmed === true) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            var formData = new URLSearchParams();
            formData.append('action', 'toggle_status');
            formData.append('id', id);

            fetch('module/Action/ac_location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Response tidak valid'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan: ' + error.message
                    });
                });
        }
    });
}
</script>
