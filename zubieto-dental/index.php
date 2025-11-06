<?php
// FILE: index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zubieto Dental Clinic - Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-color: #00796B; /* A professional teal */
        --secondary-color: #B2DFDB; /* A light, calming mint */
        --background-color: #f4f7f6;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--background-color);
        background-image: linear-gradient(to top, #e6f0f3 0%, #f4f7f6 100%);
        min-height: 100vh;
    }

    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .login-card {
        width: 100%;
        max-width: 420px;
        padding: 2.5rem;
        border: none;
        border-radius: 1rem;
        box-shadow: var(--card-shadow);
        background-color: #ffffff;
    }

    .login-logo {
        display: block;
        width: 80%;
        max-width: 250px;
        margin: 0 auto 1rem;
    }
    
    .form-control {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(0, 121, 107, 0.25);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-weight: 500;
    }
    .btn-primary:hover {
        background-color: #00695C;
        border-color: #00695C;
    }

    .btn-outline-secondary {
        border-color: #ced4da;
        color: #495057;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-weight: 500;
    }
    .btn-outline-secondary:hover {
        background-color: var(--primary-color);
        color: #ffffff;
        border-color: var(--primary-color);
    }

    .welcome-text {
        color: #6c757d;
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }
</style>
</head>
<body>
<div class="login-container">
<div class="card login-card">
<div class="card-body">
<div class="text-center">
<img src="assets/images/logo.png" alt="Zubieto Dental Clinic Logo" class="login-logo">
<p class="welcome-text">Welcome! Please log in or request an appointment.</p>
</div>

<?php
        if (isset($_SESSION['login_error'])) {
            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="login_process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid gap-3 mt-4">
                <button type="submit" class="btn btn-primary">Staff Login</button>
                <a href="public_appointment.php" class="btn btn-outline-secondary">Request an Appointment</a>
            </div>
        </form>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>