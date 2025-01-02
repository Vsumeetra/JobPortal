<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header('Location: ../auth/login.php');
    exit();
}

$search_query = "";
$filters = [];

//  SQL query based on search criteria
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search_title = $_GET['title'] ?? '';
    $search_company = $_GET['company_name'] ?? '';
    $search_location = $_GET['location'] ?? '';
    $search_type = $_GET['job_type'] ?? '';

    $search_query = "SELECT * FROM jobs WHERE 1=1";

    if (!empty($search_title)) {
        $filters[] = "title LIKE '%$search_title%'";
    }
    if (!empty($search_company)) {
        $filters[] = "company_name LIKE '%$search_company%'";
    }
    if (!empty($search_location)) {
        $filters[] = "location LIKE '%$search_location%'";
    }
    if (!empty($search_type)) {
        $filters[] = "job_type = '$search_type'";
    }

    if (!empty($filters)) {
        $search_query .= " AND " . implode(" AND ", $filters);
    }

    $result = $conn->query($search_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php $pageTitle = "Job Search"; include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h2>Search for Jobs</h2>
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Job Title" value="<?php echo $_GET['title'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <input type="text" name="company_name" class="form-control" placeholder="Company Name" value="<?php echo $_GET['company_name'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <input type="text" name="location" class="form-control" placeholder="Location" value="<?php echo $_GET['location'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <select name="job_type" class="form-select">
                    <option value="">Job Type</option>
                    <option value="Full Time" <?php if (($_GET['job_type'] ?? '') === 'Full Time') echo 'selected'; ?>>Full Time</option>
                    <option value="Part Time" <?php if (($_GET['job_type'] ?? '') === 'Part Time') echo 'selected'; ?>>Part Time</option>
                    <option value="Contract" <?php if (($_GET['job_type'] ?? '') === 'Contract') echo 'selected'; ?>>Contract</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>

        <h3>Job Results</h3>
        <?php if ($_SERVER['REQUEST_METHOD'] == 'GET' && $result && $result->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="job-details.php?id=<?php echo $row['id']; ?>" class="list-group-item list-group-item-action">
                        <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p><?php echo htmlspecialchars($row['company_name']); ?> | <?php echo htmlspecialchars($row['location']); ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($row['job_type']); ?></p>
                        <p><strong>Salary:</strong> $<?php echo htmlspecialchars($row['salary_min']); ?> - $<?php echo htmlspecialchars($row['salary_max']); ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No jobs found.</p>
        <?php endif; ?>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>
