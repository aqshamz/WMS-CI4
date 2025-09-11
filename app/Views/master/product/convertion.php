<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Master Product<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1 class="h3 mb-4">Master Product</h1>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Product Information</h5>
    </div>
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-muted">Product Name</p>
                <h6 class="fw-bold"><?= esc($detail['name']) ?></h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted">Base UOM</p>
                <h6 class="fw-bold"><?= esc($detail['uom_name']) ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">UOM Conversion List</h5>
        <?php if ($create): ?>
            <button id="addNewConvertion" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Conversion
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="convertionTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th>UOM</th>
                        <th>Conversion to Base</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add UOM Modal -->
<div class="modal fade" id="addConvertionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Convertion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addConvertionForm">
                    <div class="mb-3">
                        <label for="uom_from" class="form-label">Uom Base</label>
                        <input type="text" class="form-control" id="uom_from" name="uom_from" value="<?= esc($detail['uom_name']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="uom_to" class="form-label">Uom Convertion</label>
                        <select class="form-select" id="uom_to" name="uom_to" required>
                            <option value="">-- Select Base Uom --</option>
                            <?php if(isset($uoms)):?>
                                <?php foreach($uoms as $uom):?>
                                    <option value="<?php echo $uom['uom_id']; ?>"><?php echo $uom['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="convertion" class="form-label">Convertion</label>
                        <input type="number" class="form-control" id="convertion" name="convertion" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="addConvertionBtn" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateConvertionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Update Convertion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateConvertionForm">
                    <input type="hidden" id="update_id" name="update_id">
                    <input type="hidden" id="uom_real" name="uom_real">
                    <div class="mb-3">
                        <label for="update_uom_from" class="form-label">Uom Base</label>
                        <input type="text" class="form-control" id="update_uom_from" name="update_uom_from" value="<?= esc($detail['uom_name']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="update_uom_to" class="form-label">Uom Convertion</label>
                        <select class="form-select" id="update_uom_to" name="update_uom_to" required>
                            <option value="">-- Select Base Uom --</option>
                            <?php if(isset($uoms)):?>
                                <?php foreach($uoms as $uom):?>
                                    <option value="<?php echo $uom['uom_id']; ?>"><?php echo $uom['name']; ?></option>
                                <?php endforeach;?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_convertion" class="form-label">Convertion</label>
                        <input type="number" class="form-control" id="update_convertion" name="update_convertion" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="updateConvertionBtn" class="btn btn-primary">
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

    $('#uom_to').select2({
        placeholder: 'Select UOM',
        width: '100%',
        dropdownParent: $('#addConvertionModal')
    });

    $('#update_uom_to').select2({
        placeholder: 'Select UOM',
        width: '100%',
        dropdownParent: $('#updateConvertionModal')
    });

    const parentMenu = $("a[href='#menu-2']");
    const masterLink = $("a[href*='product']");
    
    parentMenu.addClass("active").removeClass("text-white text-white-50");
    parentMenu.attr("aria-expanded", "true");
    $("#menu-2").addClass("show");

    masterLink.addClass("active").removeClass("text-white text-white-50");
    masterLink.closest(".collapse").addClass("show");

    var manageTable = $('#convertionTable').DataTable({
        "ajax": {
            "url": "<?= site_url('product/data-convertion') ?>",
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


    $('#addNewConvertion').on('click', function (e) {
        e.preventDefault();
        $('#addConvertionModal').modal('show');
    });


    $('#addConvertionBtn').on('click', function (e) {
        e.preventDefault();
        const id = <?php echo $id; ?>;
        const uom_to = $('#uom_to').val();
        const convertion = $('#convertion').val();

        if (id && uom_to && convertion) {
            $.ajax({
                url: '<?= site_url('product/add-convertion') ?>',
                type: 'POST',
                data: {
                    id: id,
                    uom_id: uom_to,
                    convertion: convertion
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addConvertionModal').modal('hide');
                    $('#addConvertionForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

                    $('#convertionTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to save convertion: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#convertionTable').on('click', '.editConvertion', function () {
        const id = $(this).data('id');
        const uom = $(this).data('uom');
        const factor = $(this).data('factor');

        $('#update_id').val(id);
        $('#uom_real').val(uom);
        $('#update_uom_to').val(uom).trigger('change');
        $('#update_convertion').val(factor);
        
        $('#updateConvertionModal').modal('show');
    });

    $('#updateConvertionBtn').on('click', function (e) {
        e.preventDefault();

        const pid = <?php echo $id; ?>;
        const id = $('#update_id').val();
        const convertion = $('#update_convertion').val();
        const uom_to = $('#update_uom_to').val();
        const uom_real = $('#uom_real').val();

        if (id && uom_to && convertion) {
            $.ajax({
                url: '<?= site_url('product/update-convertion') ?>',
                type: 'POST',
                data: {
                    pid: pid,
                    id: id,
                    uom_id: uom_to,
                    convertion: convertion,
                    uom_real: uom_real
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#updateConvertionModal').modal('hide');
                    $('#updateConvertionForm')[0].reset();

                    if (response.csrfHash) {
                        $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                    }

                    $('#convertionTable').DataTable().ajax.reload();
                },
                error: function(xhr, status, error) {
                    let res = xhr.responseJSON;    
                    if (res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Failed to update convertion: ' + error);
                    }
                }
            });
        } else {
            toastr.error('Please fill all required fields');
        }
    });

    $('#convertionTable').on('click', '.deleteConvertion', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this convertion?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('product/delete-convertion') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);

                        if (response.csrfHash) {
                            $('meta[name="csrf-token-hash"]').attr('content', response.csrfHash);
                        }

                        $('#convertionTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete convertion: ' + error);
                        }
                    }
                });
            }
        });
    });
});

</script>
<?= $this->endSection() ?>
