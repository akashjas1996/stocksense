<?php // $history = array of ['q'=>..., 'a'=>...] from session ?>

<div class="page-head" style="margin-bottom:0;padding-bottom:12px;">
    <h1 class="page-title" style="display:flex;align-items:center;gap:8px;">
        <i class="bi bi-stars" style="color:var(--accent)"></i> Ask AI
    </h1>
    <?php if ($history): ?>
    <a href="<?= APP_URL ?>/ask/clear" class="btn-outline" style="padding:6px 12px;font-size:.78rem;white-space:nowrap;">
        <i class="bi bi-arrow-counterclockwise"></i> New chat
    </a>
    <?php endif ?>
</div>

<div id="chat-messages">
    <div id="chat-intro" <?= $history ? 'style="display:none"' : '' ?>>
        <div class="chat-avatar-big"><i class="bi bi-stars"></i></div>
        <p>Hi! I know your pantry inside out.<br>Ask me what to cook, what to buy next, or where to find something.</p>
    </div>
    <div id="chat-typing" style="display:none">
        <div class="chat-msg ai">
            <div class="typing-dots"><span></span><span></span><span></span></div>
        </div>
    </div>
</div>

<?php if (!$history): ?>
<div id="suggestions">
    <button class="suggest-chip" onclick="fillAndSend('What can I make for dinner with what I have?')">🍳 What can I make tonight?</button>
    <button class="suggest-chip" onclick="fillAndSend('What is the one thing I should buy urgently right now?')">🛒 What to buy urgently?</button>
    <button class="suggest-chip" onclick="fillAndSend('Which items in my pantry are expiring soon or already expired?')">⏰ What\'s expiring soon?</button>
    <button class="suggest-chip" onclick="fillAndSend('Where is the bay leaf?')">🌿 Where is bay leaf?</button>
</div>
<?php endif ?>

<div id="chat-form-wrap">
    <div class="chat-input-row">
        <textarea id="chat-input" placeholder="Ask about your pantry…" rows="1" autocomplete="off" autocorrect="on"></textarea>
        <button type="button" class="chat-send-btn" id="chat-send">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
const INIT_HISTORY = <?= json_encode($history, JSON_HEX_TAG | JSON_HEX_QUOT) ?>;

function esc(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function md(raw) {
    // Apply inline bold/italic to already-escaped text
    function fmt(t) {
        t = t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        t = t.replace(/\*([^*\n]+?)\*/g, '<em>$1</em>');
        return t;
    }
    const lines = raw.split('\n');
    const out   = [];
    let inList  = false;
    for (let line of lines) {
        line = esc(line); // escape HTML entities BEFORE any substitution
        const hdr    = line.match(/^#{1,3} (.+)/);
        const bullet = line.match(/^[-•*] (.+)/);
        const num    = line.match(/^\d+\. (.+)/);
        if (hdr) {
            if (inList) { out.push('</ul>'); inList = false; }
            out.push('<strong>' + fmt(hdr[1]) + '</strong>');
        } else if (bullet || num) {
            if (!inList) { out.push('<ul>'); inList = true; }
            out.push('<li>' + fmt(bullet ? bullet[1] : num[1]) + '</li>');
        } else {
            if (inList) { out.push('</ul>'); inList = false; }
            if (line.trim()) out.push(fmt(line) + '<br>');
            else if (out.length) out.push('<br>');
        }
    }
    if (inList) out.push('</ul>');
    return out.join('');
}

const msgs   = document.getElementById('chat-messages');
const typing = document.getElementById('chat-typing');

function appendMsg(role, text) {
    const intro = document.getElementById('chat-intro');
    if (intro) intro.style.display = 'none';
    const div = document.createElement('div');
    div.className = 'chat-msg ' + role;
    div.innerHTML = (role === 'ai') ? md(text) : esc(text);
    msgs.insertBefore(div, typing);
    requestAnimationFrame(() => div.scrollIntoView({ behavior: 'smooth', block: 'end' }));
}

function setTyping(show) {
    typing.style.display = show ? 'block' : 'none';
    if (show) requestAnimationFrame(() => typing.scrollIntoView({ behavior: 'smooth', block: 'end' }));
}

async function sendMessage() {
    const input = document.getElementById('chat-input');
    const btn   = document.getElementById('chat-send');
    const q     = input.value.trim();
    if (!q || btn.disabled) return;

    const sug = document.getElementById('suggestions');
    if (sug) sug.remove();

    input.value = '';
    input.style.height = 'auto';
    btn.disabled = true;

    appendMsg('user', q);
    setTyping(true);

    try {
        const res  = await fetch(APP_URL + '/ask/query', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'question=' + encodeURIComponent(q),
        });
        const data = await res.json();
        setTyping(false);
        appendMsg('ai', data.error ? '⚠️ ' + data.error : data.answer);
        if (data.action) showActionCard(data.action);
    } catch {
        setTyping(false);
        appendMsg('ai', '⚠️ Network error. Please try again.');
    }

    btn.disabled = false;
    input.focus();
}

function showActionCard(action) {
    const card = document.createElement('div');
    card.className = 'action-card ' + (action.status === 'success' ? 'success' : 'error');
    if (action.status === 'success') {
        const expiry = action.expiry ? ` · Expires ${action.expiry}` : '';
        card.innerHTML =
            `<i class="bi bi-check-circle-fill"></i>` +
            `<span>Added <strong>${esc(action.item)}</strong> — ${esc(action.qty)} in ${esc(action.location)}${esc(expiry)}</span>` +
            `<a href="${APP_URL}/explore" class="action-card-link">View <i class="bi bi-arrow-right"></i></a>`;
    } else {
        card.innerHTML =
            `<i class="bi bi-exclamation-triangle-fill"></i>` +
            `<span>Couldn't save: ${esc(action.message)}</span>`;
    }
    msgs.insertBefore(card, typing);
    requestAnimationFrame(() => card.scrollIntoView({ behavior: 'smooth', block: 'end' }));
}

function fillAndSend(q) {
    document.getElementById('chat-input').value = q;
    sendMessage();
}

// Render existing session history on page load
INIT_HISTORY.forEach(turn => { appendMsg('user', turn.q); appendMsg('ai', turn.a); });
msgs.scrollTop = msgs.scrollHeight;

// Auto-resize textarea
const chatInput = document.getElementById('chat-input');
chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

document.getElementById('chat-send').addEventListener('click', sendMessage);
</script>
