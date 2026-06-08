<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$success = $error = '';

// ── Handle Create Assignment ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_assignment'])) {
    $cu_id  = intval($_POST['courseUnit_id']);
    $title  = mysqli_real_escape_string($conn, trim($_POST['assignment_title']));
    $desc   = mysqli_real_escape_string($conn, trim($_POST['assignment_description']));
    $due    = !empty($_POST['due_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['due_date']) . "'" : 'NULL';
    $max    = floatval($_POST['max_score'] ?? 100);

    // Verify this course unit belongs to this teacher
    $chk = mysqli_query($conn, "SELECT courseUnit_id FROM courseunit WHERE courseUnit_id=$cu_id AND teacher_id=$tid");
    if (mysqli_num_rows($chk) === 0) {
        $error = 'Invalid course unit selected.';
    } elseif (empty($title)) {
        $error = 'Assignment title is required.';
    } else {
        $ins = mysqli_query($conn,
            "INSERT INTO assignment (courseUnit_id, assignment_title, assignment_description, due_date, max_score)
             VALUES ($cu_id, '$title', '$desc', $due, $max)");
        if ($ins) {
            $success = 'Assignment created successfully.';
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}

// ── Handle Delete Assignment ────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Only delete if it belongs to this teacher's course unit
    $del = mysqli_query($conn,
        "DELETE a FROM assignment a
         JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
         WHERE a.assignment_id = $del_id AND cu.teacher_id = $tid");
    if ($del && mysqli_affected_rows($conn) > 0) {
        $success = 'Assignment deleted.';
    } else {
        $error = 'Could not delete assignment.';
    }
}

// ── Fetch teacher's course units for dropdown ───────────────────────────────
$units = mysqli_query($conn,
    "SELECT cu.courseUnit_id, cu.courseUnit_name, cu.courseUnit_code
     FROM courseunit cu WHERE cu.teacher_id = $tid ORDER BY cu.courseUnit_name");

// ── Fetch assignments ───────────────────────────────────────────────────────
$filter_cu = isset($_GET['cu']) && is_numeric($_GET['cu']) ? intval($_GET['cu']) : 0;
$where_cu  = $filter_cu ? "AND a.courseUnit_id = $filter_cu" : '';

$assignments = mysqli_query($conn,
    "SELECT a.assignment_id, a.assignment_title, a.assignment_description,
            a.due_date, a.max_score, a.created_at,
            cu.courseUnit_name, cu.courseUnit_code,
            (SELECT COUNT(*) FROM submission s WHERE s.assignment_id=a.assignment_id) AS sub_count,
            (SELECT COUNT(*) FROM submission s WHERE s.assignment_id=a.assignment_id AND s.submission_status='pending') AS pending_cnt
     FROM assignment a
     JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
     WHERE cu.teacher_id = $tid $where_cu
     ORDER BY a.created_at DESC");
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="flex items-center justify-between mb-xl">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">Assignments</h1>
        <p class="text-on-surface-variant text-sm mt-0.5">Create and manage assignments for your course units</p>
    </div>
    <button onclick="document.getElementById('createModal').classList.remove('hidden')"
            class="flex items-center gap-sm bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity shadow-sm">
        <span class="material-symbols-outlined text-lg">add</span> New Assignment
    </button>
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

<!-- Filter by course unit -->
<div class="flex flex-wrap gap-sm mb-lg">
    <a href="assignment.php" class="px-md py-xs rounded-full text-sm font-medium <?php echo !$filter_cu ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">All</a>
    <?php
    mysqli_data_seek($units, 0);
    while ($u = mysqli_fetch_assoc($units)):
    ?>
    <a href="assignment.php?cu=<?php echo $u['courseUnit_id']; ?>" class="px-md py-xs rounded-full text-sm font-medium <?php echo $filter_cu == $u['courseUnit_id'] ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">
        <?php echo htmlspecialchars($u['courseUnit_code']); ?>
    </a>
    <?php endwhile; ?>
</div>

<!-- Assignments List -->
<?php if (mysqli_num_rows($assignments) > 0): ?>
<div class="space-y-md">
    <?php while ($a = mysqli_fetch_assoc($assignments)):
        $due_str = $a['due_date'] ? date('M j, Y · g:i A', strtotime($a['due_date'])) : 'No deadline';
        $is_overdue = $a['due_date'] && strtotime($a['due_date']) < time();
        $is_soon    = $a['due_date'] && !$is_overdue && strtotime($a['due_date']) < strtotime('+3 days');
    ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg hover:shadow-md transition-shadow">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-md">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-sm flex-wrap">
                    <h3 class="font-semibold text-on-surface"><?php echo htmlspecialchars($a['assignment_title']); ?></h3>
                    <span class="text-xs bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full font-mono"><?php echo htmlspecialchars($a['courseUnit_code']); ?></span>
                    <?php if ($is_overdue): ?>
                    <span class="text-[10px] bg-error-container text-on-error-container px-2 py-0.5 rounded-full font-bold uppercase">Overdue</span>
                    <?php elseif ($is_soon): ?>
                    <span class="text-[10px] bg-tertiary-fixed text-on-tertiary-fixed px-2 py-0.5 rounded-full font-bold uppercase">Due Soon</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-on-surface-variant mt-1"><?php echo htmlspecialchars($a['courseUnit_name']); ?></p>
                <?php if ($a['assignment_description']): ?>
                <p class="text-sm text-on-surface-variant mt-sm line-clamp-2"><?php echo htmlspecialchars($a['assignment_description']); ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap gap-lg mt-md text-xs text-on-surface-variant">
                    <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">schedule</span><?php echo $due_str; ?></span>
                    <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">star</span>Max: <?php echo $a['max_score']; ?> pts</span>
                    <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">assignment_turned_in</span><?php echo $a['sub_count']; ?> submitted</span>
                    <?php if ($a['pending_cnt'] > 0): ?>
                    <span class="flex items-center gap-xs text-tertiary font-medium"><span class="material-symbols-outlined text-sm">pending_actions</span><?php echo $a['pending_cnt']; ?> pending</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-sm flex-shrink-0">
                <a href="view_submission.php?assignment_id=<?php echo $a['assignment_id']; ?>" class="flex items-center gap-xs bg-primary-container/20 text-primary px-md py-xs rounded-lg text-sm font-medium hover:bg-primary-container/30 transition-colors">
                    <span class="material-symbols-outlined text-sm">grading</span> Grade
                </a>
                <a href="assignment.php?delete=<?php echo $a['assignment_id']; ?>" onclick="return confirm('Delete this assignment? All submissions will also be deleted.')"
                   class="flex items-center gap-xs bg-error-container/30 text-error px-md py-xs rounded-lg text-sm font-medium hover:bg-error-container/50 transition-colors">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center py-2xl text-on-surface-variant">
    <span class="material-symbols-outlined text-5xl mb-md opacity-40">assignment</span>
    <p class="text-sm font-medium">No assignments yet</p>
    <button onclick="document.getElementById('createModal').classList.remove('hidden')"
            class="mt-lg bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
        Create your first assignment
    </button>
</div>
<?php endif; ?>

<!-- Create Assignment Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-md">
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-lg border-b border-outline-variant/20">
            <h2 class="font-semibold text-on-surface text-lg">Create New Assignment</h2>
            <button onclick="document.getElementById('createModal').classList.add('hidden')" class="p-1 rounded-full hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant">close</span>
            </button>
        </div>
        <form method="POST" class="p-lg space-y-md">
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Course Unit <span class="text-error">*</span></label>
                <select name="courseUnit_id" required class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">Select course unit…</option>
                    <?php
                    mysqli_data_seek($units, 0);
                    while ($u = mysqli_fetch_assoc($units)):
                    ?>
                    <option value="<?php echo $u['courseUnit_id']; ?>"><?php echo htmlspecialchars($u['courseUnit_name']); ?> (<?php echo htmlspecialchars($u['courseUnit_code']); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Title <span class="text-error">*</span></label>
                <input type="text" name="assignment_title" required placeholder="e.g. Midterm Project" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Description</label>
                <textarea name="assignment_description" rows="3" placeholder="Instructions for students…" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Due Date</label>
                    <input type="datetime-local" name="due_date" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Max Score</label>
                    <input type="number" name="max_score" value="100" min="1" max="1000" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
            </div>
            <div class="flex gap-md pt-sm">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="flex-1 border border-outline-variant text-on-surface-variant px-lg py-sm rounded-xl text-sm font-medium hover:bg-surface-container transition-colors">Cancel</button>
                <button type="submit" name="create_assignment" class="flex-1 bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">Create Assignment</button>
            </div>
        </form>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
