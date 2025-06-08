document.addEventListener('DOMContentLoaded', function() {
    const editPatientForm = document.getElementById('editPatientForm');

    editPatientForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Get the patient ID (you might need to pass this from PHP)
        ```html\n<script>\n  const patientID = <?php echo isset($patientID) ? json_encode(intval($patientID)) : 0; ?>;\n</script>\n```

        // Collect form data
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;

        // Create a data object to send
        const data = {
            patientID: patientID,
            firstName: firstName,
            lastName: lastName,
            // Add other data to send as needed
        };

        // Send the data to the server using AJAX
        fetch('update_patient.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the displayed patient information
                document.getElementById('firstName').textContent = firstName;
                document.getElementById('lastName').textContent = lastName;
                 // Reload the page to reflect the changes
                 location.reload();
                // Optionally, close the modal
                var myModalEl = document.getElementById('editPatientModal')
                var modal = bootstrap.Modal.getInstance(myModalEl)
                modal.hide()
            } else {
                alert('Failed to update patient information');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});