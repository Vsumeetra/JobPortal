<?php
// Starting session and check user authentication and role
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['job_id'])) {
    echo "Job ID is required.";
    exit();
}

$job_id = intval($_GET['job_id']);
$user_id = $_SESSION['user_id'];

// Fetching job details
$job_query = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$job_query->bind_param("i", $job_id);
$job_query->execute();
$job_result = $job_query->get_result();

if ($job_result->num_rows === 0) {
    echo "Job not found.";
    exit();
}

$job = $job_result->fetch_assoc();
$job_query->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = htmlspecialchars(trim($_POST['cover_letter'] ?? ''));
    $linkedin_profile = htmlspecialchars(trim($_POST['linkedin_profile'] ?? ''));

    // Resume upload handling
    $allowed_file_types = ['pdf', 'doc', 'docx'];
    $resume_name = $_FILES['resume']['name'];
    $resume_tmp = $_FILES['resume']['tmp_name'];
    $resume_size = $_FILES['resume']['size'];
    $resume_ext = strtolower(pathinfo($resume_name, PATHINFO_EXTENSION));
    $resume_path = '../assets/uploads/resumes/' . uniqid() . '.' . $resume_ext;

    if ($resume_size > 5242880) {
        echo "Resume file size exceeds 5 MB.";
        exit();
    }

    if (!in_array($resume_ext, $allowed_file_types)) {
        echo "Invalid file type. Only PDF, DOC, and DOCX are allowed.";
        exit();
    }

    if (move_uploaded_file($resume_tmp, $resume_path)) {
        // Inserting application into database
        $sql = $conn->prepare("INSERT INTO applications (job_id, user_id, resume, cover_letter, linkedin_profile) 
                               VALUES (?, ?, ?, ?, ?)");
        $sql->bind_param("iisss", $job_id, $user_id, $resume_path, $cover_letter, $linkedin_profile);

        if ($sql->execute()) {
            // Fetching employer email for notification
            $employer_query = $conn->prepare("SELECT u.email AS employer_email, j.title AS job_title 
                                              FROM jobs j 
                                              JOIN users u ON j.user_id = u.id 
                                              WHERE j.id = ?");
            $employer_query->bind_param("i", $job_id);
            $employer_query->execute();
            $employer_result = $employer_query->get_result();

            if ($employer_result && $employer_result->num_rows > 0) {
                $employer = $employer_result->fetch_assoc();
                $employer_email = $employer['employer_email'];
                $job_title = $employer['job_title'];

                // Send email notification
                $subject = "New Application Received: $job_title";
                $message = "Dear Employer,\n\nYou have received a new application for the job '$job_title'.\n\nBest regards,\nJob Portal Team";
                $headers = "From: no-reply@jobportal.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8";

                if (mail($employer_email, $subject, $message, $headers)) {
                    echo "<div class='alert alert-success'>Application submitted successfully. Notification sent to employer.</div>";
                } else {
                    echo "<div class='alert alert-warning'>Application submitted successfully, but email notification failed.</div>";
                }
            }
            $employer_query->close();
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
        $sql->close();
    } else {
        echo "<div class='alert alert-danger'>Failed to upload resume.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php $pageTitle = "Job Applications"; include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h2>Apply for: <?php echo htmlspecialchars($job['title']); ?></h2>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company_name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
        <p><strong>Job Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
        <p><strong>Salary:</strong> $<?php echo htmlspecialchars($job['salary_min']); ?> - $<?php echo htmlspecialchars($job['salary_max']); ?></p>

        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="resume" class="form-label">Upload Resume (Max: 5MB)</label>
                <input type="file" name="resume" id="resume" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="cover_letter" class="form-label">Cover Letter (Optional)</label>
                <textarea name="cover_letter" id="cover_letter" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="linkedin_profile" class="form-label">LinkedIn Profile (Optional)</label>
                <input type="url" name="linkedin_profile" id="linkedin_profile" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
    <?php include '../templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
