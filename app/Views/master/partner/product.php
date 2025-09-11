<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Partner<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Partner</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Partner Information</h5>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted">Partner Name</p>
                <h6 class="fw-bold"><?= esc($detail['name']) ?></h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted">Type</p>
                <h6 class="fw-bold"><?= esc($detail['role']) ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Product List</h5>
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
                        <th>Product</th>
                        <th>SKU</th>
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
                        <label for="uom_from" class="form-label">Partner</label>
                        <input type="text" class="form-control" id="partner" name="partner" value="<?= esc($detail['name']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product</label>
                        <select class="form-select" id="product_id" name="product_id" required>
                            <option value="">-- Select Product --</option>
                            <?php if(isset($products)):?>
                                <?php foreach($products as $product):?>
                                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="customer_sku" class="form-label">SKU Partner</label>
                        <input type="text" class="form-control" id="customer_sku" name="customer_sku" required>
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

<div class="modal fade" id="updateProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Update Partner</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateProductForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <input type="hidden" id="product_real" name="product_real">
                    <div class="mb-3">
                        <label for="update_partner" class="form-label">Partner</label>
                        <input type="text" class="form-control" id="update_partner" name="update_partner" value="<?= esc($detail['name']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_product_id" class="form-label">Product</label>
                        <select class="form-select" id="update_product_id" name="update_product_id" required>
                            <option value="">-- Select Product --</option>
                            <?php if(isset($products)):?>
                                <?php foreach($products as $product):?>
                                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_customer_sku" class="form-label">SKU Partner</label>
                        <input type="text" class="form-control" id="update_customer_sku" name="update_customer_sku" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateProductBtn" class="btn btn-primary">
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

    $('#product_id').select2({
        placeholder: 'Select Product',
        width: '100%',
        dropdownParent: $('#addProductModal')
    });

    $('#update_product_id').select2({
        placeholder: 'Select Product',
        width: '100%',
        dropdownParent: $('#updateProductModal')
    });

    const parentMenu = $("a[href='#menu-2']");
    const masterLink = $("a[href*='partner']");
    
    parentMenu.addClass("active").removeClass("text-white text-white-50");
    parentMenu.attr("aria-expanded", "true");
    $("#menu-2").addClass("show");

    masterLink.addClass("active").removeClass("text-white text-white-50");
    masterLink.closest(".collapse").addClass("show");

    var manageTable = $('#productTable').DataTable({
        "ajax": {
            "url": "<?= site_url('partner/data-product') ?>",
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


    $('#addNewProduct').on('click', function (e) {
        e.preventDefault();
        $('#addProductModal').modal('show');
    });


    $('#addProductBtn').on('click', function (e) {
        e.preventDefault();
        const id = <?php echo $id; ?>;
        const productId = $('#product_id').val();
        const customerSku = $('#customer_sku').val();

        if (id && productId && customerSku) {
            $.ajax({
                url: '<?= site_url('partner/add-product') ?>',
                type: 'POST',
                data: {
                    id: id,
                    product_id: productId,
                    customer_sku: customerSku
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

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

    $('#productTable').on('click', '.editProduct', function () {
        const id = $(this).data('id');
        const product = $(this).data('product');
        const sku = $(this).data('sku');

        $('#update_id').val(id);
        $('#product_real').val(product);
        $('#update_product_id').val(product).trigger('change');
        $('#update_customer_sku').val(sku);
        
        $('#updateProductModal').modal('show');
    });

    $('#updateProductBtn').on('click', function (e) {
        e.preventDefault();

        const id = $('#update_id').val();
        const partnerId = <?php echo $id; ?>;
        const customerSku = $('#update_customer_sku').val();
        const productId = $('#update_product_id').val();
        const productReal = $('#product_real').val();

        if (id && productId && customerSku) {
            $.ajax({
                url: '<?= site_url('partner/update-product') ?>',
                type: 'POST',
                data: {
                    id: id,
                    partner_id: partnerId,
                    product_id: productId,
                    product_real: productReal,
                    customer_sku: customerSku,
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updateProductModal').modal('hide');
                    $('#updateProductForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

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

    $('#productTable').on('click', '.deleteProduct', function () {
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
                    url: '<?= site_url('partner/delete-product') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);

                        if (response.csrfHash) {
                            $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                        }

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
