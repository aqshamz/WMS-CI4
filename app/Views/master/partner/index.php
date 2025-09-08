<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Partner<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Partners</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Partner List</span>
        <?php if ($create): ?>
            <button id="addPartnerUom" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Partner
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="partnerTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addPartnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Add Partner
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPartnerForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Partner Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter partner name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Partner Type</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">-- Select Type --</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="vendor">Vendor</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addPartnerBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update UOM Modal -->
<div class="modal fade" id="updatePartnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit UOM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updatePartnerForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Partner Name</label>
                        <input type="text" class="form-control" id="update_name" name="update_name" placeholder="Enter partner name" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Partner Type</label>
                        <select class="form-select" id="update_role" name="update_role" required>
                            <option value="">-- Select Type --</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="vendor">Vendor</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updatePartnerBtn" class="btn btn-warning text-white">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    $('#role').select2({
        placeholder: 'Select Type',
        width: '100%',
        dropdownParent: $('#addPartnerModal')
    });

    $('#update_role').select2({
        placeholder: 'Select Type',
        width: '100%',
        dropdownParent: $('#updatePartnerModal')
    });

    var manageTable = $('#partnerTable').DataTable({
        "ajax": {
            "url": "<?= site_url('partner/data') ?>",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "pageLength": 10, 
        responsive: true,
        autoWidth: false
    });

    $('#addPartnerUom').on('click', function (e) {
        e.preventDefault();
        $('#addPartnerModal').modal('show');
    });

    $('#addPartnerBtn').on('click', function (e) {
        e.preventDefault();
        const name = $('#name').val();
        const role = $('#role').val();

        if (name && role) {
            $.ajax({
                url: '<?= site_url('partner/add') ?>',
                type: 'POST',
                data: {
                    name: name,
                    role: role,
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addPartnerModal').modal('hide');
                    $('#addPartnerForm')[0].reset();
                    $('#partnerTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to save partner: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#partnerTable').on('click', '.edit-partner', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const role = $(this).data('role');
        $('#update_id').val(id);
        $('#update_name').val(name);
        $('#update_role').val(role).trigger('change');
        $('#updatePartnerModal').modal('show');
    });

    $('#updatePartnerBtn').on('click', function (e) {
        e.preventDefault();
        const name = $('#update_name').val();
        const role = $('#update_role').val();
        const id = $('#update_id').val();

        if (name && id && role) {
            $.ajax({
                url: '<?= site_url('partner/update') ?>',
                type: 'POST',
                data: {
                    name: name,
                    id: id,
                    role: role
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updatePartnerModal').modal('hide');
                    $('#updatePartnerForm')[0].reset();
                    $('#partnerTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to update partner: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#partnerTable').on('click', '.delete-partner', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this partner?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('partner/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);
                        $('#partnerTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete partner: ' + error);
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
