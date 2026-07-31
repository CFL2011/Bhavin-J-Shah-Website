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
$DEFAULT_CONTENT = $ROOT . "/content.example.json";
$UPLOAD_DIR  = $ROOT . "/uploads";
$UPLOAD_URL  = "uploads";                         // path used inside the website
$DATA_DIR    = $ROOT . "/data";
$ENQ_FILE    = $DATA_DIR . "/enquiries.json";
$SET_FILE    = $DATA_DIR . "/settings.json";

/* Image slots shown in the panel (key => friendly label) */
$SLOTS = [
    "hero_portrait"  => "Home – Hero Portrait",
    "about_portrait" => "About Bhavin – Portrait",
    "coach_portrait" => "About Coach For Life – Portrait",
    "program_1"      => "Program 1 – Business Growth Blueprint",
    "program_2"      => "Program 2 – Unleash Your Potential",
    "program_3"      => "Program 3 – Business Energy Formula",
    "program_4"      => "Program 4 – Get Set Go!",
    "program_5"      => "Program 5 – Be Productive Not Busy!",
];
$SLOT_SIZES = [
    "hero_portrait" => "800 x 1000 px (4:5 portrait)",
    "about_portrait" => "800 x 1000 px (4:5 portrait)",
    "coach_portrait" => "800 x 1000 px (4:5 portrait)",
    "program_1" => "1200 x 675 px (16:9 landscape)",
    "program_2" => "1200 x 675 px (16:9 landscape)",
    "program_3" => "1200 x 675 px (16:9 landscape)",
    "program_4" => "1200 x 675 px (16:9 landscape)",
    "program_5" => "1200 x 675 px (16:9 landscape)",
];
$FIELDS = [
    "phone" => "Contact Number",
    "email" => "Email Address",
    "location" => "Full Office Address",
];
$TEXTS = [
    "hero_heading" => "Hero – Heading",
    "hero_accent" => "Hero – Gold Highlight",
    "hero_intro" => "Hero – Intro Paragraph",
    "hero_primary_button" => "Hero – Primary Button",
    "hero_secondary_button" => "Hero – Secondary Button",
    "hero_mission_heading" => "Hero – Mission Heading",
    "hero_mission_text" => "Hero – Mission Text",
    "hero_quote" => "Hero – Quote",
    "stat_years" => "Statistics – Years Value",
    "stat_years_label" => "Statistics – Years Label",
    "stat_lives" => "Statistics – Lives Touched Value",
    "stat_lives_label" => "Statistics – Lives Touched Label",
    "stat_coaching" => "Statistics – One-to-One Value",
    "stat_coaching_label" => "Statistics – One-to-One Label",
    "stat_workshops" => "Statistics – Workshops Value",
    "stat_workshops_label" => "Statistics – Workshops Label",
    "about_heading" => "About Bhavin – Heading",
    "about_intro" => "About Bhavin – Paragraph 1",
    "about_body_1" => "About Bhavin – Paragraph 2",
    "about_body_2" => "About Bhavin – Paragraph 3",
    "about_callout" => "About Bhavin – Callout",
    "programs_heading" => "Programs – Heading",
    "programs_intro" => "Programs – Intro",
    "social_heading" => "Social Media – Heading",
    "social_intro" => "Social Media – Intro",
    "social_total" => "Social Media – Total Community Value",
    "social_total_label" => "Social Media – Total Community Label",
    "podcasts_heading" => "Podcasts – Heading",
    "podcasts_intro" => "Podcasts – Intro",
    "reviews_heading" => "Success Stories – Heading",
    "reviews_intro" => "Success Stories – Intro",
    "coach_heading" => "Coach For Life – Heading",
    "coach_body_1" => "Coach For Life – Paragraph 1",
    "coach_body_2" => "Coach For Life – Paragraph 2",
    "coach_body_3" => "Coach For Life – Paragraph 3",
    "contact_heading" => "Contact – Heading",
    "contact_intro" => "Contact – Intro",
    "enquiry_heading" => "Enquiry Form – Heading",
    "submit_button" => "Enquiry Form – Submit Button",
    "footer_about" => "Footer – About Text",
];
$COLORS = [
    "navy" => "Primary Navy",
    "navy_2" => "Secondary Navy",
    "ink" => "Main Text",
    "muted" => "Muted Text",
    "gold" => "Primary Gold",
    "gold_2" => "Light Gold",
    "line" => "Borders",
    "paper" => "Page Background",
    "white" => "White / Surface",
];
/* ---------- 2. HELPERS ---------- */
function load_content($file) {
    if (!file_exists($file)) return ["images" => [], "fields" => [], "texts" => [], "colors" => [], "programs" => [], "socials" => [], "podcasts" => [], "testimonials" => []];
    $d = json_decode(file_get_contents($file), true);
    if (!is_array($d)) $d = [];
    foreach (["images", "fields", "texts", "colors", "programs", "socials", "podcasts", "testimonials"] as $sec) {
        if (!isset($d[$sec]) || !is_array($d[$sec])) $d[$sec] = [];
    }
    return $d;
}
function merge_content_defaults($content, $defaults) {
    foreach ($defaults as $key => $defaultValue) {
        if (is_array($defaultValue)) {
            $currentValue = (isset($content[$key]) && is_array($content[$key])) ? $content[$key] : [];
            $content[$key] = merge_content_defaults($currentValue, $defaultValue);
        } elseif (!isset($content[$key]) || (is_string($content[$key]) && trim($content[$key]) === "")) {
            $content[$key] = $defaultValue;
        }
    }
    return $content;
}
function save_content($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
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
    $content = merge_content_defaults(load_content($CONTENT), load_content($DEFAULT_CONTENT));

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

    /* e) Save text blocks */
    if ($_POST["action"] === "texts") {
        foreach ($TEXTS as $k => $label) {
            if (isset($_POST[$k])) $content["texts"][$k] = trim($_POST[$k]);
        }
        save_content($CONTENT, $content);
        $notice = "Texts saved ✔";
    }

    if ($_POST["action"] === "colors") {
        foreach ($COLORS as $k => $label) {
            $value = trim($_POST[$k] ?? "");
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) $content["colors"][$k] = $value;
        }
        save_content($CONTENT, $content);
        $notice = "Theme colours saved";
    }

    if ($_POST["action"] === "programs") {
        $programs = [];
        for ($i = 0; $i < 5; $i++) {
            $programs[] = [
                "type" => trim($_POST["program_type_$i"] ?? ""),
                "name" => trim($_POST["program_name_$i"] ?? ""),
                "description" => trim($_POST["program_description_$i"] ?? ""),
                "link" => trim($_POST["program_link_$i"] ?? "#contact"),
            ];
        }
        $content["programs"] = $programs;
        save_content($CONTENT, $content);
        $notice = "Programs saved";
    }

    if ($_POST["action"] === "socials") {
        $socials = [];
        for ($i = 0; $i < 3; $i++) {
            $socials[] = [
                "platform" => trim($_POST["social_platform_$i"] ?? ""),
                "action" => trim($_POST["social_action_$i"] ?? ""),
                "count" => trim($_POST["social_count_$i"] ?? ""),
                "metric" => trim($_POST["social_metric_$i"] ?? ""),
                "handle" => trim($_POST["social_handle_$i"] ?? ""),
                "cta" => trim($_POST["social_cta_$i"] ?? ""),
                "url" => trim($_POST["social_url_$i"] ?? ""),
            ];
        }
        $content["socials"] = $socials;
        save_content($CONTENT, $content);
        $notice = "Social media cards saved";
    }

    if ($_POST["action"] === "podcasts") {
        $podcasts = [];
        for ($i = 0; $i < 4; $i++) {
            $url = trim($_POST["podcast_url_$i"] ?? "");
            $videoId = "";
            if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~i', $url, $matches)) $videoId = $matches[1];
            $podcasts[] = [
                "title" => trim($_POST["podcast_title_$i"] ?? ""),
                "url" => $url,
                "thumbnail" => $videoId ? "https://i.ytimg.com/vi/" . $videoId . "/hqdefault.jpg" : trim($_POST["podcast_thumbnail_$i"] ?? ""),
            ];
        }
        $content["podcasts"] = $podcasts;
        save_content($CONTENT, $content);
        $notice = "Podcast carousel saved";
    }

    if ($_POST["action"] === "testimonials") {
        $testimonials = [];
        for ($i = 0; $i < 6; $i++) {
            $testimonials[] = [
                "quote" => trim($_POST["testimonial_quote_$i"] ?? ""),
                "name" => trim($_POST["testimonial_name_$i"] ?? ""),
            ];
        }
        $content["testimonials"] = $testimonials;
        save_content($CONTENT, $content);
        $notice = "Testimonials saved";
    }

    if ($_POST["action"] === "advanced") {
        $decoded = json_decode($_POST["site_json"] ?? "", true);
        if (is_array($decoded)) {
            save_content($CONTENT, $decoded);
            $content = load_content($CONTENT);
            $notice = "Complete website configuration saved";
        } else {
            $notice = "JSON is invalid. Nothing was changed.";
        }
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
$content   = merge_content_defaults(load_content($CONTENT), load_content($DEFAULT_CONTENT));
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
      <p class="text-[11px] text-amber-400 tracking-widest">WEBSITE CONTENT &amp; ENQUIRY MANAGER</p>
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
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Current Website Images</h2>
    <p class="text-sm text-slate-500 mb-5">Hero, about, video, Coach For Life ane 5 program images ahi update karo.</p>
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
              <span class="text-xs text-slate-500 text-center px-3">Current frontend image is active.<br>Upload only to replace it.</span>
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
    <p class="text-sm text-slate-500 mb-5">Website contact section ma dekhata contact number, email ane full address ahi badlo.</p>
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



  <!-- TEXT BLOCKS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Website Texts</h2>
    <p class="text-sm text-slate-500 mb-5">Darek field ma current frontend text pehle thi bharayelu che. Label batave che ke website no kayo part change thashe.</p>
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




  <!-- PROGRAMS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Program Cards</h2>
    <p class="text-sm text-slate-500 mb-5">Current website na 5 programs na type, name ane description ahi badlo.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="programs">
      <?php for ($i = 0; $i < 5; $i++): $program = $content["programs"][$i] ?? []; ?>
      <div class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-2 gap-4 shadow-sm">
        <div><label class="text-sm font-semibold block mb-1">Program <?= $i + 1 ?> – Name</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="program_name_<?= $i ?>" value="<?= htmlspecialchars($program["name"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">Type</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="program_type_<?= $i ?>" value="<?= htmlspecialchars($program["type"] ?? "") ?>"></div>
        <div class="md:col-span-2"><label class="text-sm font-semibold block mb-1">Description</label><textarea rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="program_description_<?= $i ?>"><?= htmlspecialchars($program["description"] ?? "") ?></textarea></div>
        <div class="md:col-span-2"><label class="text-sm font-semibold block mb-1">Inquire Now Button Link</label><input type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="program_link_<?= $i ?>" value="<?= htmlspecialchars($program["link"] ?? "#contact") ?>" placeholder="https://example.com/landing-page"><p class="text-xs text-slate-500 mt-1">Landing page, payment page, WhatsApp URL or #contact link paste karo.</p></div>
      </div>
      <?php endfor; ?>
      <button class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Save Programs</button>
    </form>
  </section>

  <!-- SOCIAL MEDIA -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Social Media Cards</h2>
    <p class="text-sm text-slate-500 mb-5">Instagram, YouTube ane Facebook na follower count, handle, button text ane profile link ahi update karo.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="socials">
      <?php for ($i = 0; $i < 3; $i++): $social = $content["socials"][$i] ?? []; ?>
      <div class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-2 gap-4 shadow-sm">
        <div><label class="text-sm font-semibold block mb-1">Platform</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_platform_<?= $i ?>" value="<?= htmlspecialchars($social["platform"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">Top Badge</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_action_<?= $i ?>" value="<?= htmlspecialchars($social["action"] ?? "") ?>" placeholder="Follow, Subscribe or Connect"></div>
        <div><label class="text-sm font-semibold block mb-1">Follower / Subscriber Count</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_count_<?= $i ?>" value="<?= htmlspecialchars($social["count"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">Count Label</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_metric_<?= $i ?>" value="<?= htmlspecialchars($social["metric"] ?? "") ?>" placeholder="Followers or Subscribers"></div>
        <div><label class="text-sm font-semibold block mb-1">Handle / Channel Name</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_handle_<?= $i ?>" value="<?= htmlspecialchars($social["handle"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">Button Text</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_cta_<?= $i ?>" value="<?= htmlspecialchars($social["cta"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">Profile / Channel Link</label><input type="url" class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="social_url_<?= $i ?>" value="<?= htmlspecialchars($social["url"] ?? "") ?>"></div>
      </div>
      <?php endfor; ?>
      <button class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Save Social Media</button>
    </form>
  </section>

  <!-- PODCASTS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Podcast Carousel</h2>
    <p class="text-sm text-slate-500 mb-5">Website par social media section niche dekhata 4 podcasts na title ane YouTube link ahi update karo. Thumbnail YouTube link mathi automatically aavshe.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="podcasts">
      <?php for ($i = 0; $i < 4; $i++): $podcast = $content["podcasts"][$i] ?? []; ?>
      <div class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-2 gap-4 shadow-sm">
        <div class="md:col-span-2 flex items-center gap-4">
          <?php if (!empty($podcast["thumbnail"])): $podcastThumb = preg_match('~^https?://~i', $podcast["thumbnail"]) ? $podcast["thumbnail"] : "../" . $podcast["thumbnail"]; ?><img src="<?= htmlspecialchars($podcastThumb) ?>" alt="Podcast <?= $i + 1 ?> thumbnail" class="w-36 aspect-video object-cover rounded-lg border"><?php endif; ?>
          <strong>Podcast <?= $i + 1 ?></strong>
        </div>
        <div><label class="text-sm font-semibold block mb-1">Podcast Title</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="podcast_title_<?= $i ?>" value="<?= htmlspecialchars($podcast["title"] ?? "") ?>"></div>
        <div><label class="text-sm font-semibold block mb-1">YouTube Link</label><input type="url" class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="podcast_url_<?= $i ?>" value="<?= htmlspecialchars($podcast["url"] ?? "") ?>"></div>
        <input type="hidden" name="podcast_thumbnail_<?= $i ?>" value="<?= htmlspecialchars($podcast["thumbnail"] ?? "") ?>">
      </div>
      <?php endfor; ?>
      <button class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Save Podcasts</button>
    </form>
  </section>

  <!-- TESTIMONIALS -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Success Stories / Testimonials</h2>
    <p class="text-sm text-slate-500 mb-5">Review quote ane person name update karo.</p>
    <form method="post" class="space-y-4">
      <input type="hidden" name="action" value="testimonials">
      <?php for ($i = 0; $i < 6; $i++): $testimonial = $content["testimonials"][$i] ?? []; ?>
      <div class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 md:grid-cols-3 gap-4 shadow-sm">
        <div class="md:col-span-2"><label class="text-sm font-semibold block mb-1">Testimonial <?= $i + 1 ?></label><textarea rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="testimonial_quote_<?= $i ?>"><?= htmlspecialchars($testimonial["quote"] ?? "") ?></textarea></div>
        <div><label class="text-sm font-semibold block mb-1">Name</label><input class="w-full border border-slate-300 rounded-lg px-3 py-2.5" name="testimonial_name_<?= $i ?>" value="<?= htmlspecialchars($testimonial["name"] ?? "") ?>"></div>
      </div>
      <?php endfor; ?>
      <button class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Save Testimonials</button>
    </form>
  </section>

  <!-- THEME -->
  <section>
    <h2 class="text-lg font-extrabold text-slate-900 mb-1">Theme Colours</h2>
    <p class="text-sm text-slate-500 mb-5">Website na global colours ahi update karo.</p>
    <form method="post" class="bg-white rounded-xl border border-slate-200 p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 shadow-sm">
      <input type="hidden" name="action" value="colors">
      <?php foreach ($COLORS as $k => $label): $value = $content["colors"][$k] ?? "#000000"; ?>
      <label class="text-sm font-semibold"><span class="block mb-1"><?= htmlspecialchars($label) ?></span><span class="flex gap-2"><input type="color" name="<?= $k ?>" value="<?= htmlspecialchars($value) ?>" class="h-11 w-14 border rounded"><input value="<?= htmlspecialchars($value) ?>" readonly class="flex-1 border border-slate-300 rounded-lg px-3"></span></label>
      <?php endforeach; ?>
      <div class="sm:col-span-2 lg:col-span-3"><button class="bg-slate-900 text-white font-bold px-6 py-2.5 rounded-lg">Save Theme Colours</button></div>
    </form>
  </section>

  <!-- ADVANCED -->
  <section>
    <details class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
      <summary class="font-extrabold cursor-pointer">Advanced – Edit Complete Website Configuration</summary>
      <p class="text-sm text-slate-500 my-3">Every text, program, testimonial, image reference, contact field ane colour ek jagya thi edit kari shako. Valid JSON j save thashe.</p>
      <form method="post" class="space-y-3">
        <input type="hidden" name="action" value="advanced">
        <textarea name="site_json" rows="24" spellcheck="false" class="w-full font-mono text-xs border border-slate-300 rounded-lg px-3 py-2.5"><?= htmlspecialchars(json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></textarea>
        <button class="bg-amber-500 text-slate-900 font-bold px-6 py-2.5 rounded-lg">Save Complete Configuration</button>
      </form>
    </details>
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
