<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$filter_asgn = isset($_GET['assignment_id']) && is_numeric($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where = "cu.teacher_id = $tid";
if ($filter_asgn) $where .= " AND a.assignment_id = $filter_asgn";
if ($filter_status) $where .= " AND s.submission_status = '$filter_status'";

$submissions = mysqli_query($conn,
    "SELECT s.submission_id, s.submission_date, s.submission_status, s.score, s.comments,
            s.submission_file,
            st.student_name, st.student_email,
            a.assignment_title, a.max_score, a.due_date,
            cu.courseUnit_name, cu.courseUnit_code
     FROM submission s
     JOIN assignment a  ON a.assignment_id  = s.assignment_id
     JOIN courseunit cu ON cu.courseUnit_id  = a.courseUnit_id
     JOIN student st    ON st.student_id     = s.student_id
     WHERE $where
     ORDER BY s.submission_date DESC");

// Stats
$total_q   = mysqli_query($conn, "SELECT COUNT(*) AS c FROM submission s JOIN assignment a ON a.assignment_id=s.assignment_id JOIN courseunit cu ON cu.courseUnit_id=a.courseUnit_id WHERE cu.teacher_id=$tid");
$total     = mysqli_fetch_assoc($total_q)['c'] ?? 0;
$pending_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM submission s JOIN assignment a ON a.assignment_id=s.assignment_id JOIN courseunit cu ON cu.courseUnit_id=a.courseUnit_id WHERE cu.teacher_id=$tid AND s.submission_status='pending'");
$pending   = mysqli_fetch_assoc($pending_q)['c'] ?? 0;
$graded_q  = mysqli_query($conn, "SELECT COUNT(*) AS c FROM submission s JOIN assignment a ON a.assignment_id=s.assignment_id JOIN courseunit cu ON cu.courseUnit_id=a.courseUnit_id WHERE cu.teacher_id=$tid AND s.submission_status='graded'");
$graded    = mysqli_fetch_assoc($graded_q)['c'] ?? 0;
$avg_q     = mysqli_query($conn, "SELECT AVG(s.score) AS avg FROM submission s JOIN assignment a ON a.assignment_id=s.assignment_id JOIN courseunit cu ON cu.courseUnit_id=a.courseUnit_id WHERE cu.teacher_id=$tid AND s.score IS NOT NULL");
$avg_score = round(mysqli_fetch_assoc($avg_q)['avg'] ?? 0, 1);
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="mb-xl">
    <h1 class="text-2xl font-bold text-on-surface">Submissions & Grading</h1>
    <p class="text-on-surface-variant text-sm mt-0.5">Review and grade student submissions</p>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-md mb-xl">
    <div class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-outline-variant/20 text-center">
        <div class="text-2xl font-bold text-on-surface"><?php echo $total; ?></div>
        <div class="text-xs text-on-surface-variant mt-0.5">Total</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-outline-variant/20 text-center">
        <div class="text-2xl font-bold text-tertiary"><?php echo $pending; ?></div>
        <div class="text-xs text-on-surface-variant mt-0.5">Pending</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-outline-variant/20 text-center">
        <div class="text-2xl font-bold text-primary"><?php echo $graded; ?></div>
        <div class="text-xs text-on-surface-variant mt-0.5">Graded</div>
    </div>
    <div class="bg-surface-container-lowest rounded-xl p-md shadow-sm border border-outline-variant/20 text-center">
        <div class="text-2xl font-bold text-on-surface"><?php echo $avg_score; ?></div>
        <div class="text-xs text-on-surface-variant mt-0.5">Avg Score</div>
    </div>
</div>

<!-- Filters -->
<div class="flex flex-wrap gap-sm mb-lg">
    <a href="view_submission.php" class="px-md py-xs rounded-full text-sm font-medium <?php echo !$filter_status && !$filter_asgn ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">All</a>
    <a href="view_submission.php?status=pending" class="px-md py-xs rounded-full text-sm font-medium <?php echo $filter_status=='pending' ? 'bg-tertiary text-on-tertiary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">Pending <?php if($pending>0): ?><span class="ml-1 bg-white/30 px-1 rounded-full text-xs"><?php echo $pending; ?></span><?php endif; ?></a>
    <a href="view_submission.php?status=graded" class="px-md py-xs rounded-full text-sm font-medium <?php echo $filter_status=='graded' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">Graded</a>
</div>

<!-- Table -->
<div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
    <?php if (mysqli_num_rows($submissions) > 0): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-lg py-sm text-left font-medium">Student</th>
                    <th class="px-lg py-sm text-left font-medium">Assignment</th>
                    <th class="px-lg py-sm text-left font-medium hidden md:table-cell">Course Unit</th>
                    <th class="px-lg py-sm text-left font-medium hidden lg:table-cell">Submitted</th>
                    <th class="px-lg py-sm text-left font-medium">Status</th>
                    <th class="px-lg py-sm text-left font-medium">Score</th>
                    <th class="px-lg py-sm text-right font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                <?php while ($row = mysqli_fetch_assoc($submissions)): ?>
                <tr class="hover:bg-surface-container-low/50 transition-colors">
                    <td class="px-lg py-md">
                        <div class="flex items-center gap-sm">
                            <div class="w-8 h-8 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="font-medium text-on-surface"><?php echo htmlspecialchars($row['student_name']); ?></p>
                                <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($row['student_email']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-lg py-md">
                        <p class="text-on-surface"><?php echo htmlspecialchars($row['assignment_title']); ?></p>
                        <p class="text-xs text-on-surface-variant">Max: <?php echo $row['max_score']; ?> pts</p>
                    </td>
                    <td class="px-lg py-md text-on-surface-variant text-xs hidden md:table-cell"><?php echo htmlspecialchars($row['courseUnit_name']); ?></td>
                    <td class="px-lg py-md text-on-surface-variant text-xs hidden lg:table-cell"><?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?></td>
                    <td class="px-lg py-md">
                        <?php
                        $s = $row['submission_status'];
                        $cls = match($s) {
                            'graded'  => 'bg-primary-container/20 text-primary',
                            'pending' => 'bg-tertiary-fixed/50 text-tertiary',
                            default   => 'bg-surface-container text-on-surface-variant',
                        };
                        ?>
                        <span class="<?php echo $cls; ?> text-xs font-semibold px-2 py-0.5 rounded-full capitalize"><?php echo $s; ?></span>
                    </td>
                    <td class="px-lg py-md">
                        <?php if ($row['score'] !== null): ?>
                        <span class="font-semibold text-on-surface"><?php echo $row['score']; ?></span>
                        <span class="text-xs text-on-surface-variant">/ <?php echo $row['max_score']; ?></span>
                        <?php else: ?>
                        <span class="text-on-surface-variant text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-lg py-md text-right">
                        <a href="grade_submission.php?id=<?php echo $row['submission_id']; ?>" class="<?php echo $row['submission_status']==='pending' ? 'bg-primary text-on-primary' : 'border border-primary text-primary'; ?> text-xs font-semibold px-md py-1 rounded-lg hover:opacity-90 transition-opacity">
                            <?php echo $row['submission_status']==='pending' ? 'Grade' : 'View/Edit'; ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="flex flex-col items-center justify-center py-2xl text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl mb-md opacity-40">inbox</span>
        <p class="text-sm">No submissions found.</p>
    </div>
    <?php endif; ?>
</div>
</main>

<?php require_once 'includes/footer.php'; ?>
