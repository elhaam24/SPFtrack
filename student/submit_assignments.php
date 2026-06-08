<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);
$success_msg = '';
$error_msg   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $courseUnit_id = intval($_POST['courseUnit_id']);

    // Check not already submitted
    $chk = mysqli_query($conn, "SELECT submission_id FROM submission WHERE assignment_id=$assignment_id AND student_id=$student_id");
    if (mysqli_num_rows($chk) > 0) {
        $error_msg = "You have already submitted this assignment.";
    } elseif (empty($_FILES['submission_file']['name'])) {
        $error_msg = "Please select a file to upload.";
    } else {
        $upload_dir = 'uploads/submissions/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext      = strtolower(pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION));
        $allowed  = ['pdf','doc','docx','zip','txt','jpg','jpeg','png'];
        if (!in_array($ext, $allowed)) {
            $error_msg = "File type not allowed. Allowed: " . implode(', ', $allowed);
        } elseif ($_FILES['submission_file']['size'] > 10 * 1024 * 1024) {
            $error_msg = "File too large. Maximum 10 MB.";
        } else {
            $filename = "submission_{$student_id}_{$assignment_id}_" . time() . ".$ext";
            $filepath = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $filepath)) {
                $filepath_db = mysqli_real_escape_string($conn, $filepath);
                mysqli_query($conn,
                    "INSERT INTO submission (assignment_id, student_id, submission_file, submission_status, courseUnit_id)
                     VALUES ($assignment_id, $student_id, '$filepath_db', 'submitted', $courseUnit_id)");
                $success_msg = "Assignment submitted successfully!";
            } else {
                $error_msg = "Failed to upload file. Please try again.";
            }
        }
    }
}

// Pre-select assignment from URL
$preselect_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

// Get assignments not yet submitted
$assign_q = mysqli_query($conn,
    "SELECT a.*, cu.courseUnit_name, cu.courseUnit_id
     FROM assignment a
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE cu.course_id = $course_id
       AND a.assignment_id NOT IN (
           SELECT assignment_id FROM submission WHERE student_id = $student_id
       )
     ORDER BY a.due_date ASC");
$pending_assignments = [];
while ($row = mysqli_fetch_assoc($assign_q)) $pending_assignments[] = $row;
?>

<main class="max-w-2xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Upload Work</p>
        <h2 class="font-h1 text-h1 text-on-surface">Submit Assignment</h2>
        <p class="font-body-md text-on-surface-variant">Upload your completed assignment file.</p>
    </section>

    <?php if ($success_msg): ?>
    <div class="bg-primary-container/20 border border-primary/30 text-on-primary-container rounded-xl p-md flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">check_circle</span>
        <p class="font-body-md"><?php echo htmlspecialchars($success_msg); ?></p>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="bg-error-container border border-error/30 text-on-error-container rounded-xl p-md flex items-center gap-3">
        <span class="material-symbols-outlined text-error">error</span>
        <p class="font-body-md"><?php echo htmlspecialchars($error_msg); ?></p>
    </div>
    <?php endif; ?>

    <?php if (empty($pending_assignments)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3 text-primary">task_alt</span>
        <p class="font-body-lg font-semibold text-on-surface">All caught up!</p>
        <p class="font-body-md mt-1">You have no pending assignments to submit.</p>
        <a href="view_assignments.php" class="inline-block mt-4 px-6 py-2 bg-primary text-on-primary rounded-xl font-button hover:bg-on-primary-fixed-variant transition-colors">
            View All Assignments
        </a>
    </div>
    <?php else: ?>
    <form method="POST" enctype="multipart/form-data" class="bg-surface-container-lowest rounded-xl p-lg shadow-level-1 border border-surface-container space-y-lg">

        <!-- Assignment selector -->
        <div class="space-y-sm">
            <label class="font-label-caps text-label-caps text-on-surface-variant block">SELECT ASSIGNMENT</label>
            <select name="assignment_id" id="assignment_select" required
                class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md text-on-surface">
                <option value="">-- Choose an assignment --</option>
                <?php foreach ($pending_assignments as $a): ?>
                <option value="<?php echo $a['assignment_id']; ?>"
                        data-unit="<?php echo $a['courseUnit_id']; ?>"
                        data-desc="<?php echo htmlspecialchars($a['assignment_description'] ?? ''); ?>"
                        data-due="<?php echo $a['due_date'] ? date('M d, Y', strtotime($a['due_date'])) : 'No deadline'; ?>"
                        data-max="<?php echo $a['max_score']; ?>"
                        <?php echo $preselect_id === intval($a['assignment_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($a['assignment_title']); ?> — <?php echo htmlspecialchars($a['courseUnit_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="courseUnit_id" id="courseUnit_id_input" value="">
        </div>

        <!-- Assignment details (shown after selection) -->
        <div id="assignment_details" class="hidden bg-surface-container-low rounded-xl p-md space-y-2">
            <p class="font-body-sm text-on-surface-variant" id="detail_desc"></p>
            <div class="flex gap-4 flex-wrap">
                <span class="flex items-center gap-1 font-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                    Due: <span id="detail_due" class="font-semibold text-on-surface ml-1"></span>
                </span>
                <span class="flex items-center gap-1 font-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px]">score</span>
                    Max score: <span id="detail_max" class="font-semibold text-on-surface ml-1"></span>
                </span>
            </div>
        </div>

        <!-- File upload -->
        <div class="space-y-sm">
            <label class="font-label-caps text-label-caps text-on-surface-variant block">UPLOAD FILE</label>
            <div class="border-2 border-dashed border-outline-variant rounded-xl p-lg text-center hover:border-primary transition-colors cursor-pointer" onclick="document.getElementById('file_input').click()">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant block mb-2">upload_file</span>
                <p class="font-body-md text-on-surface-variant">Click to browse or drag & drop</p>
                <p class="font-body-sm text-on-surface-variant mt-1">PDF, DOC, DOCX, ZIP, TXT, JPG, PNG — max 10 MB</p>
                <p id="file_name_display" class="font-body-sm text-primary mt-2 font-semibold hidden"></p>
            </div>
            <input type="file" name="submission_file" id="file_input" class="hidden" accept=".pdf,.doc,.docx,.zip,.txt,.jpg,.jpeg,.png" required>
        </div>

        <button type="submit" name="submit_assignment"
            class="w-full py-4 bg-primary text-on-primary rounded-xl font-button text-button hover:bg-on-primary-fixed-variant transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">upload</span>
            Submit Assignment
        </button>
    </form>
    <?php endif; ?>

    <!-- Already submitted -->
    <div class="space-y-md">
        <h3 class="font-h2 text-h2 text-on-surface">Already Submitted</h3>
        <?php
        $done_q = mysqli_query($conn,
            "SELECT sub.*, a.assignment_title, cu.courseUnit_name
             FROM submission sub
             LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
             LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
             WHERE sub.student_id = $student_id
             ORDER BY sub.submission_date DESC");
        $done_list = [];
        while ($row = mysqli_fetch_assoc($done_q)) $done_list[] = $row;
        ?>
        <?php if (empty($done_list)): ?>
        <p class="text-on-surface-variant font-body-sm">No submissions yet.</p>
        <?php else: ?>
        <div class="space-y-sm">
            <?php foreach ($done_list as $sub):
                $sc_map = ['graded'=>'text-primary bg-primary-container/20','pending'=>'text-tertiary bg-tertiary-container/20','submitted'=>'text-secondary bg-secondary-container/30'];
                $sc = $sc_map[$sub['submission_status']] ?? 'text-on-surface-variant bg-surface-container';
            ?>
            <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary flex-shrink-0">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-body-md font-semibold truncate"><?php echo htmlspecialchars($sub['assignment_title'] ?? ''); ?></p>
                    <p class="font-body-sm text-on-surface-variant"><?php echo htmlspecialchars($sub['courseUnit_name'] ?? ''); ?> &bull; <?php echo date('M d, Y', strtotime($sub['submission_date'])); ?></p>
                </div>
                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                    <span class="px-2 py-0.5 rounded-full font-label-caps text-[10px] <?php echo $sc; ?> capitalize"><?php echo $sub['submission_status']; ?></span>
                    <?php if ($sub['score'] !== null): ?>
                    <span class="font-body-sm text-primary font-semibold"><?php echo $sub['score']; ?> pts</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
const select = document.getElementById('assignment_select');
const details = document.getElementById('assignment_details');
const unitInput = document.getElementById('courseUnit_id_input');
const fileInput = document.getElementById('file_input');
const fileDisplay = document.getElementById('file_name_display');

function updateDetails() {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) { details.classList.add('hidden'); unitInput.value = ''; return; }
    document.getElementById('detail_desc').textContent = opt.dataset.desc || 'No description.';
    document.getElementById('detail_due').textContent  = opt.dataset.due;
    document.getElementById('detail_max').textContent  = opt.dataset.max + ' pts';
    unitInput.value = opt.dataset.unit;
    details.classList.remove('hidden');
}

select.addEventListener('change', updateDetails);
fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
        fileDisplay.textContent = '✓ ' + fileInput.files[0].name;
        fileDisplay.classList.remove('hidden');
    }
});

// Pre-select if URL param
if (select.value) updateDetails();
</script>

<?php require_once 'includes/footer.php'; ?>
