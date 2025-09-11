<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Product<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Product</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Product List</span>
        <?php if ($create): ?>
            <button id="addNewProduct" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Product
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="productTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Base UOM</th>
                        <th>Handling</th>
                        <th>Status</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm">
                    <div class="mb-3">
                        <label for="sku_code" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="sku_code" name="sku_code" placeholder="Enter SKU Code" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Product Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="rotation" class="form-label">Product Type</label>
                        <select class="form-select" id="rotation" name="rotation" required>
                            <option value="">-- Select Product Type --</option>
                            <option value="FIFO">FIFO</option>
                            <option value="FEFO">FEFO</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="base_uom_id" class="form-label">Base Uom Id</label>
                        <select class="form-select" id="base_uom_id" name="base_uom_id" required>
                            <option value="">-- Select Base Uom --</option>
                            <?php if(isset($uoms)):?>
                                <?php foreach($uoms as $uom):?>
                                    <option value="<?php echo $uom['uom_id']; ?>"><?php echo $uom['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_pack_free" name="is_pack_free" value="1">
                        <label class="form-check-label" for="is_pack_free">Pack Free</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addProductBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Product Modal -->
<div class="modal fade" id="updateProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateProductForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <div class="mb-3">
                        <label for="update_sku_code" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="update_sku_code" name="update_sku_code" placeholder="Enter SKU Code" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="update_name" name="update_name" placeholder="Enter Product Name" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_rotation" class="form-label">Product Type</label>
                        <select class="form-select" id="update_rotation" name="update_rotation" required>
                            <option value="">-- Select Product Type --</option>
                            <option value="FIFO">FIFO</option>
                            <option value="FEFO">FEFO</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_base_uom_id" class="form-label">Base Uom Id</label>
                        <select class="form-select" id="update_base_uom_id" name="update_base_uom_id" required>
                            <option value="">-- Select Base Uom --</option>
                            <?php if(isset($uoms)):?>
                                <?php foreach($uoms as $uom):?>
                                    <option value="<?php echo $uom['uom_id']; ?>"><?php echo $uom['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="update_is_pack_free" name="update_is_pack_free" value="1">
                        <label class="form-check-label" for="update_is_pack_free">Pack Free</label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="update_is_active" name="update_is_active" value="1">
                        <label class="form-check-label" for="update_is_active">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateProductBtn" class="btn btn-warning text-white">
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

    $('#rotation').select2({
        placeholder: 'Select Product Type',
        width: '100%',
        dropdownParent: $('#addProductModal')
    });

    $('#base_uom_id').select2({
        placeholder: 'Select Base Uom',
        width: '100%',
        dropdownParent: $('#addProductModal')
    });
    
    $('#update_rotation').select2({
        placeholder: 'Select Product Type',
        width: '100%',
        dropdownParent: $('#updateProductModal')
    });

    $('#update_base_uom_id').select2({
        placeholder: 'Select Base Uom',
        width: '100%',
        dropdownParent: $('#updateProductModal')
    });


    var manageTable = $('#productTable').DataTable({
        "ajax": {
            "url": "<?= site_url('product/data') ?>",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "pageLength": 10, 
        responsive: true,
        autoWidth: false
    });

    $('#addNewProduct').on('click', function (e) {
        e.preventDefault();
        $('#addProductModal').modal('show');
    });

    $('#addProductBtn').on('click', function (e) {
        e.preventDefault();
        const sku_code = $('#sku_code').val();
        const name = $('#name').val();
        const rotation = $('#rotation').val();
        const base_uom_id = $('#base_uom_id').val();
        const is_pack_free = $('#is_pack_free').is(':checked') ? 1 : 0;
        const is_active = $('#is_active').is(':checked') ? 1 : 0;

        if (name && sku_code && rotation && base_uom_id) {
            $.ajax({
                url: '<?= site_url('product/add') ?>',
                type: 'POST',
                data: {
                    sku_code: sku_code,
                    name: name,
                    rotation: rotation,
                    base_uom_id: base_uom_id,
                    is_pack_free: is_pack_free,
                    is_active: is_active
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();
                    $('#productTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to save product: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#productTable').on('click', '.edit-product', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const sku = $(this).data('sku');
        const active = $(this).data('active');
        const fee = $(this).data('fee');
        const uom = $(this).data('uom');
        const rotation = $(this).data('rotation');

        $('#update_id').val(id);
        $('#update_name').val(name);
        $('#update_sku_code').val(sku);

        $('#update_is_active').prop('checked', active == 1);
        $('#update_is_pack_free').prop('checked', fee == 1);
            
        $('#update_base_uom_id').val(uom).trigger('change');
        $('#update_rotation').val(rotation).trigger('change');
        $('#updateProductModal').modal('show');
    });

    $('#updateProductBtn').on('click', function (e) {
        e.preventDefault();

        const id = $('#update_id').val();
        const name = $('#update_name').val();
        const sku_code = $('#update_sku_code').val();

        const is_active = $('#update_is_active').is(':checked') ? 1 : 0;
        const is_pack_free = $('#update_is_pack_free').is(':checked') ? 1 : 0;

        const base_uom_id = $('#update_base_uom_id').val();
        const rotation = $('#update_rotation').val();

        if (name && id && sku_code && base_uom_id && rotation) {
            $.ajax({
                url: '<?= site_url('product/update') ?>',
                type: 'POST',
                data: {
                    id: id,
                    name: name,
                    sku_code: sku_code,
                    is_active: is_active,
                    is_pack_free: is_pack_free,
                    base_uom_id: base_uom_id,
                    rotation: rotation
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updateProductModal').modal('hide');
                    $('#updateProductForm')[0].reset();
                    $('#productTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to update product: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#productTable').on('click', '.delete-product', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this product?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('product/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);
                        $('#productTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete product: ' + error);
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
