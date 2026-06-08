<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

// Fetch this teacher's course units with related info
$units = mysqli_query($conn,
    "SELECT cu.courseUnit_id, cu.courseUnit_name, cu.courseUnit_code,
            cu.description, cu.start_date, cu.end_date,
            c.course_name, sem.semester_name,
            d.department_name,
            (SELECT COUNT(*) FROM assignment a WHERE a.courseUnit_id=cu.courseUnit_id) AS asgn_count,
            (SELECT COUNT(*) FROM materials m WHERE m.courseUnit_id=cu.courseUnit_id) AS mat_count,
            (SELECT COUNT(DISTINCT s.student_id) FROM submission s
             JOIN assignment a2 ON a2.assignment_id=s.assignment_id
             WHERE a2.courseUnit_id=cu.courseUnit_id) AS student_count
     FROM courseunit cu
     LEFT JOIN course c     ON c.course_id     = cu.course_id
     LEFT JOIN semester sem ON sem.semester_id  = cu.semester_id
     LEFT JOIN department d ON d.department_id  = (SELECT department_id FROM teacher WHERE teacher_id=$tid LIMIT 1)
     WHERE cu.teacher_id = $tid
     ORDER BY cu.courseUnit_name ASC");
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="mb-xl">
    <h1 class="text-2xl font-bold text-on-surface">My Course Units</h1>
    <p class="text-on-surface-variant text-sm mt-0.5">Course units assigned to you</p>
</div>

<?php if (mysqli_num_rows($units) > 0): ?>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-md">
    <?php while ($cu = mysqli_fetch_assoc($units)): ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 hover:shadow-md transition-shadow overflow-hidden">
        <!-- Header -->
        <div class="bg-primary/5 border-b border-outline-variant/20 p-lg">
            <div class="flex items-start justify-between gap-sm">
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-on-surface"><?php echo htmlspecialchars($cu['courseUnit_name']); ?></h3>
                    <span class="inline-block mt-1 text-xs bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full font-mono"><?php echo htmlspecialchars($cu['courseUnit_code']); ?></span>
                </div>
                <div class="bg-primary-container/20 p-2 rounded-lg flex-shrink-0">
                    <span class="material-symbols-outlined text-primary text-xl">class</span>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-lg space-y-sm">
            <?php if ($cu['description']): ?>
            <p class="text-sm text-on-surface-variant line-clamp-2"><?php echo htmlspecialchars($cu['description']); ?></p>
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-sm text-xs text-on-surface-variant">
                <div class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-sm">school</span>
                    <span class="truncate"><?php echo htmlspecialchars($cu['course_name'] ?? '—'); ?></span>
                </div>
                <div class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    <span><?php echo htmlspecialchars($cu['semester_name'] ?? '—'); ?></span>
                </div>
                <?php if ($cu['start_date']): ?>
                <div class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-sm">play_arrow</span>
                    <span><?php echo date('M j, Y', strtotime($cu['start_date'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($cu['end_date']): ?>
                <div class="flex items-center gap-xs">
                    <span class="material-symbols-outlined text-sm">stop</span>
                    <span><?php echo date('M j, Y', strtotime($cu['end_date'])); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Stats row -->
            <div class="flex gap-md pt-sm border-t border-outline-variant/20 text-xs">
                <div class="flex-1 text-center">
                    <div class="font-bold text-on-surface text-base"><?php echo $cu['asgn_count']; ?></div>
                    <div class="text-on-surface-variant">Assignments</div>
                </div>
                <div class="flex-1 text-center border-x border-outline-variant/20">
                    <div class="font-bold text-on-surface text-base"><?php echo $cu['mat_count']; ?></div>
                    <div class="text-on-surface-variant">Materials</div>
                </div>
                <div class="flex-1 text-center">
                    <div class="font-bold text-on-surface text-base"><?php echo $cu['student_count']; ?></div>
                    <div class="text-on-surface-variant">Students</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-lg pb-lg flex gap-sm">
            <a href="assignment.php?cu=<?php echo $cu['courseUnit_id']; ?>" class="flex-1 flex items-center justify-center gap-xs bg-primary-container/20 text-primary py-sm rounded-xl text-xs font-semibold hover:bg-primary-container/30 transition-colors">
                <span class="material-symbols-outlined text-sm">assignment</span> Assignments
            </a>
            <a href="materials.php?cu=<?php echo $cu['courseUnit_id']; ?>" class="flex-1 flex items-center justify-center gap-xs bg-surface-container-high text-on-surface-variant py-sm rounded-xl text-xs font-semibold hover:bg-surface-container-highest transition-colors">
                <span class="material-symbols-outlined text-sm">folder_open</span> Materials
            </a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center py-2xl text-on-surface-variant">
    <span class="material-symbols-outlined text-5xl mb-md opacity-40">menu_book</span>
    <p class="text-sm font-medium">No course units assigned to you yet.</p>
    <p class="text-xs mt-1">Contact your administrator to get course units assigned.</p>
</div>
<?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
