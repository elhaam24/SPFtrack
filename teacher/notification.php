<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notification SET notification_status='read' WHERE teacher_id=$tid");
    header('location: notification.php');
    exit();
}

// Mark single as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $nid = intval($_GET['read']);
    mysqli_query($conn, "UPDATE notification SET notification_status='read' WHERE notification_id=$nid AND teacher_id=$tid");
}

$notifications = mysqli_query($conn,
    "SELECT * FROM notification WHERE teacher_id=$tid ORDER BY date_sent DESC");

$unread_count = 0;
$nq = mysqli_query($conn, "SELECT COUNT(*) AS c FROM notification WHERE teacher_id=$tid AND notification_status='unread'");
if ($nq) $unread_count = mysqli_fetch_assoc($nq)['c'] ?? 0;
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="flex items-center justify-between mb-xl">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">Notifications</h1>
        <p class="text-on-surface-variant text-sm mt-0.5">
            <?php echo $unread_count > 0 ? "$unread_count unread notification" . ($unread_count > 1 ? 's' : '') : 'All caught up'; ?>
        </p>
    </div>
    <?php if ($unread_count > 0): ?>
    <a href="notification.php?mark_all_read=1" class="flex items-center gap-sm bg-surface-container-high text-on-surface-variant px-lg py-sm rounded-xl text-sm font-medium hover:bg-surface-container-highest transition-colors">
        <span class="material-symbols-outlined text-lg">done_all</span> Mark all read
    </a>
    <?php endif; ?>
</div>

<div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden">
    <?php if ($notifications && mysqli_num_rows($notifications) > 0): ?>
    <div class="divide-y divide-outline-variant/10">
        <?php while ($n = mysqli_fetch_assoc($notifications)):
            $is_unread = $n['notification_status'] === 'unread';
        ?>
        <div class="flex items-start gap-md px-lg py-md <?php echo $is_unread ? 'bg-primary-container/5' : ''; ?> hover:bg-surface-container-low/50 transition-colors">
            <div class="w-9 h-9 rounded-full <?php echo $is_unread ? 'bg-primary-container/30' : 'bg-surface-container'; ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="material-symbols-outlined <?php echo $is_unread ? 'text-primary' : 'text-on-surface-variant'; ?> text-lg">notifications</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm <?php echo $is_unread ? 'font-medium text-on-surface' : 'text-on-surface-variant'; ?>">
                    <?php echo htmlspecialchars($n['notification_message']); ?>
                </p>
                <div class="flex items-center gap-md mt-xs text-xs text-on-surface-variant">
                    <?php if ($n['sender_name']): ?>
                    <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">person</span><?php echo htmlspecialchars($n['sender_name']); ?></span>
                    <?php endif; ?>
                    <span class="flex items-center gap-xs"><span class="material-symbols-outlined text-sm">schedule</span><?php echo $n['date_sent'] ? date('M j, Y g:i A', strtotime($n['date_sent'])) : '—'; ?></span>
                </div>
            </div>
            <div class="flex items-center gap-sm flex-shrink-0">
                <?php if ($is_unread): ?>
                <a href="notification.php?read=<?php echo $n['notification_id']; ?>" class="text-xs text-primary hover:underline">Mark read</a>
                <div class="w-2 h-2 rounded-full bg-primary"></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="flex flex-col items-center justify-center py-2xl text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl mb-md opacity-40">notifications_off</span>
        <p class="text-sm">No notifications yet.</p>
    </div>
    <?php endif; ?>
</div>
</main>
<?php require_once 'includes/footer.php'; ?>
