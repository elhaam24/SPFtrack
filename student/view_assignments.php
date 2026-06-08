<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);
$filter_unit = isset($_GET['unit']) ? intval($_GET['unit']) : 0;

// Build query
$where = "cu.course_id = $course_id";
if ($filter_unit > 0) $where .= " AND a.courseUnit_id = $filter_unit";

$assign_q = mysqli_query($conn,
    "SELECT a.*, cu.courseUnit_name, cu.courseUnit_id,
            sub.submission_id, sub.submission_status, sub.score, sub.submission_date AS sub_date
     FROM assignment a
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     LEFT JOIN submission sub ON sub.assignment_id = a.assignment_id AND sub.student_id = $student_id
     WHERE $where
     ORDER BY a.due_date ASC");
$assignments = [];
while ($row = mysqli_fetch_assoc($assign_q)) $assignments[] = $row;

// Course units for filter
$units_q = mysqli_query($conn, "SELECT courseUnit_id, courseUnit_name FROM courseunit WHERE course_id = $course_id ORDER BY courseUnit_name");
$units = [];
while ($row = mysqli_fetch_assoc($units_q)) $units[] = $row;
?>

<main class="max-w-4xl mx-auto px-margin_mobile py-lg space-y-lg">
    <!-- Header -->
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Academic Tasks</p>
        <h2 class="font-h1 text-h1 text-on-surface">Assignments</h2>
        <p class="font-body-md text-on-surface-variant">All assignments for your enrolled course units.</p>
    </section>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-md">
        <?php
        $total = count($assignments);
        $submitted = count(array_filter($assignments, fn($a) => $a['submission_id']));
        $pending   = $total - $submitted;
        ?>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-on-surface"><?php echo $total; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">TOTAL</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-primary"><?php echo $submitted; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">SUBMITTED</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-error"><?php echo $pending; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">PENDING</p>
        </div>
    </div>

    <!-- Filter by unit -->
    <?php if (!empty($units)): ?>
    <div class="flex gap-2 overflow-x-auto pb-1">
        <a href="view_assignments.php" class="px-4 py-2 rounded-full font-button text-button whitespace-nowrap <?php echo $filter_unit === 0 ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container hover:bg-secondary-fixed'; ?>">
            All Units
        </a>
        <?php foreach ($units as $u): ?>
        <a href="view_assignments.php?unit=<?php echo $u['courseUnit_id']; ?>" class="px-4 py-2 rounded-full font-button text-button whitespace-nowrap <?php echo $filter_unit === intval($u['courseUnit_id']) ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container hover:bg-secondary-fixed'; ?>">
            <?php echo htmlspecialchars($u['courseUnit_name']); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Assignment list -->
    <?php if (empty($assignments)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3">assignment</span>
        No assignments found.
    </div>
    <?php else: ?>
    <div class="space-y-md">
        <?php foreach ($assignments as $a):
            $now = new DateTime();
            $due = $a['due_date'] ? new DateTime($a['due_date']) : null;
            $is_overdue = $due && $due < $now && !$a['submission_id'];
            $is_submitted = (bool)$a['submission_id'];

            if ($is_submitted) {
                $badge_cls = 'bg-primary-container/20 text-primary';
                $badge_txt = ucfirst($a['submission_status'] ?? 'Submitted');
            } elseif ($is_overdue) {
                $badge_cls = 'bg-error-container text-on-error-container';
                $badge_txt = 'Overdue';
            } else {
                $badge_cls = 'bg-tertiary-container/20 text-tertiary';
                $badge_txt = 'Pending';
            }
        ?>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 <?php echo $is_submitted ? 'bg-primary-container/20 text-primary' : ($is_overdue ? 'bg-error-container text-error' : 'bg-tertiary-container/20 text-tertiary'); ?>">
                        <span class="material-symbols-outlined"><?php echo $is_submitted ? 'task_alt' : 'assignment'; ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-h3 text-h3 text-on-surface"><?php echo htmlspecialchars($a['assignment_title']); ?></h4>
                        <p class="font-body-sm text-on-surface-variant mt-1"><?php echo htmlspecialchars($a['courseUnit_name'] ?? ''); ?></p>
                        <?php if ($a['assignment_description']): ?>
                        <p class="font-body-sm text-on-surface-variant mt-2 line-clamp-2"><?php echo htmlspecialchars($a['assignment_description']); ?></p>
                        <?php endif; ?>
                        <div class="flex flex-wrap gap-4 mt-3">
                            <?php if ($a['due_date']): ?>
                            <span class="flex items-center gap-1 font-body-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                Due: <?php echo date('M d, Y', strtotime($a['due_date'])); ?>
                            </span>
                            <?php endif; ?>
                            <span class="flex items-center gap-1 font-body-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">score</span>
                                Max: <?php echo $a['max_score']; ?> pts
                            </span>
                            <?php if ($is_submitted && $a['score'] !== null): ?>
                            <span class="flex items-center gap-1 font-body-sm text-primary font-semibold">
                                <span class="material-symbols-outlined text-[16px]">grade</span>
                                Score: <?php echo $a['score']; ?>/<?php echo $a['max_score']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="px-3 py-1 rounded-full font-label-caps text-[10px] <?php echo $badge_cls; ?>"><?php echo $badge_txt; ?></span>
                    <?php if (!$is_submitted): ?>
                    <a href="submit_assignments.php?assignment_id=<?php echo $a['assignment_id']; ?>" class="px-4 py-2 bg-primary text-on-primary rounded-xl font-button text-button hover:bg-on-primary-fixed-variant transition-colors text-sm">
                        Submit
                    </a>
                    <?php else: ?>
                    <a href="submission_status.php" class="px-4 py-2 border border-primary/30 text-primary rounded-xl font-button text-button hover:bg-primary-container/10 transition-colors text-sm">
                        View
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
