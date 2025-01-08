<?php
session_start();
include '../config/db.php';

// Ensuring the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

// Handling Job Posting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $job_type = isset($_POST['job_type']) ? trim($_POST['job_type']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $salary_min = isset($_POST['salary_min']) ? intval($_POST['salary_min']) : 0;
    $salary_max = isset($_POST['salary_max']) ? intval($_POST['salary_max']) : 0;
    $application_deadline = isset($_POST['application_deadline']) ? trim($_POST['application_deadline']) : '';
    $user_id = $_SESSION['user_id'];

    // Validating job title
    if (strlen($title) < 3 || strlen($title) > 100) {
        echo "Job title must be between 3 and 100 characters.";
        exit();
    }

    // upload logos
    $logo_name = $_FILES['company_logo']['name'] ?? '';
    $logo_tmp = $_FILES['company_logo']['tmp_name'] ?? '';
    $logo_size = $_FILES['company_logo']['size'] ?? 0;
    $logo_path = '../assets/uploads/logos/' . $logo_name;

    // Restrict logo size to 2MB
    if ($logo_size > 2097152) {
        echo "Company logo size exceeds 2 MB.";
        exit();
    }

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

// Handle Search and Filters
$filter_query = "SELECT * FROM jobs WHERE 1=1";

if (!empty($_GET['job_type'])) {
    $filter_query .= " AND job_type = '" . $_GET['job_type'] . "'";
}
if (!empty($_GET['location'])) {
    $filter_query .= " AND location LIKE '%" . $_GET['location'] . "%'";
}
if (!empty($_GET['salary_min'])) {
    $filter_query .= " AND salary_min >= " . intval($_GET['salary_min']);
}
if (!empty($_GET['salary_max'])) {
    $filter_query .= " AND salary_max <= " . intval($_GET['salary_max']);
}

$jobs_result = $conn->query($filter_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Included Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description',
            plugins: 'link image code',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | code'
        });
    </script>
</head>
<body>
<?php $pageTitle = "Job Listing"; include '../templates/header.php'; ?>

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
            <div class="col-md-12 mb-3">
                <label for="description" class="form-label">Job Description</label>
                <textarea name="description" id="description" class="form-control" rows="6" required></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="job_type" class="form-label">Job Type</label>
                <select name="job_type" id="job_type" class="form-select">
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="salary_min" class="form-label">Salary Min</label>
                <input type="number" name="salary_min" id="salary_min" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label for="salary_max" class="form-label">Salary Max</label>
                <input type="number" name="salary_max" id="salary_max" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label for="application_deadline" class="form-label">Application Deadline</label>
                <input type="date" name="application_deadline" id="application_deadline" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
                <label for="company_logo" class="form-label">Company Logo</label>
                <input type="file" name="company_logo" id="company_logo" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Post Job</button>
    </form>
</div>

<div class="container mt-5">
    <h2>Search & Filter Jobs</h2>
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <label for="job_type" class="form-label">Job Type</label>
            <select name="job_type" id="job_type" class="form-select">
                <option value="">All</option>
                <option value="Full Time">Full Time</option>
                <option value="Part Time">Part Time</option>
                <option value="Contract">Contract</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="salary_min" class="form-label">Salary Min</label>
            <input type="number" name="salary_min" id="salary_min" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="salary_max" class="form-label">Salary Max</label>
            <input type="number" name="salary_max" id="salary_max" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 mt-4">Search</button>
        </div>
    </form>
</div>

<div class="container mt-5">
    <h2>Job Listings</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Company</th>
                <th>Type</th>
                <th>Location</th>
                <th>Salary</th>
                <th>Deadline</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($job = $jobs_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $job['title']; ?></td>
                <td><?php echo $job['company_name']; ?></td>
                <td><?php echo $job['job_type']; ?></td>
                <td><?php echo $job['location']; ?></td>
                <td><?php echo $job['salary_min'] . ' - ' . $job['salary_max']; ?></td>
                <td><?php echo $job['application_deadline']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="../assets/js/main.js"></script>
<?php include '../templates/footer.php'; ?>
</body>
</html>
