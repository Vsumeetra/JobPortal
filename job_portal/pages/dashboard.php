<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$jobs_query = "SELECT * FROM jobs WHERE user_id = $user_id";
$jobs_result = $conn->query($jobs_query);

// Fetching applications with filters
$filter_status = $_GET['status'] ?? '';
$applications_query = "SELECT a.*, j.title AS job_title, u.name AS applicant_name 
                       FROM applications a
                       JOIN jobs j ON a.job_id = j.id
                       JOIN users u ON a.user_id = u.id
                       WHERE j.user_id = $user_id";

if (!empty($filter_status)) {
    $applications_query .= " AND a.status = '$filter_status'";
}

$applications_query .= " ORDER BY a.created_at DESC";
$applications_result = $conn->query($applications_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php $pageTitle = "Job Analytics"; include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h2>Application Tracking Dashboard</h2>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Filter by Status</option>
                    <option value="New" <?php if ($filter_status === 'New') echo 'selected'; ?>>New</option>
                    <option value="Reviewed" <?php if ($filter_status === 'Reviewed') echo 'selected'; ?>>Reviewed</option>
                    <option value="Interview Scheduled" <?php if ($filter_status === 'Interview Scheduled') echo 'selected'; ?>>Interview Scheduled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <h3>Job Applications</h3>
        <?php if ($applications_result && $applications_result->num_rows > 0): ?>
            <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($application = $applications_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($application['applicant_name']); ?></td>
                            <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($application['status']); ?></td>
                            <td>
                                <form method="POST" action="update-status.php" class="d-inline">
                                    <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                    <select name="status" class="form-select form-select-sm d-inline w-auto">
                                        <option value="New" <?php if ($application['status'] === 'New') echo 'selected'; ?>>New</option>
                                        <option value="Reviewed" <?php if ($application['status'] === 'Reviewed') echo 'selected'; ?>>Reviewed</option>
                                        <option value="Interview Scheduled" <?php if ($application['status'] === 'Interview Scheduled') echo 'selected'; ?>>Interview Scheduled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p>No applications found.</p>
        <?php endif; ?>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
