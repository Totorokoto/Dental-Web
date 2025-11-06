<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request an Appointment - Zubieto Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .card { border-radius: 1rem; }
        .choice-card { text-decoration: none; color: inherit; display: block; transition: transform 0.2s; }
        .choice-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container d-flex flex-column justify-content-center" style="min-height: 100vh;">
        <div class="text-center mb-5">
            <img src="assets/images/logo.png" alt="Zubieto Dental Clinic Logo" style="max-width: 250px;">
            <h2 class="mt-4">Book Your Visit</h2>
            <p class="text-muted">Please select an option below to continue.</p>
        </div>

        <div class="row g-4">
            <!-- Existing Patient Option -->
            <div class="col-md-6">
                <a href="patient_login.php" class="card text-center p-4 h-100 choice-card">
                    <div class="card-body">
                        <i class="fas fa-user-check fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">I'm an Existing Patient</h4>
                        <p class="card-text">Log in to your patient portal to view your history and book a new appointment.</p>
                    </div>
                </a>
            </div>

            <!-- New Patient Option -->
            <div class="col-md-6">
                <a href="#" class="card text-center p-4 h-100 choice-card" data-bs-toggle="modal" data-bs-target="#newPatientRequestModal">
                    <div class="card-body">
                        <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                        <h4 class="card-title">I'm a New Patient</h4>
                        <p class="card-text">Provide a few details and we'll get in touch to confirm your first appointment.</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="index.php"><i class="fas fa-arrow-left me-2"></i>Back to Staff Login</a>
        </div>
    </div>

    <!-- New Patient Request Modal -->
    <div class="modal fade" id="newPatientRequestModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">New Patient Appointment Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="newPatientForm">
            <div class="modal-body">
                <p class="small text-muted">Please provide your contact information. A staff member will call or email you to confirm your appointment details and time.</p>
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="mobile_no" class="form-label">Mobile Number</label>
                    <input type="tel" class="form-control" name="mobile_no" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                 <div class="mb-3">
                    <label for="service_description" class="form-label">Reason for Visit</label>
                    <textarea class="form-control" name="service_description" rows="3" required placeholder="e.g., Annual Check-up, Tooth Pain, Cleaning..."></textarea>
                </div>
                <div id="form-feedback" class="mt-3"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Submit Request</button>
            </div>
          </form>
        </div>
      </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#newPatientForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var feedbackDiv = $('#form-feedback');

        feedbackDiv.html('<div class="spinner-border spinner-border-sm" role="status"></div><span class="ms-2">Submitting...</span>').removeClass('alert alert-danger alert-success');

        $.ajax({
            url: 'ajax_new_patient_request.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    feedbackDiv.html(response.message).addClass('alert alert-success');
                    form.trigger('reset');
                    setTimeout(function() {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('newPatientRequestModal'));
                        modal.hide();
                        feedbackDiv.html('').removeClass('alert alert-success');
                    }, 4000); // Hide modal after 4 seconds
                } else {
                    feedbackDiv.html(response.message).addClass('alert alert-danger');
                }
            },
            error: function() {
                feedbackDiv.html('A server error occurred. Please try again later.').addClass('alert alert-danger');
            }
        });
    });
});
</script>
</body>
</html>