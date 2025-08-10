<?php
// FILE: admin/reports.php
require 'includes/header.php';

// RBAC Check: Only Admins can view this page
if ($_SESSION['role'] !== 'Admin') {
    $_SESSION['message'] = "Access Denied.";
    $_SESSION['message_type'] = 'danger';
    header("Location: dashboard.php");
    exit();
}

// Set default dates for the filter (current month)
$first_day_of_month = date('Y-m-01');
$last_day_of_month = date('Y-m-t');
?>

<!-- Include Chart.js library for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Clinic Reports & Analytics</h1>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        Generate Report
    </div>
    <div class="card-body">
        <form id="reportFilterForm">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $first_day_of_month; ?>" required>
                </div>
                <div class="col-md-5">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $last_day_of_month; ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Section -->
<div id="reportResults">
    <!-- This section will be populated by AJAX -->
    <div class="row">
        <!-- KPI Cards -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="card-text" id="totalRevenue">₱0.00</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">New Patients</h5>
                    <h2 class="card-text" id="newPatients">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Appointments Completed</h5>
                    <h2 class="card-text" id="appointmentsCompleted">0</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    Appointment Status Breakdown
                </div>
                <div class="card-body">
                    <canvas id="appointmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Loading Spinner (optional but good for UX) -->
<div id="loadingSpinner" class="text-center mt-5" style="display: none;">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>


<!-- =================================================================
     CUSTOM JAVASCRIPT FOR THIS PAGE
     ================================================================= -->
<script>
$(document).ready(function() {
    var appointmentChart; // Variable to hold the chart instance

    // Function to format currency
    function formatCurrency(value) {
        return '₱' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Function to fetch and display report data
    function loadReportData(startDate, endDate) {
        $('#reportResults').hide();
        $('#loadingSpinner').show();

        $.ajax({
            url: 'ajax_get_report_data.php',
            type: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update KPI Cards
                    $('#totalRevenue').text(formatCurrency(response.data.total_revenue || 0));
                    $('#newPatients').text(response.data.new_patients || 0);
                    $('#appointmentsCompleted').text(response.data.appointment_statuses.Completed || 0);

                    // --- Update Chart ---
                    var ctx = document.getElementById('appointmentStatusChart').getContext('2d');
                    var statuses = response.data.appointment_statuses;
                    
                    if (appointmentChart) {
                        appointmentChart.destroy(); // Destroy previous chart instance before creating a new one
                    }

                    appointmentChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Scheduled', 'Completed', 'Cancelled', 'No-Show'],
                            datasets: [{
                                label: 'Appointment Statuses',
                                data: [
                                    statuses.Scheduled || 0,
                                    statuses.Completed || 0,
                                    statuses.Cancelled || 0,
                                    statuses['No-Show'] || 0
                                ],
                                backgroundColor: [
                                    '#0d6efd', // Blue
                                    '#198754', // Green
                                    '#6c757d', // Gray
                                    '#dc3545'  // Red
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Appointment Statuses from ' + startDate + ' to ' + endDate
                                }
                            }
                        }
                    });

                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while fetching report data.');
            },
            complete: function() {
                $('#loadingSpinner').hide();
                $('#reportResults').show();
            }
        });
    }

    // Handle form submission
    $('#reportFilterForm').on('submit', function(e) {
        e.preventDefault();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        loadReportData(startDate, endDate);
    });

    // Load the initial report for the default date range
    loadReportData($('#start_date').val(), $('#end_date').val());
});
</script>


<?php
require 'includes/footer.php';
?>