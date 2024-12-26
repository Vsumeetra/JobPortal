<?php
session_start();
include './config/db.php';

// Fetching recent jobs for display
$recent_jobs_query = "SELECT title, company_name, location, job_type, salary_min, salary_max FROM jobs ORDER BY created_at DESC LIMIT 5";
$recent_jobs_result = $conn->query($recent_jobs_query);

$user_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">Job Portal</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if ($user_logged_in): ?>
                            <?php if ($user_role === 'job_seeker'): ?>
                                <li class="nav-item"><a class="nav-link" href="./pages/job-search.php">Search Jobs</a></li>
                                <li class="nav-item"><a class="nav-link" href="./pages/application-history.php">My Applications</a></li>
                            <?php elseif ($user_role === 'employer'): ?>
                                <li class="nav-item"><a class="nav-link" href="./pages/job-listing.php">Post a Job</a></li>
                                <li class="nav-item"><a class="nav-link" href="./pages/dashboard.php">Manage Applications</a></li>
                                <li class="nav-item"><a class="nav-link" href="./pages/analytics.php">Analytics</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="./auth/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="./auth/login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="./auth/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content Section -->
    <main class="container my-5">
        <div class="text-center">
            <h1 class="display-4">Welcome to Job Portal</h1>
            <p class="lead">Connecting job seekers and employers for better opportunities.</p>
            <?php if (!$user_logged_in): ?>
                <a href="./auth/register.php" class="btn btn-primary btn-lg mt-3">Get Started</a>
            <?php endif; ?>
        </div>

        <div class="row mt-5">
            <!-- Job Seekers Section -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title">For Job Seekers</h3>
                        <p class="card-text">Create your profile, search for jobs, and apply for positions with ease.</p>
                        <a href="./pages/job-search.php" class="btn btn-success">Search Jobs</a>
                    </div>
                </div>
            </div>
            <!-- Employers Section -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title">For Employers</h3>
                        <p class="card-text">Post job listings, manage applications, and find the perfect candidates.</p>
                        <a href="./pages/job-listing.php" class="btn btn-primary">Post a Job</a>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mt-5">
            <div class="col-md-4">
                <h4>Job Search</h4>
                <p>Find the perfect job by searching through our extensive database of opportunities.</p>
            </div>
            <div class="col-md-4">
                <h4>Application Tracking</h4>
                <p>Employers can easily manage and track job applications from candidates.</p>
            </div>
            <div class="col-md-4">
                <h4>Job Analytics</h4>
                <p>Employers can view key metrics to understand the performance of their job postings.</p>
            </div>
        </div>

        <!-- Recent Job Postings -->
        <div class="mt-5">
            <h3 class="mb-4">Recent Job Postings</h3>
            <?php if ($recent_jobs_result && $recent_jobs_result->num_rows > 0): ?>
                <div class="list-group">
                    <?php while ($job = $recent_jobs_result->fetch_assoc()): ?>
                        <a href="./pages/job-details.php?id=<?php echo $job['id']; ?>" class="list-group-item list-group-item-action">
                            <h5 class="mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <p class="mb-1"><?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['location']); ?></p>
                            <small>Type: <?php echo htmlspecialchars($job['job_type']); ?> | Salary: $<?php echo htmlspecialchars($job['salary_min']); ?> - $<?php echo htmlspecialchars($job['salary_max']); ?></small>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No recent job postings available.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer Section -->
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; <?php echo date('Y'); ?> Job Portal. All Rights Reserved.</p>
        <p>
            <a href="#" class="text-white text-decoration-none me-2">Terms</a>
            <a href="#" class="text-white text-decoration-none me-2">Privacy</a>
            <a href="#" class="text-white text-decoration-none">Contact</a>
        </p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
