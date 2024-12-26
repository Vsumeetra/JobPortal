<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetching analytics data for employer's job postings
$analytics_query = "
    SELECT j.title, 
           COUNT(DISTINCT ja.id) AS views, 
           COUNT(DISTINCT a.id) AS applications
    FROM jobs j
    LEFT JOIN job_analytics ja ON j.id = ja.job_id
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.user_id = $user_id
    GROUP BY j.id
";
$analytics_result = $conn->query($analytics_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $pageTitle = "Job Analytics"; include '../templates/header.php'; ?>

    <div class="container mt-5">
        <h2>Job Analytics Dashboard</h2>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Views</th>
                    <th>Applications Received</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($analytics_result && $analytics_result->num_rows > 0): ?>
                    <?php while ($row = $analytics_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo $row['views']; ?></td>
                            <td><?php echo $row['applications']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No data available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
