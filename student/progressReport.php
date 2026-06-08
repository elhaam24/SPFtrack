<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);

// All submissions with scores
$sub_q = mysqli_query($conn,
    "SELECT sub.score, sub.submission_date, a.max_score, cu.courseUnit_name, cu.courseUnit_id
     FROM submission sub
     LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE sub.student_id = $student_id AND sub.score IS NOT NULL
     ORDER BY sub.submission_date ASC");
$submissions = [];
while ($row = mysqli_fetch_assoc($sub_q)) $submissions[] = $row;

// Per-unit stats
$unit_stats = [];
foreach ($submissions as $s) {
    $key = $s['courseUnit_name'] ?? 'Unknown';
    if (!isset($unit_stats[$key])) $unit_stats[$key] = ['scores'=>[], 'max'=>0];
    $unit_stats[$key]['scores'][] = floatval($s['score']);
    $unit_stats[$key]['max']      = max($unit_stats[$key]['max'], floatval($s['max_score']));
}

// Overall stats
$all_scores = !empty($unit_stats) ? array_merge(...array_map(fn($u) => $u['scores'], array_values($unit_stats))) : [];
$all_scores = array_filter($all_scores);
$overall_avg = count($all_scores) > 0 ? round(array_sum($all_scores) / count($all_scores), 1) : null;
$highest     = count($all_scores) > 0 ? max($all_scores) : null;

// Progress reports from DB
$prog_q = mysqli_query($conn,
    "SELECT pr.*, cu.courseUnit_name FROM progressreport pr
     LEFT JOIN courseunit cu ON pr.courseUnit_id = cu.courseUnit_id
     WHERE pr.student_id = $student_id");
$progress_reports = [];
while ($row = mysqli_fetch_assoc($prog_q)) $progress_reports[] = $row;

// Enrollment
$enroll_q = mysqli_query($conn,
    "SELECT e.*, sem.semester_name, ay.year_name FROM enrollment e
     LEFT JOIN semester sem ON e.semester_id=sem.semester_id
     LEFT JOIN academic_year ay ON e.year_id=ay.year_id
     WHERE e.student_id=$student_id ORDER BY e.enrollment_id DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_q);

// Monthly scores for chart (last 6 months)
$monthly_q = mysqli_query($conn,
    "SELECT DATE_FORMAT(sub.submission_date, '%b') AS month,
            DATE_FORMAT(sub.submission_date, '%Y-%m') AS ym,
            AVG(sub.score) AS avg_score
     FROM submission sub
     LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE sub.student_id = $student_id AND sub.score IS NOT NULL
       AND sub.submission_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY ym, month
     ORDER BY ym ASC");
$monthly = [];
while ($row = mysqli_fetch_assoc($monthly_q)) $monthly[] = $row;
?>

<main class="max-w-5xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Academic Insights</p>
        <h2 class="font-h1 text-h1 text-on-surface">Progress Report</h2>
        <p class="font-body-md text-on-surface-variant max-w-lg">
            A detailed breakdown of your academic performance
            <?php if ($enrollment): ?>for <?php echo htmlspecialchars($enrollment['semester_name'] . ', ' . $enrollment['year_name']); ?><?php endif; ?>.
        </p>
    </section>

    <!-- Overview bento -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-md">
        <!-- Avg score card -->
        <div class="md:col-span-1 bg-surface-container-lowest p-md rounded-xl shadow-level-1 border border-outline-variant/10 flex flex-col justify-between">
            <div>
                <span class="material-symbols-outlined text-primary mb-sm block">grade</span>
                <h3 class="font-label-caps text-label-caps text-on-surface-variant">Average Score</h3>
            </div>
            <div class="mt-lg">
                <p class="font-h1 text-h1 text-on-surface"><?php echo $overall_avg !== null ? $overall_avg : '—'; ?></p>
                <?php if ($highest !== null): ?>
                <p class="font-body-sm text-primary mt-1">Best: <?php echo $highest; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Monthly chart -->
        <div class="md:col-span-3 bg-surface-container-lowest p-md rounded-xl shadow-level-1 border border-outline-variant/10">
            <div class="flex justify-between items-center mb-lg">
                <h3 class="font-h3 text-h3 text-on-surface">Performance Over Time</h3>
                <span class="font-label-caps text-label-caps text-on-surface-variant">Last 6 months</span>
            </div>
            <?php if (!empty($monthly)): ?>
            <canvas id="monthlyChart" style="max-height:200px;"></canvas>
            <?php else: ?>
            <div class="flex items-center justify-center h-32 text-on-surface-variant">
                <p class="font-body-md">No graded submissions yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Subject breakdown -->
    <?php if (!empty($unit_stats)): ?>
    <section class="space-y-md">
        <h3 class="font-h3 text-h3 text-on-surface">Subject Performance</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <?php foreach ($unit_stats as $unit_name => $stats):
                $avg = count($stats['scores']) > 0 ? round(array_sum($stats['scores']) / count($stats['scores']), 1) : 0;
                $pct = $stats['max'] > 0 ? round(($avg / $stats['max']) * 100) : 0;
                $grade = $pct >= 90 ? 'A+' : ($pct >= 80 ? 'A' : ($pct >= 70 ? 'B' : ($pct >= 60 ? 'C' : 'D')));
                $grade_color = $pct >= 80 ? 'text-primary' : ($pct >= 60 ? 'text-tertiary' : 'text-error');
                $bar_color   = $pct >= 80 ? 'progress-gradient' : ($pct >= 60 ? 'bg-tertiary' : 'bg-error');
            ?>
            <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border border-outline-variant/10 space-y-md">
                <div class="flex justify-between items-start">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary-container/20 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                        <div>
                            <h4 class="font-body-lg font-semibold text-on-surface"><?php echo htmlspecialchars($unit_name); ?></h4>
                            <p class="font-body-sm text-on-surface-variant"><?php echo count($stats['scores']); ?> graded submission<?php echo count($stats['scores']) !== 1 ? 's' : ''; ?></p>
                        </div>
                    </div>
                    <p class="font-h3 text-h3 <?php echo $grade_color; ?>"><?php echo $grade; ?></p>
                </div>
                <div class="space-y-xs">
                    <div class="flex justify-between font-label-caps text-[10px] text-on-surface-variant">
                        <span>AVG SCORE</span>
                        <span><?php echo $avg; ?>/<?php echo $stats['max']; ?> (<?php echo $pct; ?>%)</span>
                    </div>
                    <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                        <div class="h-full rounded-full <?php echo $bar_color; ?>" style="width:<?php echo $pct; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Progress reports from DB -->
    <?php if (!empty($progress_reports)): ?>
    <section class="space-y-md">
        <h3 class="font-h3 text-h3 text-on-surface">Teacher Progress Reports</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <?php foreach ($progress_reports as $pr):
                $level_color = match(strtolower($pr['progress_level'] ?? '')) {
                    'excellent','high' => 'text-primary bg-primary-container/20',
                    'good','medium'    => 'text-tertiary bg-tertiary-container/20',
                    default            => 'text-error bg-error-container/20'
                };
            ?>
            <div class="bg-surface-container-lowest p-md rounded-xl shadow-level-1 border border-surface-container">
                <div class="flex justify-between items-start">
                    <h4 class="font-body-md font-semibold text-on-surface"><?php echo htmlspecialchars($pr['courseUnit_name'] ?? 'Course Unit'); ?></h4>
                    <?php if ($pr['progress_level']): ?>
                    <span class="px-2 py-1 rounded-full font-label-caps text-[10px] <?php echo $level_color; ?> capitalize"><?php echo $pr['progress_level']; ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($pr['average_score'] !== null): ?>
                <p class="font-h2 text-h2 text-primary mt-2"><?php echo $pr['average_score']; ?></p>
                <p class="font-label-caps text-label-caps text-on-surface-variant">AVERAGE SCORE</p>
                <?php endif; ?>
                <p class="font-body-sm text-on-surface-variant mt-2">Updated: <?php echo date('M d, Y', strtotime($pr['last_update'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Smart insight -->
    <?php if ($overall_avg !== null): ?>
    <section class="bg-inverse-surface text-inverse-on-surface p-lg rounded-xl shadow-level-2 relative overflow-hidden">
        <div class="relative z-10 space-y-md">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed">lightbulb</span>
                <h3 class="font-h3 text-h3 text-white">Smart Insight</h3>
            </div>
            <p class="font-body-md text-inverse-on-surface/80 max-w-xl">
                <?php if ($overall_avg >= 80): ?>
                    Great work, <strong class="text-primary-fixed"><?php echo $student_name; ?></strong>! Your average score of <strong class="text-primary-fixed"><?php echo $overall_avg; ?></strong> puts you in excellent standing. Keep up the momentum!
                <?php elseif ($overall_avg >= 60): ?>
                    You're making good progress with an average of <strong class="text-primary-fixed"><?php echo $overall_avg; ?></strong>. Focus on your weaker units to push your scores higher.
                <?php else: ?>
                    Your current average is <strong class="text-primary-fixed"><?php echo $overall_avg; ?></strong>. Consider reaching out to your teachers for extra support to improve your performance.
                <?php endif; ?>
            </p>
            <a href="messages.php" class="inline-block bg-primary-container text-on-primary-container px-lg py-sm rounded-xl font-button text-button hover:brightness-110 transition-all">
                Message a Teacher
            </a>
        </div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary/20 rounded-full -mr-20 -mt-20 blur-3xl pointer-events-none"></div>
    </section>
    <?php endif; ?>
</main>

<?php if (!empty($monthly)): ?>
<script>
const mCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(mCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly, 'month')); ?>,
        datasets: [{
            label: 'Avg Score',
            data: <?php echo json_encode(array_map(fn($m) => round(floatval($m['avg_score']),1), $monthly)); ?>,
            borderColor: '#2ecc71',
            backgroundColor: 'rgba(46,204,113,0.1)',
            borderWidth: 2,
            pointBackgroundColor: '#006d37',
            pointRadius: 5,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100, grid: { color: 'rgba(100,116,139,0.15)' }, ticks: { color: '#3d4a3e' } },
            x: { grid: { display: false }, ticks: { color: '#3d4a3e' } }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
