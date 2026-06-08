<?php
if (session_id() == '') {
    session_start();
}
if (empty($_SESSION['teacher_login'])) {
    header('location:index.php');
    exit();
}
require_once __DIR__ . '/config.php';

$teacher_id   = intval($_SESSION['teacher_id']);
$teacher_name = htmlspecialchars($_SESSION['teacher_name'] ?? 'Teacher');

// Unread notification count for bell
$bell_count = 0;
$bell_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM notification WHERE teacher_id=$teacher_id AND notification_status='unread'");
if ($bell_q) { $bell_row = mysqli_fetch_assoc($bell_q); $bell_count = $bell_row['c'] ?? 0; }

// Unread message count
$msg_count = 0;
$msg_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM messages WHERE receiver_id=$teacher_id AND receiver_type='teacher' AND is_read=0");
if ($msg_q) { $msg_row = mysqli_fetch_assoc($msg_q); $msg_count = $msg_row['c'] ?? 0; }

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPF Track – Teacher Portal</title>
    <link rel="shortcut icon" href="../images/logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-container": "#005027",
                        "inverse-primary": "#4ae183",
                        "primary-fixed": "#6bfe9c",
                        "tertiary-container": "#d7ae00",
                        "tertiary": "#735c00",
                        "surface-tint": "#006d37",
                        "on-error-container": "#93000a",
                        "on-error": "#ffffff",
                        "secondary": "#545f73",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#00210c",
                        "surface": "#f8f9ff",
                        "surface-container-high": "#dce9ff",
                        "on-secondary-fixed-variant": "#3c475a",
                        "error": "#ba1a1a",
                        "inverse-surface": "#213145",
                        "primary-container": "#2ecc71",
                        "secondary-container": "#d5e0f8",
                        "on-tertiary": "#ffffff",
                        "surface-container-highest": "#d3e4fe",
                        "on-tertiary-fixed-variant": "#574500",
                        "tertiary-fixed": "#ffe084",
                        "on-surface-variant": "#3d4a3e",
                        "surface-container-low": "#eff4ff",
                        "secondary-fixed-dim": "#bcc7de",
                        "on-tertiary-container": "#544300",
                        "outline-variant": "#bbcbbb",
                        "on-secondary-container": "#586377",
                        "on-secondary": "#ffffff",
                        "surface-container": "#e5eeff",
                        "surface-bright": "#f8f9ff",
                        "primary": "#006d37",
                        "surface-dim": "#cbdbf5",
                        "background": "#f8f9ff",
                        "on-primary-fixed-variant": "#005228",
                        "tertiary-fixed-dim": "#eec209",
                        "primary-fixed-dim": "#4ae183",
                        "on-tertiary-fixed": "#231b00",
                        "on-surface": "#0b1c30",
                        "surface-variant": "#d3e4fe",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-fixed": "#111c2d",
                        "on-background": "#0b1c30",
                        "outline": "#6c7b6d",
                        "inverse-on-surface": "#eaf1ff",
                        "error-container": "#ffdad6"
                    },
                    borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
                    spacing: { gutter: "16px", xl: "32px", xs: "4px", lg: "24px", md: "16px", margin_mobile: "20px", sm: "8px" },
                    fontFamily: {
                        "body-lg": ["Inter"], "label-caps": ["Inter"], "body-md": ["Inter"],
                        "body-sm": ["Inter"], "button": ["Inter"], "h3": ["Lexend"], "h1": ["Lexend"], "h2": ["Lexend"]
                    },
                    fontSize: {
                        "body-lg": ["18px", {lineHeight:"28px",fontWeight:"400"}],
                        "label-caps": ["12px", {lineHeight:"16px",letterSpacing:"0.05em",fontWeight:"700"}],
                        "body-md": ["16px", {lineHeight:"24px",fontWeight:"400"}],
                        "body-sm": ["14px", {lineHeight:"20px",fontWeight:"400"}],
                        "button": ["14px", {lineHeight:"20px",fontWeight:"600"}],
                        "h3": ["20px", {lineHeight:"28px",fontWeight:"500"}],
                        "h1": ["30px", {lineHeight:"36px",letterSpacing:"-0.02em",fontWeight:"600"}],
                        "h2": ["24px", {lineHeight:"32px",letterSpacing:"-0.01em",fontWeight:"600"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .shadow-level-1 { box-shadow: 0 4px 12px rgba(30,41,59,0.05); }
        .shadow-level-2 { box-shadow: 0 12px 24px rgba(30,41,59,0.1); }
        /* Sidebar drawer */
        #sidebar-drawer { transition: transform 0.3s ease; }
        #sidebar-overlay { transition: opacity 0.3s ease; }
        .progress-gradient { background: linear-gradient(90deg, #2ecc71 0%, #006d37 100%); }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md antialiased min-h-screen pb-24 md:pb-0">

<!-- ===== TOP APP BAR ===== -->
<header class="bg-surface shadow-level-1 flex justify-between items-center px-margin_mobile h-16 w-full sticky top-0 z-50">
    <div class="flex items-center gap-4">
        <button id="sidebar-toggle" class="text-primary hover:bg-surface-container-low p-2 rounded-full transition-colors" aria-label="Open menu">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <a href="dashboard.php" class="font-h2 text-h2 font-semibold text-on-surface no-underline">SPF Track</a>
    </div>
    <div class="flex items-center gap-3">
        <!-- Notifications bell -->
        <a href="notification.php" class="relative p-2 rounded-full hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined text-on-surface-variant">notifications</span>
            <?php if ($bell_count > 0): ?>
            <span class="absolute top-1 right-1 w-4 h-4 bg-error text-white text-[9px] font-bold rounded-full flex items-center justify-center"><?php echo $bell_count; ?></span>
            <?php endif; ?>
        </a>
        <!-- Messages -->
        <a href="messages.php" class="relative p-2 rounded-full hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined text-on-surface-variant">chat_bubble</span>
            <?php if ($msg_count > 0): ?>
            <span class="absolute top-1 right-1 w-4 h-4 bg-error text-white text-[9px] font-bold rounded-full flex items-center justify-center"><?php echo $msg_count; ?></span>
            <?php endif; ?>
        </a>
        <!-- Profile avatar -->
        <a href="Profile.php" class="w-9 h-9 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-semibold text-sm border-2 border-primary hover:opacity-90 transition-opacity">
            <?php echo strtoupper(substr($teacher_name, 0, 1)); ?>
        </a>
    </div>
</header>

<!-- ===== SIDEBAR OVERLAY ===== -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0" onclick="closeSidebar()"></div>

<!-- ===== SIDEBAR DRAWER ===== -->
<aside id="sidebar-drawer" class="fixed top-0 left-0 h-full w-72 bg-surface z-50 shadow-level-2 transform -translate-x-full flex flex-col">
    <div class="flex items-center justify-between px-6 h-16 border-b border-outline-variant">
        <span class="font-h2 text-h2 font-semibold text-primary">SPF Track</span>
        <button onclick="closeSidebar()" class="p-2 rounded-full hover:bg-surface-container-low transition-colors">
            <span class="material-symbols-outlined text-on-surface-variant">close</span>
        </button>
    </div>
    <!-- Teacher info -->
    <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-lg">
                <?php echo strtoupper(substr($teacher_name, 0, 1)); ?>
            </div>
            <div>
                <p class="font-body-md font-semibold text-on-surface"><?php echo $teacher_name; ?></p>
                <p class="font-body-sm text-on-surface-variant">Teacher</p>
            </div>
        </div>
    </div>
    <!-- Nav links -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <?php
        $nav_items = [
            ['icon'=>'dashboard',        'label'=>'Dashboard',    'href'=>'dashboard.php'],
            ['icon'=>'menu_book',        'label'=>'Course Units', 'href'=>'courseUnit.php'],
            ['icon'=>'assignment',       'label'=>'Assignments',  'href'=>'assignment.php'],
            ['icon'=>'grading',          'label'=>'Submissions',  'href'=>'view_submission.php'],
            ['icon'=>'group',            'label'=>'Students',     'href'=>'students.php'],
            ['icon'=>'folder_open',      'label'=>'Materials',    'href'=>'materials.php'],
            ['icon'=>'chat_bubble',      'label'=>'Messages',     'href'=>'messages.php'],
            ['icon'=>'notifications',    'label'=>'Notifications','href'=>'notification.php'],
            ['icon'=>'person',           'label'=>'Profile',      'href'=>'Profile.php'],
        ];
        foreach ($nav_items as $item):
            $active = ($current_page === $item['href']) ? 'bg-primary-container/20 text-primary font-semibold' : 'text-on-surface-variant hover:bg-surface-container-low';
        ?>
        <a href="<?php echo $item['href']; ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl mb-1 transition-colors <?php echo $active; ?>">
            <span class="material-symbols-outlined text-[20px]"><?php echo $item['icon']; ?></span>
            <span class="font-body-md"><?php echo $item['label']; ?></span>
            <?php if ($item['href'] === 'notification.php' && $bell_count > 0): ?>
            <span class="ml-auto bg-error text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $bell_count; ?></span>
            <?php endif; ?>
            <?php if ($item['href'] === 'messages.php' && $msg_count > 0): ?>
            <span class="ml-auto bg-error text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $msg_count; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <!-- Logout -->
    <div class="px-3 py-4 border-t border-outline-variant">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-error hover:bg-error-container/20 transition-colors">
            <span class="material-symbols-outlined text-[20px]">logout</span>
            <span class="font-body-md">Logout</span>
        </a>
    </div>
</aside>

<!-- ===== BOTTOM NAV (mobile) ===== -->
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-2 bg-inverse-surface shadow-lg z-40">
    <?php
    $bottom_nav = [
        ['icon'=>'dashboard',   'label'=>'Home',     'href'=>'dashboard.php'],
        ['icon'=>'menu_book',   'label'=>'Courses',  'href'=>'courseUnit.php'],
        ['icon'=>'grading',     'label'=>'Grading',  'href'=>'view_submission.php'],
        ['icon'=>'group',       'label'=>'Students', 'href'=>'students.php'],
        ['icon'=>'chat_bubble', 'label'=>'Messages', 'href'=>'messages.php'],
    ];
    foreach ($bottom_nav as $bn):
        $is_active = ($current_page === $bn['href']);
        $cls = $is_active
            ? 'flex flex-col items-center justify-center bg-primary-container text-on-primary-container rounded-xl px-4 py-1'
            : 'flex flex-col items-center justify-center text-surface-variant hover:text-primary-fixed-dim transition-all';
    ?>
    <a href="<?php echo $bn['href']; ?>" class="<?php echo $cls; ?>">
        <span class="material-symbols-outlined"><?php echo $bn['icon']; ?></span>
        <span class="font-label-caps text-label-caps"><?php echo $bn['label']; ?></span>
    </a>
    <?php endforeach; ?>
</nav>

<script>
function openSidebar() {
    const drawer = document.getElementById('sidebar-drawer');
    const overlay = document.getElementById('sidebar-overlay');
    drawer.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
}
function closeSidebar() {
    const drawer = document.getElementById('sidebar-drawer');
    const overlay = document.getElementById('sidebar-overlay');
    drawer.classList.add('-translate-x-full');
    overlay.classList.add('opacity-0');
    setTimeout(() => overlay.classList.add('hidden'), 300);
}
document.getElementById('sidebar-toggle').addEventListener('click', openSidebar);
</script>
