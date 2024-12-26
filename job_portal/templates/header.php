<?php
session_start();
$user_role = $_SESSION['role'] ?? null;
$user_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Job Portal'; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="../index.php">Job Portal</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if ($user_logged_in): ?>
                            <?php if ($user_role === 'job_seeker'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../pages/job-search.php">Search Jobs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../pages/application-history.php">My Applications</a>
                                </li>
                            <?php elseif ($user_role === 'employer'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="../pages/job-listing.php">Post a Job</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../pages/dashboard.php">Manage Applications</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../pages/analytics.php">Analytics</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../auth/logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../auth/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../auth/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    </body>
    </html>
