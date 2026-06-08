<?php
require_once 'includes/header.php';

$course_id   = intval($student['course_id'] ?? 0);
$filter_unit = isset($_GET['unit']) ? intval($_GET['unit']) : 0;
$search      = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Build WHERE
$where = "cu.course_id = $course_id";
if ($filter_unit > 0) $where .= " AND m.courseUnit_id = $filter_unit";
if ($search) $where .= " AND (m.material_title LIKE '%$search%' OR m.material_description LIKE '%$search%')";

$mat_q = mysqli_query($conn,
    "SELECT m.*, cu.courseUnit_name, t.teacher_name
     FROM materials m
     LEFT JOIN courseunit cu ON m.courseUnit_id = cu.courseUnit_id
     LEFT JOIN teacher t ON m.teacher_id = t.teacher_id
     WHERE $where
     ORDER BY m.upload_date DESC");
$materials = [];
while ($row = mysqli_fetch_assoc($mat_q)) $materials[] = $row;

// Units for filter chips
$units_q = mysqli_query($conn, "SELECT courseUnit_id, courseUnit_name FROM courseunit WHERE course_id=$course_id ORDER BY courseUnit_name");
$units = [];
while ($row = mysqli_fetch_assoc($units_q)) $units[] = $row;

// Group by unit
$by_unit = [];
foreach ($materials as $m) {
    $key = $m['courseUnit_name'] ?? 'General';
    $by_unit[$key][] = $m;
}

function file_icon($type) {
    $map = ['pdf'=>'picture_as_pdf','doc'=>'description','docx'=>'description','zip'=>'folder_zip','txt'=>'article','jpg'=>'image','jpeg'=>'image','png'=>'image','mp4'=>'movie','mp3'=>'audio_file'];
    return $map[strtolower($type ?? '')] ?? 'attach_file';
}
function file_icon_color($type) {
    $map = ['pdf'=>'text-error bg-error-container/20','doc'=>'text-secondary bg-secondary-container/30','docx'=>'text-secondary bg-secondary-container/30','zip'=>'text-tertiary bg-tertiary-container/20','jpg'=>'text-primary bg-primary-container/20','jpeg'=>'text-primary bg-primary-container/20','png'=>'text-primary bg-primary-container/20'];
    return $map[strtolower($type ?? '')] ?? 'text-on-surface-variant bg-surface-container';
}
function format_size($bytes) {
    if (!$bytes) return '';
    if ($bytes >= 1048576) return round($bytes/1048576,1) . ' MB';
    if ($bytes >= 1024) return round($bytes/1024,1) . ' KB';
    return $bytes . ' B';
}
?>

<main class="max-w-5xl mx-auto px-margin_mobile py-lg space-y-lg">
    <!-- Header & Search -->
    <section class="space-y-md">
        <div class="space-y-xs">
            <p class="font-label-caps text-label-caps text-primary uppercase">Resources</p>
            <h2 class="font-h1 text-h1 text-on-surface">Study Materials</h2>
            <p class="font-body-md text-on-surface-variant">Access course resources uploaded by your teachers.</p>
        </div>
        <form method="GET" class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
            <input name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                class="w-full pl-12 pr-4 py-4 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary-container/20 transition-all outline-none font-body-md"
                placeholder="Search materials...">
            <?php if ($filter_unit): ?><input type="hidden" name="unit" value="<?php echo $filter_unit; ?>"><?php endif; ?>
        </form>
        <!-- Unit filter chips -->
        <div class="flex gap-2 overflow-x-auto pb-1">
            <a href="materials.php<?php echo $search ? '?search='.urlencode($_GET['search']??'') : ''; ?>"
               class="px-4 py-2 rounded-full font-button text-button whitespace-nowrap <?php echo $filter_unit === 0 ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container hover:bg-secondary-fixed'; ?>">
                All Units
            </a>
            <?php foreach ($units as $u): ?>
            <a href="materials.php?unit=<?php echo $u['courseUnit_id']; ?><?php echo $search ? '&search='.urlencode($_GET['search']??'') : ''; ?>"
               class="px-4 py-2 rounded-full font-button text-button whitespace-nowrap <?php echo $filter_unit === intval($u['courseUnit_id']) ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container hover:bg-secondary-fixed'; ?>">
                <?php echo htmlspecialchars($u['courseUnit_name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (empty($materials)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3">menu_book</span>
        <p class="font-body-lg font-semibold text-on-surface">No materials found.</p>
        <p class="font-body-md mt-1">Your teachers haven't uploaded any materials yet.</p>
    </div>
    <?php else: ?>
    <!-- Grouped by unit -->
    <?php foreach ($by_unit as $unit_name => $mats): ?>
    <section class="space-y-md">
        <div class="flex items-center justify-between">
            <h3 class="font-h3 text-h3 text-primary"><?php echo htmlspecialchars($unit_name); ?></h3>
            <span class="font-label-caps text-label-caps text-on-surface-variant"><?php echo count($mats); ?> file<?php echo count($mats) !== 1 ? 's' : ''; ?></span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            <?php foreach ($mats as $m):
                $icon  = file_icon($m['file_type']);
                $icolor = file_icon_color($m['file_type']);
                $size  = format_size($m['file_size']);
            ?>
            <div class="flex items-center gap-md p-md bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container hover:shadow-level-2 transition-all">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 <?php echo $icolor; ?>">
                    <span class="material-symbols-outlined text-2xl"><?php echo $icon; ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-body-md font-bold truncate text-on-surface"><?php echo htmlspecialchars($m['material_title']); ?></h4>
                    <?php if ($m['material_description']): ?>
                    <p class="font-body-sm text-on-surface-variant truncate"><?php echo htmlspecialchars($m['material_description']); ?></p>
                    <?php endif; ?>
                    <p class="font-body-sm text-on-surface-variant mt-0.5">
                        <?php echo strtoupper($m['file_type'] ?? ''); ?>
                        <?php echo $size ? ' &bull; ' . $size : ''; ?>
                        &bull; <?php echo date('M d, Y', strtotime($m['upload_date'])); ?>
                    </p>
                </div>
                <a href="../<?php echo htmlspecialchars($m['file_path']); ?>" target="_blank" download
                   class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center hover:bg-primary-container/20 transition-colors flex-shrink-0">
                    <span class="material-symbols-outlined text-primary">download</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
