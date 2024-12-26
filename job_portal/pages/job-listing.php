<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $company_name = $_POST['company_name'];
    $description = $_POST['description'];
    $job_type = $_POST['job_type'];
    $location = $_POST['location'];
    $salary_min = $_POST['salary_min'];
    $salary_max = $_POST['salary_max'];
    $application_deadline = $_POST['application_deadline'];
    $user_id = $_SESSION['user_id'];

    // Handle logo upload
    $logo_name = $_FILES['company_logo']['name'];
    $logo_tmp = $_FILES['company_logo']['tmp_name'];
    $logo_path = '../assets/uploads/logos/' . $logo_name;

    if (move_uploaded_file($logo_tmp, $logo_path)) {
        $sql = "INSERT INTO jobs (title, company_name, description, job_type, location, salary_min, salary_max, application_deadline, company_logo, user_id) 
                VALUES ('$title', '$company_name', '$description', '$job_type', '$location', '$salary_min', '$salary_max', '$application_deadline', '$logo_path', '$user_id')";

        if ($conn->query($sql)) {
            echo "Job listing added successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Failed to upload logo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $pageTitle = "Job Analytics"; include '../templates/header.php'; ?>
    <div class="container mt-5">
    <div class="container mt-5">
    <h2>Post a Job</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="title" class="form-label">Job Title</label>
                <input type="text" name="title" id="title" class="form-control" maxlength="100" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" name="company_name" id="company_name" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Job Description</label>
            <textarea name="description" id="description" class="form-control" rows="5" required></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="job_type" class="form-label">Job Type</label>
                <select name="job_type" id="job_type" class="form-select" required>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="application_deadline" class="form-label">Application Deadline</label>
                <input type="date" name="application_deadline" id="application_deadline" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="salary_min" class="form-label">Salary Range (Min)</label>
                <input type="number" name="salary_min" id="salary_min" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="salary_max" class="form-label">Salary Range (Max)</label>
                <input type="number" name="salary_max" id="salary_max" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="company_logo" class="form-label">Company Logo (400x400px)</label>
            <input type="file" name="company_logo" id="company_logo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Post Job</button>
    </form>
</div>
<script src="../assets/js/main.js"></script>
<?php $pageTitle = "Job Analytics"; include '../templates/header.php'; ?>
</body>
</html>
