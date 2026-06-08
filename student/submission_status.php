<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);
$search    = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$where = "sub.student_id = $student_id";
if ($search) $where .= " AND (a.assignment_title LIKE '%$search%' OR cu.courseUnit_name LIKE '%$search%')";

$sub_q = mysqli_query($conn,
    "SELECT sub.*, a.assignment_title, a.due_date, a.max_score, cu.courseUnit_name
     FROM submission sub
     LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE $where
     ORDER BY sub.submission_date DESC");
$submissions = [];
while ($row = mysqli_fetch_assoc($sub_q)) $submissions[] = $row;

// Stats
$total     = count($submissions);
$graded    = count(array_filter($submissions, fn($s) => $s['submission_status'] === 'graded'));
$pending   = count(array_filter($submissions, fn($s) => $s['submission_status'] === 'pending' || $s['submission_status'] === 'submitted'));
$overdue   = 0;
foreach ($submissions as $s) {
    if ($s['due_date'] && new DateTime($s['due_date']) < new DateTime() && $s['submission_status'] !== 'graded') $overdue++;
}
?>

<main class="max-w-3xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Track Work</p>
        <h2 class="font-h1 text-h1 text-on-surface">Submission Status</h2>
        <p class="font-body-md text-on-surface-variant">Track your academic progress and submission history.</p>
    </section>

    <!-- Stats bento -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border-l-4 border-primary flex flex-col justify-between h-28">
            <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">task_alt</span>
            <div>
                <p class="font-h2 text-h2 text-on-surface"><?php echo $total; ?></p>
                <p class="font-label-caps text-label-caps text-on-surface-variant">TOTAL</p>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border-l-4 border-primary-container flex flex-col justify-between h-28">
            <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">verified</span>
            <div>
                <p class="font-h2 text-h2 text-on-surface"><?php echo $graded; ?></p>
                <p class="font-label-caps text-label-caps text-on-surface-variant">GRADED</p>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border-l-4 border-tertiary flex flex-col justify-between h-28">
            <span class="material-symbols-outlined text-tertiary" style="font-variation-settings:'FILL' 1;">pending</span>
            <div>
                <p class="font-h2 text-h2 text-on-surface"><?php echo $pending; ?></p>
                <p class="font-label-caps text-label-caps text-on-surface-variant">PENDING</p>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border-l-4 border-error flex flex-col justify-between h-28">
            <span class="material-symbols-outlined text-error" style="font-variation-settings:'FILL' 1;">warning</span>
            <div>
                <p class="font-h2 text-h2 text-on-surface"><?php echo $overdue; ?></p>
                <p class="font-label-caps text-label-caps text-on-surface-variant">OVERDUE</p>
            </div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
        <input name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
            class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all font-body-md"
            placeholder="Search assignments...">
    </form>

    <!-- Submission list -->
    <?php if (empty($submissions)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3">upload_file</span>
        No submissions found. <a href="submit_assignments.php" class="text-primary hover:underline">Submit an assignment</a>.
    </div>
    <?php else: ?>
    <div class="space-y-md">
        <?php foreach ($submissions as $sub):
            $status = $sub['submission_status'];
            $status_map = [
                'graded'    => ['cls'=>'bg-primary-container text-on-primary-container',   'icon'=>'verified'],
                'submitted' => ['cls'=>'bg-secondary-container/30 text-secondary',          'icon'=>'upload_file'],
                'pending'   => ['cls'=>'bg-surface-container-high text-on-surface-variant', 'icon'=>'pending'],
            ];
            $sm = $status_map[$status] ?? $status_map['pending'];
            $pct = ($sub['score'] !== null && $sub['max_score'] > 0) ? round(($sub['score'] / $sub['max_score']) * 100) : null;
        ?>
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 <?php echo $sm['cls']; ?>">
                <span class="material-symbols-outlined"><?php echo $sm['icon']; ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-h3 text-h3 text-on-surface truncate"><?php echo htmlspecialchars($sub['assignment_title'] ?? 'Assignment'); ?></h3>
                <p class="font-body-sm text-on-surface-variant"><?php echo htmlspecialchars($sub['courseUnit_name'] ?? ''); ?></p>
                <p class="font-body-sm text-on-surface-variant mt-0.5">Submitted: <?php echo date('M d, Y g:i A', strtotime($sub['submission_date'])); ?></p>
                <?php if ($sub['comments']): ?>
                <p class="font-body-sm text-primary mt-1 italic">"<?php echo htmlspecialchars($sub['comments']); ?>"</p>
                <?php endif; ?>
            </div>
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span class="px-3 py-1 rounded-full font-label-caps text-[10px] <?php echo $sm['cls']; ?> capitalize"><?php echo $status; ?></span>
                <?php if ($sub['score'] !== null): ?>
                <span class="font-h3 text-h3 text-primary"><?php echo $sub['score']; ?><span class="font-body-sm text-on-surface-variant">/<?php echo $sub['max_score']; ?></span></span>
                <?php if ($pct !== null): ?>
                <div class="w-20 h-1.5 bg-surface-container rounded-full overflow-hidden">
                    <div class="h-full rounded-full <?php echo $pct >= 80 ? 'bg-primary' : ($pct >= 60 ? 'bg-tertiary' : 'bg-error'); ?>" style="width:<?php echo $pct; ?>%"></div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
