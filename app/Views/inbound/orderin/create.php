<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Purchase Order<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Inbound</h1>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold">Create Purchase Order</span>
    </div>
    <div class="card-body">
        <form action="<?= site_url('orderin/create') ?>" method="post" id="poForm">
            <?= csrf_field() ?> 
            <!-- Partner (Owner) -->
            <div class="mb-3">
                <label for="partner_id" class="form-label">Owner (Partner)</label>
                <select name="partner_id" id="partner_id" class="form-select" required>
                    <option value="">-- Select Owner --</option>
                    <?php foreach ($customers as $cust): ?>
                        <option value="<?= esc($cust['partner_id']) ?>">
                            <?= esc($cust['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Vendor (Counterparty) -->
            <div class="mb-3">
                <label for="counterparty_id" class="form-label">Vendor</label>
                <select name="counterparty_id" id="counterparty_id" class="form-select" required>
                    <option value="">-- Select Vendor --</option>
                    <?php foreach ($vendors as $ven): ?>
                        <option value="<?= esc($ven['partner_id']) ?>">
                            <?= esc($ven['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Warehouse -->
            <div class="mb-3">
                <label for="warehouse_id" class="form-label">Warehouse</label>
                <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                    <option value="">-- Select Warehouse --</option>
                    <?php foreach ($warehouses as $wh): ?>
                        <option value="<?= esc($wh['warehouse_id']) ?>">
                            <?= esc($wh['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Reference Number -->
            <div class="mb-3">
                <label for="ref_number" class="form-label">Reference Number</label>
                <input type="text" name="ref_number" id="ref_number" class="form-control" placeholder="e.g., PO-2025-001">
            </div>

            <!-- Status (default draft) -->
            <input type="hidden" name="status" id="statusField" value="">
            <input type="hidden" name="doc_type" value="PO">

            <!-- Product Lines Table -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Products</span>
                    <button type="button" id="addRowBtn" class="btn btn-sm btn-success" disabled>
                        <i class="fas fa-plus me-1"></i> Add Row
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="productsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%">Product</th>
                                <th style="width: 20%">UOM</th>
                                <th style="width: 15%">Qty</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- dynamic rows go here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-secondary me-2 submitBtn" data-status="draft">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <button type="submit" class="btn btn-primary submitBtn" data-status="open">
                    <i class="fas fa-check me-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    const parentMenu = $("a[href='#menu-3']");
    const masterLink = $("a[href*='orderin']");
    
    parentMenu.addClass("active").removeClass("text-white text-white-50");
    parentMenu.attr("aria-expanded", "true");
    $("#menu-3").addClass("show");

    masterLink.addClass("active").removeClass("text-white text-white-50");
    masterLink.closest(".collapse").addClass("show");

    $('#partner_id').select2({
        placeholder: 'Select Vendor',
        width: '100%',
    });
    $('#counterparty_id').select2({
        placeholder: 'Select Customer',
        width: '100%',
    });
    $('#warehouse_id').select2({
        placeholder: 'Select Warehouse',
        width: '100%',
    });
    
    let productList = []; // will hold product data from backend
    let rowCount = 0;

    // When vendor changes, fetch product list & reset table
    $('#counterparty_id').on('change', function () {
        let vendorId = $(this).val();
        $('#productsTable tbody').empty();
        rowCount = 0;

        if (!vendorId) {
            $('#addRowBtn').prop('disabled', true);
            return;
        }

        $.ajax({
            url: "<?= site_url('product/data-product-partner') ?>",
            type: "POST",
            data: { vendor: vendorId },
            success: function (res) {
                if (res.status === 'success') {
                    productList = res.data;
                    $('#addRowBtn').prop('disabled', false);
                } else {
                    toastr.error(res.message || "Failed to load products");
                }
            },
            error: function (xhr) {
                toastr.error("Error loading products");
            }
        });
    });

    // Add new row
    $('#addRowBtn').on('click', function () {
        rowCount++;
        let rowId = 'row' + rowCount;

        let options = '<option value="">-- Select Product --</option>';
        productList.forEach(function (p) {
            options += `<option value="${p.product_id}"
                        data-base-uom="${p.base_uom}"
                        data-base-uom-name="${p.base_uom_name}">
                           ${p.product_name} (${p.customer_sku})
                        </option>`;
        });

        let row = `
            <tr id="${rowId}">
                <td>
                    <select name="products[${rowCount}][product_id]" class="form-select product-select" required>
                        ${options}
                    </select>
                </td>
                <td>
                    <select name="products[${rowCount}][uom_id]"
                            class="form-select uom-select" required>
                        <option value="">-- Select UOM --</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="products[${rowCount}][qty]" 
                           class="form-control" placeholder="0" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger removeRowBtn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#' + rowId).find('.product-select').select2({ placeholder: 'Select Product', width: '100%' });
        $('#' + rowId).find('.uom-select').select2({ placeholder: 'Select UOM', width: '100%' });

        $('#productsTable tbody').append(row);
    });

    $('#productsTable').on('change', '.product-select', function () {
        let productId = $(this).val();
        let baseUomId = $(this).find(':selected').data('base-uom');
        let baseUomName = $(this).find(':selected').data('base-uom-name');
        let uomSelect = $(this).closest('tr').find('.uom-select');

        uomSelect.empty().append(`<option value="">-- Select UOM --</option>`);

        if (!productId) return;

        $.ajax({
            url: "<?= site_url('product/data-convertion-json') ?>",
            type: "POST",
            data: { productId: productId },
            success: function (res) {
                if (res.status === 'success') {
                    let uoms = res.data;

                    // Add base uom first (highlighted maybe)
                    uomSelect.append(`<option value="${baseUomId}">
                                        ${baseUomName}
                                    </option>`);

                    // Add all other uoms
                    uoms.forEach(function (u) {
                        uomSelect.append(`<option value="${u.uom_id}">
                                            ${u.uom_name}
                                        </option>`);
                    });
                } else {
                    toastr.error(res.message || "Failed to load UOMs");
                }
            },
            error: function () {
                toastr.error("Error loading UOMs");
            }
        });
    });

    // Remove row
    $('#productsTable').on('click', '.removeRowBtn', function () {
        $(this).closest('tr').remove();
    });

    $('.submitBtn').on('click', function (e) {
        e.preventDefault(); // stop immediate submit
        let status = $(this).data('status');
        $('#statusField').val(status); // set hidden input

        $('#poForm').submit(); // submit the form
    });
});
</script>
<?= $this->endSection() ?>
