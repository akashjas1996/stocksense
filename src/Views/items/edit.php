<div class="page-head">
    <a href="<?= APP_URL ?>/items" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1>Edit Item</h1>
</div>

<div class="card" style="padding:20px;margin-bottom:14px;">

    <!-- Image preview -->
    <div style="text-align:center;margin-bottom:20px;">
        <div id="img-preview" style="width:88px;height:88px;border-radius:18px;background:var(--accent-l);border:1.5px solid var(--border);margin:0 auto 10px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:2.8rem;">
            <?php if (!empty($item['image_url'])): ?>
            <img id="img-tag" src="<?= e($item['image_url']) ?>" style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.style.display='none';document.getElementById('img-emoji').style.display='flex'">
            <span id="img-emoji" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:2.4rem;"><?= itemEmoji($item['name'], $item['name_en'] ?? '') ?></span>
            <?php else: ?>
            <span id="img-emoji" style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;font-size:2.4rem;"><?= itemEmoji($item['name'], $item['name_en'] ?? '') ?></span>
            <?php endif ?>
        </div>
        <div style="font-size:.75rem;color:var(--text-3);font-weight:600;">Paste a URL below to change the image</div>
    </div>

    <form method="POST" action="<?= APP_URL ?>/items/<?= $item['id'] ?>/update">

        <div class="form-group">
            <label>Image URL</label>
            <div style="display:flex;gap:8px;">
                <input type="url" name="image_url" id="image-url-input" class="form-control"
                       value="<?= e($item['image_url'] ?? '') ?>"
                       placeholder="https://…"
                       oninput="previewUrl(this.value)"
                       style="flex:1;">
                <button type="button" onclick="fetchFromOff()" class="btn-outline"
                        style="padding:0 14px;white-space:nowrap;flex-shrink:0;" id="fetch-btn">
                    <i class="bi bi-upc-scan"></i> Auto-fetch
                </button>
            </div>
            <div style="font-size:.72rem;color:var(--text-3);margin-top:5px;">
                Right-click any image online → "Copy image address" and paste here.
            </div>
        </div>

        <div class="form-group">
            <label>Name <span style="font-weight:500;color:var(--text-3)">(displayed in app)</span></label>
            <input type="text" name="name" class="form-control" required value="<?= e($item['name']) ?>">
        </div>

        <div class="form-group">
            <label>English name <span style="font-weight:500;color:var(--text-3)">(used for image search)</span></label>
            <input type="text" name="name_en" class="form-control" value="<?= e($item['name_en'] ?? '') ?>"
                   placeholder="e.g. Cumin Seeds">
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label>Hindi / alternate name <span style="font-weight:500;color:var(--text-3)">(for search)</span></label>
            <input type="text" name="name_hi" class="form-control" value="<?= e($item['name_hi'] ?? '') ?>"
                   placeholder="e.g. जीरा / Jeera">
        </div>

        <button type="submit" class="btn-accent">Save</button>
    </form>
</div>

<script>
function previewUrl(url) {
    const wrap = document.getElementById('img-preview');
    const emoji = document.getElementById('img-emoji');
    let img = document.getElementById('img-tag');
    if (!url) {
        if (img) img.style.display = 'none';
        if (emoji) { emoji.style.display = 'flex'; }
        return;
    }
    if (!img) {
        img = document.createElement('img');
        img.id = 'img-tag';
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        img.onerror = () => { img.style.display = 'none'; if (emoji) emoji.style.display = 'flex'; };
        wrap.prepend(img);
    }
    img.src = url;
    img.style.display = 'block';
    if (emoji) emoji.style.display = 'none';
}

async function fetchFromOff() {
    const btn = document.getElementById('fetch-btn');
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
    btn.disabled = true;

    try {
        const r    = await fetch(`<?= APP_URL ?>/items/<?= $item['id'] ?>/fetch-image`, { method: 'POST' });
        const data = await r.json();
        if (data.success) {
            document.getElementById('image-url-input').value = data.image_url;
            previewUrl(data.image_url);
            btn.innerHTML = '<i class="bi bi-check"></i> Done';
        } else {
            btn.innerHTML = '<i class="bi bi-x"></i> Not found';
            setTimeout(() => { btn.innerHTML = '<i class="bi bi-upc-scan"></i> Auto-fetch'; btn.disabled = false; }, 2000);
            return;
        }
    } catch(e) {
        btn.innerHTML = '<i class="bi bi-upc-scan"></i> Auto-fetch';
    }
    btn.disabled = false;
}
</script>
