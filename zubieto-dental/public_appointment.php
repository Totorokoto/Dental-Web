<?php
// FILE: public_appointment.php

// This code must be at the top to fetch data before the HTML is rendered.
require 'includes/db_connect.php';
$procedures_result = $conn->query("SELECT name FROM lookup_procedures WHERE is_active = 1 ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request an Appointment - Zubieto Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #00796B;
            --secondary-color: #B2DFDB;
            --background-color: #f4f7f6;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            background-image: linear-gradient(to top, #e6f0f3 0%, #f4f7f6 100%);
        }

        .form-container { max-width: 700px; }
        .form-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); background-color: #ffffff; }
        .form-header { text-align: center; margin-bottom: 2rem; }
        .form-header .icon-wrapper { background-color: var(--secondary-color); color: var(--primary-color); height: 80px; width: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .form-header h2 { font-weight: 600; }
        .form-label { font-weight: 500; color: #343a40; }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.75rem 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25); }
        .form-select:disabled { background-color: #e9ecef; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); border-radius: 0.5rem; padding: 0.85rem; font-weight: 500; font-size: 1.1rem; }
        .btn-primary:hover { background-color: #00695C; border-color: #00695C; }
    </style>
</head>
<body>
    <div class="container form-container my-5">
        <div class="card form-card">
            <div class="card-body p-4 p-md-5">
                <div class="form-header">
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h2>Appointment Request</h2>
                    <p class="text-muted" id="form-subtitle">Enter your email to begin.</p>
                </div>

                <!-- This div will hold our success message -->
                <div id="form-feedback" class="mt-3"></div>

                <!-- STEP 1: Email Check -->
                <div id="email-check-step">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                        <div class="form-text">If you have an existing record with us, please use the same email address.</div>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="checkEmailBtn">Next <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- STEP 2: Main Form (Initially Hidden) -->
                <form id="publicRequestForm" style="display: none;">
                    <!-- Email will be carried over -->
                    <input type="hidden" name="email" id="form_email">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="mobile_no" name="mobile_no" placeholder="e.g., 09171234567" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="branch" class="form-label">Preferred Clinic Branch <span class="text-danger">*</span></label>
                        <select class="form-select" id="branch" name="branch" required>
                            <option value="" selected disabled>Select a branch...</option>
                            <option value="Sta. Rosa">Sta. Rosa, Laguna</option>
                            <option value="Lucban">Lucban, Quezon</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="preferred_date" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="preferred_date" name="preferred_date" placeholder="Select a date..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="preferred_time" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                            <select class="form-select" id="preferred_time" name="preferred_time" required disabled>
                                <option value="">Select a date first</option>
                            </select>
                        </div>
                    </div>
                    
                     <div class="mb-3">
                        <label for="service_description_select" class="form-label">Reason for Visit <span class="text-danger">*</span></label>
                        <select class="form-select" name="service_description" id="service_description_select" required>
                            <option value="" selected disabled>Choose a reason...</option>
                            <?php while($proc = $procedures_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($proc['name']); ?>"><?php echo htmlspecialchars($proc['name']); ?></option>
                            <?php endwhile; ?>
                            <option value="Other">Other...</option>
                        </select>
                    </div>

                    <div class="mb-3" id="other_reason_wrapper" style="display: none;">
                        <label for="service_description_other" class="form-label">Please specify your reason for visiting</label>
                        <textarea class="form-control" name="service_description_other" id="service_description_other" rows="2"></textarea>
                    </div>

                    <div class="d-grid mt-4">
                      <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="index.php" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-2"></i>Back to Staff Login</a>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
$(document).ready(function() {
    
    // --- STEP 1: EMAIL CHECK ---
    $('#checkEmailBtn').on('click', function() {
        const email = $('#email').val();
        const btn = $(this);

        if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Checking...');

        $.ajax({
            url: 'ajax_check_patient_email.php',
            type: 'GET',
            data: { email: email },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#form_email').val(email);
                    
                    if (response.found) {
                        const patient = response.patient;
                        $('#first_name').val(patient.first_name).prop('readonly', true);
                        $('#last_name').val(patient.last_name).prop('readonly', true);
                        $('#mobile_no').val(patient.mobile_no);
                        $('#branch').val(patient.branch).prop('disabled', true);
                        $('#form-subtitle').text('Welcome back! Please confirm your details and appointment request.');
                    } else {
                        $('#first_name').val('').prop('readonly', false);
                        $('#last_name').val('').prop('readonly', false);
                        $('#mobile_no').val('');
                        $('#branch').val('').prop('disabled', false);
                        $('#form-subtitle').text('Please fill out the form below to request your appointment.');
                    }

                    $('#email-check-step').slideUp();
                    $('#publicRequestForm').slideDown();
                } else {
                    alert(response.message || 'An error occurred.');
                    btn.prop('disabled', false).html('Next <i class="fas fa-arrow-right ms-2"></i>');
                }
            },
            error: function() {
                alert('A server error occurred while checking your email. Please try again.');
                btn.prop('disabled', false).html('Next <i class="fas fa-arrow-right ms-2"></i>');
            }
        });
    });

    // --- STEP 2: FORM LOGIC ---
    flatpickr("#preferred_date", {
        dateFormat: "Y-m-d",
        minDate: "today",
        "disable": [ function(date) { return (date.getDay() === 0); } ],
        onChange: function(selectedDates, dateStr, instance) {
            const timeDropdown = $('#preferred_time');
            if (dateStr) {
                timeDropdown.prop('disabled', true).html('<option value="">Loading times...</option>');
                $.ajax({
                    url: 'ajax_get_public_slots.php',
                    type: 'GET',
                    data: { date: dateStr },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.slots.length > 0) {
                            var options = '<option value="" selected disabled>Select a time...</option>';
                            response.slots.forEach(function(slot) {
                                options += `<option value="${slot.value}">${slot.display}</option>`;
                            });
                            timeDropdown.html(options).prop('disabled', false);
                        } else {
                            timeDropdown.html('<option value="">No slots available</option>');
                        }
                    },
                    error: function() { timeDropdown.html('<option value="">Could not load times</option>'); }
                });
            }
        }
    });

    $('#service_description_select').on('change', function() {
        if ($(this).val() === 'Other') {
            $('#other_reason_wrapper').slideDown();
            $('#service_description_other').prop('required', true);
        } else {
            $('#other_reason_wrapper').slideUp();
            $('#service_description_other').prop('required', false);
        }
    });

    $('#publicRequestForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var feedbackDiv = $('#form-feedback');
        var submitButton = form.find('button[type="submit"]');

        $('#branch').prop('disabled', false);

        submitButton.prop('disabled', true).html('<div class="spinner-border spinner-border-sm"></div> Submitting...');
        feedbackDiv.html('').removeClass('alert alert-danger alert-success');
        
        $.ajax({
            url: 'ajax_public_appointment_request.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // --- MODIFIED: Enhanced Success Feedback ---
                    
                    // Hide the form and its subtitle
                    form.slideUp();
                    $('#form-subtitle').slideUp();
                    
                    // Construct the new success message
                    const successHtml = `
                        <div class="text-center p-4">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="mb-3">Request Submitted!</h4>
                            <p class="text-muted">${response.message}</p>
                            <div class="mt-4 text-muted small">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Reloading page...
                            </div>
                        </div>
                    `;
                    
                    // Display the new success message and start the reload timer
                    feedbackDiv.html(successHtml).addClass('alert alert-success border-0');
                    setTimeout(function() {
                        location.reload();
                    }, 5000); // 5 seconds

                } else {
                    feedbackDiv.html(response.message).addClass('alert alert-danger');
                }
            },
            error: function() { 
                feedbackDiv.html('A server error occurred. Please try again later.').addClass('alert alert-danger'); 
            },
            complete: function() { 
                if (!feedbackDiv.hasClass('alert-success')) { 
                    submitButton.prop('disabled', false).html('Submit Request'); 
                    if ($('#first_name').prop('readonly')) {
                        $('#branch').prop('disabled', true);
                    }
                }
            }
        });
    });
});
</script>
</body>
</html>