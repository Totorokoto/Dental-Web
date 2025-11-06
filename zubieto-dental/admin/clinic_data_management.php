<?php
// FILE: admin/clinic_data_management.php
require 'includes/header.php';
// The database connection is now open from header.php

// RBAC Check
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied."; $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}

// Fetch data for both tables
$findings_list = $conn->query("SELECT * FROM lookup_findings ORDER BY name ASC");
$treatments_list = $conn->query("SELECT * FROM lookup_treatments ORDER BY name ASC");
?>

<!-- Add Toastr CSS for notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    /* Custom styles for this page */
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .btn-primary:hover {
        background-color: #00695C;
        border-color: #00695C;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25);
    }
    .modal-content {
        border-radius: 1rem;
        border: none;
    }
    .modal-header {
        background-color: #f4f7f6;
        border-bottom: none;
    }
    .modal-footer {
        border-top: none;
    }
    .table .btn {
        transition: all 0.2s ease-in-out;
    }
    /* Toastr Notification Style */
    .toast-success { background-color: var(--primary-color) !important; }
    .toast-error { background-color: #dc3545 !important; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Clinic Data Options</h1>
</div>

<div class="alert alert-light border d-flex align-items-center" role="alert">
    <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
    <div>
        Use this page to manage the pre-defined options that appear in dropdown menus for "Clinical Findings" and "Proposed Treatments" on a patient's record.
    </div>
</div>


<div class="row">
    <!-- Clinical Findings Management Column -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-search-plus me-2 text-primary"></i>Clinical Findings List</h5>
            </div>
            <div class="card-body">
                <form class="mb-3 add-item-form" data-type="finding">
                    <div class="input-group">
                        <input type="text" class="form-control" name="name" placeholder="Add new finding..." required>
                        <button class="btn btn-primary" type="submit" title="Add Finding">Add</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody id="findings-table-body">
                            <?php while($item = $findings_list->fetch_assoc()): ?>
                                <tr id="finding-<?php echo $item['id']; ?>">
                                    <td class="align-middle"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-warning btn-sm edit-btn" title="Edit" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-type="finding"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger btn-sm delete-btn" title="Delete" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-type="finding"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Proposed Treatments Management Column -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-notes-medical me-2 text-primary"></i>Proposed Treatments List</h5>
            </div>
            <div class="card-body">
                <form class="mb-3 add-item-form" data-type="treatment">
                    <div class="input-group">
                        <input type="text" class="form-control" name="name" placeholder="Add new treatment..." required>
                        <button class="btn btn-primary" type="submit" title="Add Treatment">Add</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <tbody id="treatments-table-body">
                            <?php while($item = $treatments_list->fetch_assoc()): ?>
                                <tr id="treatment-<?php echo $item['id']; ?>">
                                    <td class="align-middle"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-warning btn-sm edit-btn" title="Edit" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-type="treatment"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-outline-danger btn-sm delete-btn" title="Delete" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-type="treatment"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal (used for both types) -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="edit-item-form">
        <div class="modal-body">
            <input type="hidden" name="id" id="edit-item-id">
            <input type="hidden" name="type" id="edit-item-type">
            <div class="mb-3">
                <label for="edit-item-name" class="form-label">Name</label>
                <input type="text" class="form-control" id="edit-item-name" name="name" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete the item: <strong id="itemNameToDelete"></strong>?</p>
        <p class="text-danger small">This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>


<?php require 'includes/footer.php'; ?>

<!-- Add Toastr JS for notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000",
    };

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let itemToDelete = {};

    $('.add-item-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const type = form.data('type');
        const name = form.find('input[name="name"]').val();

        $.ajax({
            url: 'ajax_manage_clinic_data.php',
            type: 'POST',
            data: { action: 'add', type: type, name: name },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const newRow = `
                        <tr id="${type}-${response.id}">
                            <td class="align-middle">${response.name}</td>
                            <td class="text-end">
                                <button class="btn btn-outline-warning btn-sm edit-btn" title="Edit" data-id="${response.id}" data-name="${response.name}" data-type="${type}"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-sm delete-btn" title="Delete" data-id="${response.id}" data-name="${response.name}" data-type="${type}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    $(`#${type}s-table-body`).append(newRow);
                    form.trigger('reset');
                    toastr.success(`'${name}' was added successfully.`);
                    new bootstrap.Tooltip(document.querySelector(`#${type}-${response.id} [title]`));
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() { toastr.error('A server error occurred.'); }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        itemToDelete = { id: $(this).data('id'), name: $(this).data('name'), type: $(this).data('type') };
        $('#itemNameToDelete').text(itemToDelete.name);
        deleteConfirmModal.show();
    });

    $('#confirmDeleteBtn').on('click', function() {
        $.ajax({
            url: 'ajax_manage_clinic_data.php',
            type: 'POST',
            data: { action: 'delete', type: itemToDelete.type, id: itemToDelete.id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`#${itemToDelete.type}-${itemToDelete.id}`).fadeOut(300, function() { $(this).remove(); });
                    toastr.success(`'${itemToDelete.name}' was deleted.`);
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() { toastr.error('A server error occurred.'); },
            complete: function() { deleteConfirmModal.hide(); }
        });
    });

    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const type = $(this).data('type');
        $('#edit-item-id').val(id);
        $('#edit-item-type').val(type);
        $('#edit-item-name').val(name);
        $('#editModalLabel').text(`Edit ${type.charAt(0).toUpperCase() + type.slice(1)}`);
        editModal.show();
    });

    $('#edit-item-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-item-id').val();
        const type = $('#edit-item-type').val();
        const name = $('#edit-item-name').val();

        $.ajax({
            url: 'ajax_manage_clinic_data.php',
            type: 'POST',
            data: { action: 'update', type: type, id: id, name: name },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const row = $(`#${type}-${id}`);
                    row.find('td:first').text(name);
                    row.find('.edit-btn').data('name', name);
                    editModal.hide();
                    toastr.success('Item updated successfully.');
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() { toastr.error('A server error occurred.'); }
        });
    });
});
</script>