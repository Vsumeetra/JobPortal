<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';

// Ensure the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle Job Posting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $company_name = $_POST['company_name'];
    $description = $_POST['description']; // Rich text content
    $job_type = $_POST['job_type'];
    $location = $_POST['location'];
    $salary_min = intval($_POST['salary_min']);
    $salary_max = intval($_POST['salary_max']);
    $application_deadline = $_POST['application_deadline'];
    $user_id = $_SESSION['user_id'];

    // Validate salary
    if ($salary_min > $salary_max) {
        echo "<div class='alert alert-danger'>Minimum salary cannot be greater than maximum salary.</div>";
        exit();
    }

    // Handle logo upload
    $logo_name = $_FILES['company_logo']['name'];
    $logo_tmp = $_FILES['company_logo']['tmp_name'];
    $logo_path = '../assets/uploads/logos/' . $logo_name;

    // Restrict file size to 2MB and validate MIME type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($logo_tmp);

    if ($_FILES['company_logo']['size'] > 2097152 || !in_array($file_type, $allowed_types)) {
        echo "<div class='alert alert-danger'>Invalid file. Only images (JPEG, PNG, GIF) under 2 MB are allowed.</div>";
        exit();
    }

    if (move_uploaded_file($logo_tmp, $logo_path)) {
        $sql = "INSERT INTO jobs (title, company_name, description, job_type, location, salary_min, salary_max, application_deadline, company_logo, user_id) 
                VALUES ('$title', '$company_name', '$description', '$job_type', '$location', '$salary_min', '$salary_max', '$application_deadline', '$logo_path', '$user_id')";

        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>Job listing added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Failed to upload logo.</div>";
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

// Add pagination
$limit = 10; // Jobs per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filter_query .= " LIMIT $limit OFFSET $offset";

$jobs_result = $conn->query($filter_query);

// Fetch total count for pagination
$total_jobs = $conn->query("SELECT COUNT(*) AS total FROM jobs")->fetch_assoc()['total'];
$total_pages = ceil($total_jobs / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#description',
            height: 300
        });
    </script>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const salaryMin = parseInt(document.getElementById('salary_min').value);
            const salaryMax = parseInt(document.getElementById('salary_max').value);

            if (salaryMin > salaryMax) {
                alert('Minimum salary cannot be greater than maximum salary.');
                e.preventDefault();
            }
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
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Job Description</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="job_type" class="form-label">Job Type</label>
                <select name="job_type" id="job_type" class="form-select" required>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="salary_min" class="form-label">Salary Min</label>
                <input type="number" name="salary_min" id="salary_min" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label for="salary_max" class="form-label">Salary Max</label>
                <input type="number" name="salary_max" id="salary_max" class="form-control">
            </div>
        </div>
        <div class="mb-3">
            <label for="application_deadline" class="form-label">Application Deadline</label>
            <input type="date" name="application_deadline" id="application_deadline" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="company_logo" class="form-label">Company Logo (Max: 2MB)</label>
            <input type="file" name="company_logo" id="company_logo" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Post Job</button>
    </form>
</div>

<div class="container mt-5">
    <h2>Job Listings</h2>
    <?php if ($jobs_result->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($job = $jobs_result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <h5><?php echo $job['title']; ?></h5>
                    <p><?php echo $job['description']; ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No jobs found.</p>
    <?php endif; ?>

    <!-- for multiple page support -->
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="../assets/js/main.js"></script>
<?php include '../templates/footer.php'; ?>
</body>
</html>
