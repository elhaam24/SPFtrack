<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$success = $error = '';

// Fetch teacher data
$teacher = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT t.*, d.department_name
     FROM teacher t
     LEFT JOIN department d ON d.department_id = t.department_id
     WHERE t.teacher_id = $tid"));

// ── Handle Profile Update ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = mysqli_real_escape_string($conn, trim($_POST['teacher_name']));
    $phone   = mysqli_real_escape_string($conn, trim($_POST['phone_number']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);

    if (empty($name)) {
        $error = 'Name is required.';
    } else {
        $upd = mysqli_query($conn,
            "UPDATE teacher SET teacher_name='$name', phone_number='$phone', address='$address', gender='$gender'
             WHERE teacher_id=$tid");
        if ($upd) {
            $_SESSION['teacher_name'] = $name;
            $teacher['teacher_name']  = $name;
            $teacher['phone_number']  = $phone;
            $teacher['address']       = $address;
            $teacher['gender']        = $gender;
            $success = 'Profile updated successfully.';
        } else {
            $error = 'Update failed: ' . mysqli_error($conn);
        }
    }
}

// ── Handle Password Change ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if ($current !== $teacher['teacher_password']) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_pass) < 4) {
        $error = 'New password must be at least 4 characters.';
    } elseif ($new_pass !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $new_esc = mysqli_real_escape_string($conn, $new_pass);
        $upd = mysqli_query($conn, "UPDATE teacher SET teacher_password='$new_esc' WHERE teacher_id=$tid");
        $success = $upd ? 'Password changed successfully.' : 'Error: ' . mysqli_error($conn);
    }
}
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="mb-xl">
    <h1 class="text-2xl font-bold text-on-surface">My Profile</h1>
    <p class="text-on-surface-variant text-sm mt-0.5">Manage your account information</p>
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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">

    <!-- Profile Card -->
    <div class="lg:col-span-4 space-y-gutter">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg text-center">
            <div class="w-20 h-20 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-3xl mx-auto mb-md">
                <?php echo strtoupper(substr($teacher['teacher_name'], 0, 1)); ?>
            </div>
            <h2 class="font-bold text-on-surface text-lg"><?php echo htmlspecialchars($teacher['teacher_name']); ?></h2>
            <p class="text-sm text-on-surface-variant"><?php echo htmlspecialchars($teacher['teacher_email']); ?></p>
            <span class="inline-block mt-sm bg-primary-container/20 text-primary text-xs font-semibold px-md py-xs rounded-full capitalize"><?php echo htmlspecialchars($teacher['user_type'] ?? 'teacher'); ?></span>

            <div class="mt-lg space-y-sm text-sm text-left">
                <?php if ($teacher['department_name']): ?>
                <div class="flex items-center gap-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base">business</span>
                    <span><?php echo htmlspecialchars($teacher['department_name']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($teacher['phone_number']): ?>
                <div class="flex items-center gap-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base">phone</span>
                    <span><?php echo htmlspecialchars($teacher['phone_number']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($teacher['address']): ?>
                <div class="flex items-center gap-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base">location_on</span>
                    <span><?php echo htmlspecialchars($teacher['address']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($teacher['gender']): ?>
                <div class="flex items-center gap-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base">person</span>
                    <span><?php echo htmlspecialchars($teacher['gender']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($teacher['date_joined']): ?>
                <div class="flex items-center gap-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base">calendar_today</span>
                    <span>Joined <?php echo date('M j, Y', strtotime($teacher['date_joined'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="lg:col-span-8 space-y-gutter">

        <!-- Edit Profile -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-lg flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">edit</span>
                Edit Profile
            </h2>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-on-surface mb-xs">Full Name <span class="text-error">*</span></label>
                    <input type="text" name="teacher_name" value="<?php echo htmlspecialchars($teacher['teacher_name']); ?>" required
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-on-surface mb-xs">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($teacher['teacher_email']); ?>" disabled
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container text-on-surface-variant cursor-not-allowed">
                    <p class="text-xs text-on-surface-variant mt-xs">Email cannot be changed. Contact admin.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Phone Number</label>
                    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($teacher['phone_number'] ?? ''); ?>"
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Gender</label>
                    <select name="gender" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary outline-none">
                        <option value="">Select…</option>
                        <option value="Male"   <?php echo $teacher['gender']==='Male'   ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $teacher['gender']==='Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other"  <?php echo $teacher['gender']==='Other'  ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-on-surface mb-xs">Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($teacher['address'] ?? ''); ?>"
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" name="update_profile" class="bg-primary text-on-primary px-xl py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 p-lg">
            <h2 class="font-semibold text-on-surface mb-lg flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">lock</span>
                Change Password
            </h2>
            <form method="POST" class="space-y-md max-w-md">
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">New Password</label>
                    <input type="password" name="new_password" required minlength="4"
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-on-surface mb-xs">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                           class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <button type="submit" name="change_password" class="bg-primary text-on-primary px-xl py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
