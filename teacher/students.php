<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$search_where = $search ? "AND (st.student_name LIKE '%$search%' OR st.student_email LIKE '%$search%')" : '';

// Students enrolled in semesters that have this teacher's course units
$students = mysqli_query($conn,
    "SELECT DISTINCT st.student_id, st.student_name, st.student_email, st.gender, st.phone_number,
            c.course_name, ses.session_name,
            (SELECT COUNT(*) FROM submission s2
             JOIN assignment a2 ON a2.assignment_id=s2.assignment_id
             JOIN courseunit cu2 ON cu2.courseUnit_id=a2.courseUnit_id
             WHERE s2.student_id=st.student_id AND cu2.teacher_id=$tid) AS sub_count,
            (SELECT AVG(s3.score) FROM submission s3
             JOIN assignment a3 ON a3.assignment_id=s3.assignment_id
             JOIN courseunit cu3 ON cu3.courseUnit_id=a3.courseUnit_id
             WHERE s3.student_id=st.student_id AND cu3.teacher_id=$tid AND s3.score IS NOT NULL) AS avg_score
     FROM student st
     JOIN enrollment e ON e.student_id = st.student_id
     JOIN courseunit cu ON cu.semester_id = e.semester_id AND cu.teacher_id = $tid
     LEFT JOIN course c ON c.course_id = st.course_id
     LEFT JOIN session ses ON ses.session_id = st.session_id
     WHERE 1=1 $search_where
     ORDER BY st.student_name ASC");

$total_students = mysqli_num_rows($students);
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-md mb-xl">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">My Students</h1>
        <p class="text-on-surface-variant text-sm mt-0.5"><?php echo $total_students; ?> student<?php echo $total_students != 1 ? 's' : ''; ?> enrolled in your course units</p>
    </div>
    <form method="GET" class="flex gap-sm">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search students…"
                   class="pl-10 pr-md py-sm border border-outline-variant rounded-xl text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none w-64">
        </div>
        <button type="submit" class="bg-primary text-on-primary px-md py-sm rounded-xl text-sm font-medium hover:opacity-90 transition-opacity">Search</button>
        <?php if ($search): ?>
        <a href="students.php" class="bg-surface-container text-on-surface-variant px-md py-sm rounded-xl text-sm font-medium hover:bg-surface-container-high transition-colors">Clear</a>
        <?php endif; ?>
    </form>
</div>

<?php if ($total_students > 0): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-md">
    <?php while ($st = mysqli_fetch_assoc($students)):
        $avg = $st['avg_score'] !== null ? round($st['avg_score'], 1) : null;
        $avg_color = $avg === null ? 'text-on-surface-variant' : ($avg >= 70 ? 'text-primary' : ($avg >= 50 ? 'text-tertiary' : 'text-error'));
        $bar_color  = $avg === null ? 'bg-outline-variant' : ($avg >= 70 ? 'bg-primary-container' : ($avg >= 50 ? 'bg-tertiary-container' : 'bg-error'));
        $bar_width  = $avg !== null ? min(100, $avg) : 0;
        $left_border = $avg === null ? 'border-l-outline-variant' : ($avg >= 70 ? 'border-l-primary-container' : ($avg >= 50 ? 'border-l-tertiary-container' : 'border-l-error'));
    ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 border-l-4 <?php echo $left_border; ?> p-lg hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-md">
            <div class="flex items-center gap-md">
                <div class="w-12 h-12 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-lg flex-shrink-0">
                    <?php echo strtoupper(substr($st['student_name'], 0, 1)); ?>
                </div>
                <div>
                    <h3 class="font-semibold text-on-surface text-sm"><?php echo htmlspecialchars($st['student_name']); ?></h3>
                    <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($st['student_email']); ?></p>
                </div>
            </div>
            <?php if ($avg !== null): ?>
            <div class="text-right">
                <span class="text-xl font-bold <?php echo $avg_color; ?>"><?php echo $avg; ?>%</span>
                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide">Avg Grade</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($avg !== null): ?>
        <div class="h-1.5 w-full bg-surface-container-high rounded-full overflow-hidden mb-md">
            <div class="h-full <?php echo $bar_color; ?> rounded-full transition-all" style="width:<?php echo $bar_width; ?>%"></div>
        </div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-sm text-xs text-on-surface-variant mb-md">
            <?php if ($st['course_name']): ?>
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">school</span><?php echo htmlspecialchars($st['course_name']); ?></span>
            <?php endif; ?>
            <?php if ($st['session_name']): ?>
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">schedule</span><?php echo htmlspecialchars($st['session_name']); ?></span>
            <?php endif; ?>
            <?php if ($st['gender']): ?>
            <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">person</span><?php echo htmlspecialchars($st['gender']); ?></span>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-between text-xs text-on-surface-variant border-t border-outline-variant/20 pt-md">
            <span><?php echo $st['sub_count']; ?> submission<?php echo $st['sub_count'] != 1 ? 's' : ''; ?></span>
            <div class="flex gap-sm">
                <a href="messages.php?to=<?php echo $st['student_id']; ?>" class="flex items-center gap-xs bg-surface-container-high text-on-surface-variant px-sm py-1 rounded-lg hover:bg-secondary-container/30 transition-colors">
                    <span class="material-symbols-outlined text-sm">chat</span> Message
                </a>
                <a href="view_submission.php?student_id=<?php echo $st['student_id']; ?>" class="flex items-center gap-xs bg-primary-container/20 text-primary px-sm py-1 rounded-lg hover:bg-primary-container/30 transition-colors">
                    <span class="material-symbols-outlined text-sm">grading</span> Grades
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center py-2xl text-on-surface-variant">
    <span class="material-symbols-outlined text-5xl mb-md opacity-40">group</span>
    <p class="text-sm font-medium"><?php echo $search ? 'No students match your search.' : 'No students enrolled in your course units yet.'; ?></p>
</div>
<?php endif; ?>
</main>
<?php require_once 'includes/footer.php'; ?>
