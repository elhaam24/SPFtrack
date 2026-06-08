<?php
// Quick AJAX/redirect endpoint to mark a submission as graded
session_start();
require_once 'includes/config.php';

if (empty($_SESSION['teacher_login'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$tid    = intval($_SESSION['teacher_id']);
$sub_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
$score  = isset($_GET['score']) ? floatval($_GET['score']) : null;

if ($sub_id) {
    // Verify ownership
    $chk = mysqli_query($conn,
        "SELECT s.submission_id FROM submission s
         JOIN assignment a ON a.assignment_id=s.assignment_id
         JOIN courseunit cu ON cu.courseUnit_id=a.courseUnit_id
         WHERE s.submission_id=$sub_id AND cu.teacher_id=$tid");
    if (mysqli_num_rows($chk) > 0) {
        $score_sql = $score !== null ? "score=$score," : '';
        mysqli_query($conn, "UPDATE submission SET {$score_sql} submission_status='graded' WHERE submission_id=$sub_id");
    }
}

header('location: view_submission.php');
exit();
