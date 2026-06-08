<?php
require_once 'includes/header.php';
$tid = intval($_SESSION['teacher_id']);

$success = $error = '';

// ── Handle Send Message ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id   = intval($_POST['receiver_id']);
    $receiver_type = mysqli_real_escape_string($conn, $_POST['receiver_type']);
    $msg_text      = mysqli_real_escape_string($conn, trim($_POST['message_text']));

    if (empty($msg_text)) {
        $error = 'Message cannot be empty.';
    } elseif (!in_array($receiver_type, ['student', 'admin'])) {
        $error = 'Invalid recipient.';
    } else {
        $ins = mysqli_query($conn,
            "INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, message_text)
             VALUES ($tid, 'teacher', $receiver_id, '$receiver_type', '$msg_text')");
        $success = $ins ? 'Message sent.' : 'Error: ' . mysqli_error($conn);
    }
}

// ── Mark messages as read when viewing a conversation ──────────────────────
$view_id   = isset($_GET['with']) && is_numeric($_GET['with']) ? intval($_GET['with']) : 0;
$view_type = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

if ($view_id && $view_type) {
    mysqli_query($conn,
        "UPDATE messages SET is_read=1
         WHERE receiver_id=$tid AND receiver_type='teacher'
         AND sender_id=$view_id AND sender_type='$view_type'");
}

// ── Fetch conversation list ─────────────────────────────────────────────────
// Get unique contacts (students + admin) who have exchanged messages with this teacher
$conversations = mysqli_query($conn,
    "SELECT m.sender_id AS contact_id, m.sender_type AS contact_type,
            MAX(m.created_at) AS last_msg_time,
            (SELECT message_text FROM messages WHERE
                ((sender_id=m.sender_id AND sender_type=m.sender_type AND receiver_id=$tid AND receiver_type='teacher')
                OR (sender_id=$tid AND sender_type='teacher' AND receiver_id=m.sender_id AND receiver_type=m.sender_type))
             ORDER BY created_at DESC LIMIT 1) AS last_msg,
            SUM(CASE WHEN m.receiver_id=$tid AND m.receiver_type='teacher' AND m.is_read=0 THEN 1 ELSE 0 END) AS unread_cnt
     FROM messages m
     WHERE (m.receiver_id=$tid AND m.receiver_type='teacher')
        OR (m.sender_id=$tid AND m.sender_type='teacher')
     GROUP BY m.sender_id, m.sender_type
     HAVING contact_id != $tid OR contact_type != 'teacher'
     ORDER BY last_msg_time DESC");

// Build contact name map
$contact_names = [];
while ($cv = mysqli_fetch_assoc($conversations)) {
    $cid = $cv['contact_id'];
    $ctype = $cv['contact_type'];
    if ($ctype === 'student') {
        $r = mysqli_query($conn, "SELECT student_name FROM student WHERE student_id=$cid");
        $row = mysqli_fetch_assoc($r);
        $contact_names[$ctype . '_' . $cid] = $row['student_name'] ?? 'Unknown Student';
    } elseif ($ctype === 'admin') {
        $r = mysqli_query($conn, "SELECT admin_name FROM admin WHERE admin_id=$cid");
        $row = mysqli_fetch_assoc($r);
        $contact_names[$ctype . '_' . $cid] = $row['admin_name'] ?? 'Admin';
    }
}
mysqli_data_seek($conversations, 0);

// ── Fetch messages for active conversation ──────────────────────────────────
$thread = [];
$contact_name = '';
if ($view_id && $view_type) {
    $thread_q = mysqli_query($conn,
        "SELECT * FROM messages
         WHERE (sender_id=$tid AND sender_type='teacher' AND receiver_id=$view_id AND receiver_type='$view_type')
            OR (sender_id=$view_id AND sender_type='$view_type' AND receiver_id=$tid AND receiver_type='teacher')
         ORDER BY created_at ASC");
    while ($t = mysqli_fetch_assoc($thread_q)) $thread[] = $t;
    $contact_name = $contact_names[$view_type . '_' . $view_id] ?? 'Contact';
}

// ── Fetch students for new message ─────────────────────────────────────────
$my_students = mysqli_query($conn,
    "SELECT DISTINCT st.student_id, st.student_name
     FROM student st
     JOIN enrollment e ON e.student_id=st.student_id
     JOIN courseunit cu ON cu.semester_id=e.semester_id AND cu.teacher_id=$tid
     ORDER BY st.student_name");

$admins = mysqli_query($conn, "SELECT admin_id, admin_name FROM admin ORDER BY admin_name");
?>

<main class="max-w-7xl mx-auto px-margin_mobile py-lg md:py-xl">
<div class="flex items-center justify-between mb-xl">
    <div>
        <h1 class="text-2xl font-bold text-on-surface">Messages</h1>
        <p class="text-on-surface-variant text-sm mt-0.5">Communicate with students and administrators</p>
    </div>
    <button onclick="document.getElementById('newMsgModal').classList.remove('hidden')"
            class="flex items-center gap-sm bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity shadow-sm">
        <span class="material-symbols-outlined text-lg">edit_square</span> New Message
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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter h-[calc(100vh-280px)] min-h-[500px]">

    <!-- Conversation List -->
    <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden flex flex-col">
        <div class="px-lg py-md border-b border-outline-variant/20">
            <h2 class="font-semibold text-on-surface text-sm">Conversations</h2>
        </div>
        <div class="overflow-y-auto flex-1 divide-y divide-outline-variant/10">
            <?php if (mysqli_num_rows($conversations) > 0):
                mysqli_data_seek($conversations, 0);
                while ($cv = mysqli_fetch_assoc($conversations)):
                    $cid   = $cv['contact_id'];
                    $ctype = $cv['contact_type'];
                    $cname = $contact_names[$ctype . '_' . $cid] ?? 'Unknown';
                    $is_active = ($view_id == $cid && $view_type == $ctype);
                    $unread = intval($cv['unread_cnt']);
            ?>
            <a href="messages.php?with=<?php echo $cid; ?>&type=<?php echo $ctype; ?>"
               class="flex items-center gap-md px-lg py-md hover:bg-surface-container-low transition-colors <?php echo $is_active ? 'bg-primary-container/10 border-l-2 border-primary' : ''; ?>">
                <div class="w-10 h-10 rounded-full bg-<?php echo $ctype==='admin' ? 'tertiary-fixed' : 'primary-container'; ?>/30 flex items-center justify-center text-<?php echo $ctype==='admin' ? 'tertiary' : 'primary'; ?> font-bold text-sm flex-shrink-0">
                    <?php echo strtoupper(substr($cname, 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-on-surface truncate"><?php echo htmlspecialchars($cname); ?></p>
                        <span class="text-[10px] text-on-surface-variant flex-shrink-0"><?php echo date('M j', strtotime($cv['last_msg_time'])); ?></span>
                    </div>
                    <p class="text-xs text-on-surface-variant truncate mt-0.5"><?php echo htmlspecialchars(substr($cv['last_msg'] ?? '', 0, 50)); ?></p>
                </div>
                <?php if ($unread > 0): ?>
                <span class="bg-primary text-on-primary text-[10px] font-bold px-1.5 py-0.5 rounded-full flex-shrink-0"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
            <?php endwhile; else: ?>
            <div class="flex flex-col items-center justify-center py-xl text-on-surface-variant text-sm">
                <span class="material-symbols-outlined text-4xl mb-sm opacity-40">chat</span>
                <p>No conversations yet</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message Thread -->
    <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/20 overflow-hidden flex flex-col">
        <?php if ($view_id && $view_type): ?>
        <!-- Thread Header -->
        <div class="flex items-center gap-md px-lg py-md border-b border-outline-variant/20">
            <div class="w-9 h-9 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                <?php echo strtoupper(substr($contact_name, 0, 1)); ?>
            </div>
            <div>
                <p class="font-semibold text-on-surface text-sm"><?php echo htmlspecialchars($contact_name); ?></p>
                <p class="text-xs text-on-surface-variant capitalize"><?php echo $view_type; ?></p>
            </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-lg space-y-md" id="msgThread">
            <?php if (empty($thread)): ?>
            <div class="flex items-center justify-center h-full text-on-surface-variant text-sm">
                <p>No messages yet. Say hello!</p>
            </div>
            <?php else: foreach ($thread as $msg):
                $is_mine = ($msg['sender_id'] == $tid && $msg['sender_type'] === 'teacher');
            ?>
            <div class="flex <?php echo $is_mine ? 'justify-end' : 'justify-start'; ?> gap-sm">
                <?php if (!$is_mine): ?>
                <div class="w-7 h-7 rounded-full bg-primary-container/30 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0 mt-1">
                    <?php echo strtoupper(substr($contact_name, 0, 1)); ?>
                </div>
                <?php endif; ?>
                <div class="max-w-[70%]">
                    <div class="<?php echo $is_mine ? 'bg-primary text-on-primary rounded-xl rounded-tr-sm' : 'bg-surface-container text-on-surface rounded-xl rounded-tl-sm'; ?> px-md py-sm text-sm">
                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                    </div>
                    <p class="text-[10px] text-on-surface-variant mt-0.5 <?php echo $is_mine ? 'text-right' : ''; ?>">
                        <?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Reply Form -->
        <div class="p-md border-t border-outline-variant/20 bg-surface-container-low">
            <form method="POST" class="flex gap-sm items-end">
                <input type="hidden" name="receiver_id" value="<?php echo $view_id; ?>">
                <input type="hidden" name="receiver_type" value="<?php echo $view_type; ?>">
                <textarea name="message_text" rows="2" placeholder="Type your message…" required
                          class="flex-1 border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
                <button type="submit" name="send_message" class="bg-primary text-on-primary p-sm rounded-xl hover:opacity-90 transition-opacity flex-shrink-0">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">send</span>
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center justify-center h-full text-on-surface-variant">
            <span class="material-symbols-outlined text-5xl mb-md opacity-40">chat</span>
            <p class="text-sm">Select a conversation or start a new one</p>
            <button onclick="document.getElementById('newMsgModal').classList.remove('hidden')"
                    class="mt-lg bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
                New Message
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Message Modal -->
<div id="newMsgModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-md">
    <div class="bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-lg border-b border-outline-variant/20">
            <h2 class="font-semibold text-on-surface text-lg">New Message</h2>
            <button onclick="document.getElementById('newMsgModal').classList.add('hidden')" class="p-1 rounded-full hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-on-surface-variant">close</span>
            </button>
        </div>
        <form method="POST" class="p-lg space-y-md">
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Recipient Type</label>
                <select id="recipientType" name="receiver_type" onchange="toggleRecipient()" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary outline-none">
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div id="studentSelect">
                <label class="block text-sm font-medium text-on-surface mb-xs">Select Student</label>
                <select name="receiver_id" id="studentId" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary outline-none">
                    <?php while ($s = mysqli_fetch_assoc($my_students)): ?>
                    <option value="<?php echo $s['student_id']; ?>"><?php echo htmlspecialchars($s['student_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div id="adminSelect" class="hidden">
                <label class="block text-sm font-medium text-on-surface mb-xs">Select Admin</label>
                <select name="receiver_id" id="adminId" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary outline-none">
                    <?php while ($a = mysqli_fetch_assoc($admins)): ?>
                    <option value="<?php echo $a['admin_id']; ?>"><?php echo htmlspecialchars($a['admin_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-on-surface mb-xs">Message</label>
                <textarea name="message_text" rows="4" required placeholder="Type your message…" class="w-full border border-outline-variant rounded-xl px-md py-sm text-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
            </div>
            <div class="flex gap-md">
                <button type="button" onclick="document.getElementById('newMsgModal').classList.add('hidden')" class="flex-1 border border-outline-variant text-on-surface-variant px-lg py-sm rounded-xl text-sm font-medium hover:bg-surface-container transition-colors">Cancel</button>
                <button type="submit" name="send_message" class="flex-1 bg-primary text-on-primary px-lg py-sm rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRecipient() {
    const type = document.getElementById('recipientType').value;
    document.getElementById('studentSelect').classList.toggle('hidden', type !== 'student');
    document.getElementById('adminSelect').classList.toggle('hidden', type !== 'admin');
    // Swap the name attribute so only the visible one submits
    document.getElementById('studentId').name = type === 'student' ? 'receiver_id' : '';
    document.getElementById('adminId').name   = type === 'admin'   ? 'receiver_id' : '';
}
// Scroll to bottom of thread
const thread = document.getElementById('msgThread');
if (thread) thread.scrollTop = thread.scrollHeight;

// Pre-fill new message modal if ?to= param
<?php if (isset($_GET['to']) && is_numeric($_GET['to'])): ?>
document.getElementById('newMsgModal').classList.remove('hidden');
document.getElementById('studentId').value = '<?php echo intval($_GET['to']); ?>';
<?php endif; ?>
</script>
</main>
<?php require_once 'includes/footer.php'; ?>
