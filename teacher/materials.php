<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$success = $error = '';
$upload_dir = '../uploads/materials/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// ── Handle Upload ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $cu_id = intval($_POST['courseUnit_id']);
    $title = mysqli_real_escape_string($conn, trim($_POST['material_title']));
    $desc  = mysqli_real_escape_string($conn, trim($_POST['material_description']));

    // Verify course unit belongs to this teacher
    $chk = mysqli_query($conn, "SELECT courseUnit_id FROM courseunit WHERE courseUnit_id=$cu_id AND teacher_id=$tid");
    if (mysqli_num_rows($chk) === 0) {
        $error = 'Invalid course unit.';
    } elseif (empty($title)) {
        $error = 'Title is required.';
    } elseif (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        $file     = $_FILES['material_file'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','zip','png','jpg','jpeg'];
        if (!in_array($ext, $allowed)) {
            $error = 'File type not allowed. Allowed: ' . implode(', ', $allowed);
        } elseif ($file['size'] > 20 * 1024 * 1024) {
            $error = 'File too large. Max 20MB.';
        } else {
            $filename  = 'material_' . $tid . '_' . $cu_id . '_' . time() . '.' . $ext;
            $dest      = $upload_dir . $filename;
            $file_path = 'uploads/materials/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $ftype = $file['type'];
                $fsize = $file['size'];
                $ins = mysqli_query($conn,
                    "INSERT INTO materials (courseUnit_id, teacher_id, material_title, material_description, file_path, file_type, file_size)
                     VALUES ($cu_id, $tid, '$title', '$desc', '$file_path', '$ftype', $fsize)");
                $success = $ins ? 'Material uploaded successfully.' : 'DB error: ' . mysqli_error($conn);
            } else {
                $error = 'Failed to save file. Check upload directory permissions.';
            }
        }
    }
}

// ── Handle Delete ───────────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $mat = mysqli_query($conn, "SELECT file_path FROM materials WHERE material_id=$del_id AND teacher_id=$tid");
    if (mysqli_num_rows($mat) > 0) {
        $mat_row = mysqli_fetch_assoc($mat);
        $full_path = '../' . $mat_row['file_path'];
        if (file_exists($full_path)) @unlink($full_path);
        mysqli_query($conn, "DELETE FROM materials WHERE material_id=$del_id AND teacher_id=$tid");
        $success = 'Material deleted.';
    } else {
        $error = 'Material not found.';
    }
}

// ── Fetch course units ──────────────────────────────────────────────────────
$units = mysqli_query($conn,
    "SELECT cu.courseUnit_id, cu.courseUnit_name, cu.courseUnit_code
     FROM courseunit cu WHERE cu.teacher_id=$tid ORDER BY cu.courseUnit_name");

$filter_cu = isset($_GET['cu']) && is_numeric($_GET['cu']) ? intval($_GET['cu']) : 0;
$where_cu  = $filter_cu ? "AND m.courseUnit_id=$filter_cu" : '';

// ── Fetch materials ─────────────────────────────────────────────────────────
$materials = mysqli_query($conn,
    "SELECT m.*, cu.courseUnit_name, cu.courseUnit_code
     FROM materials m
     JOIN courseunit cu ON cu.courseUnit_id = m.courseUnit_id
     WHERE m.teacher_id = $tid $where_cu
     ORDER BY m.upload_date DESC");

function format_bytes($bytes) {
    if ($bytes >= 1048576) return round($bytes/1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes/1024, 1) . ' KB';
    return $bytes . ' B';
}
function file_icon($type) {
    if (str_contains($type, 'pdf'))   return 'picture_as_pdf';
    if (str_contains($type, 'word') || str_contains($type, 'doc')) return 'description';
    if (str_contains($type, 'presentation') || str_contains($type, 'ppt')) return 'slideshow';
    if (str_contains($type, 'sheet') || str_contains($type, 'xls')) return 'table_chart';
    if (str_contains($type, 'image')) return 'image';
    if (str_contains($type, 'zip'))   return 'folder_zip';
    return 'attach_file';
}
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="flex items-center justify-between mb-xl">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">Materials</h1>
        <p class="text-on-surface-variant text-sm mt-0.5">Upload and manage course materials</p>
    </div>
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
            class="flex items-center gap-sm bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity shadow-sm">
        <span class="material-symbols-outlined text-lg">upload_file</span> Upload Material
    </button>
</div>

<?php if ($success): ?>
<div class="mb-lg bg-primary-container/20 border border-primary/20 text-primary px-lg py-md rounded-xl text-sm flex items-center gap-sm">
    <span class="material-symbols-outlined">check_circle</span> <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="mb-lg bg-error-container border border-error/20 text-on-error-container px-lg py-md rounded-xl text-sm flex items-center gap-sm">
    <span class="material-symbols-outlined">error</span> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Filter chips -->
<div class="flex flex-wrap gap-sm mb-lg">
    <a href="materials.php" class="px-md py-xs rounded-full text-sm font-medium <?php echo !$filter_cu ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">All</a>
    <?php
    mysqli_data_seek($units, 0);
    while ($u = mysqli_fetch_assoc($units)):
    ?>
    <a href="materials.php?cu=<?php echo $u['courseUnit_id']; ?>" class="px-md py-xs rounded-full text-sm font-medium <?php echo $filter_cu==$u['courseUnit_id'] ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'; ?> transition-colors">
        <?php echo htmlspecialchars($u['courseUnit_code']); ?>
    </a>
    <?php endwhile; ?>
</div>

<!-- Materials Grid -->
<?php if (mysqli_num_rows($materials) > 0): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-md">
    <?php while ($m = mysqli_fetch_assoc($materials)): ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg hover:shadow-md transition-shadow">
        <div class="flex items-start gap-md mb-md">
            <div class="w-10 h-10 bg-primary-container/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary"><?php echo file_icon($m['file_type'] ?? ''); ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-on-surface text-sm truncate"><?php echo htmlspecialchars($m['material_title']); ?></h3>
                <p class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($m['courseUnit_code']); ?> · <?php echo htmlspecialchars($m['courseUnit_name']); ?></p>
            </div>
        </div>
        <?php if ($m['material_description']): ?>
        <p class="text-xs text-on-surface-variant mb-md line-clamp-2"><?php echo htmlspecialchars($m['material_description']); ?></p>
        <?php endif; ?>
        <div class="flex items-center justify-between text-xs text-on-surface-variant mb-md">
            <span><?php echo $m['file_size'] ? format_bytes($m['file_size']) : '—'; ?></span>
            <span><?php echo date('M j, Y', strtotime($m['upload_date'])); ?></span>
        </div>
        <div class="flex gap-sm">
            <a href="../<?php echo htmlspecialchars($m['file_path']); ?>" target="_blank"
               class="flex-1 flex items-center justify-center gap-xs bg-primary-container/20 text-primary py-xs rounded-lg text-xs font-semibold hover:bg-primary-container/30 transition-colors">
                <span class="material-symbols-outlined text-sm">download</span> Download
            </a>
            <a href="materials.php?delete=<?php echo $m['material_id']; ?>" onclick="return confirm('Delete this material?')"
               class="flex items-center justify-center gap-xs bg-error-container/30 text-error px-sm py-xs rounded-lg text-xs font-semibold hover:bg-error-container/50 transition-colors">
                <span class="material-symbols-outlined text-sm">delete</span>
            </a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/20 flex flex-col items-center justify-center py-2xl text-on-surface-variant">
    <span class="material-symbols-outlined text-5xl mb-md opacity-40">folder_open</span>
    <p class="text-sm font-medium">No materials uploaded yet.</p>
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
            class="mt-lg bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
        Upload your first material
    </button>
</div>
<?php endif; ?>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-md">
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-lg border-b border-outline-variant/20">
            <h2 class="font-semibold text-on-surface text-lg">Upload Material</h2>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="p-1 rounded-full hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant">close</span>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-lg space-y-md">
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Course Unit <span class="text-error">*</span></label>
                <select name="courseUnit_id" required class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">Select course unit…</option>
                    <?php
                    mysqli_data_seek($units, 0);
                    while ($u = mysqli_fetch_assoc($units)):
                    ?>
                    <option value="<?php echo $u['courseUnit_id']; ?>" <?php echo $filter_cu==$u['courseUnit_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($u['courseUnit_name']); ?> (<?php echo htmlspecialchars($u['courseUnit_code']); ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Title <span class="text-error">*</span></label>
                <input type="text" name="material_title" required placeholder="e.g. Week 3 Lecture Notes" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Description</label>
                <textarea name="material_description" rows="2" placeholder="Brief description…" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">File <span class="text-error">*</span></label>
                <input type="file" name="material_file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.png,.jpg,.jpeg"
                       class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary outline-none file:mr-md file:py-xs file:px-md file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-container/20 file:text-primary hover:file:bg-primary-container/30">
                <p class="text-xs text-on-surface-variant mt-xs">Max 20MB. Allowed: PDF, DOC, PPT, XLS, TXT, ZIP, images</p>
            </div>
            <div class="flex gap-md pt-sm">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="flex-1 border border-outline-variant text-on-surface-variant px-lg py-sm rounded-xl text-sm font-medium hover:bg-surface-container transition-colors">Cancel</button>
                <button type="submit" name="upload_material" class="flex-1 bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">Upload</button>
            </div>
        </form>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
