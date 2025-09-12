<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Warehouse<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Warehouse</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Warehouse List</span>
        <?php if ($create): ?>
            <button id="addNewWarehouse" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Warehouse
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="warehouseTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Warehouse</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addWarehouseForm">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Warehouse Name" required>
                        <label for="name">Warehouse Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="address" name="address" placeholder="Address" required>
                        <label for="address">Address</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addWarehouseBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Product Modal -->
<div class="modal fade" id="updateWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Warehouse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateWarehouseForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="update_name" name="update_name" placeholder="Warehouse Name" required>
                        <label for="name">Warehouse Name</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="update_address" name="update_address" placeholder="Address" required>
                        <label for="address">Address</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateWarehouseBtn" class="btn btn-warning text-white">
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

    var manageTable = $('#warehouseTable').DataTable({
        "ajax": {
            "url": "<?= site_url('warehouse/data') ?>",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "pageLength": 10, 
        responsive: true,
        autoWidth: false
    });

    $('#addNewWarehouse').on('click', function (e) {
        e.preventDefault();
        $('#addWarehouseModal').modal('show');
    });

    $('#addWarehouseBtn').on('click', function (e) {
        e.preventDefault();
        const name = $('#name').val();
        const address = $('#address').val();
        
        if (name && address) {
            $.ajax({
                url: '<?= site_url('warehouse/add') ?>',
                type: 'POST',
                data: {
                    name: name,
                    address: address,
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addWarehouseModal').modal('hide');
                    $('#addWarehouseForm')[0].reset();
                    $('#warehouseTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to save warehouse: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#warehouseTable').on('click', '.editWarehouse', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const address = $(this).data('address');

        $('#update_id').val(id);
        $('#update_name').val(name);
        $('#update_address').val(address);

        $('#updateWarehouseModal').modal('show');
    });

    $('#updateWarehouseBtn').on('click', function (e) {
        e.preventDefault();

        const id = $('#update_id').val();
        const name = $('#update_name').val();
        const address = $('#update_address').val();

        if (name && id && address) {
            $.ajax({
                url: '<?= site_url('warehouse/update') ?>',
                type: 'POST',
                data: {
                    id: id,
                    name: name,
                    address: address,
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updateWarehouseModal').modal('hide');
                    $('#updateWarehouseForm')[0].reset();
                    $('#warehouseTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to update warehouse: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#warehouseTable').on('click', '.deleteWarehouse', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this warehouse?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('warehouse/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);
                        $('#warehouseTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete warehouse: ' + error);
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
