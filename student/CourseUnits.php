<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);

// Get all course units for this student's course
$units_q = mysqli_query($conn,
    "SELECT cu.*, t.teacher_name, t.teacher_email, sem.semester_name
     FROM courseunit cu
     LEFT JOIN teacher t ON cu.teacher_id = t.teacher_id
     LEFT JOIN semester sem ON cu.semester_id = sem.semester_id
     WHERE cu.course_id = $course_id
     ORDER BY cu.courseUnit_id ASC");
$units = [];
while ($row = mysqli_fetch_assoc($units_q)) $units[] = $row;

// Enrollment info
$enroll_q = mysqli_query($conn,
    "SELECT e.*, sem.semester_name, ay.year_name FROM enrollment e
     LEFT JOIN semester sem ON e.semester_id=sem.semester_id
     LEFT JOIN academic_year ay ON e.year_id=ay.year_id
     WHERE e.student_id=$student_id ORDER BY e.enrollment_id DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_q);
?>

<main class="max-w-5xl mx-auto px-margin_mobile py-lg space-y-lg">
    <!-- Banner -->
    <section class="bg-primary-container/10 p-lg rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-md border border-primary-container/20">
        <div>
            <h1 class="font-h1 text-h1 text-on-primary-container">Active Course Units</h1>
            <p class="font-body-md text-on-surface-variant mt-1">Continue your academic journey and track your semester progress.</p>
        </div>
        <?php if ($enrollment): ?>
        <div class="flex items-center gap-2 bg-surface-container-lowest px-md py-sm rounded-lg shadow-level-1">
            <span class="material-symbols-outlined text-primary">calendar_today</span>
            <span class="font-label-caps text-label-caps text-secondary"><?php echo htmlspecialchars($enrollment['semester_name'] . ', ' . $enrollment['year_name']); ?></span>
        </div>
        <?php endif; ?>
    </section>

    <!-- Units grid -->
    <?php if (empty($units)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3">school</span>
        No course units found for your course.
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
        <?php foreach ($units as $i => $unit):
            // Assignment stats
            $ua_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM assignment WHERE courseUnit_id=" . intval($unit['courseUnit_id']));
            $ua_row = mysqli_fetch_assoc($ua_q);
            $unit_assign_count = $ua_row['c'] ?? 0;

            $us_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM submission WHERE student_id=$student_id AND assignment_id IN (SELECT assignment_id FROM assignment WHERE courseUnit_id=" . intval($unit['courseUnit_id']) . ")");
            $us_row = mysqli_fetch_assoc($us_q);
            $unit_submitted = $us_row['c'] ?? 0;

            $progress = $unit_assign_count > 0 ? round(($unit_submitted / $unit_assign_count) * 100) : 0;

            // Materials count
            $mat_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM materials WHERE courseUnit_id=" . intval($unit['courseUnit_id']));
            $mat_row = mysqli_fetch_assoc($mat_q);
            $mat_count = $mat_row['c'] ?? 0;

            $col_class = ($i === 0) ? 'md:col-span-8' : 'md:col-span-4';
            if (count($units) === 1) $col_class = 'md:col-span-12';
        ?>
        <div class="<?php echo $col_class; ?> bg-surface-container-lowest p-lg rounded-xl shadow-level-1 border border-surface-container flex flex-col justify-between hover:shadow-level-2 transition-all">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-md">
                    <div class="w-12 h-12 rounded-xl bg-primary-container/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">menu_book</span>
                    </div>
                    <div>
                        <h3 class="font-h3 text-h3 text-on-surface"><?php echo htmlspecialchars($unit['courseUnit_name']); ?></h3>
                        <p class="font-body-sm text-on-surface-variant"><?php echo $unit['teacher_name'] ? htmlspecialchars($unit['teacher_name']) : 'No teacher assigned'; ?></p>
                    </div>
                </div>
                <span class="font-label-caps text-label-caps px-md py-1 bg-surface-container text-on-surface-variant rounded-full text-[10px]">
                    <?php echo htmlspecialchars($unit['courseUnit_code']); ?>
                </span>
            </div>

            <?php if ($unit['description']): ?>
            <p class="font-body-sm text-on-surface-variant mt-md line-clamp-2"><?php echo htmlspecialchars($unit['description']); ?></p>
            <?php endif; ?>

            <div class="mt-lg">
                <div class="flex justify-between items-end mb-xs">
                    <span class="font-label-caps text-label-caps text-secondary">Submission Progress</span>
                    <span class="font-h3 text-h3 text-primary"><?php echo $progress; ?>%</span>
                </div>
                <div class="w-full bg-secondary-container h-2 rounded-full overflow-hidden">
                    <div class="bg-primary h-full rounded-full transition-all" style="width: <?php echo $progress; ?>%"></div>
                </div>
                <div class="flex gap-4 mt-md flex-wrap">
                    <div class="flex items-center gap-1 text-on-surface-variant">
                        <span class="material-symbols-outlined text-sm">assignment</span>
                        <span class="font-body-sm"><?php echo $unit_submitted; ?>/<?php echo $unit_assign_count; ?> Submitted</span>
                    </div>
                    <div class="flex items-center gap-1 text-on-surface-variant">
                        <span class="material-symbols-outlined text-sm">description</span>
                        <span class="font-body-sm"><?php echo $mat_count; ?> Materials</span>
                    </div>
                    <?php if ($unit['semester_name']): ?>
                    <div class="flex items-center gap-1 text-on-surface-variant">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        <span class="font-body-sm"><?php echo htmlspecialchars($unit['semester_name']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex gap-2 mt-lg">
                <a href="view_assignments.php?unit=<?php echo $unit['courseUnit_id']; ?>"
                   class="flex-1 py-2 bg-primary text-on-primary rounded-xl font-button text-button hover:bg-on-primary-fixed-variant transition-colors text-center text-sm">
                    Assignments
                </a>
                <a href="materials.php?unit=<?php echo $unit['courseUnit_id']; ?>"
                   class="flex-1 py-2 border border-primary/30 text-primary rounded-xl font-button text-button hover:bg-primary-container/10 transition-colors text-center text-sm">
                    Materials
                </a>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Upcoming deadline card -->
        <?php
        $next_q = mysqli_query($conn,
            "SELECT a.assignment_title, a.due_date, cu.courseUnit_name
             FROM assignment a
             LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
             WHERE cu.course_id = $course_id
               AND a.due_date > NOW()
               AND a.assignment_id NOT IN (SELECT assignment_id FROM submission WHERE student_id=$student_id)
             ORDER BY a.due_date ASC LIMIT 1");
        $next_assign = mysqli_fetch_assoc($next_q);
        if ($next_assign):
            $due_dt = new DateTime($next_assign['due_date']);
            $now_dt = new DateTime();
            $diff   = $now_dt->diff($due_dt);
            $remaining = '';
            if ($diff->days > 0) $remaining = $diff->days . 'd ' . $diff->h . 'h';
            else $remaining = $diff->h . 'h ' . $diff->i . 'm';
        ?>
        <div class="md:col-span-12 bg-inverse-surface p-lg rounded-xl shadow-level-2 flex flex-col md:flex-row items-start md:items-center justify-between gap-md text-inverse-on-surface">
            <div class="flex items-center gap-md">
                <div class="w-12 h-12 rounded-full bg-error-container/20 flex items-center justify-center text-error">
                    <span class="material-symbols-outlined">alarm</span>
                </div>
                <div>
                    <p class="font-label-caps text-label-caps text-surface-dim">UPCOMING DEADLINE</p>
                    <h4 class="font-body-lg font-bold text-white"><?php echo htmlspecialchars($next_assign['assignment_title']); ?></h4>
                    <p class="font-body-sm text-inverse-on-surface/70"><?php echo htmlspecialchars($next_assign['courseUnit_name']); ?></p>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <p class="font-h3 text-h3 text-primary-fixed"><?php echo $remaining; ?></p>
                <p class="font-body-sm text-inverse-on-surface/60">Remaining</p>
                <a href="submit_assignments.php" class="mt-2 px-4 py-2 bg-primary-container text-on-primary-container rounded-xl font-button text-button hover:brightness-110 transition-all text-sm">
                    Submit Now
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
