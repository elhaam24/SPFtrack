<?php
require_once 'includes/header.php';

// ── Stats for this teacher ──────────────────────────────────────────────────
$tid = intval($_SESSION['teacher_id']);

// Course units assigned to this teacher
$cu_res  = mysqli_query($conn, "SELECT COUNT(*) AS c FROM courseunit WHERE teacher_id=$tid");
$cu_count = mysqli_fetch_assoc($cu_res)['c'] ?? 0;

// Students enrolled in courses that contain this teacher's course units
$stu_res = mysqli_query($conn,
    "SELECT COUNT(DISTINCT e.student_id) AS c
     FROM enrollment e
     JOIN courseunit cu ON cu.semester_id = e.semester_id
     WHERE cu.teacher_id = $tid");
$stu_count = mysqli_fetch_assoc($stu_res)['c'] ?? 0;

// Total assignments created by this teacher (via their course units)
$asgn_res = mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM assignment a
     JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
     WHERE cu.teacher_id = $tid");
$asgn_count = mysqli_fetch_assoc($asgn_res)['c'] ?? 0;

// Pending (ungraded) submissions
$pend_res = mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM submission s
     JOIN assignment a ON a.assignment_id = s.assignment_id
     JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
     WHERE cu.teacher_id = $tid AND s.submission_status = 'pending'");
$pend_count = mysqli_fetch_assoc($pend_res)['c'] ?? 0;

// Materials uploaded by this teacher
$mat_res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM materials WHERE teacher_id=$tid");
$mat_count = mysqli_fetch_assoc($mat_res)['c'] ?? 0;

// Unread messages
$unread_res = mysqli_query($conn,
    "SELECT COUNT(*) AS c FROM messages WHERE receiver_id=$tid AND receiver_type='teacher' AND is_read=0");
$unread_count = mysqli_fetch_assoc($unread_res)['c'] ?? 0;

// ── Recent submissions (last 5) ─────────────────────────────────────────────
$recent_subs = mysqli_query($conn,
    "SELECT s.submission_id, s.submission_date, s.submission_status, s.score,
            st.student_name, a.assignment_title, cu.courseUnit_name
     FROM submission s
     JOIN assignment a  ON a.assignment_id  = s.assignment_id
     JOIN courseunit cu ON cu.courseUnit_id  = a.courseUnit_id
     JOIN student st    ON st.student_id     = s.student_id
     WHERE cu.teacher_id = $tid
     ORDER BY s.submission_date DESC
     LIMIT 5");

// ── Upcoming assignment deadlines ───────────────────────────────────────────
$upcoming = mysqli_query($conn,
    "SELECT a.assignment_id, a.assignment_title, a.due_date, a.max_score,
            cu.courseUnit_name,
            (SELECT COUNT(*) FROM submission s2 WHERE s2.assignment_id=a.assignment_id) AS sub_count
     FROM assignment a
     JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
     WHERE cu.teacher_id = $tid AND (a.due_date IS NULL OR a.due_date >= NOW())
     ORDER BY a.due_date ASC
     LIMIT 4");

// ── Recent notifications ────────────────────────────────────────────────────
$notifs = mysqli_query($conn,
    "SELECT notification_message, notification_status, date_sent, sender_name
     FROM notification
     WHERE teacher_id = $tid
     ORDER BY date_sent DESC
     LIMIT 4");

// ── My course units ─────────────────────────────────────────────────────────
$my_units = mysqli_query($conn,
    "SELECT cu.courseUnit_id, cu.courseUnit_name, cu.courseUnit_code,
            c.course_name, sem.semester_name,
            (SELECT COUNT(*) FROM assignment a WHERE a.courseUnit_id=cu.courseUnit_id) AS asgn_cnt,
            (SELECT COUNT(*) FROM materials m WHERE m.courseUnit_id=cu.courseUnit_id) AS mat_cnt
     FROM courseunit cu
     LEFT JOIN course c   ON c.course_id   = cu.course_id
     LEFT JOIN semester sem ON sem.semester_id = cu.semester_id
     WHERE cu.teacher_id = $tid
     LIMIT 6");
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<!-- Welcome Banner -->
<div class="mb-xl">
    <h1 class="text-3xl font-bold text-primary">Welcome back, <?php echo $teacher_name; ?> 👋</h1>
    <p class="text-on-surface-variant mt-1">Here's your academic overview for today — <?php echo date('l, F j, Y'); ?></p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-md mb-xl">
    <?php
    $stats = [
        ['icon'=>'menu_book',    'label'=>'Course Units',  'value'=>$cu_count,    'color'=>'text-primary',   'bg'=>'bg-primary-container/20',   'link'=>'courseUnit.php'],
        ['icon'=>'group',        'label'=>'Students',      'value'=>$stu_count,   'color'=>'text-secondary', 'bg'=>'bg-secondary-container/30', 'link'=>'students.php'],
        ['icon'=>'assignment',   'label'=>'Assignments',   'value'=>$asgn_count,  'color'=>'text-tertiary',  'bg'=>'bg-tertiary-fixed/40',      'link'=>'assignment.php'],
        ['icon'=>'pending_actions','label'=>'Pending',     'value'=>$pend_count,  'color'=>'text-error',     'bg'=>'bg-error-container/30',     'link'=>'view_submission.php'],
        ['icon'=>'folder_open',  'label'=>'Materials',     'value'=>$mat_count,   'color'=>'text-primary',   'bg'=>'bg-primary-container/20',   'link'=>'materials.php'],
        ['icon'=>'chat',         'label'=>'Unread Msgs',   'value'=>$unread_count,'color'=>'text-secondary', 'bg'=>'bg-secondary-container/30', 'link'=>'messages.php'],
    ];
    foreach ($stats as $s):
    ?>
    <a href="<?php echo $s['link']; ?>" class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-outline-variant/20 hover:shadow-md transition-all hover:-translate-y-0.5 group">
        <div class="<?php echo $s['bg']; ?> w-10 h-10 rounded-xl flex items-center justify-center mb-sm">
            <span class="material-symbols-outlined <?php echo $s['color']; ?> text-xl"><?php echo $s['icon']; ?></span>
        </div>
        <div class="text-2xl font-bold <?php echo $s['color']; ?>"><?php echo $s['value']; ?></div>
        <div class="text-xs text-on-surface-variant mt-0.5"><?php echo $s['label']; ?></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Main Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">

    <!-- Left: Recent Submissions + Quick Actions -->
    <div class="lg:col-span-8 space-y-gutter">

        <!-- Recent Submissions -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
            <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant/20">
                <h2 class="font-semibold text-on-surface flex items-center gap-sm">
                    <span class="material-symbols-outlined text-primary">grading</span>
                    Recent Submissions
                </h2>
                <a href="view_submission.php" class="text-sm text-primary hover:underline font-medium">View all →</a>
            </div>
            <?php if (mysqli_num_rows($recent_subs) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-lg py-sm text-left font-medium">Student</th>
                            <th class="px-lg py-sm text-left font-medium">Assignment</th>
                            <th class="px-lg py-sm text-left font-medium hidden md:table-cell">Course Unit</th>
                            <th class="px-lg py-sm text-left font-medium">Status</th>
                            <th class="px-lg py-sm text-right font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <?php while ($row = mysqli_fetch_assoc($recent_subs)): ?>
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-lg py-md">
                                <div class="flex items-center gap-sm">
                                    <div class="w-8 h-8 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                        <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                    </div>
                                    <span class="font-medium text-on-surface truncate max-w-[120px]"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                </div>
                            </td>
                            <td class="px-lg py-md text-on-surface-variant truncate max-w-[150px]"><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                            <td class="px-lg py-md text-on-surface-variant hidden md:table-cell text-xs"><?php echo htmlspecialchars($row['courseUnit_name']); ?></td>
                            <td class="px-lg py-md">
                                <?php
                                $status = $row['submission_status'];
                                $badge = match($status) {
                                    'graded'  => 'bg-primary-container/20 text-primary',
                                    'pending' => 'bg-tertiary-fixed/50 text-tertiary',
                                    default   => 'bg-surface-container text-on-surface-variant',
                                };
                                ?>
                                <span class="<?php echo $badge; ?> text-xs font-semibold px-2 py-0.5 rounded-full capitalize"><?php echo $status; ?></span>
                            </td>
                            <td class="px-lg py-md text-right">
                                <?php if ($status === 'pending'): ?>
                                <a href="grade_submission.php?id=<?php echo $row['submission_id']; ?>" class="bg-primary text-on-primary text-xs font-semibold px-md py-1 rounded-lg hover:opacity-90 transition-opacity">Grade</a>
                                <?php else: ?>
                                <a href="grade_submission.php?id=<?php echo $row['submission_id']; ?>" class="border border-primary text-primary text-xs font-semibold px-md py-1 rounded-lg hover:bg-primary-container/10 transition-colors">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-2xl text-on-surface-variant">
                <span class="material-symbols-outlined text-5xl mb-md opacity-40">inbox</span>
                <p class="text-sm">No submissions yet.</p>
                <a href="assignment.php" class="mt-md text-sm text-primary hover:underline">Create an assignment →</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- My Course Units -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
            <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant/20">
                <h2 class="font-semibold text-on-surface flex items-center gap-sm">
                    <span class="material-symbols-outlined text-primary">menu_book</span>
                    My Course Units
                </h2>
                <a href="courseUnit.php" class="text-sm text-primary hover:underline font-medium">View all →</a>
            </div>
            <?php if (mysqli_num_rows($my_units) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-md p-lg">
                <?php while ($cu = mysqli_fetch_assoc($my_units)): ?>
                <a href="courseUnit.php?id=<?php echo $cu['courseUnit_id']; ?>" class="border border-outline-variant/30 rounded-xl p-md hover:border-primary/40 hover:bg-surface-container-low/50 transition-all group">
                    <div class="flex items-start justify-between mb-sm">
                        <div class="bg-primary-container/20 p-2 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-lg">class</span>
                        </div>
                        <span class="text-xs bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full font-mono"><?php echo htmlspecialchars($cu['courseUnit_code']); ?></span>
                    </div>
                    <h3 class="font-semibold text-on-surface text-sm mt-sm group-hover:text-primary transition-colors"><?php echo htmlspecialchars($cu['courseUnit_name']); ?></h3>
                    <p class="text-xs text-on-surface-variant mt-0.5"><?php echo htmlspecialchars($cu['course_name'] ?? '—'); ?> · <?php echo htmlspecialchars($cu['semester_name'] ?? '—'); ?></p>
                    <div class="flex gap-md mt-md text-xs text-on-surface-variant">
                        <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">assignment</span><?php echo $cu['asgn_cnt']; ?> assignments</span>
                        <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">folder</span><?php echo $cu['mat_cnt']; ?> materials</span>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="flex flex-col items-center justify-center py-xl text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-sm opacity-40">menu_book</span>
                <p class="text-sm">No course units assigned yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Upcoming Deadlines + Notifications + Quick Actions -->
    <div class="lg:col-span-4 space-y-gutter">

        <!-- Quick Actions -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-md flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">bolt</span>
                Quick Actions
            </h2>
            <div class="space-y-sm">
                <a href="assignment.php" class="flex items-center gap-md bg-primary text-on-primary px-md py-sm rounded-xl text-sm font-medium hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-lg">add_task</span> Create Assignment
                </a>
                <a href="materials.php" class="flex items-center gap-md bg-surface-container-high text-on-surface px-md py-sm rounded-xl text-sm font-medium hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-lg">upload_file</span> Upload Materials
                </a>
                <a href="messages.php" class="flex items-center gap-md bg-surface-container-high text-on-surface px-md py-sm rounded-xl text-sm font-medium hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-lg">chat</span> Send Message
                    <?php if ($unread_count > 0): ?>
                    <span class="ml-auto bg-error text-on-error text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="view_submission.php" class="flex items-center gap-md bg-surface-container-high text-on-surface px-md py-sm rounded-xl text-sm font-medium hover:bg-surface-container-highest transition-colors">
                    <span class="material-symbols-outlined text-lg">grading</span> Grade Submissions
                    <?php if ($pend_count > 0): ?>
                    <span class="ml-auto bg-tertiary text-on-tertiary text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $pend_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
            <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant/20">
                <h2 class="font-semibold text-on-surface flex items-center gap-sm">
                    <span class="material-symbols-outlined text-tertiary">schedule</span>
                    Upcoming Deadlines
                </h2>
                <a href="assignment.php" class="text-sm text-primary hover:underline font-medium">All →</a>
            </div>
            <div class="divide-y divide-outline-variant/10">
                <?php if (mysqli_num_rows($upcoming) > 0):
                    while ($asgn = mysqli_fetch_assoc($upcoming)):
                        $due = $asgn['due_date'] ? date('M j, Y', strtotime($asgn['due_date'])) : 'No deadline';
                        $is_soon = $asgn['due_date'] && strtotime($asgn['due_date']) < strtotime('+3 days');
                ?>
                <div class="px-lg py-md hover:bg-surface-container-low/50 transition-colors">
                    <div class="flex items-start justify-between gap-sm">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-on-surface truncate"><?php echo htmlspecialchars($asgn['assignment_title']); ?></p>
                            <p class="text-xs text-on-surface-variant mt-0.5"><?php echo htmlspecialchars($asgn['courseUnit_name']); ?></p>
                        </div>
                        <span class="text-xs <?php echo $is_soon ? 'text-error font-semibold' : 'text-on-surface-variant'; ?> flex-shrink-0"><?php echo $due; ?></span>
                    </div>
                    <div class="flex items-center gap-sm mt-sm">
                        <span class="text-xs text-on-surface-variant"><?php echo $asgn['sub_count']; ?> submissions</span>
                        <?php if ($is_soon): ?>
                        <span class="text-[10px] bg-error-container text-on-error-container px-1.5 py-0.5 rounded-full font-bold">Due Soon</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="px-lg py-xl text-center text-on-surface-variant text-sm">
                    <span class="material-symbols-outlined text-3xl block mb-sm opacity-40">event_available</span>
                    No upcoming deadlines
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
            <div class="flex items-center justify-between px-lg py-md border-b border-outline-variant/20">
                <h2 class="font-semibold text-on-surface flex items-center gap-sm">
                    <span class="material-symbols-outlined text-primary">notifications</span>
                    Notifications
                </h2>
                <a href="notification.php" class="text-sm text-primary hover:underline font-medium">All →</a>
            </div>
            <div class="divide-y divide-outline-variant/10">
                <?php if ($notifs && mysqli_num_rows($notifs) > 0):
                    while ($n = mysqli_fetch_assoc($notifs)):
                        $unread_cls = $n['notification_status'] === 'unread' ? 'bg-primary-container/10' : '';
                ?>
                <div class="px-lg py-md <?php echo $unread_cls; ?> hover:bg-surface-container-low/50 transition-colors">
                    <div class="flex items-start gap-sm">
                        <div class="w-7 h-7 rounded-full bg-primary-container/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-primary text-sm">notifications</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-on-surface line-clamp-2"><?php echo htmlspecialchars($n['notification_message']); ?></p>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                <?php echo $n['sender_name'] ? htmlspecialchars($n['sender_name']).' · ' : ''; ?>
                                <?php echo $n['date_sent'] ? date('M j, g:i A', strtotime($n['date_sent'])) : ''; ?>
                            </p>
                        </div>
                        <?php if ($n['notification_status'] === 'unread'): ?>
                        <div class="w-2 h-2 rounded-full bg-primary flex-shrink-0 mt-1.5"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="px-lg py-xl text-center text-on-surface-variant text-sm">
                    <span class="material-symbols-outlined text-3xl block mb-sm opacity-40">notifications_off</span>
                    No notifications
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Chart Section -->
<div class="mt-gutter bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
    <h2 class="font-semibold text-on-surface mb-lg flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary">bar_chart</span>
        Submission Overview
    </h2>
    <?php
    // Gather submission counts per assignment for chart
    $chart_data = mysqli_query($conn,
        "SELECT a.assignment_title,
                SUM(CASE WHEN s.submission_status='graded'  THEN 1 ELSE 0 END) AS graded,
                SUM(CASE WHEN s.submission_status='pending' THEN 1 ELSE 0 END) AS pending_cnt,
                COUNT(s.submission_id) AS total
         FROM assignment a
         JOIN courseunit cu ON cu.courseUnit_id = a.courseUnit_id
         LEFT JOIN submission s ON s.assignment_id = a.assignment_id
         WHERE cu.teacher_id = $tid
         GROUP BY a.assignment_id
         ORDER BY a.created_at DESC
         LIMIT 6");
    $chart_labels = $chart_graded = $chart_pending = [];
    while ($cd = mysqli_fetch_assoc($chart_data)) {
        $chart_labels[]  = addslashes(substr($cd['assignment_title'], 0, 20));
        $chart_graded[]  = intval($cd['graded']);
        $chart_pending[] = intval($cd['pending_cnt']);
    }
    $labels_json  = json_encode($chart_labels);
    $graded_json  = json_encode($chart_graded);
    $pending_json = json_encode($chart_pending);
    ?>
    <canvas id="submissionChart" style="max-height:280px;"></canvas>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('submissionChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo $labels_json; ?>,
        datasets: [
            {
                label: 'Graded',
                data: <?php echo $graded_json; ?>,
                backgroundColor: 'rgba(0,109,55,0.7)',
                borderColor: '#006d37',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Pending',
                data: <?php echo $pending_json; ?>,
                backgroundColor: 'rgba(215,174,0,0.6)',
                borderColor: '#d7ae00',
                borderWidth: 1,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { font: { family: 'Lexend' } } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Lexend' } }, grid: { color: 'rgba(100,116,139,0.15)' } },
            x: { ticks: { font: { family: 'Lexend' } }, grid: { display: false } }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
