<?php
// FILE: admin/includes/footer.php

// Close the database connection if it's still open and is a valid mysqli object.
// The '$conn' variable was created in header.php
if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) {
    $conn->close();
}
?>

        </main> <!-- End of main content -->
    </div>
</div>

<!-- Core JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Toastr JS for notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Select2 JS for advanced dropdowns -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Flatpickr JS for date-time pickers -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Custom Global JS -->
<script>
$(document).ready(function() {
    // Universal Toastr options
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000",
    };

    // AJAX for updating availability status from the header modal
    $('#availabilityForm').on('submit', function(e) {
        e.preventDefault();
        var selectedStatus = $('#availability_status').val();
        $.ajax({
            url: 'ajax_update_availability.php', 
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success('Your status has been updated.');
                    
                    var badge = $('#user-status-badge');
                    badge.text(selectedStatus);
                    
                    // Determine badge class based on the new status
                    var badge_class = 'bg-success'; // Default for 'Available'
                    if (selectedStatus == 'On Leave') badge_class = 'bg-secondary';
                    if (selectedStatus == 'Training') badge_class = 'bg-info';
                    if (selectedStatus == 'Sick Day') badge_class = 'bg-warning text-dark';
                    
                    badge.removeClass('bg-success bg-secondary bg-info bg-warning text-dark').addClass(badge_class);
                    
                    var availabilityModal = bootstrap.Modal.getInstance(document.getElementById('availabilityModal'));
                    availabilityModal.hide();
                } else {
                    toastr.error('Error: ' + response.message);
                }
            },
            error: function() {
                toastr.error('A server error occurred.');
            }
        });
    });
});
</script>

</body>
</html>