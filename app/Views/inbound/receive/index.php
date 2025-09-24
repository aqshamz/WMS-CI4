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
        <span class="fw-bold">Receive Order</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="receiveTable" class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Reff Number</th>
                        <th>Vendor</th>
                        <th>Warehouse</th>
                        <th>Total Product</th>
                        <th>Status</th>
                        <th style="width: 15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    var manageTable = $('#receiveTable').DataTable({
        "ajax": {
            "url": "<?= site_url('receive/data') ?>",
            "type": "GET",
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "pageLength": 10, 
        responsive: true,
        autoWidth: false
    });

    $('#receiveTable').on('click', '.delete-receive', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this Receive Document?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('receive/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);
                        $('#receiveTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to delete po: ' + error);
                        }
                    }
                });
            }
        });
    });

    $('#receiveTable').on('click', '.create-receive', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Create Receive Document?',
            text: "Once created, PO cannot be edited!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create receive document!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('receive/create') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function (response) {                        
                        toastr.success(response.message);
                        $('#receiveTable').DataTable().ajax.reload();
                    },
                    error: function () {
                        let res = xhr.responseJSON;    
                        if (res && res.message) {
                            toastr.error(res.message);
                        } else {
                            toastr.error('Failed to create receive: ' + error);
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
