<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Location<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Warehouse</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Warehouse Information</h5>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted">Name</p>
                <h6 class="fw-bold"><?= esc($detail['name']) ?></h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted">Address</p>
                <h6 class="fw-bold"><?= esc($detail['address']) ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Location List</h5>
        <?php if ($create): ?>
            <button id="addNewLocation" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Location
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="locationTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Location</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addLocationForm">
                    <div class="mb-3">
                        <label for="location_type" class="form-label">Location Type</label>
                        <select class="form-select" id="location_type" name="location_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="dock">Dock</option>
                            <option value="staging">Staging</option>
                            <option value="storage">Storage</option>
                            <option value="pickface">Pickface</option>
                            <option value="qc">QC</option>
                            <option value="quarantine">Quarantine</option>
                            <option value="disposal">Disposal</option>
                            <option value="outbound">Outbound</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="location_code" class="form-label">Location Code</label>
                        <input type="text" class="form-control" id="location_code" name="location_code" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addLocationBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Update Location</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateLocationForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <div class="mb-3">
                        <label for="update_location_type" class="form-label">Location Type</label>
                        <select class="form-select" id="update_location_type" name="update_location_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="dock">Dock</option>
                            <option value="staging">Staging</option>
                            <option value="storage">Storage</option>
                            <option value="pickface">Pickface</option>
                            <option value="qc">QC</option>
                            <option value="quarantine">Quarantine</option>
                            <option value="disposal">Disposal</option>
                            <option value="outbound">Outbound</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_location_code" class="form-label">Location Code</label>
                        <input type="text" class="form-control" id="update_location_code" name="update_location_code" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateLocationBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    $('#location_type').select2({
        placeholder: 'Select Type',
        width: '100%',
        dropdownParent: $('#addLocationModal')
    });

    $('#update_location_type').select2({
        placeholder: 'Select Type',
        width: '100%',
        dropdownParent: $('#updateLocationModal')
    });

    const parentMenu = $("a[href='#menu-2']");
    const masterLink = $("a[href*='warehouse']");
    
    parentMenu.addClass("active").removeClass("text-white text-white-50");
    parentMenu.attr("aria-expanded", "true");
    $("#menu-2").addClass("show");

    masterLink.addClass("active").removeClass("text-white text-white-50");
    masterLink.closest(".collapse").addClass("show");

    var manageTable = $('#locationTable').DataTable({
        "ajax": {
            "url": "<?= site_url('warehouse/data-location') ?>",
            "type": "POST",
            "data": function (d) {
                d['id'] = <?= json_encode($id) ?>; 
                let tokenName = $('meta[name="csrf-token-name"]').attr('content');
                let tokenHash = $('meta[name="csrf-token-hash"]').attr('content');
                d[tokenName] = tokenHash;
                return d;
            },
            "dataSrc": function (json) {
                if (json.csrfHash) {
                    $('meta[name="csrf-token-hash"]').attr('content', json.csrfHash);
                }
                return json.data;
            },
        },
        "pageLength": 10,
        responsive: true,
        autoWidth: false
    });


    $('#addNewLocation').on('click', function (e) {
        e.preventDefault();
        $('#addLocationModal').modal('show');
    });


    $('#addLocationBtn').on('click', function (e) {
        e.preventDefault();
        const id = <?php echo $id; ?>;
        const locationType = $('#location_type').val();
        const locationCode = $('#location_code').val();

        if (id && locationType && locationCode) {
            $.ajax({
                url: '<?= site_url('warehouse/add-location') ?>',
                type: 'POST',
                data: {
                    id: id,
                    location_type: locationType,
                    location_code: locationCode
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addLocationModal').modal('hide');
                    $('#addLocationForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

                    $('#locationTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to save location: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#locationTable').on('click', '.editLocation', function () {
        const id = $(this).data('id');
        const code = $(this).data('code');
        const type = $(this).data('type');

        $('#update_id').val(id);
        $('#update_location_code').val(code);
        $('#update_location_type').val(type).trigger('change');
        
        $('#updateLocationModal').modal('show');
    });

    $('#updateLocationBtn').on('click', function (e) {
        e.preventDefault();

        const wid = <?php echo $id; ?>;
        const id = $('#update_id').val();
        const locationCode = $('#update_location_code').val();
        const locationType = $('#update_location_type').val();

        if (id && locationCode && locationType) {
            $.ajax({
                url: '<?= site_url('warehouse/update-location') ?>',
                type: 'POST',
                data: {
                    wid: wid,
                    id: id,
                    location_code: locationCode,
                    location_type: locationType,
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updateLocationModal').modal('hide');
                    $('#updateLocationForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

                    $('#locationTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to update location: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#locationTable').on('click', '.deleteLocation', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this location?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('warehouse/delete-location') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);

                        if (response.csrfHash) {
                            $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                        }

                        $('#locationTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete location: ' + error);
                        }
                    }
                });
            }
        });
    });
});

</script>
<?= $this->endSection() ?>
