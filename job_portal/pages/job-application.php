<?php
session_start();
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

// Fetch job details
$job_query = "SELECT * FROM jobs WHERE id = $job_id";
$job_result = $conn->query($job_query);

if ($job_result->num_rows === 0) {
    echo "Job not found.";
    exit();
}

$job = $job_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = $_POST['cover_letter'] ?? '';
    $linkedin_profile = $_POST['linkedin_profile'] ?? '';

    //$github_profile = $_POST['github_profile'] ?? '';

    $resume_name = $_FILES['resume']['name'];
    $resume_tmp = $_FILES['resume']['tmp_name'];
    $resume_path = '../assets/uploads/resumes/' . $resume_name;

    if ($_FILES['resume']['size'] > 5242880) {
        echo "Resume file size exceeds 5 MB.";
        exit();
    }

    if (move_uploaded_file($resume_tmp, $resume_path)) {
        $sql = "INSERT INTO applications (job_id, user_id, resume, cover_letter, linkedin_profile) 
                VALUES ('$job_id', '$user_id', '$resume_path', '$cover_letter', '$linkedin_profile')";

        if ($conn->query($sql)) {
            // Fetching employer email
            $employer_query = "SELECT u.email AS employer_email, j.title AS job_title 
                               FROM jobs j 
                               JOIN users u ON j.user_id = u.id 
                               WHERE j.id = $job_id";
            $employer_result = $conn->query($employer_query);

            if ($employer_result && $employer_result->num_rows > 0) {
                $employer = $employer_result->fetch_assoc();
                $employer_email = $employer['employer_email'];
                $job_title = $employer['job_title'];

                // Sending notification email to employer
                $subject = "New Application Received: $job_title";
                $message = "Dear Employer,\n\nYou have received a new application for the job '$job_title'.\n\nBest regards,\nJob Portal Team";
                $headers = "From: no-reply@jobportal.com"; //used a non-function mail id.

                if (mail($employer_email, $subject, $message, $headers)) {
                    echo "Application submitted successfully.";
                } else {
                    echo "Error sending email notification.";
                }
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Failed to upload resume.";
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
<?php $pageTitle = "Job  Applications"; include '../templates/header.php'; ?>
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
</body>
</html>
