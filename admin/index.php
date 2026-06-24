<?php
/* =====================================================================
   BHAVIN J. SHAH — Admin Panel
   Login + Photo manager + Contact editor. Saves to ../content.json
   and uploads photos to ../uploads/. No database needed.
   ===================================================================== */

session_start();

/* ---------- 1. SETTINGS (change your password here) ---------- */
$ADMIN_PASSWORD = "bjs@admin123";   // <-- IMPORTANT: change this password

$ROOT        = dirname(__DIR__);                 // website root folder
$CONTENT     = $ROOT . "/content.json";
$UPLOAD_DIR  = $ROOT . "/uploads";
$UPLOAD_URL  = "uploads";                         // path used inside the website
$DATA_DIR    = $ROOT . "/data";
$ENQ_FILE    = $DATA_DIR . "/enquiries.json";
$SET_FILE    = $DATA_DIR . "/settings.json";

/* Image slots shown in the panel (key => friendly label) */
$SLOTS = [
    "home_hero"         => "Home – Hero / Main Photo",
    "home_meet"         => "Home – Meet Bhavin Photo",
    "about_portrait"    => "About Page – Portrait",
    "life_coaching"     => "Life Coaching – Photo",
    "business_coaching" => "Business Coaching – Photo",
    "uyp_workshop"      => "Unleash Potential – Workshop Photo",
    "bgb_poster"        => "Business Growth – Poster Photo",
    "corporate_session" => "Corporate Training – Session Photo",
    "blog_1"            => "Blog Cover 1",
    "blog_2"            => "Blog Cover 2",
    "blog_3"            => "Blog Cover 3",
];
/* Recommended image size per slot (width x height in pixels, based on the
   aspect ratio used on the website). Generate images at this size for best fit. */
$SLOT_SIZES = [
    "home_hero"         => "800 x 1000 px (4:5 portrait)",
    "home_meet"         => "600 x 800 px (3:4 portrait)",
    "about_portrait"    => "800 x 1000 px (4:5 portrait)",
    "life_coaching"     => "800 x 600 px (4:3 landscape)",
    "business_coaching" => "800 x 600 px (4:3 landscape)",
    "uyp_workshop"      => "800 x 600 px (4:3 landscape)",
    "bgb_poster"        => "800 x 600 px (4:3 landscape)",
    "corporate_session" => "800 x 600 px (4:3 landscape)",
    "blog_1"            => "1280 x 720 px (16:9 wide)",
    "blog_2"            => "1280 x 720 px (16:9 wide)",
    "blog_3"            => "1280 x 720 px (16:9 wide)",
];
$FIELDS = [
    "phone"    => "Phone Number",
    "whatsapp" => "WhatsApp Number / Link",
    "email"    => "Email Address",
];
/* Video / social / page links (key => label) */
$LINKS = [
    "social_youtube"      => "Social: YouTube Channel",
    "social_instagram"    => "Social: Instagram",
    "social_facebook"     => "Social: Facebook",
    "social_linkedin"     => "Social: LinkedIn",
    "home_youtube_1"      => "Home – YouTube Video 1",
    "home_youtube_2"      => "Home – YouTube Video 2",
    "home_instagram_1"    => "Home – Instagram Reel 1",
    "home_workshop_video" => "Home – Workshop Highlight Video",
    "home_testi_1"        => "Home – Testimonial Video 1",
    "home_testi_2"        => "Home – Testimonial Video 2",
    "home_testi_3"        => "Home – Testimonial Screenshot/Video 3",
];
/* Editable text blocks (key => label) */
$TEXTS = [
    "hero_subtext" => "Home – Hero Intro Paragraph",
    "footer_about" => "Footer – About Paragraph",
];
/* Map embed (key => label) */
$MAPS = [
    "google_map" => "Contact – Google Map (paste embed link or iframe code)",
];

/* ---------- 2. HELPERS ---------- */
function load_content($file) {
    if (!file_exists($file)) return ["images" => [], "fields" => []];
    $d = json_decode(file_get_contents($file), true);
    if (!is_array($d)) $d = [];
    foreach (["images", "fields", "links", "texts", "maps"] as $sec) {
        if (!isset($d[$sec]) || !is_array($d[$sec])) $d[$sec] = [];
    }
    return $d;
}
function save_content($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
function load_json_array($file) {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

/* ---------- 3. LOGIN / LOGOUT ---------- */
if (isset($_GET["logout"])) { session_destroy(); header("Location: index.php"); exit; }

if (isset($_POST["login_password"])) {
    if ($_POST["login_password"] === $ADMIN_PASSWORD) {
        $_SESSION["bjs_admin"] = true;
    } else {
        $login_error = "Wrong password. Try again.";
    }
}
$logged_in = !empty($_SESSION["bjs_admin"]);

/* ---------- 4. HANDLE SAVE (only when logged in) ---------- */
$notice = "";
if ($logged_in && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $content = load_content($CONTENT);

    /* a) Photo upload for a slot */
    if ($_POST["action"] === "upload" && isset($_POST["slot"])) {
        $slot = $_POST["slot"];
        if (isset($SLOTS[$slot]) && isset($_FILES["photo"]) && $_FILES["photo"]["error"] === 0) {
            if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0775, true);
            $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg","jpeg","png","webp","gif"];
            if (in_array($ext, $allowed)) {
                $fname = $slot . "_" . time() . "." . $ext;
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $UPLOAD_DIR . "/" . $fname)) {
                    $content["images"][$slot] = $UPLOAD_URL . "/" . $fname;
                    save_content($CONTENT, $content);
                    $notice = "Photo updated successfully ✔";
                } else { $notice = "Upload failed. Check folder permissions on uploads/."; }
            } else { $notice = "Only JPG, PNG, WEBP or GIF images are allowed."; }
        }
    }

    /* b) Remove a photo */
    if ($_POST["action"] === "remove" && isset($_POST["slot"])) {
        $slot = $_POST["slot"];
        if (isset($content["images"][$slot])) {
            $content["images"][$slot] = "";
            save_content($CONTENT, $content);
            $notice = "Photo removed.";
        }
    }

    /* c) Save contact fields */
    if ($_POST["action"] === "fields") {
        foreach ($FIELDS as $k => $label) {
            if (isset($_POST[$k])) $content["fields"][$k] = trim($_POST[$k]);
        }
        save_content($CONTENT, $content);
        $notice = "Contact details saved ✔";
    }

    /* d) Save video / social links */
    if ($_POST["action"] === "links") {
        foreach ($LINKS as $k => $label) {
            if (isset($_POST[$k])) $content["links"][$k] = trim($_POST[$k]);
        }
        save_content($CONTENT, $content);
        $notice = "Links saved ✔";
    }

    /* e) Save text blocks */
    if ($_POST["action"] === "texts") {
        foreach ($TEXTS as $k => $label) {
            if (isset($_POST[$k])) $content["texts"][$k] = trim($_POST[$k]);
        }
        save_content($CONTENT, $content);
        $notice = "Texts saved ✔";
    }

    /* f) Save map */
    if ($_POST["action"] === "maps") {
        foreach ($MAPS as $k => $label) {
            if (isset($_POST[$k])) $content["maps"][$k] = trim($_POST[$k]);
        }
        save_content($CONTENT, $content);
        $notice = "Map saved ✔";
    }
}

/* ---------- 4b. ENQUIRIES + INTEGRATIONS (only when logged in) ---------- */
if ($logged_in) {

    /* CSV export of all enquiries */
    if (isset($_GET["export"]) && $_GET["export"] === "csv") {
        $rows = load_json_array($ENQ_FILE);
        header("Content-Type: text/csv; charset=utf-8");
        header('Content-Disposition: attachment; filename="enquiries-' . date("Y-m-d") . '.csv"');
        $out = fopen("php://output", "w");
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
        // collect all field keys
        $cols = ["date", "form_type"];
        foreach ($rows as $r) foreach (($r["fields"] ?? []) as $k => $v) if (!in_array($k, $cols)) $cols[] = $k;
        fputcsv($out, $cols);
        foreach ($rows as $r) {
            $line = [];
            foreach ($cols as $c) {
                if ($c === "date") $line[] = $r["date"] ?? "";
                elseif ($c === "form_type") $line[] = $r["form_type"] ?? "";
                else $line[] = $r["fields"][$c] ?? "";
            }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    /* Delete one enquiry */
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "del_enquiry" && isset($_POST["id"])) {
        $rows = array_values(array_filter(load_json_array($ENQ_FILE), function ($r) {
            return ($r["id"] ?? "") !== $_POST["id"];
        }));
        if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0775, true);
        file_put_contents($ENQ_FILE, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $notice = "Enquiry deleted.";
    }

    /* Save integration settings (webhooks + notify email) */
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "integrations") {
        if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0775, true);
        $set = [
            "webhook_url"  => trim($_POST["webhook_url"]  ?? ""),
            "webhook_url2" => trim($_POST["webhook_url2"] ?? ""),
            "notify_email" => trim($_POST["notify_email"] ?? ""),
        ];
        file_put_contents($SET_FILE, json_encode($set, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $notice = "Integration settings saved ✔";
    }
}

$enquiries = array_reverse(load_json_array($ENQ_FILE));   // newest first
$settings  = load_json_array($SET_FILE);
$content   = load_content($CONTENT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel | Bhavin J. Shah</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Inter',sans-serif}</style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

<?php if (!$logged_in): ?>
<!-- ===== LOGIN SCREEN ===== -->
<div class="min-h-screen flex items-center justify-center px-4">
  <form method="post" class="bg-white w-full max-w-sm rounded-2xl shadow-xl p-8 space-y-5 border border-slate-200">
    <div class="text-center">
      <h1 class="text-xl font-extrabold text-slate-900">BHAVIN J. SHAH</h1>
      <p class="text-xs tracking-widest text-amber-600 font-semibold">ADMIN PANEL</p>
    </div>
    <?php if (!empty($login_error)): ?>
      <p class="bg-red-50 text-red-600 text-sm rounded-lg px-3 py-2 text-center"><?= htmlspecialchars($login_error) ?></p>
    <?php endif; ?>
    <div>
      <label class="text-sm font-semibold block mb-1">Password</label>
      <input type="password" name="login_password" required autofocus
             class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
    </div>
    <button class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg transition-colors">Login</button>
  </form>
</div>

<?php else: ?>
<!-- ===== DASHBOARD ===== -->
<header class="bg-slate-900 text-white">
  <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
    <div>
      <h1 class="font-extrabold tracking-wide">BHAVIN J. SHAH — Admin</h1>
      <p class="text-[11px] text-amber-400 tracking-widest">PHOTO &amp; CONTENT MANAGER</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="../index.html" target="_blank" class="text-xs bg-white/10 hover:bg-white/20 px-3 py-2 rounded-lg">View Website ↗</a>
      <a href="?logout=1" class="text-xs bg-red-500 hover:bg-red-600 px-3 py-2 rounded-lg font-semibold">Logout</a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8 space-y-8">

  <?php if ($notice): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm font-semibold"><?= htmlspecialchars($notice) ?></div>
  <?php endif; ?>

  <!-- PHOTOS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Photos</h2>
    <p class="text-sm text-slate-500 mb-5">Koi pan jagya ni photo badalva mate, photo pasand karo ane "Update" dabavo.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($SLOTS as $key => $label):
            $cur = $content["images"][$key] ?? ""; ?>
        <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3 shadow-sm">
          <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($label) ?></p>
          <p class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 inline-flex items-center gap-1">
            <span>📐</span> Recommended size: <?= htmlspecialchars($SLOT_SIZES[$key] ?? "any") ?>
          </p>
          <div class="aspect-video rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
            <?php if ($cur): ?>
              <img src="../<?= htmlspecialchars($cur) ?>?v=<?= time() ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <span class="text-xs text-slate-400">No photo yet</span>
            <?php endif; ?>
          </div>
          <form method="post" enctype="multipart/form-data" class="space-y-2">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="slot" value="<?= htmlspecialchars($key) ?>">
            <input type="file" name="photo" accept="image/*" required
                   class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-900 file:text-white file:text-xs file:font-semibold">
            <div class="flex gap-2">
              <button class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-sm py-2 rounded-lg transition-colors">Update</button>
              <?php if ($cur): ?>
              <button form="rm_<?= $key ?>" class="px-3 bg-slate-200 hover:bg-red-100 hover:text-red-600 text-slate-600 text-sm rounded-lg">Remove</button>
              <?php endif; ?>
            </div>
          </form>
          <?php if ($cur): ?>
          <form id="rm_<?= $key ?>" method="post" class="hidden">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="slot" value="<?= htmlspecialchars($key) ?>">
          </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CONTACT -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Contact Details</h2>
    <p class="text-sm text-slate-500 mb-5">Footer ma dekhati phone, WhatsApp ane email ahi badlo.</p>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 shadow-sm max-w-xl">
      <input type="hidden" name="action" value="fields">
      <?php foreach ($FIELDS as $k => $label):
            $val = $content["fields"][$k] ?? ""; ?>
        <div>
          <label class="text-sm font-semibold block mb-1"><?= htmlspecialchars($label) ?></label>
          <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars($val) ?>"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>
      <?php endforeach; ?>
      <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors">Save Contact Details</button>
    </form>
  </section>

  <!-- VIDEO / SOCIAL LINKS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Video &amp; Social Links</h2>
    <p class="text-sm text-slate-500 mb-5">YouTube, Instagram, video ane social media ni links ahi paste karo (full link, e.g. https://youtu.be/...).</p>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-2 gap-4 shadow-sm">
      <input type="hidden" name="action" value="links">
      <?php foreach ($LINKS as $k => $label):
            $val = $content["links"][$k] ?? ""; ?>
        <div>
          <label class="text-sm font-semibold block mb-1"><?= htmlspecialchars($label) ?></label>
          <input type="text" name="<?= $k ?>" value="<?= htmlspecialchars($val) ?>" placeholder="https://..."
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>
      <?php endforeach; ?>
      <div class="md:col-span-2">
        <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors">Save Links</button>
      </div>
    </form>
  </section>

  <!-- TEXT BLOCKS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Website Texts</h2>
    <p class="text-sm text-slate-500 mb-5">Aa text website par dekhati lakhan badle che. Khali rakho to default rahe.</p>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 shadow-sm max-w-3xl">
      <input type="hidden" name="action" value="texts">
      <?php foreach ($TEXTS as $k => $label):
            $val = $content["texts"][$k] ?? ""; ?>
        <div>
          <label class="text-sm font-semibold block mb-1"><?= htmlspecialchars($label) ?></label>
          <textarea name="<?= $k ?>" rows="3"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"><?= htmlspecialchars($val) ?></textarea>
        </div>
      <?php endforeach; ?>
      <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors">Save Texts</button>
    </form>
  </section>

  <!-- MAP -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Google Map</h2>
    <p class="text-sm text-slate-500 mb-5">Google Maps ma jagya kholo → Share → "Embed a map" → HTML code copy karo ane ahi paste karo.</p>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 shadow-sm max-w-3xl">
      <input type="hidden" name="action" value="maps">
      <?php foreach ($MAPS as $k => $label):
            $val = $content["maps"][$k] ?? ""; ?>
        <div>
          <label class="text-sm font-semibold block mb-1"><?= htmlspecialchars($label) ?></label>
          <textarea name="<?= $k ?>" rows="3" placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>'
                 class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"><?= htmlspecialchars($val) ?></textarea>
        </div>
      <?php endforeach; ?>
      <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors">Save Map</button>
    </form>
  </section>

  <!-- ENQUIRIES -->
  <section>
    <div class="flex items-center justify-between mb-1">
      <h2 class="text-lg font-extrabold text-slate-900">Enquiries / Form Submissions</h2>
      <?php if (!empty($enquiries)): ?>
        <a href="?export=csv" class="text-xs bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-2 rounded-lg">⬇ Export CSV (Excel/Sheets)</a>
      <?php endif; ?>
    </div>
    <p class="text-sm text-slate-500 mb-5">Website na form mathi aavel badhi enquiry ahi dekhashe. CSV download kari ne Google Sheet/Excel ma kholi shako.</p>

    <?php if (empty($enquiries)): ?>
      <div class="bg-white border border-slate-200 rounded-xl p-8 text-center text-slate-400 text-sm">Have sudhi koi enquiry aavi nathi.</div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($enquiries as $e): ?>
          <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3 mb-2">
              <div>
                <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded"><?= htmlspecialchars($e["form_type"] ?? "Enquiry") ?></span>
                <span class="text-xs text-slate-400 ml-2"><?= htmlspecialchars($e["date"] ?? "") ?></span>
              </div>
              <form method="post" onsubmit="return confirm('Delete this enquiry?');">
                <input type="hidden" name="action" value="del_enquiry">
                <input type="hidden" name="id" value="<?= htmlspecialchars($e["id"] ?? "") ?>">
                <button class="text-xs text-slate-400 hover:text-red-600">✕ Delete</button>
              </form>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm">
              <?php foreach (($e["fields"] ?? []) as $k => $v): if ($v === "") continue; ?>
                <div><span class="font-semibold text-slate-600"><?= htmlspecialchars($k) ?>:</span> <span class="text-slate-800"><?= htmlspecialchars($v) ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- INTEGRATIONS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Send Data Anywhere (Integrations)</h2>
    <p class="text-sm text-slate-500 mb-2">Niche webhook URL nakho, etle dareke nava enquiry e jagya e auto pahochi jashe — Google Sheet, Notion, Zapier, Make, ke koi pan platform.</p>
    <details class="text-xs text-slate-500 mb-5 bg-amber-50 border border-amber-200 rounded-lg p-3">
      <summary class="cursor-pointer font-semibold text-amber-700">Webhook URL kya thi malshe? (click karo)</summary>
      <ul class="list-disc ml-5 mt-2 space-y-1">
        <li><b>Google Sheet:</b> Sheety.co / SheetDB.io / SheetMonkey par account banavi, sheet jodo ane temani API/webhook URL ahi paste karo. (Athva Zapier/Make vapro.)</li>
        <li><b>Notion:</b> Zapier ke Make.com ma "Webhook → Notion" automation banavo, ane temni "Catch Hook" URL ahi paste karo.</li>
        <li><b>Zapier:</b> Zap banavo → Trigger "Webhooks by Zapier → Catch Hook" → URL copy → ahi paste.</li>
        <li><b>Make.com:</b> "Custom webhook" module no address ahi paste karo.</li>
      </ul>
    </details>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 shadow-sm max-w-3xl">
      <input type="hidden" name="action" value="integrations">
      <div>
        <label class="text-sm font-semibold block mb-1">Webhook URL 1</label>
        <input type="url" name="webhook_url" value="<?= htmlspecialchars($settings["webhook_url"] ?? "") ?>" placeholder="https://hooks.zapier.com/..."
               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Webhook URL 2 (optional)</label>
        <input type="url" name="webhook_url2" value="<?= htmlspecialchars($settings["webhook_url2"] ?? "") ?>" placeholder="biji platform mate..."
               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Email Notification (optional)</label>
        <input type="email" name="notify_email" value="<?= htmlspecialchars($settings["notify_email"] ?? "") ?>" placeholder="tamaru@email.com"
               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
        <p class="text-xs text-slate-400 mt-1">Dareke nava enquiry e aa email par notification aavshe (hosting par email support hovu joie).</p>
      </div>
      <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors">Save Integrations</button>
    </form>
  </section>

</main>
<?php endif; ?>

</body>
</html>
