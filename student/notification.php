<?php
require_once 'includes/header.php';

// Mark all as read
mysqli_query($conn, "UPDATE notification SET notification_status='read' WHERE student_id=$student_id");

// Fetch all notifications
$notif_q = mysqli_query($conn,
    "SELECT * FROM notification WHERE student_id=$student_id ORDER BY date_sent DESC");
$notifications = [];
while ($row = mysqli_fetch_assoc($notif_q)) $notifications[] = $row;
?>

<main class="max-w-2xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Alerts</p>
        <h2 class="font-h1 text-h1 text-on-surface">Notifications</h2>
        <p class="font-body-md text-on-surface-variant"><?php echo count($notifications); ?> notification<?php echo count($notifications) !== 1 ? 's' : ''; ?></p>
    </section>

    <?php if (empty($notifications)): ?>
    <div class="bg-surface-container-lowest rounded-xl p-xl shadow-level-1 border border-surface-container text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-5xl block mb-3">notifications_none</span>
        <p class="font-body-lg font-semibold text-on-surface">All clear!</p>
        <p class="font-body-md mt-1">You have no notifications.</p>
    </div>
    <?php else: ?>
    <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container overflow-hidden">
        <div class="divide-y divide-surface-container">
            <?php foreach ($notifications as $notif): ?>
            <div class="flex items-start gap-4 p-4 hover:bg-surface-container-low transition-colors">
                <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary flex-shrink-0 mt-0.5">
                    <span class="material-symbols-outlined">notifications</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-body-md text-on-surface"><?php echo htmlspecialchars($notif['notification_message']); ?></p>
                    <?php if ($notif['sender_name']): ?>
                    <p class="font-body-sm text-primary mt-0.5">From: <?php echo htmlspecialchars($notif['sender_name']); ?></p>
                    <?php endif; ?>
                    <p class="font-body-sm text-on-surface-variant mt-1"><?php echo date('M d, Y g:i A', strtotime($notif['date_sent'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
