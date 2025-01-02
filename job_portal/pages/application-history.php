<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetching application history for the job seeker
$history_query = "
    SELECT a.*, j.title AS job_title, j.company_name, j.location, j.job_type
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.user_id = $user_id
    ORDER BY a.created_at DESC
";
$history_result = $conn->query($history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $pageTitle = "Job AppHistory"; include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h2>Application History</h2>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Job Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history_result && $history_result->num_rows > 0): ?>
                    <?php while ($row = $history_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo htmlspecialchars($row['job_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No applications found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
