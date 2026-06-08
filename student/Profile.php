<?php
require_once 'includes/header.php';

$success_msg = '';
$error_msg   = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone   = mysqli_real_escape_string($conn, trim($_POST['phone_number'] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));

    // Password change (optional)
    $pw_sql = '';
    if (!empty($_POST['new_password'])) {
        $current_pw = $_POST['current_password'] ?? '';
        // Verify current password
        $pw_chk = mysqli_query($conn, "SELECT student_password FROM student WHERE student_id=$student_id");
        $pw_row = mysqli_fetch_assoc($pw_chk);
        if ($pw_row['student_password'] !== $current_pw) {
            $error_msg = "Current password is incorrect.";
        } elseif (strlen($_POST['new_password']) < 6) {
            $error_msg = "New password must be at least 6 characters.";
        } else {
            $new_pw = mysqli_real_escape_string($conn, $_POST['new_password']);
            $pw_sql = ", student_password='$new_pw'";
        }
    }

    if (!$error_msg) {
        mysqli_query($conn,
            "UPDATE student SET phone_number='$phone', address='$address' $pw_sql WHERE student_id=$student_id");
        $success_msg = "Profile updated successfully.";
        // Refresh student data from DB
        $student_q = mysqli_query($conn, "SELECT s.*, c.course_name, ss.session_name FROM student s LEFT JOIN course c ON s.course_id=c.course_id LEFT JOIN session ss ON s.session_id=ss.session_id WHERE s.student_id=$student_id");
        $student = mysqli_fetch_assoc($student_q);
    }
}

// Enrollment info
$enroll_q = mysqli_query($conn,
    "SELECT e.*, sem.semester_name, ay.year_name FROM enrollment e
     LEFT JOIN semester sem ON e.semester_id=sem.semester_id
     LEFT JOIN academic_year ay ON e.year_id=ay.year_id
     WHERE e.student_id=$student_id ORDER BY e.enrollment_id DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_q);
?>

<main class="max-w-2xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Account</p>
        <h2 class="font-h1 text-h1 text-on-surface">My Profile</h2>
    </section>

    <?php if ($success_msg): ?>
    <div class="bg-primary-container/20 border border-primary/30 text-on-primary-container rounded-xl p-md flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">check_circle</span>
        <p class="font-body-md"><?php echo htmlspecialchars($success_msg); ?></p>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="bg-error-container border border-error/30 text-on-error-container rounded-xl p-md flex items-center gap-3">
        <span class="material-symbols-outlined text-error">error</span>
        <p class="font-body-md"><?php echo htmlspecialchars($error_msg); ?></p>
    </div>
    <?php endif; ?>

    <!-- Profile card -->
    <div class="bg-inverse-surface rounded-xl p-lg shadow-level-2 flex items-center gap-6">
        <div class="w-20 h-20 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container text-4xl font-bold flex-shrink-0">
            <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
        </div>
        <div>
            <h3 class="font-h2 text-h2 text-white"><?php echo htmlspecialchars($student['student_name']); ?></h3>
            <p class="font-body-md text-inverse-on-surface/80"><?php echo htmlspecialchars($student['student_email']); ?></p>
            <p class="font-body-sm text-primary-fixed mt-1"><?php echo htmlspecialchars($student['course_name'] ?? 'No course'); ?> &bull; <?php echo htmlspecialchars($student['session_name'] ?? ''); ?></p>
        </div>
    </div>

    <!-- Info grid -->
    <div class="grid grid-cols-2 gap-md">
        <?php
        $info_items = [
            ['icon'=>'badge',        'label'=>'Student ID',   'value'=>'STU-' . str_pad($student['student_id'], 4, '0', STR_PAD_LEFT)],
            ['icon'=>'cake',         'label'=>'Date of Birth','value'=>$student['date_of_birth'] ? date('M d, Y', strtotime($student['date_of_birth'])) : '—'],
            ['icon'=>'person',       'label'=>'Gender',       'value'=>$student['gender'] ?? '—'],
            ['icon'=>'school',       'label'=>'Enrolled',     'value'=>$enrollment ? ($enrollment['semester_name'] . ', ' . $enrollment['year_name']) : '—'],
        ];
        foreach ($info_items as $item):
        ?>
        <div class="bg-surface-container-lowest rounded-xl p-md shadow-level-1 border border-surface-container flex items-center gap-3">
            <span class="material-symbols-outlined text-primary"><?php echo $item['icon']; ?></span>
            <div>
                <p class="font-label-caps text-label-caps text-on-surface-variant"><?php echo $item['label']; ?></p>
                <p class="font-body-md font-semibold text-on-surface"><?php echo htmlspecialchars($item['value']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Edit form -->
    <form method="POST" class="bg-surface-container-lowest rounded-xl p-lg shadow-level-1 border border-surface-container space-y-lg">
        <h3 class="font-h2 text-h2 text-on-surface">Edit Profile</h3>

        <div class="space-y-sm">
            <label class="font-label-caps text-label-caps text-on-surface-variant block">PHONE NUMBER</label>
            <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($student['phone_number'] ?? ''); ?>"
                class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md text-on-surface"
                placeholder="e.g. 0712345678">
        </div>

        <div class="space-y-sm">
            <label class="font-label-caps text-label-caps text-on-surface-variant block">ADDRESS</label>
            <textarea name="address" rows="2"
                class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md text-on-surface resize-none"
                placeholder="Your address"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
        </div>

        <div class="border-t border-outline-variant pt-lg space-y-md">
            <h4 class="font-h3 text-h3 text-on-surface">Change Password <span class="font-body-sm text-on-surface-variant font-normal">(optional)</span></h4>
            <div class="space-y-sm">
                <label class="font-label-caps text-label-caps text-on-surface-variant block">CURRENT PASSWORD</label>
                <input type="password" name="current_password"
                    class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md text-on-surface"
                    placeholder="Enter current password">
            </div>
            <div class="space-y-sm">
                <label class="font-label-caps text-label-caps text-on-surface-variant block">NEW PASSWORD</label>
                <input type="password" name="new_password"
                    class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md text-on-surface"
                    placeholder="Min. 6 characters">
            </div>
        </div>

        <button type="submit" name="update_profile"
            class="w-full py-4 bg-primary text-on-primary rounded-xl font-button text-button hover:bg-on-primary-fixed-variant transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">save</span>
            Save Changes
        </button>
    </form>

    <!-- Logout -->
    <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-4 border-2 border-error/30 text-error rounded-xl font-button text-button hover:bg-error-container/20 transition-colors">
        <span class="material-symbols-outlined">logout</span>
        Logout
    </a>
</main>

<?php require_once 'includes/footer.php'; ?>
