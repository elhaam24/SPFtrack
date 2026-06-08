<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: view_submission.php');
    exit();
}
$sub_id = intval($_GET['id']);

// Verify this submission belongs to this teacher's assignment
$sub = mysqli_query($conn,
    "SELECT s.*, st.student_name, st.student_email,
            a.assignment_title, a.max_score, a.due_date, a.assignment_description,
            cu.courseUnit_name, cu.courseUnit_code
     FROM submission s
     JOIN assignment a  ON a.assignment_id  = s.assignment_id
     JOIN courseunit cu ON cu.courseUnit_id  = a.courseUnit_id
     JOIN student st    ON st.student_id     = s.student_id
     WHERE s.submission_id = $sub_id AND cu.teacher_id = $tid");

if (mysqli_num_rows($sub) === 0) {
    header('location: view_submission.php');
    exit();
}
$sub = mysqli_fetch_assoc($sub);

$success = $error = '';

// ── Handle Grade Submission ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $score    = floatval($_POST['score']);
    $comments = mysqli_real_escape_string($conn, trim($_POST['comments']));
    $max      = floatval($sub['max_score']);

    if ($score < 0 || $score > $max) {
        $error = "Score must be between 0 and $max.";
    } else {
        $upd = mysqli_query($conn,
            "UPDATE submission SET score=$score, comments='$comments', submission_status='graded'
             WHERE submission_id=$sub_id");
        if ($upd) {
            $success = 'Submission graded successfully.';
            // Refresh data
            $sub['score']             = $score;
            $sub['comments']          = $comments;
            $sub['submission_status'] = 'graded';
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="mb-lg">
    <a href="view_submission.php" class="flex items-center gap-xs text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <span class="material-symbols-outlined text-base">arrow_back</span> Back to Submissions
    </a>
    <h1 class="text-2xl font-bold text-on-surface">Grade Submission</h1>
</div>

<?php if ($success): ?>
<div class="mb-lg bg-primary-container/20 border border-primary/20 text-primary px-lg py-md rounded-xl text-sm flex items-center gap-sm">
    <span class="material-symbols-outlined">check_circle</span> <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="mb-lg bg-error-container border border-error/20 text-on-error-container px-lg py-md rounded-xl text-sm flex items-center gap-sm">
    <span class="material-symbols-outlined">error</span> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">

    <!-- Submission Details -->
    <div class="lg:col-span-7 space-y-gutter">

        <!-- Assignment Info -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-md flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">assignment</span>
                Assignment Details
            </h2>
            <div class="space-y-sm text-sm">
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Title</span>
                    <span class="font-medium text-on-surface"><?php echo htmlspecialchars($sub['assignment_title']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Course Unit</span>
                    <span class="font-medium text-on-surface"><?php echo htmlspecialchars($sub['courseUnit_name']); ?> (<?php echo htmlspecialchars($sub['courseUnit_code']); ?>)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Max Score</span>
                    <span class="font-medium text-on-surface"><?php echo $sub['max_score']; ?> pts</span>
                </div>
                <?php if ($sub['due_date']): ?>
                <div class="flex justify-between">
                    <span class="text-on-surface-variant">Due Date</span>
                    <span class="font-medium text-on-surface"><?php echo date('M j, Y g:i A', strtotime($sub['due_date'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($sub['assignment_description']): ?>
                <div class="pt-sm border-t border-outline-variant/20">
                    <p class="text-on-surface-variant mb-xs">Instructions</p>
                    <p class="text-on-surface"><?php echo nl2br(htmlspecialchars($sub['assignment_description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Submitted File -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-md flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">attach_file</span>
                Submitted File
            </h2>
            <?php if ($sub['submission_file']): ?>
            <div class="flex items-center gap-md p-md bg-surface-container-low rounded-xl border border-outline-variant/20">
                <div class="w-10 h-10 bg-primary-container/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-primary">description</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-on-surface truncate"><?php echo htmlspecialchars(basename($sub['submission_file'])); ?></p>
                    <p class="text-xs text-on-surface-variant">Submitted <?php echo date('M j, Y g:i A', strtotime($sub['submission_date'])); ?></p>
                </div>
                <a href="../<?php echo htmlspecialchars($sub['submission_file']); ?>" target="_blank"
                   class="flex items-center gap-xs bg-primary text-on-primary px-md py-xs rounded-lg text-sm font-medium hover:opacity-90 transition-opacity flex-shrink-0">
                    <span class="material-symbols-outlined text-sm">download</span> Open
                </a>
            </div>
            <?php else: ?>
            <p class="text-sm text-on-surface-variant">No file submitted.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grading Panel -->
    <div class="lg:col-span-5 space-y-gutter">

        <!-- Student Info -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <div class="flex items-center gap-md">
                <div class="w-12 h-12 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-lg flex-shrink-0">
                    <?php echo strtoupper(substr($sub['student_name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="font-semibold text-on-surface"><?php echo htmlspecialchars($sub['student_name']); ?></p>
                    <p class="text-sm text-on-surface-variant"><?php echo htmlspecialchars($sub['student_email']); ?></p>
                </div>
                <?php
                $s = $sub['submission_status'];
                $cls = match($s) {
                    'graded'  => 'bg-primary-container/20 text-primary',
                    'pending' => 'bg-tertiary-fixed/50 text-tertiary',
                    default   => 'bg-surface-container text-on-surface-variant',
                };
                ?>
                <span class="ml-auto <?php echo $cls; ?> text-xs font-semibold px-2 py-0.5 rounded-full capitalize"><?php echo $s; ?></span>
            </div>
        </div>

        <!-- Grade Form -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-lg flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">grading</span>
                Grade This Submission
            </h2>
            <form method="POST" class="space-y-md">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">
                        Score <span class="text-on-surface-variant font-normal">(out of <?php echo $sub['max_score']; ?>)</span>
                    </label>
                    <input type="number" name="score" step="0.5" min="0" max="<?php echo $sub['max_score']; ?>"
                           value="<?php echo $sub['score'] ?? ''; ?>"
                           placeholder="Enter score…"
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Feedback / Comments</label>
                    <textarea name="comments" rows="4" placeholder="Provide feedback to the student…"
                              class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none"><?php echo htmlspecialchars($sub['comments'] ?? ''); ?></textarea>
                </div>
                <button type="submit" name="grade_submission" class="w-full bg-primary text-on-primary py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    <?php echo $sub['submission_status'] === 'graded' ? 'Update Grade' : 'Submit Grade'; ?>
                </button>
            </form>
        </div>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
