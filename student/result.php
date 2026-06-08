<?php
require_once 'includes/header.php';

$course_id = intval($student['course_id'] ?? 0);

// All graded submissions
$sub_q = mysqli_query($conn,
    "SELECT sub.*, a.assignment_title, a.max_score, cu.courseUnit_name, cu.courseUnit_id
     FROM submission sub
     LEFT JOIN assignment a ON sub.assignment_id = a.assignment_id
     LEFT JOIN courseunit cu ON a.courseUnit_id = cu.courseUnit_id
     WHERE sub.student_id = $student_id
     ORDER BY sub.submission_date DESC");
$submissions = [];
while ($row = mysqli_fetch_assoc($sub_q)) $submissions[] = $row;

// Stats
$graded = array_filter($submissions, fn($s) => $s['score'] !== null);
$avg    = count($graded) > 0 ? round(array_sum(array_column(array_values($graded), 'score')) / count($graded), 1) : null;
$highest = count($graded) > 0 ? max(array_column(array_values($graded), 'score')) : null;
$lowest  = count($graded) > 0 ? min(array_column(array_values($graded), 'score')) : null;

// Per-unit averages for chart
$unit_avgs = [];
foreach ($submissions as $s) {
    if ($s['score'] !== null && $s['courseUnit_name']) {
        $key = $s['courseUnit_name'];
        if (!isset($unit_avgs[$key])) $unit_avgs[$key] = ['sum'=>0,'count'=>0,'max'=>0];
        $unit_avgs[$key]['sum']   += floatval($s['score']);
        $unit_avgs[$key]['count'] += 1;
        $unit_avgs[$key]['max']    = max($unit_avgs[$key]['max'], floatval($s['max_score']));
    }
}
?>

<main class="max-w-4xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Academic Performance</p>
        <h2 class="font-h1 text-h1 text-on-surface">Results</h2>
        <p class="font-body-md text-on-surface-variant">Your graded assignments and scores.</p>
    </section>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-md">
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-on-surface"><?php echo count($submissions); ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">TOTAL SUBMITTED</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-primary"><?php echo count($graded); ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">GRADED</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-on-surface"><?php echo $avg !== null ? $avg : '—'; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">AVG SCORE</p>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container text-center">
            <p class="font-h2 text-h2 text-primary"><?php echo $highest !== null ? $highest : '—'; ?></p>
            <p class="font-label-caps text-label-caps text-on-surface-variant">HIGHEST</p>
        </div>
    </div>

    <!-- Per-unit chart -->
    <?php if (!empty($unit_avgs)): ?>
    <div class="bg-inverse-surface rounded-xl p-lg shadow-level-2">
        <h3 class="font-h2 text-h2 text-inverse-on-surface mb-lg">Performance by Course Unit</h3>
        <canvas id="unitChart" style="max-height:280px;"></canvas>
    </div>
    <?php endif; ?>

    <!-- Results table -->
    <div class="space-y-md">
        <h3 class="font-h2 text-h2 text-on-surface">Submission Details</h3>
        <?php if (empty($submissions)): ?>
        <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-5xl block mb-3">grade</span>
            No results yet. Submit assignments to see your grades here.
        </div>
        <?php else: ?>
        <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container">
                        <tr>
                            <th class="text-left px-4 py-3 font-label-caps text-label-caps text-on-surface-variant">ASSIGNMENT</th>
                            <th class="text-left px-4 py-3 font-label-caps text-label-caps text-on-surface-variant hidden md:table-cell">UNIT</th>
                            <th class="text-left px-4 py-3 font-label-caps text-label-caps text-on-surface-variant hidden md:table-cell">DATE</th>
                            <th class="text-center px-4 py-3 font-label-caps text-label-caps text-on-surface-variant">SCORE</th>
                            <th class="text-center px-4 py-3 font-label-caps text-label-caps text-on-surface-variant">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        <?php foreach ($submissions as $sub):
                            $pct = ($sub['score'] !== null && $sub['max_score'] > 0)
                                ? round(($sub['score'] / $sub['max_score']) * 100) : null;
                            $grade_color = $pct === null ? 'text-on-surface-variant'
                                : ($pct >= 80 ? 'text-primary' : ($pct >= 60 ? 'text-tertiary' : 'text-error'));
                            $sc_map = ['graded'=>'bg-primary-container/20 text-primary','pending'=>'bg-tertiary-container/20 text-tertiary','submitted'=>'bg-secondary-container/30 text-secondary'];
                            $sc = $sc_map[$sub['submission_status']] ?? 'bg-surface-container text-on-surface-variant';
                        ?>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-body-md font-semibold text-on-surface"><?php echo htmlspecialchars($sub['assignment_title'] ?? ''); ?></p>
                                <p class="font-body-sm text-on-surface-variant md:hidden"><?php echo htmlspecialchars($sub['courseUnit_name'] ?? ''); ?></p>
                            </td>
                            <td class="px-4 py-3 font-body-sm text-on-surface-variant hidden md:table-cell"><?php echo htmlspecialchars($sub['courseUnit_name'] ?? ''); ?></td>
                            <td class="px-4 py-3 font-body-sm text-on-surface-variant hidden md:table-cell"><?php echo date('M d, Y', strtotime($sub['submission_date'])); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($sub['score'] !== null): ?>
                                <span class="font-h3 <?php echo $grade_color; ?>"><?php echo $sub['score']; ?></span>
                                <span class="font-body-sm text-on-surface-variant">/<?php echo $sub['max_score']; ?></span>
                                <?php if ($pct !== null): ?>
                                <div class="w-full h-1.5 bg-surface-container rounded-full mt-1 overflow-hidden">
                                    <div class="h-full rounded-full <?php echo $pct >= 80 ? 'bg-primary' : ($pct >= 60 ? 'bg-tertiary' : 'bg-error'); ?>" style="width:<?php echo $pct; ?>%"></div>
                                </div>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-on-surface-variant font-body-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full font-label-caps text-[10px] <?php echo $sc; ?> capitalize"><?php echo $sub['submission_status']; ?></span>
                                <?php if ($sub['comments']): ?>
                                <p class="font-body-sm text-on-surface-variant mt-1 text-left"><?php echo htmlspecialchars($sub['comments']); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php if (!empty($unit_avgs)): ?>
<script>
const unitCtx = document.getElementById('unitChart').getContext('2d');
new Chart(unitCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($unit_avgs)); ?>,
        datasets: [{
            label: 'Average Score',
            data: <?php echo json_encode(array_map(fn($u) => round($u['sum']/$u['count'],1), $unit_avgs)); ?>,
            backgroundColor: 'rgba(46,204,113,0.7)',
            borderColor: '#2ecc71',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, max: 100, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#eaf1ff' } },
            x: { grid: { display: false }, ticks: { color: '#eaf1ff' } }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
