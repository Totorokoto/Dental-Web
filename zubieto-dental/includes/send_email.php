<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// This line loads the PHPMailer library using Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

function sendAppointmentEmail($toEmail, $patientName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // --- SERVER SETTINGS ---
        // This example uses Gmail. For production, a dedicated service is recommended.
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'zubietodentalclinic@gmail.com'; // **<-- REPLACE WITH YOUR GMAIL ADDRESS**
        $mail->Password   = 'igcj kjka lzyj kwkd';           // **<-- REPLACE WITH YOUR GMAIL APP PASSWORD**
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- RECIPIENTS ---
        $mail->setFrom('zubietodentalclinic@gmail.com', 'Zubieto Dental Clinic');
        $mail->addAddress($toEmail, $patientName);

        // --- EMAIL CONTENT ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // A plain-text version for non-HTML email clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        // For debugging, you can write the error to a log file
        // error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>