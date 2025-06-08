<?php
// auth.php

// Start session
session_start();

// Check if username and password were submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['username']) && !empty($_POST['password'])) {
    
    require_once "config.php";
    
    // Prepare a select statement
    $sql = "SELECT UserID, Username, Password, FirstName, Role FROM users WHERE Username = :username";
    
    if ($stmt = $pdo->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":username", $_POST['username'], PDO::PARAM_STR);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Check if username exists, if yes then verify password
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $id = $row["UserID"];
                    $username = $row["Username"];
                    $hashed_password = $row["Password"];
                    $firstName = $row["FirstName"];
                    $role = $row["Role"];
                    
                    // Verify the submitted password against the hashed password in the database
                    if (password_verify($_POST['password'], $hashed_password)) {
                        // Password is correct, so start a new session
                        
                        // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["name"] = $firstName;
                        $_SESSION["role"] = $role;
                        
                        // Redirect user to the patient list page
                        header("location: patient_list.php");
                        exit;

                    } else {
                        // Password is not valid
                        header("location: login.php?error=Invalid username or password.");
                        exit;
                    }
                }
            } else {
                // Username doesn't exist
                header("location: login.php?error=Invalid username or password.");
                exit;
            }
        } else {
            // SQL execution error
            header("location: login.php?error=Oops! Something went wrong. Please try again later.");
            exit;
        }

        // Close statement
        unset($stmt);
    }
    
    // Close connection
    unset($pdo);
} else {
    // If accessed directly or without POST data
    header("location: login.php");
    exit;
}
?>