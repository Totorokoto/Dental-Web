<?php
// 1. START THE SESSION & GATEKEEPER
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 2. REQUIRE CONFIG
require_once 'config.php';

// 3. FETCH DATA FOR MODALS
$all_patients = $all_procedures = [];
$error_message = '';
try {
    $all_patients = $pdo->query("SELECT PatientID, FirstName, LastName FROM Patients ORDER BY LastName, FirstName")->fetchAll(PDO::FETCH_ASSOC);
    $all_procedures = $pdo->query("SELECT ProcedureID, ProcedureName FROM Procedures ORDER BY ProcedureName")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Could not load required data for the page. Please try again.";
    error_log("Reservation Page Load Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations Calendar</title>
    
    <!-- Required CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <link rel="stylesheet" href="style.css"> 
    
    <style>
        /* Custom styles for FullCalendar */
        .fc {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .fc .fc-toolbar-title {
            font-size: 1.5em;
        }
        .fc-event {
            cursor: pointer;
            border: none !important;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content-area">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Appointment Calendar</h2>
            <button class="btn btn-primary" id="addEventBtn"><i class="fas fa-plus me-1"></i> Add Appointment</button>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- FullCalendar Container -->
        <div id='calendar'></div>
    </main>
    
    <!-- Add/Edit Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reservationModalLabel">Manage Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationForm">
                        <input type="hidden" name="treatmentID" id="treatmentID">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="patientID" class="form-label">Patient <span class="text-danger">*</span></label>
                                <select class="form-select" id="patientID" name="patientID" required>
                                    <option value="">-- Select Patient --</option>
                                    <?php foreach ($all_patients as $patient): ?>
                                        <option value="<?php echo $patient['PatientID']; ?>"><?php echo htmlspecialchars($patient['LastName'] . ', ' . $patient['FirstName']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="procedureID" class="form-label">Procedure / Reason <span class="text-danger">*</span></label>
                                <select class="form-select" id="procedureID" name="procedureID" required>
                                    <option value="">-- Select Procedure --</option>
                                     <?php foreach ($all_procedures as $procedure): ?>
                                        <option value="<?php echo $procedure['ProcedureID']; ?>"><?php echo htmlspecialchars($procedure['ProcedureName']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nextAppointment" class="form-label">Appointment Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="nextAppointment" name="nextAppointment" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveEventBtn">Save Appointment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- REQUIRED SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const reservationModal = new bootstrap.Modal(document.getElementById('reservationModal'));
        const form = document.getElementById('reservationForm');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: 'get_reservations.php',
            editable: true,
            selectable: true,
            
            // Handle clicking on a date to create a new event
            select: function(info) {
                form.reset();
                document.getElementById('treatmentID').value = '';
                document.getElementById('deleteEventBtn').style.display = 'none';
                
                // Format date correctly for datetime-local input
                const selectedDate = new Date(info.start);
                selectedDate.setMinutes(selectedDate.getMinutes() - selectedDate.getTimezoneOffset());
                const localISOString = selectedDate.toISOString().slice(0, 16);
                document.getElementById('nextAppointment').value = localISOString;
                
                reservationModal.show();
            },
            
            // Handle clicking an existing event to edit
            eventClick: function(info) {
                form.reset();
                const props = info.event.extendedProps;
                
                document.getElementById('treatmentID').value = info.event.id;
                document.getElementById('patientID').value = props.patientID;
                document.getElementById('procedureID').value = props.procedureID;
                document.getElementById('notes').value = props.notes || '';
                
                const eventDate = new Date(info.event.start);
                eventDate.setMinutes(eventDate.getMinutes() - eventDate.getTimezoneOffset());
                document.getElementById('nextAppointment').value = eventDate.toISOString().slice(0, 16);
                
                document.getElementById('deleteEventBtn').style.display = 'inline-block';
                reservationModal.show();
            },

            // Handle drag-and-drop event resizing/moving
            eventDrop: function(info) {
                updateEvent(info.event);
            },
            eventResize: function(info) {
                updateEvent(info.event);
            }
        });

        calendar.render();

        // Manual "Add Appointment" button
        document.getElementById('addEventBtn').addEventListener('click', function() {
            form.reset();
            document.getElementById('treatmentID').value = '';
            document.getElementById('deleteEventBtn').style.display = 'none';
            reservationModal.show();
        });

        // Save button inside modal
        document.getElementById('saveEventBtn').addEventListener('click', function() {
            const formData = new FormData(form);
            const action = formData.get('treatmentID') ? 'update' : 'create';
            formData.append('action', action);

            fetch('manage_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.refetchEvents();
                    reservationModal.hide();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        // Delete button inside modal
        document.getElementById('deleteEventBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this appointment?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('treatmentID', document.getElementById('treatmentID').value);

                 fetch('manage_reservation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.refetchEvents();
                        reservationModal.hide();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        });
        
        // Function to handle updates from drag-and-drop or resize
        function updateEvent(event) {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('treatmentID', event.id);
            formData.append('patientID', event.extendedProps.patientID);
            formData.append('procedureID', event.extendedProps.procedureID);
            formData.append('notes', event.extendedProps.notes || '');

            const eventDate = new Date(event.start);
            eventDate.setMinutes(eventDate.getMinutes() - eventDate.getTimezoneOffset());
            formData.append('nextAppointment', eventDate.toISOString().slice(0, 16));

            fetch('manage_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating event: ' + data.message);
                    info.revert(); // Revert change on failure
                }
            });
        }
    });
    </script>
</body>
</html>