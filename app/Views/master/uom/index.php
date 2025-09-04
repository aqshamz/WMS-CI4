<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master UOM<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master UOM</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">UOM List</span>
        <?php if ($create): ?>
            <button id="addNewUom" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New UOM
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="uomTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>UOM Name</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addUomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add UOM</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUomForm">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="name" name="name" placeholder="UOM Name" required>
                        <label for="name">UOM Name</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addUomBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update UOM Modal -->
<div class="modal fade" id="updateUomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit UOM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateUomForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="update_name" name="update_name" placeholder="UOM Name" required>
                        <label for="update_name">UOM Name</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateUomBtn" class="btn btn-warning text-white">
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

    var manageTable = $('#uomTable').DataTable({
        "ajax": {
            "url": "<?= site_url('uom/data') ?>",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "pageLength": 10, 
        responsive: true,
        autoWidth: false
    });

    $('#addNewUom').on('click', function (e) {
        e.preventDefault();
        $('#addUomModal').modal('show');
    });

    $('#addUomBtn').on('click', function (e) {
        e.preventDefault();
        const name = $('#name').val();

        if (name) {
            $.ajax({
                url: '<?= site_url('uom/add') ?>',
                type: 'POST',
                data: {
                    name: name,
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#addUomModal').modal('hide');
                        $('#addUomForm')[0].reset();
                        $('#uomTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to save uom: ' + error);
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#uomTable').on('click', '.edit-uom', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#update_id').val(id);
        $('#update_name').val(name);
        $('#updateUomModal').modal('show');
    });

    $('#updateUomBtn').on('click', function (e) {
        e.preventDefault();
        const name = $('#update_name').val();
        const id = $('#update_id').val();

        if (name && id) {
            $.ajax({
                url: '<?= site_url('uom/update') ?>',
                type: 'POST',
                data: {
                    name: name,
                    id: id
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#updateUomModal').modal('hide');
                        $('#updateUomForm')[0].reset();
                        $('#uomTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to update uom: ' + error);
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#uomTable').on('click', '.delete-uom', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this uom?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('uom/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        if(response.status === 'success'){
                            toastr.success(response.message);
                            $('#uomTable').DataTable().ajax.reload();
                        }else{
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('An error occurred');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
