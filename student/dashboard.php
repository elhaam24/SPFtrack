<?php
require_once 'includes/header.php';

// ── Enrollment info ───────────────────────────────────────────────────────────
$enroll_q = mysqli_query($conn,
    "SELECT e.*, sem.semester_name, ay.year_name
     FROM enrollment e
     LEFT JOIN semester sem ON e.semester_id = sem.semester_id
     LEFT JOIN academic_year ay ON e.year_id = ay.year_id
     WHERE e.student_id = $student_id
     ORDER BY e.enrollment_id DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_q);

// ── Course units for this student's course ────────────────────────────────────
$course_id = intval($student['course_id'] ?? 0);
$units_q = mysqli_query($conn,
    "SELECT cu.*, t.teacher_name
     FROM courseunit cu
     LEFT JOIN teacher t ON cu.teacher_id = t.teacher_id
     WHERE cu.course_id = $course_id
     ORDER BY cu.courseUnit_id ASC");
$course_units = [];
while ($row = mysqli_fetch_assoc($units_q)) $course_units[] = $row;
$total_units = count($course_units);

// ── Assignments (pending / upcoming) ─────────────────────────────────────────
$assign_q = mysqli_query($conn,
    "SELECT a.*, cu.courseUnit_name,
            (SELECT sub.submission_id FROM submission sub
             WHERE sub.assignment_id = a.assignment_id AND sub.student_id = $student_id LIMIT 1) AS submitted_id,
            (SELECT sub.submission_status FROM submission sub
             WHERE sub.assignment_id = a.assignment_id AND sub.student_id = $student_id LIMIT 1) AS sub_status,
            (SELECT sub.score FROM submission sub
             WHERE sub.assignment_id = a.assignment_id AND sub.student_id = $student_id LIMIT 1) AS sub_score
     FROM assignment a
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE cu.course_id = $course_id
     ORDER BY a.due_date ASC");
$assignments = [];
while ($row = mysqli_fetch_assoc($assign_q)) $assignments[] = $row;
$total_assignments = count($assignments);
$submitted_count   = 0;
$pending_count     = 0;
foreach ($assignments as $a) {
    if ($a['submitted_id']) $submitted_count++;
    else $pending_count++;
}

// ── Submissions / grades ──────────────────────────────────────────────────────
$sub_q = mysqli_query($conn,
    "SELECT sub.*, a.assignment_title, cu.courseUnit_name
     FROM submission sub
     LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE sub.student_id = $student_id
     ORDER BY sub.submission_date DESC LIMIT 5");
$recent_submissions = [];
while ($row = mysqli_fetch_assoc($sub_q)) $recent_submissions[] = $row;

// ── Average score ─────────────────────────────────────────────────────────────
$avg_q = mysqli_query($conn,
    "SELECT AVG(score) AS avg_score FROM submission
     WHERE student_id = $student_id AND score IS NOT NULL");
$avg_row   = mysqli_fetch_assoc($avg_q);
$avg_score = $avg_row['avg_score'] ? round($avg_row['avg_score'], 1) : null;

// ── Notifications (recent 3) ──────────────────────────────────────────────────
$notif_q = mysqli_query($conn,
    "SELECT * FROM notification WHERE student_id = $student_id
     ORDER BY date_sent DESC LIMIT 3");
$notifications = [];
while ($row = mysqli_fetch_assoc($notif_q)) $notifications[] = $row;

// ── Materials count ───────────────────────────────────────────────────────────
$mat_q = mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM materials m
     JOIN courseunit cu ON m.courseUnit_id = cu.courseUnit_id
     WHERE cu.course_id = $course_id");
$mat_row = mysqli_fetch_assoc($mat_q);
$materials_count = $mat_row['c'] ?? 0;

// ── Progress report data ──────────────────────────────────────────────────────
$prog_q = mysqli_query($conn,
    "SELECT pr.*, cu.courseUnit_name FROM progressreport pr
     LEFT JOIN courseunit cu ON pr.courseUnit_id = cu.courseUnit_id
     WHERE pr.student_id = $student_id");
$progress_reports = [];
while ($row = mysqli_fetch_assoc($prog_q)) $progress_reports[] = $row;
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">

    <!-- ── Welcome Banner ──────────────────────────────────────────────────── -->
    <section class="mb-xl">
        <div class="relative overflow-hidden rounded-xl bg-inverse-surface p-8 text-inverse-on-surface flex flex-col md:flex-row justify-between items-center shadow-level-2">
            <div class="relative z-10 space-y-2 text-center md:text-left">
                <p class="font-label-caps text-label-caps text-primary-fixed uppercase">Student Dashboard</p>
                <h2 class="font-h1 text-h1 text-white">Welcome back, <?php echo $student_name; ?>!</h2>
                <p class="font-body-lg text-body-lg opacity-80 max-w-md">
                    <?php if ($enrollment): ?>
                        <?php echo htmlspecialchars($enrollment['semester_name'] ?? ''); ?> &bull;
                        <?php echo htmlspecialchars($enrollment['year_name'] ?? ''); ?> &bull;
                        <span class="capitalize"><?php echo htmlspecialchars($enrollment['enrollment_status'] ?? ''); ?></span>
                    <?php else: ?>
                        <?php echo htmlspecialchars($student['course_name'] ?? 'No course assigned'); ?>
                    <?php endif; ?>
                </p>
                <div class="flex flex-wrap gap-3 mt-4 justify-center md:justify-start">
                    <a href="view_assignments.php" class="px-5 py-2 bg-primary-container text-on-primary-container font-button text-button rounded-xl hover:brightness-110 transition-all">
                        View Assignments
                    </a>
                    <a href="progressReport.php" class="px-5 py-2 border border-primary-fixed/40 text-primary-fixed font-button text-button rounded-xl hover:bg-white/10 transition-all">
                        My Progress
                    </a>
                </div>
            </div>
            <div class="mt-6 md:mt-0 relative z-10 flex flex-col items-center gap-2">
                <div class="w-24 h-24 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container text-5xl font-bold shadow-level-2">
                    <?php echo strtoupper(substr($student_name, 0, 1)); ?>
                </div>
                <p class="font-label-caps text-label-caps text-primary-fixed"><?php echo htmlspecialchars($student['course_name'] ?? ''); ?></p>
            </div>
            <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-primary/20 to-transparent pointer-events-none"></div>
        </div>
    </section>

    <!-- ── Stats Cards ─────────────────────────────────────────────────────── -->
    <section class="grid grid-cols-2 md:grid-cols-4 gap-md mb-xl">
        <!-- Course Units -->
        <a href="CourseUnits.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">school</span>
            </div>
            <p class="font-h2 text-h2 text-on-surface"><?php echo $total_units; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">COURSE UNITS</p>
        </a>
        <!-- Assignments -->
        <a href="view_assignments.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-tertiary-container/20 flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined">assignment</span>
            </div>
            <p class="font-h2 text-h2 text-on-surface"><?php echo $total_assignments; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">ASSIGNMENTS</p>
        </a>
        <!-- Submitted -->
        <a href="submission_status.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined">task_alt</span>
            </div>
            <p class="font-h2 text-h2 text-on-surface"><?php echo $submitted_count; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">SUBMITTED</p>
        </a>
        <!-- Avg Score -->
        <a href="result.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col gap-2">
            <div class="w-10 h-10 rounded-lg bg-secondary-container/50 flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined">grade</span>
            </div>
            <p class="font-h2 text-h2 text-on-surface"><?php echo $avg_score !== null ? $avg_score : '—'; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">AVG SCORE</p>
        </a>
    </section>

    <!-- ── Main Grid ───────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-lg">

        <!-- ── Active Course Units (left, 8 cols) ──────────────────────────── -->
        <div class="md:col-span-8 space-y-md">
            <div class="flex justify-between items-end">
                <h3 class="font-h2 text-h2 text-on-surface">Active Course Units</h3>
                <a href="CourseUnits.php" class="font-button text-button text-primary hover:underline">View All</a>
            </div>

            <?php if (empty($course_units)): ?>
            <div class="bg-surface-container-lowest rounded-xl p-lg shadow-level-1 border border-surface-container text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 block">school</span>
                No course units found for your course.
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                <?php foreach (array_slice($course_units, 0, 4) as $unit):
                    // Count assignments for this unit
                    $ua_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM assignment WHERE courseUnit_id=" . intval($unit['courseUnit_id']));
                    $ua_row = mysqli_fetch_assoc($ua_q);
                    $unit_assign_count = $ua_row['c'] ?? 0;
                    // Count submitted for this unit
                    $us_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM submission WHERE student_id=$student_id AND assignment_id IN (SELECT assignment_id FROM assignment WHERE courseUnit_id=" . intval($unit['courseUnit_id']) . ")");
                    $us_row = mysqli_fetch_assoc($us_q);
                    $unit_submitted = $us_row['c'] ?? 0;
                    $progress = $unit_assign_count > 0 ? round(($unit_submitted / $unit_assign_count) * 100) : 0;
                ?>
                <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container flex flex-col gap-3 hover:shadow-level-2 transition-all">
                    <div class="flex justify-between items-start">
                        <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                        <span class="px-2 py-1 bg-surface-container text-on-surface-variant font-label-caps text-label-caps rounded-full">
                            <?php echo htmlspecialchars($unit['courseUnit_code']); ?>
                        </span>
                    </div>
                    <div>
                        <h4 class="font-h3 text-h3 text-on-surface leading-tight"><?php echo htmlspecialchars($unit['courseUnit_name']); ?></h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                            <?php echo $unit['teacher_name'] ? htmlspecialchars($unit['teacher_name']) : 'No teacher assigned'; ?>
                        </p>
                    </div>
                    <div class="mt-auto pt-2">
                        <div class="flex justify-between mb-1">
                            <span class="font-label-caps text-label-caps text-on-surface-variant">Submissions</span>
                            <span class="font-label-caps text-label-caps text-primary"><?php echo $unit_submitted; ?>/<?php echo $unit_assign_count; ?></span>
                        </div>
                        <div class="w-full h-2 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="h-full bg-primary-container rounded-full transition-all" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    <a href="view_assignments.php?unit=<?php echo $unit['courseUnit_id']; ?>" class="text-primary font-button text-button hover:underline text-sm">
                        View Assignments →
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Upcoming Assignments (right, 4 cols) ─────────────────────────── -->
        <div class="md:col-span-4 space-y-md">
            <h3 class="font-h2 text-h2 text-on-surface">Upcoming Assignments</h3>
            <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container space-y-3">
                <?php
                $upcoming = array_filter($assignments, fn($a) => !$a['submitted_id'] && $a['due_date']);
                $upcoming = array_slice(array_values($upcoming), 0, 4);
                if (empty($upcoming)):
                ?>
                <p class="text-on-surface-variant font-body-sm text-center py-4">No pending assignments.</p>
                <?php else: foreach ($upcoming as $a):
                    $due = new DateTime($a['due_date']);
                    $now = new DateTime();
                    $diff = $now->diff($due);
                    $is_overdue = $due < $now;
                    $month = $due->format('M');
                    $day   = $due->format('d');
                    $color = $is_overdue ? 'bg-error-container/30 text-error' : 'bg-primary-container/10 text-primary';
                ?>
                <div class="flex gap-3 items-center p-2 rounded-lg hover:bg-surface-container transition-colors">
                    <div class="flex flex-col items-center justify-center <?php echo $color; ?> min-w-[44px] h-12 rounded-lg">
                        <span class="font-label-caps text-[9px]"><?php echo $month; ?></span>
                        <span class="font-h3 text-h3 leading-tight"><?php echo $day; ?></span>
                    </div>
                    <div class="overflow-hidden flex-1">
                        <h5 class="font-body-md font-semibold truncate text-on-surface"><?php echo htmlspecialchars($a['assignment_title']); ?></h5>
                        <p class="font-body-sm text-on-surface-variant truncate"><?php echo htmlspecialchars($a['courseUnit_name'] ?? ''); ?></p>
                    </div>
                    <?php if ($is_overdue): ?>
                    <span class="text-error text-[10px] font-bold whitespace-nowrap">OVERDUE</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
                <a href="view_assignments.php" class="block w-full py-2 border-2 border-primary-container/20 text-primary font-button text-button rounded-xl hover:bg-primary-container/10 transition-all text-center mt-2">
                    View All Assignments
                </a>
            </div>
        </div>

        <!-- ── Recent Submissions ───────────────────────────────────────────── -->
        <div class="md:col-span-7 space-y-md">
            <h3 class="font-h2 text-h2 text-on-surface">Recent Submissions</h3>
            <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container overflow-hidden">
                <?php if (empty($recent_submissions)): ?>
                <div class="p-lg text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl block mb-2">upload_file</span>
                    No submissions yet. <a href="submit_assignments.php" class="text-primary hover:underline">Submit your first assignment</a>.
                </div>
                <?php else: ?>
                <div class="divide-y divide-surface-container">
                    <?php foreach ($recent_submissions as $sub):
                        $status_colors = [
                            'graded'  => 'text-primary bg-primary-container/20',
                            'pending' => 'text-tertiary bg-tertiary-container/20',
                            'submitted'=> 'text-secondary bg-secondary-container/30',
                        ];
                        $sc = $status_colors[$sub['submission_status']] ?? 'text-on-surface-variant bg-surface-container';
                    ?>
                    <div class="flex items-center gap-4 p-4 hover:bg-surface-container-low transition-colors">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary flex-shrink-0">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-body-md font-semibold truncate"><?php echo htmlspecialchars($sub['assignment_title'] ?? 'Assignment'); ?></p>
                            <p class="font-body-sm text-on-surface-variant"><?php echo htmlspecialchars($sub['courseUnit_name'] ?? ''); ?> &bull; <?php echo date('M d, Y', strtotime($sub['submission_date'])); ?></p>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="px-2 py-0.5 rounded-full font-label-caps text-[10px] <?php echo $sc; ?> capitalize">
                                <?php echo $sub['submission_status']; ?>
                            </span>
                            <?php if ($sub['score'] !== null): ?>
                            <span class="font-h3 text-primary"><?php echo $sub['score']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Notifications & Quick Links ─────────────────────────────────── -->
        <div class="md:col-span-5 space-y-md">
            <h3 class="font-h2 text-h2 text-on-surface">Notifications</h3>
            <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container overflow-hidden">
                <?php if (empty($notifications)): ?>
                <div class="p-lg text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl block mb-2">notifications_none</span>
                    No notifications yet.
                </div>
                <?php else: ?>
                <div class="divide-y divide-surface-container">
                    <?php foreach ($notifications as $notif): ?>
                    <div class="flex items-start gap-3 p-4 hover:bg-surface-container-low transition-colors <?php echo $notif['notification_status'] === 'unread' ? 'bg-primary-container/5' : ''; ?>">
                        <div class="w-8 h-8 rounded-full bg-primary-container/20 flex items-center justify-center text-primary flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[16px]">notifications</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-body-sm text-on-surface"><?php echo htmlspecialchars($notif['notification_message']); ?></p>
                            <p class="font-body-sm text-on-surface-variant mt-0.5"><?php echo date('M d, g:i A', strtotime($notif['date_sent'])); ?></p>
                        </div>
                        <?php if ($notif['notification_status'] === 'unread'): ?>
                        <span class="w-2 h-2 bg-primary rounded-full flex-shrink-0 mt-2"></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="p-3 border-t border-surface-container">
                    <a href="notification.php" class="block text-center text-primary font-button text-button hover:underline">View All Notifications</a>
                </div>
            </div>

            <!-- Quick Links -->
            <h3 class="font-h2 text-h2 text-on-surface">Quick Links</h3>
            <div class="grid grid-cols-2 gap-md">
                <a href="materials.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col items-center gap-2 text-center">
                    <span class="material-symbols-outlined text-primary text-3xl">menu_book</span>
                    <span class="font-body-sm font-semibold text-on-surface">Materials</span>
                    <span class="font-label-caps text-label-caps text-on-surface-variant"><?php echo $materials_count; ?> files</span>
                </a>
                <a href="result.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col items-center gap-2 text-center">
                    <span class="material-symbols-outlined text-tertiary text-3xl">grade</span>
                    <span class="font-body-sm font-semibold text-on-surface">Results</span>
                    <span class="font-label-caps text-label-caps text-on-surface-variant">View grades</span>
                </a>
                <a href="submit_assignments.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col items-center gap-2 text-center">
                    <span class="material-symbols-outlined text-secondary text-3xl">upload_file</span>
                    <span class="font-body-sm font-semibold text-on-surface">Submit</span>
                    <span class="font-label-caps text-label-caps text-on-surface-variant"><?php echo $pending_count; ?> pending</span>
                </a>
                <a href="messages.php" class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex flex-col items-center gap-2 text-center">
                    <span class="material-symbols-outlined text-primary text-3xl">chat_bubble</span>
                    <span class="font-body-sm font-semibold text-on-surface">Messages</span>
                    <span class="font-label-caps text-label-caps text-on-surface-variant"><?php echo $msg_count > 0 ? $msg_count . ' unread' : 'Inbox'; ?></span>
                </a>
            </div>
        </div>

        <!-- ── Progress Chart ───────────────────────────────────────────────── -->
        <?php if (!empty($assignments)): ?>
        <div class="md:col-span-12 space-y-md">
            <h3 class="font-h2 text-h2 text-on-surface">Assignment Overview</h3>
            <div class="bg-inverse-surface text-inverse-on-surface rounded-xl p-lg shadow-level-2 flex flex-col md:flex-row gap-lg items-center">
                <div class="w-full md:w-64 h-64">
                    <canvas id="assignmentChart"></canvas>
                </div>
                <div class="flex-1 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-primary-container inline-block"></span>
                        <span class="font-body-md text-inverse-on-surface">Submitted: <?php echo $submitted_count; ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-error inline-block"></span>
                        <span class="font-body-md text-inverse-on-surface">Pending: <?php echo $pending_count; ?></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-tertiary-fixed-dim inline-block"></span>
                        <span class="font-body-md text-inverse-on-surface">Total: <?php echo $total_assignments; ?></span>
                    </div>
                    <?php if ($avg_score !== null): ?>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <p class="font-label-caps text-label-caps text-primary-fixed">AVERAGE SCORE</p>
                        <p class="font-h1 text-h1 text-primary-fixed"><?php echo $avg_score; ?><span class="font-body-md text-inverse-on-surface/60">/100</span></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- end main grid -->
</main>

<script>
<?php if (!empty($assignments)): ?>
const ctx = document.getElementById('assignmentChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Submitted', 'Pending'],
        datasets: [{
            data: [<?php echo $submitted_count; ?>, <?php echo $pending_count; ?>],
            backgroundColor: ['#2ecc71', '#ba1a1a'],
            borderColor: ['#213145', '#213145'],
            borderWidth: 3,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
