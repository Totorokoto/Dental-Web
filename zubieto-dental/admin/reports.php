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

<!-- Page-specific styles for better visuals and printing -->
<style>
    /* On-Screen Styles */
    .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
    .btn-primary:hover { background-color: #00695C; border-color: #00695C; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25); }
    .kpi-card { border: none; border-radius: 1rem; box-shadow: var(--card-shadow); color: #ffffff; transition: transform 0.2s ease-in-out; }
    .kpi-card:hover { transform: translateY(-5px); }
    .kpi-card .card-body { display: flex; justify-content: space-between; align-items: center; }
    .kpi-card i { font-size: 3rem; opacity: 0.3; }
    .kpi-card .card-text { font-size: 2rem; font-weight: 700; margin-bottom: 0; }
    .kpi-card .card-title { font-weight: 500; font-size: 0.95rem; }

    .print-only { display: none; }

    /* PRINT STYLES */
    @media print {
        body * { visibility: hidden; }
        #reportResults, #reportResults * { visibility: visible; }
        #reportResults { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        #report-header, .print-only { display: block !important; visibility: visible !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; page-break-inside: avoid; }
        .print-only h3 { border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-top: 25px; font-size: 1.5rem; }
        .print-only p, .print-only li { font-size: 1rem; }
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom no-print">
    <h1 class="h2">Clinic Reports & Analytics</h1>
    <button class="btn btn-outline-secondary" onclick="window.print();"><i class="fas fa-print me-2"></i>Print Report</button>
</div>

<!-- Filter Card -->
<div class="card shadow-sm mb-4 no-print">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Generate Report</h5>
    </div>
    <div class="card-body">
        <form id="reportFilterForm">
            <div class="row align-items-end g-3">
                <div class="col-md-4"><label for="start_date" class="form-label">Start Date</label><input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $first_day_of_month; ?>" required></div>
                <div class="col-md-4"><label for="end_date" class="form-label">End Date</label><input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $last_day_of_month; ?>" required></div>
                <div class="col-md-3"><label for="branch" class="form-label">Branch</label><select class="form-select" id="branch" name="branch"><option value="All">All Branches</option><option value="Lucban">Lucban</option><option value="Sta. Rosa">Sta. Rosa</option></select></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button></div>
            </div>
        </form>
    </div>
</div>

<!-- Loading Spinner and No Data Message -->
<div id="loadingSpinner" class="text-center mt-5 no-print" style="display: none;"><div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Generating Report...</p></div>
<div id="noDataMessage" class="text-center mt-5 no-print" style="display: none;"><div class="card bg-light"><div class="card-body p-5"><i class="fas fa-info-circle fa-3x text-primary mb-3"></i><h4 class="card-title">No Data Found</h4><p class="card-text text-muted">There is no activity recorded for the selected date range and branch.</p></div></div></div>

<!-- Results Section -->
<div id="reportResults" style="display: none;">
    <!-- Report Header for Printing -->
    <div id="report-header">
        <img src="../assets/images/logo.png" style="max-height: 80px; margin-bottom: 1rem;" alt="Clinic Logo">
        <h2>Zubieto Dental Clinic - Performance Report</h2>
        <p><strong>Date Range:</strong> <span id="printDateRange"></span></p>
        <p><strong>Branch:</strong> <span id="printBranch"></span></p>
        <hr>
    </div>

    <!-- Print-Only Detailed Text Section -->
    <div class="print-only">
        <h3>Summary</h3>
        <div id="print-summary-data"></div>
        <h3 class="mt-4">Appointment Status Breakdown</h3>
        <div id="print-appointment-data"></div>
    </div>

    <!-- KPI Cards (Marked as no-print) -->
    <div class="row no-print">
        <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-success kpi-card"><div class="card-body"><div><h6 class="card-title">Total Revenue</h6><p class="card-text" id="totalRevenue">₱0.00</p></div><i class="fas fa-peso-sign"></i></div></div></div>
        <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-danger kpi-card"><div class="card-body"><div><h6 class="card-title">Outstanding Balance</h6><p class="card-text" id="outstandingBalance">₱0.00</p></div><i class="fas fa-file-invoice-dollar"></i></div></div></div>
        <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-primary kpi-card"><div class="card-body"><div><h6 class="card-title">New Patients</h6><p class="card-text" id="newPatients">0</p></div><i class="fas fa-user-plus"></i></div></div></div>
        <div class="col-lg-3 col-md-6 mb-4"><div class="card bg-warning kpi-card"><div class="card-body text-dark"><div><h6 class="card-title">No-Show Rate</h6><p class="card-text" id="noShowRate">0%</p></div><i class="fas fa-user-clock"></i></div></div></div>
    </div>

    <!-- Charts Row (Marked as no-print) -->
    <div class="row no-print">
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Appointment Status Breakdown</h5></div>
                <div class="card-body"><canvas id="appointmentStatusChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Top 5 Services</h5></div>
                <div class="card-body"><canvas id="topServicesChart"></canvas></div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Table for Top Services (Visible on screen AND print) -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Top Services Details</h5></div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-light"><tr><th>Service/Procedure</th><th class="text-end">Count</th></tr></thead>
                        <tbody id="topServicesTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom JavaScript for this page -->
<script>
$(document).ready(function() {
    var appointmentChart, topServicesChart;

    function formatCurrency(value) { return '₱' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    function loadReportData(startDate, endDate, branch) {
        $('#reportResults').hide();
        $('#noDataMessage').hide();
        $('#loadingSpinner').show();

        $.ajax({
            url: 'ajax_get_report_data.php', type: 'POST', data: { start_date: startDate, end_date: endDate, branch: branch }, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const statuses = data.appointment_statuses;
                    if (data.total_revenue == 0 && data.new_patients == 0 && statuses.Completed == 0 && statuses.Scheduled == 0 && data.top_procedures.length == 0) {
                        $('#noDataMessage').fadeIn();
                        return;
                    }
                    $('#printDateRange').text(startDate + ' to ' + endDate);
                    $('#printBranch').text(branch);
                    $('#totalRevenue').text(formatCurrency(data.total_revenue || 0));
                    $('#outstandingBalance').text(formatCurrency(data.outstanding_balance || 0));
                    $('#newPatients').text(data.new_patients || 0);
                    const completed = statuses.Completed || 0;
                    const noShows = statuses['No-Show'] || 0;
                    const totalRelevantAppts = completed + noShows;
                    const noShowRate = totalRelevantAppts > 0 ? ((noShows / totalRelevantAppts) * 100).toFixed(1) + '%' : '0%';
                    $('#noShowRate').text(noShowRate);
                    if (appointmentChart) appointmentChart.destroy();
                    appointmentChart = new Chart(document.getElementById('appointmentStatusChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Scheduled', 'Completed', 'Cancelled', 'No-Show'],
                            datasets: [{ label: 'Number of Appointments', data: [statuses.Scheduled || 0, completed, statuses.Cancelled || 0, noShows], backgroundColor: ['#0d6efd', '#198754', '#6c757d', '#dc3545'] }]
                        },
                        options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
                    });
                    const procedureLabels = data.top_procedures.map(p => p.procedure_done);
                    const procedureCounts = data.top_procedures.map(p => p.count);
                    if (topServicesChart) topServicesChart.destroy();
                    topServicesChart = new Chart(document.getElementById('topServicesChart').getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: procedureLabels,
                            datasets: [{ data: procedureCounts, backgroundColor: ['#00796B', '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d'] }]
                        },
                        options: { responsive: true, plugins: { legend: { position: 'top' } } }
                    });
                    let tableHtml = '';
                    if (data.top_procedures.length > 0) {
                        data.top_procedures.forEach(proc => { tableHtml += `<tr><td>${proc.procedure_done}</td><td class="text-end">${proc.count}</td></tr>`; });
                    } else {
                        tableHtml = '<tr><td colspan="2" class="text-center text-muted">No procedures recorded in this period.</td></tr>';
                    }
                    $('#topServicesTableBody').html(tableHtml);
                    const summaryHtml = `<p><strong>Total Revenue:</strong> ${formatCurrency(data.total_revenue || 0)}</p><p><strong>Total Outstanding Balance:</strong> ${formatCurrency(data.outstanding_balance || 0)}</p><p><strong>New Patients Registered:</strong> ${data.new_patients || 0}</p><p><strong>No-Show Rate:</strong> ${noShowRate}</p>`;
                    $('#print-summary-data').html(summaryHtml);
                    const appointmentHtml = `<ul><li><strong>Scheduled:</strong> ${statuses.Scheduled || 0}</li><li><strong>Completed:</strong> ${completed}</li><li><strong>Cancelled:</strong> ${statuses.Cancelled || 0}</li><li><strong>No-Shows:</strong> ${noShows}</li></ul>`;
                    $('#print-appointment-data').html(appointmentHtml);
                    $('#reportResults').fadeIn();
                } else { alert('Error: ' + response.message); }
            },
            error: function() { alert('An error occurred while fetching report data.'); },
            complete: function() { $('#loadingSpinner').hide(); }
        });
    }
    $('#reportFilterForm').on('submit', function(e) { e.preventDefault(); loadReportData($('#start_date').val(), $('#end_date').val(), $('#branch').val()); });
    loadReportData($('#start_date').val(), $('#end_date').val(), $('#branch').val());
});
</script>