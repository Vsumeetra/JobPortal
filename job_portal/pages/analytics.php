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
        <div class="row">
            <div class="col-12 text-center">
                <h2>Job Analytics Dashboard</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <?php if ($analytics_result && $analytics_result->num_rows > 0): ?>
                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Job Title</th>
                                    <th>Views</th>
                                    <th>Applications Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $analytics_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['views']); ?></td>
                                        <td><?php echo htmlspecialchars($row['applications']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
