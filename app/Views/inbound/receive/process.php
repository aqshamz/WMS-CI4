<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Receive Order<?= $this->endSection() ?>

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
        <span class="fw-bold">Process Receive Order</span>
    </div>
    <div class="card-body">
        <form action="<?= site_url('receive/processReceive') ?>" method="post" id="poForm">
            <?= csrf_field() ?> 
            <!-- Partner (Owner) -->
            <div class="mb-3">
                <label for="partner_id" class="form-label">Owner (Partner)</label>
                <input type="text" name="partner_name" id="partner_name" class="form-control" value="<?= esc($docheader['owner']) ?>" readonly>
                <input type="hidden" name="partner_id" id="partner_id" class="form-control" value="<?= esc($docheader['partner_id']) ?>" readonly>
            </div>

            <!-- Vendor (Counterparty) -->
            <div class="mb-3">
                <label for="counterparty_id" class="form-label">Vendor</label>
                <input type="text" name="counterparty_id" id="counterparty_id" class="form-control" value="<?= esc($docheader['vendor']) ?>" readonly>
            </div>

            <!-- Warehouse -->
            <div class="mb-3">
                <label for="warehouse_id" class="form-label">Warehouse</label>
                <input type="text" name="warehouse_id" id="warehouse_id" class="form-control" value="<?= esc($docheader['warehousename']) ?>" readonly>
            </div>

            <!-- Reference Number -->
            <div class="mb-3">
                <label for="ref_number" class="form-label">Reference Number</label>
                <input type="text" name="ref_number" id="ref_number" class="form-control" value="<?= esc($docheader['ref_number']) ?>">
            </div>

            <!-- Status (default draft) -->
            <input type="hidden" name="status" id="statusField" value="<?= esc($docheader['status']) ?>">
            <input type="hidden" name="doc_type" value="INBOUND">
            <input type="hidden" name="ref_doc_id" value="<?= esc($docheader['ref_document_id'])?>">
            <input type="hidden" name="doc_id" value="<?= esc($doc_id)?>">

            <!-- Product Lines Table -->
            <div class="card mt-4">
                <div class="card-body">
                    <table class="table table-bordered" id="receiveTable">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product</th>
                                <th>UOM</th>
                                <th>Ordered</th>
                                <th>Location</th>
                                <th>Receive</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docdetail as $i => $line): ?>
                            <tr>
                                <td><?= esc($line['sku']) ?></td>
                                <td><?= esc($line['productname']) ?></td>
                                <td><?= esc($line['uomname']) ?></td>
                                <td><?= esc($line['qty_ordered']) ?></td>
                                <td>
                                    <select name="lines[<?= $i ?>][location_id]" class="form-select">
                                        <?php foreach ($loc as $l): ?>
                                            <option value="<?= $l['location_id'] ?>" 
                                                <?= $l['location_id'] == $line['source_location_id'] ? 'selected' : '' ?>>
                                                <?= $l['location_code'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="lines[<?= $i ?>][doc_line_id]" value="<?= $line['document_line_id'] ?>">
                                    <input type="hidden" name="lines[<?= $i ?>][type]" value="<?= $line['type'] ?>">

                                    <?php if ($line['type'] === 'FEFO'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary addLotBtn" data-line="<?= $i ?>">
                                            <i class="fas fa-plus"></i> Add Lot
                                        </button>
                                        <div id="lotContainer-<?= $i ?>" class="mt-2">
                                            <?php if(!empty($line['lots'])): ?>
                                            <?php foreach ($line['lots'] as $lotIndex => $lot): ?>
                                                <div class="row g-2 mb-2 lotRow">
                                                    <div class="col-md-3">
                                                        <label class="form-label small mb-1">Lot No</label>
                                                        <input type="text" name="lines[<?= $i ?>][lots][<?= $lotIndex ?>][lot_no]" 
                                                            value="<?= esc($lot['lot_no']) ?>" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small mb-1">MFG Date</label>
                                                        <input type="date" name="lines[<?= $i ?>][lots][<?= $lotIndex ?>][mfg_date]" 
                                                            value="<?= esc($lot['mfg_date']) ?>" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small mb-1">EXP Date</label>
                                                        <input type="date" name="lines[<?= $i ?>][lots][<?= $lotIndex ?>][exp_date]" 
                                                            value="<?= esc($lot['exp_date']) ?>" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small mb-1">Qty</label>
                                                        <input type="number" name="lines[<?= $i ?>][lots][<?= $lotIndex ?>][qty]" data-line="<?= $i ?>"
                                                            value="<?= esc($lot['qty']) ?>" class="form-control lotQty" required>
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger btn-sm removeLotBtn">&times;</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                         <input type="hidden" 
                                            name="lines[<?= $i ?>][qty_receive]" 
                                            value="<?= $line['qty_received'] ?>" 
                                            max="<?= $line['qty_ordered'] ?>" 
                                            data-max="<?= $line['qty_ordered'] ?>">
                                            <input type="hidden" name="lines[<?= $i ?>][product_id]" value="<?= $line['product_id'] ?>">
                                            <input type="hidden" name="lines[<?= $i ?>][uom_id]" value="<?= $line['uom_id'] ?>">
                                            <input type="hidden" name="lines[<?= $i ?>][qty_ordered]" value="<?= $line['qty_ordered'] ?>">
                                    <?php else: ?>
                                        <input type="number" class="form-control"
                                            name="lines[<?= $i ?>][qty_receive]"
                                            value="<?= $line['qty_received'] ?>"
                                            min="0" max="<?= $line['qty_ordered'] ?>">
                                        <input type="hidden" name="lines[<?= $i ?>][product_id]" value="<?= $line['product_id'] ?>">
                                        <input type="hidden" name="lines[<?= $i ?>][uom_id]" value="<?= $line['uom_id'] ?>">
                                        <input type="hidden" name="lines[<?= $i ?>][qty_ordered]" value="<?= $line['qty_ordered'] ?>">
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-secondary me-2 submitBtn" data-status="open">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <button type="submit" class="btn btn-primary me-2 submitBtn" data-status="partial">
                    <i class="fas fa-check me-1"></i> Partial
                </button>
                <button type="submit" class="btn btn-primary submitBtn" data-status="completed">
                    <i class="fas fa-check me-1"></i> Finish
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
    const masterLink = $("a[href*='receive']");
    
    parentMenu.addClass("active").removeClass("text-white text-white-50");
    parentMenu.attr("aria-expanded", "true");
    $("#menu-3").addClass("show");

    masterLink.addClass("active").removeClass("text-white text-white-50");
    masterLink.closest(".collapse").addClass("show");

    $('#location').select2({
        placeholder: 'Select Location',
        width: '100%',
    });
    

    $('.submitBtn').on('click', function (e) {
        e.preventDefault(); // stop immediate submit
        let status = $(this).data('status');
        $('#statusField').val(status); // set hidden input

        $('#poForm').submit(); // submit the form
    });

    $(document).on("click", ".addLotBtn", function () {
        let lineIndex = $(this).data("line");

        let lotIndex = $(`#lotContainer-${lineIndex} .lotRow`).length;

        let lotRow = `
            <div class="row g-2 mb-2 lotRow">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Lot No</label>
                    <input type="text" name="lines[${lineIndex}][lots][${lotIndex}][lot_no]" 
                        class="form-control" placeholder="Lot No" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Manufacture Date</label>
                    <input type="date" name="lines[${lineIndex}][lots][${lotIndex}][mfg_date]" 
                        class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Expiry Date</label>
                    <input type="date" name="lines[${lineIndex}][lots][${lotIndex}][exp_date]" 
                        class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Qty</label>
                    <input type="number" name="lines[${lineIndex}][lots][${lotIndex}][qty]" 
                        class="form-control lotQty" data-line="${lineIndex}" placeholder="Qty" required>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm removeLotBtn">&times;</button>
                </div>
            </div>`;

        $(`#lotContainer-${lineIndex}`).append(lotRow);
    });

    $(document).on("click", ".removeLotBtn", function () {
        $(this).closest(".lotRow").remove();
        updateLineQty($(this).closest(".lotRow").find(".lotQty").data("line"));
    });

    $(document).on("input", "input[type='number']", function () {
        let max = parseFloat($(this).attr("max"));
        let min = parseFloat($(this).attr("min")) || 0;
        let val = parseFloat($(this).val());

        if (!isNaN(max) && val > max) {
            $(this).val(max);
            toastr.warning(`Value cannot exceed ${max}`);
        }
        if (!isNaN(min) && val < min) {
            $(this).val(min);
        }
    });


    $(document).on("input", ".lotQty", function () {
        let lineIndex = $(this).data("line");
        updateLineQty(lineIndex);
    });

    function updateLineQty(lineIndex) {
        let totalQty = 0;

        $(`#lotContainer-${lineIndex} .lotQty`).each(function () {
            totalQty += parseFloat($(this).val()) || 0;
        });

        let $qtyReceive = $(`input[name='lines[${lineIndex}][qty_receive]']`);
        let maxQty = parseFloat($qtyReceive.attr("max")) || parseFloat($qtyReceive.data("max")) || null;

        if (maxQty !== null && totalQty > maxQty) {
            toastr.warning(`Total received lots (${totalQty}) exceeds max allowed (${maxQty}).`);
        }

        $qtyReceive.val(totalQty);
    }

});
</script>
<?= $this->endSection() ?>
