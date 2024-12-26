<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['status'];

    // Fetching candidate's email and job details
    $query = "SELECT a.*, u.email AS candidate_email, j.title AS job_title 
              FROM applications a 
              JOIN users u ON a.user_id = u.id 
              JOIN jobs j ON a.job_id = j.id 
              WHERE a.id = $application_id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $application = $result->fetch_assoc();
        $candidate_email = $application['candidate_email'];
        $job_title = $application['job_title'];

        // Updating the application status
        $update_sql = "UPDATE applications SET status = '$new_status' WHERE id = $application_id";
        if ($conn->query($update_sql)) {
            // Sending notification email
            $subject = "Application Status Update: $job_title";
            $message = "Dear Candidate,\n\nYour application status for the job '$job_title' has been updated to '$new_status'.\n\nBest regards,\nJob Portal Team";
            $headers = "From: no-reply@jobportal.com"; //non-function mail id.

            if (mail($candidate_email, $subject, $message, $headers)) {
                header('Location: dashboard.php');
                exit();
            } else {
                echo "Error sending email notification.";
            }
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "Application not found.";
    }
}
?>
