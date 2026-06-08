<?php
require_once 'includes/header.php';

$success_msg = '';
$error_msg   = '';

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $msg_text    = mysqli_real_escape_string($conn, trim($_POST['message_text'] ?? ''));
    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $recv_type   = in_array($_POST['receiver_type'] ?? '', ['admin','teacher']) ? $_POST['receiver_type'] : 'admin';

    if (empty($msg_text)) {
        $error_msg = "Message cannot be empty.";
    } elseif ($receiver_id <= 0) {
        $error_msg = "Please select a recipient.";
    } else {
        mysqli_query($conn,
            "INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, message_text)
             VALUES ($student_id, 'student', $receiver_id, '$recv_type', '$msg_text')");
        $success_msg = "Message sent successfully!";
    }
}

// Mark incoming messages as read
mysqli_query($conn, "UPDATE messages SET is_read=1 WHERE receiver_id=$student_id AND receiver_type='student'");

// Fetch conversation threads (group by sender)
$threads_q = mysqli_query($conn,
    "SELECT m.*,
            CASE WHEN m.sender_type='student' THEN m.receiver_id ELSE m.sender_id END AS contact_id,
            CASE WHEN m.sender_type='student' THEN m.receiver_type ELSE m.sender_type END AS contact_type
     FROM messages m
     WHERE (m.sender_id=$student_id AND m.sender_type='student')
        OR (m.receiver_id=$student_id AND m.receiver_type='student')
     ORDER BY m.created_at DESC");

$threads = [];
while ($row = mysqli_fetch_assoc($threads_q)) {
    $key = $row['contact_type'] . '_' . $row['contact_id'];
    if (!isset($threads[$key])) $threads[$key] = $row;
}

// Get teachers for compose
$teachers_q = mysqli_query($conn, "SELECT teacher_id, teacher_name FROM teacher ORDER BY teacher_name");
$teachers = [];
while ($row = mysqli_fetch_assoc($teachers_q)) $teachers[] = $row;

// Get admins for compose
$admins_q = mysqli_query($conn, "SELECT admin_id, admin_name FROM admin ORDER BY admin_name");
$admins = [];
while ($row = mysqli_fetch_assoc($admins_q)) $admins[] = $row;

// Active conversation
$active_id   = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
$active_type = isset($_GET['contact_type']) ? $_GET['contact_type'] : '';
$conversation = [];
if ($active_id > 0 && in_array($active_type, ['admin','teacher'])) {
    $conv_q = mysqli_query($conn,
        "SELECT m.* FROM messages m
         WHERE (m.sender_id=$student_id AND m.sender_type='student' AND m.receiver_id=$active_id AND m.receiver_type='$active_type')
            OR (m.sender_id=$active_id AND m.sender_type='$active_type' AND m.receiver_id=$student_id AND m.receiver_type='student')
         ORDER BY m.created_at ASC");
    while ($row = mysqli_fetch_assoc($conv_q)) $conversation[] = $row;
}

function get_contact_name($conn, $id, $type) {
    if ($type === 'teacher') {
        $q = mysqli_query($conn, "SELECT teacher_name AS name FROM teacher WHERE teacher_id=" . intval($id));
    } else {
        $q = mysqli_query($conn, "SELECT admin_name AS name FROM admin WHERE admin_id=" . intval($id));
    }
    $r = mysqli_fetch_assoc($q);
    return $r ? $r['name'] : ucfirst($type);
}
?>

<main class="max-w-5xl mx-auto px-margin_mobile py-lg space-y-lg">
    <section class="space-y-xs">
        <p class="font-label-caps text-label-caps text-primary uppercase">Communication</p>
        <h2 class="font-h1 text-h1 text-on-surface">Messages</h2>
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

    <div class="grid grid-cols-1 md:grid-cols-12 gap-lg">
        <!-- Conversations list -->
        <div class="md:col-span-4 space-y-md">
            <h3 class="font-h2 text-h2 text-on-surface">Conversations</h3>
            <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container overflow-hidden">
                <?php if (empty($threads)): ?>
                <div class="p-lg text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl block mb-2">chat_bubble_outline</span>
                    No conversations yet.
                </div>
                <?php else: ?>
                <div class="divide-y divide-surface-container">
                    <?php foreach ($threads as $thread):
                        $cname = get_contact_name($conn, $thread['contact_id'], $thread['contact_type']);
                        $is_active = ($active_id === intval($thread['contact_id']) && $active_type === $thread['contact_type']);
                    ?>
                    <a href="messages.php?contact_id=<?php echo $thread['contact_id']; ?>&contact_type=<?php echo $thread['contact_type']; ?>"
                       class="flex items-center gap-3 p-4 hover:bg-surface-container-low transition-colors <?php echo $is_active ? 'bg-primary-container/10 border-l-4 border-primary' : ''; ?>">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary font-bold flex-shrink-0">
                            <?php echo strtoupper(substr($cname, 0, 1)); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-body-md font-semibold text-on-surface truncate"><?php echo htmlspecialchars($cname); ?></p>
                            <p class="font-body-sm text-on-surface-variant capitalize"><?php echo $thread['contact_type']; ?></p>
                        </div>
                        <span class="font-label-caps text-[10px] text-on-surface-variant"><?php echo date('M d', strtotime($thread['created_at'])); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compose / Conversation -->
        <div class="md:col-span-8 space-y-md">
            <?php if ($active_id > 0 && !empty($conversation)): ?>
            <!-- Active conversation -->
            <?php $cname = get_contact_name($conn, $active_id, $active_type); ?>
            <div class="flex items-center gap-3 pb-md border-b border-outline-variant">
                <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary font-bold">
                    <?php echo strtoupper(substr($cname, 0, 1)); ?>
                </div>
                <div>
                    <p class="font-body-md font-semibold text-on-surface"><?php echo htmlspecialchars($cname); ?></p>
                    <p class="font-body-sm text-on-surface-variant capitalize"><?php echo $active_type; ?></p>
                </div>
            </div>
            <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container p-md space-y-3 max-h-80 overflow-y-auto" id="chat-box">
                <?php foreach ($conversation as $msg):
                    $is_mine = ($msg['sender_id'] == $student_id && $msg['sender_type'] === 'student');
                ?>
                <div class="flex <?php echo $is_mine ? 'justify-end' : 'justify-start'; ?>">
                    <div class="max-w-[75%] px-4 py-2 rounded-2xl <?php echo $is_mine ? 'bg-primary text-on-primary rounded-br-sm' : 'bg-surface-container text-on-surface rounded-bl-sm'; ?>">
                        <p class="font-body-md"><?php echo htmlspecialchars($msg['message_text']); ?></p>
                        <p class="font-body-sm opacity-60 mt-1 text-right"><?php echo date('g:i A', strtotime($msg['created_at'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Reply form -->
            <form method="POST" class="flex gap-2">
                <input type="hidden" name="receiver_id" value="<?php echo $active_id; ?>">
                <input type="hidden" name="receiver_type" value="<?php echo $active_type; ?>">
                <input type="text" name="message_text" required placeholder="Type a reply..."
                    class="flex-1 px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md">
                <button type="submit" name="send_message"
                    class="px-6 py-3 bg-primary text-on-primary rounded-xl font-button hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined">send</span>
                </button>
            </form>
            <?php endif; ?>

            <!-- Compose new message -->
            <div class="bg-surface-container-lowest rounded-xl shadow-level-1 border border-surface-container p-lg space-y-md">
                <h3 class="font-h2 text-h2 text-on-surface">New Message</h3>
                <form method="POST" class="space-y-md">
                    <div class="space-y-sm">
                        <label class="font-label-caps text-label-caps text-on-surface-variant block">RECIPIENT TYPE</label>
                        <select name="receiver_type" id="recv_type_sel"
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary outline-none font-body-md text-on-surface">
                            <option value="teacher">Teacher</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="space-y-sm">
                        <label class="font-label-caps text-label-caps text-on-surface-variant block">RECIPIENT</label>
                        <select name="receiver_id" id="recv_id_sel"
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary outline-none font-body-md text-on-surface">
                            <optgroup label="Teachers" id="teacher_opts">
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?php echo $t['teacher_id']; ?>"><?php echo htmlspecialchars($t['teacher_name']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Admins" id="admin_opts" style="display:none">
                                <?php foreach ($admins as $a): ?>
                                <option value="<?php echo $a['admin_id']; ?>"><?php echo htmlspecialchars($a['admin_name']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="space-y-sm">
                        <label class="font-label-caps text-label-caps text-on-surface-variant block">MESSAGE</label>
                        <textarea name="message_text" rows="3" required
                            class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md resize-none"
                            placeholder="Write your message..."></textarea>
                    </div>
                    <button type="submit" name="send_message"
                        class="w-full py-3 bg-primary text-on-primary rounded-xl font-button text-button hover:bg-on-primary-fixed-variant transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">send</span>
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Toggle teacher/admin options based on type selection
document.getElementById('recv_type_sel').addEventListener('change', function() {
    const teacherOpts = document.getElementById('teacher_opts');
    const adminOpts   = document.getElementById('admin_opts');
    if (this.value === 'teacher') {
        teacherOpts.style.display = '';
        adminOpts.style.display   = 'none';
    } else {
        teacherOpts.style.display = 'none';
        adminOpts.style.display   = '';
    }
});
// Scroll chat to bottom
const chatBox = document.getElementById('chat-box');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>
