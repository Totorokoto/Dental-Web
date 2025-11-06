// FILE: admin/assets/js/appointments.js (FINAL CORRECTED VERSION)

$(document).ready(function() {
    
    // --- Variable Declarations ---
    const fp = flatpickr("#appointment_date", { enableTime: true, dateFormat: "Y-m-d H:i", minTime: "08:00", maxTime: "17:00", minuteIncrement: 15, time_24hr: false });
    var calendarEl = document.getElementById('calendar');
    var sidePanel = $('#appointment-side-panel');
    var calendarContainer = $('#calendar-container');
    var form = $('#appointmentForm');
    var newPatientModal = new bootstrap.Modal(document.getElementById('newPatientModal'));
    var currentViewingEventId = null;
    var currentViewingEventData = null; 
    
    var userBranch = window.userConfig.branch;
    var userRole = window.userConfig.role;

    $('.searchable-select').select2({ theme: 'bootstrap-5', dropdownParent: sidePanel });

    // --- Helper Functions ---
    function openSidePanel() { if (!sidePanel.hasClass('is-open')) { sidePanel.addClass('is-open'); calendarContainer.addClass('panel-is-open'); setTimeout(() => { calendar.updateSize(); }, 350); } }
    function closeSidePanel() { if (sidePanel.hasClass('is-open')) { sidePanel.removeClass('is-open'); calendarContainer.removeClass('panel-is-open'); setTimeout(() => { calendar.updateSize(); }, 350); } currentViewingEventId = null; currentViewingEventData = null; }
    function showViewMode() { $('#side-panel-view-content').show(); $('#side-panel-form-content').hide(); }
    function showFormMode() { $('#side-panel-view-content').hide(); $('#side-panel-form-content').show(); }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        events: 'ajax_get_appointments.php',
        selectable: true,
        editable: true, 
        eventDisplay: 'block',
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); 
            if (info.event.extendedProps.isFollowUp) {
                populateAndShowFollowUpPanel(info.event.id);
            } else {
                populateAndShowViewPanel(info.event.id);
            }
        },
        select: function(info) {
            let selectedDate = info.start;
            if (info.allDay) { selectedDate.setHours(9, 0, 0); }
            prepareAndShowNewForm(selectedDate);
        },
        eventDrop: function(info) {
             if (info.event.extendedProps.isFollowUp) {
                if (confirm("Are you sure you want to reschedule this follow-up?")) {
                    $.post('followup_drag_update_process.php', {
                        id: info.event.id,
                        new_date: info.event.start.toISOString().split('T')[0] 
                    }, function(response) {
                        if (!response.success) {
                            alert("Error: " + response.message);
                            info.revert();
                        } else {
                            toastr.success(response.message);
                            if (sidePanel.hasClass('is-open') && currentViewingEventId == info.event.id) {
                                const newStartDate = info.event.start;
                                const formattedNewDate = formatEventDateOnly(newStartDate);
                                $('#view-datetime').text(formattedNewDate);
                                if (currentViewingEventData) {
                                    currentViewingEventData.appointment_date = newStartDate.toISOString().split('T')[0];
                                }
                            }
                        }
                    }, 'json');
                } else {
                    info.revert();
                }
            } else {
                if (confirm("Are you sure you want to reschedule this appointment?")) {
                    $.post('appointment_drag_update_process.php', {
                        appointment_id: info.event.id,
                        new_date: info.event.start.toISOString().slice(0, 19).replace('T', ' ')
                    }, function(response) {
                        if (!response.success) {
                            alert("Error: " + response.message);
                            info.revert();
                        } else {
                            toastr.success(response.message);
                            if (sidePanel.hasClass('is-open') && currentViewingEventId == info.event.id) {
                                const newStartDate = info.event.start;
                                const formattedNewDateTime = formatEventDateTime(newStartDate);
                                $('#view-datetime').text(formattedNewDateTime);
                                if (currentViewingEventData) {
                                    const year = newStartDate.getFullYear();
                                    const month = ('0' + (newStartDate.getMonth() + 1)).slice(-2);
                                    const day = ('0' + newStartDate.getDate()).slice(-2);
                                    const hours = ('0' + newStartDate.getHours()).slice(-2);
                                    const minutes = ('0' + newStartDate.getMinutes()).slice(-2);
                                    const seconds = ('0' + newStartDate.getSeconds()).slice(-2);
                                    currentViewingEventData.appointment_date = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                                }
                            }
                        }
                    }, 'json');
                } else {
                    info.revert();
                }
            }
        }
    });
    calendar.render();

    // --- ================================================================== ---
    // --- THE DEFINITIVE FIX: DYNAMIC DENTIST FILTERING LOGIC ---
    // --- ================================================================== ---
    function filterDentistDropdown() {
        const selectedPatient = $('#patient_id').find('option:selected');
        const patientBranch = selectedPatient.data('branch');
        const dentistSelect = $('#dentist_id');

        dentistSelect.val(''); // Reset dentist selection

        dentistSelect.find('option').each(function() {
            const option = $(this);
            if (!option.val()) { // Always show the placeholder
                option.prop('hidden', false);
                return;
            }

            const dentistBranch = option.data('branch');
            const dentistRole = option.data('role');

            let shouldShow = false;

            // If a patient is selected, filter based on their branch
            if (patientBranch) {
                // Show if dentist branch matches patient branch, OR if dentist is an Admin
                if (dentistBranch === patientBranch || dentistRole === 'Admin') {
                    shouldShow = true;
                }
            } 
            // If no patient is selected (initial form state)
            else {
                // Show if dentist is in the logged-in user's branch, OR if the logged-in user is an Admin
                if (dentistBranch === userBranch || userRole === 'Admin') {
                    shouldShow = true;
                }
            }
            
            option.prop('hidden', !shouldShow);
        });
    }

    // Attach the filter function to the patient dropdown's change event
    $('#patient_id').on('change', filterDentistDropdown);


    function populateAndShowViewPanel(eventId) {
        $.getJSON('ajax_get_appointment_details.php', { id: eventId }, function(data) {
            if (data) {
                currentViewingEventId = eventId;
                currentViewingEventData = data;
                $('#view-title').text('Appointment Details');
                $('#view-patient-name').text(data.patient_name || 'N/A');
                $('#view-datetime').text(formatEventDateTime(data.appointment_date));
                $('#view-dentist-name').text(data.dentist_name || 'N/A');
                $('#view-service').text(data.service_description || 'N/A');
                var statusColor = data.color || '#6c757d';
                var textColor = (data.status === 'Pending Approval' ? '#000' : '#fff');
                $('#view-status').html(`<span class="badge" style="background-color:${statusColor}; color: ${textColor}; font-size: 1rem;">${data.status}</span>`);
                $('#view-patient-btn').attr('href', `patient_view.php?id=${data.patient_id}`);
                const approvalActions = $('#approval-actions');
                const standardActions = $('#standard-actions');
                const markCompletedBtn = $('#mark-completed-btn');
                approvalActions.addClass('d-none');
                standardActions.addClass('d-none');
                $('#edit-panel-btn, #delete-panel-btn').removeClass('d-none');
                if (data.status === 'Pending Approval') {
                    approvalActions.removeClass('d-none');
                    $('#approve-btn').data('id', eventId);
                    $('#reschedule-btn').data('id', eventId);
                } else {
                    standardActions.removeClass('d-none');
                    $('#edit-panel-btn').data('id', eventId);
                    $('#delete-panel-btn').data('id', eventId);
                    if (data.status === 'Scheduled') {
                        markCompletedBtn.removeClass('d-none').data('id', eventId);
                    } else {
                        markCompletedBtn.addClass('d-none');
                    }
                }
                showViewMode();
                openSidePanel();
            } else {
                toastr.error("Could not load appointment details.");
                closeSidePanel();
                calendar.refetchEvents();
            }
        });
    }

    function populateAndShowFollowUpPanel(followUpId) {
        $.getJSON('ajax_get_followup_details.php', { id: followUpId }, function(data) {
            if(data) {
                currentViewingEventId = followUpId;
                currentViewingEventData = data;
                $('#view-title').text('Follow-up Details');
                $('#view-patient-name').text(data.patient_name || 'N/A');
                $('#view-datetime').text(formatEventDateOnly(data.appointment_date));
                $('#view-dentist-name').text(data.dentist_name || 'N/A');
                $('#view-service').text(data.service_description || 'N/A');
                $('#view-status').html(`<span class="badge" style="background-color:${data.color}; color: white; font-size: 1rem;">${data.status}</span>`);
                $('#view-patient-btn').attr('href', `patient_view.php?id=${data.patient_id}`);
                $('#approval-actions').addClass('d-none');
                $('#edit-panel-btn, #delete-panel-btn, #mark-completed-btn').addClass('d-none');
                $('#standard-actions').removeClass('d-none');
                showViewMode();
                openSidePanel();
            } else { toastr.error('Could not retrieve follow-up details.'); }
        });
    }

    function prepareAndShowNewForm(start) {
        form[0].reset();
        $('#appointment_id').val('');
        $('#patient_id').val('').trigger('change');
        fp.setDate(start, true);
        $('#status').val('Scheduled');
        $('#form-title').text('New Appointment');
        $('#cancel-edit-btn').hide();
        $('#showNewPatientModalBtn').show();
        showFormMode();
        openSidePanel();
    }

    function prepareAndShowEditForm(eventId) {
        $.getJSON('ajax_get_appointment_details.php', { id: eventId }, function(data) {
            if(data) {
                form[0].reset();
                $('#appointment_id').val(data.appointment_id);
                $('#patient_id').val(data.patient_id).trigger('change');
                $('#dentist_id').val(data.dentist_id);
                $('#service_description').val(data.service_description);
                fp.setDate(data.appointment_date, true);
                $('#status').val(data.status);
                $('#form-title').text('Edit Appointment');
                $('#cancel-edit-btn').show();
                $('#showNewPatientModalBtn').hide();
                showFormMode();
            }
        });
    }
    
    function formatEventDateTime(start) { const date = new Date(start); return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' at ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }); }
    function formatEventDateOnly(start) { const date = new Date(start); const userTimezoneOffset = date.getTimezoneOffset() * 60000; const correctedDate = new Date(date.getTime() + userTimezoneOffset); return correctedDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }); }
    function deleteAppointment(id) { if(id && confirm('Are you sure you want to delete this appointment?')) { $.post('appointment_delete_process.php', { appointment_id: id }, (response) => { if(response.success) { toastr.success(response.message); closeSidePanel(); calendar.refetchEvents(); } else { toastr.error("Error: " + response.message); } }, 'json'); } }

    $('#addAppointmentBtn').on('click', () => { prepareAndShowNewForm(new Date()); });
    $('#close-panel-btn-view, #close-panel-btn-form').on('click', closeSidePanel);
    $('#cancel-edit-btn').on('click', () => { if ($('#appointment_id').val()) { populateAndShowViewPanel($('#appointment_id').val()); } else { closeSidePanel(); } });
    $('#edit-panel-btn').on('click', function() { prepareAndShowEditForm($(this).data('id')); });
    $('#showNewPatientModalBtn').on('click', () => { $('#newPatientForm')[0].reset(); newPatientModal.show(); });
    
    $('#newPatientForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax_add_quick_patient.php',
            type: 'POST',
            data: {
                first_name: $('#new_first_name').val(),
                last_name: $('#new_last_name').val(),
                mobile_no: $('#new_mobile_no').val(),
                email: $('#new_email').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var newOption = new Option(response.full_name + ' (' + response.branch + ')', response.new_patient_id, true, true);
                    $(newOption).data('branch', response.branch);
                    $('#patient_id').append(newOption).trigger('change');
                    newPatientModal.hide();
                    toastr.success('Patient added successfully.');
                } else {
                    toastr.error('Error: ' + response.message);
                }
            }
        });
    });

    form.on('submit', function(e) { e.preventDefault(); var url = $('#appointment_id').val() ? 'appointment_edit_process.php' : 'appointment_add_process.php'; $.ajax({ url: url, type: 'POST', data: $(this).serialize(), dataType: 'json', success: function(response) { if (response.success) { toastr.success(response.message); closeSidePanel(); calendar.refetchEvents(); } else { toastr.error('Error: ' + response.message); } }, error: function() { toastr.error('A critical server error occurred.'); } }); });
    $('#delete-panel-btn').on('click', function() { deleteAppointment($(this).data('id')); });

    function processAppointmentAction(id, action, extraData = {}) {
        var postData = { appointment_id: id, action: action, ...extraData };
        $.ajax({
            url: 'ajax_process_appointment_action.php', type: 'POST', data: postData, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#declineModal').modal('hide');
                    closeSidePanel();
                    calendar.refetchEvents();
                } else { toastr.error('Error: ' + response.message); }
            },
            error: function() { toastr.error('A critical server error occurred.'); }
        });
    }
    
    $('#mark-completed-btn').on('click', function() { 
        var appointmentId = $(this).data('id'); 
        if (appointmentId && confirm('Mark as completed?')) { 
            $.post('appointment_status_process.php', { appointment_id: appointmentId, status: 'Completed' }, function(response){
                if(response.success){
                    toastr.success('Appointment marked as completed.');
                    closeSidePanel();
                    calendar.refetchEvents();
                } else { toastr.error('Error: ' + response.message); }
            }, 'json');
        } 
    });

    $('#approve-btn').on('click', function() {
        if (!currentViewingEventData) return;
        var eventId = currentViewingEventData.appointment_id;
        var patientName = currentViewingEventData.patient_name;
        var apptTime = formatEventDateTime(currentViewingEventData.appointment_date);
        var patientOption = $('#patient_id option[value="' + currentViewingEventData.patient_id + '"]');
        var patientBranch = patientOption.data('branch');
        $('#approve_appointment_id').val(eventId);
        $('#approve_patient_name').text(patientName);
        $('#approve_appointment_time').text(apptTime);
        var dentistDropdown = $('#approve_dentist_id');
        dentistDropdown.html('<option value="" selected disabled>Select a dentist...</option>');
        $('#dentist_id option').each(function() {
            var option = $(this);
            if (option.val()) {
                var isAvailable = !option.prop('disabled');
                var dentistBranch = option.data('branch');
                var dentistRole = option.data('role');
                if (isAvailable && (dentistBranch === patientBranch || dentistRole === 'Admin')) {
                    dentistDropdown.append($('<option>', {
                        value: option.val(),
                        text: option.text().trim()
                    }));
                }
            }
        });
        var approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
        approveModal.show();
    });

    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#approve_appointment_id').val();
        var dentistId = $('#approve_dentist_id').val();
        if (dentistId) {
            processAppointmentAction(id, 'approve', { dentist_id: dentistId });
            var approveModal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
            approveModal.hide();
        } else {
            alert("Please select a dentist.");
        }
    });
    
    $('#reschedule-btn').on('click', function() {
        if (!currentViewingEventData) return;
        $('#declineForm')[0].reset();
        $('#decline_custom_reason_wrapper').hide();
        var eventId = currentViewingEventData.appointment_id;
        var date = currentViewingEventData.appointment_date.split(' ')[0];
        var patientOption = $('#patient_id option[value="' + currentViewingEventData.patient_id + '"]');
        var patientBranch = patientOption.data('branch');
        $('#decline_appointment_id').val(eventId);
        $('#suggested-slots-container').html('<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Fetching suggestions...</div>');
        $.getJSON('ajax_get_alternative_slots.php', { start_date: date, branch: patientBranch }, function(response){
            if(response.success && response.slots.length > 0){
                var slotsHtml = '<ul class="list-group">';
                response.slots.forEach(function(slot){
                    slotsHtml += `<li class="list-group-item">${slot}</li>`;
                });
                slotsHtml += '</ul>';
                $('#suggested-slots-container').html(slotsHtml);
            } else {
                $('#suggested-slots-container').html('<p class="text-danger text-center">Could not find alternative slots automatically.</p>');
            }
        });
    });

    $('#decline_reason_select').on('change', function() {
        if ($(this).val() === 'Other') {
            $('#decline_custom_reason_wrapper').slideDown();
        } else {
            $('#decline_custom_reason_wrapper').slideUp();
        }
    });

    $('#declineForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#decline_appointment_id').val();
        var reasonSelect = $('#decline_reason_select').val();
        var reasonCustom = $('#decline_reason_custom').val();
        var suggestions = $('#suggested-slots-container').html(); 
        
        processAppointmentAction(id, 'decline', { 
            reason_select: reasonSelect, 
            reason_custom: reasonCustom,
            suggestions: suggestions 
        });
    });

    function updateDentistStatuses() {
        $.ajax({
            url: 'ajax_get_dentist_statuses.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.dentists) {
                    const dentistSelect = $('#dentist_id');
                    response.dentists.forEach(function(dentist) {
                        const option = dentistSelect.find('option[value="' + dentist.user_id + '"]');
                        if (option.length) {
                            const isAvailable = (dentist.availability_status === 'Available');
                            const statusText = isAvailable ? '' : ' - ' + dentist.availability_status;
                            const newText = dentist.full_name + statusText;
                            if (option.text() !== newText) { option.text(newText); }
                            if (option.prop('disabled') === isAvailable) { option.prop('disabled', !isAvailable); }
                            if (isAvailable) { option.removeClass('dentist-unavailable'); } else { option.addClass('dentist-unavailable'); }
                        }
                    });
                }
            },
            error: function() { console.log("Could not poll for dentist statuses."); }
        });
    }

    setInterval(updateDentistStatuses, 30000); 

    // Initial call to set the correct state on page load
    filterDentistDropdown();
});