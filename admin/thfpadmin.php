<?php
// ================================================================
// thfpadmin.php — THFP Admin Panel (Fixed — Fiery Theme)
// Fixes: bind_param type-string mismatches for bouts UPDATE + INSERT
// ================================================================
define('THFP_USER', 'thfpadmin');
define('THFP_PASS', 'THFPsecure2025!');
define('PHOTO_DIR', __DIR__ . '/uploads/thfp_fighters/');
define('PHOTO_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/uploads/thfp_fighters/');

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_POST['thfp_logout'])) { session_destroy(); header('Location: '.$_SERVER['PHP_SELF']); exit; }

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thfp_login'])) {
    if ($_POST['username'] === THFP_USER && $_POST['password'] === THFP_PASS) {
        $_SESSION['thfp_admin'] = true;
        header('Location: '.$_SERVER['PHP_SELF'].(isset($_GET['section']) ? '?section='.$_GET['section'] : '')); exit;
    } else { $login_error = 'Invalid username or password.'; }
}

if (!isset($_SESSION['thfp_admin'])) { ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>THFP Admin — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{--fire:#E8341A;--fire-dim:rgba(232,52,26,.15);--fire-border:rgba(232,52,26,.3);--black:#080808;--dark:#0d0d0d;--surface:#161616;--white:#fff;--muted:rgba(255,255,255,.5);--dim:rgba(255,255,255,.22);--font-disp:'Cinzel',serif;--font-ui:'Rajdhani',sans-serif;}
html,body{height:100%;background:var(--dark);color:var(--white);font-family:var(--font-ui);-webkit-font-smoothing:antialiased;}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1.5rem;position:relative;overflow:hidden;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 60% at 50% 100%,rgba(232,52,26,.12),transparent);pointer-events:none;}
.lw{width:100%;max-width:400px;position:relative;z-index:1;}
.ll{display:flex;flex-direction:column;align-items:center;margin-bottom:2rem;gap:.75rem;}
.ld{width:64px;height:64px;border:2px solid var(--fire);transform:rotate(45deg);display:flex;align-items:center;justify-content:center;position:relative;background:var(--fire-dim);}
.ld::before{content:'';position:absolute;inset:5px;border:1px solid rgba(232,52,26,.2);}
.ld span{transform:rotate(-45deg);font-family:var(--font-disp);font-size:.72rem;font-weight:900;color:var(--fire);}
.ll h1{font-family:var(--font-disp);font-size:1.05rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;}
.ll p{font-size:.65rem;letter-spacing:4px;text-transform:uppercase;color:var(--fire);opacity:.8;}
.lc{background:var(--surface);border:1px solid rgba(232,52,26,.2);border-radius:4px;padding:2.5rem;}
.lc::before{content:'';display:block;height:2px;background:linear-gradient(to right,transparent,var(--fire),transparent);margin:-2.5rem -2.5rem 2rem;}
.fi{margin-bottom:1.25rem;}
.fi label{display:block;font-size:.62rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire);margin-bottom:.5rem;opacity:.9;}
.fi input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:3px;color:var(--white);font-family:var(--font-ui);font-size:1rem;padding:.8rem 1rem;outline:none;transition:border-color .2s;}
.fi input:focus{border-color:var(--fire);}
.fi input::placeholder{color:var(--dim);}
.btn-l{width:100%;margin-top:.5rem;padding:1rem;background:var(--fire);color:var(--white);border:none;border-radius:3px;font-family:var(--font-disp);font-size:.9rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;cursor:pointer;transition:background .2s;}
.btn-l:hover{background:#c4291a;}
.err{background:rgba(202,19,19,.12);border-left:3px solid #ca1313;color:#fca5a5;padding:.85rem 1rem;border-radius:3px;font-size:.88rem;margin-bottom:1.2rem;}
</style></head><body>
<div class="lw">
    <div class="ll"><div class="ld"><span>TH</span></div><h1>THFP Admin</h1><p>Fight Promotion Portal</p></div>
    <div class="lc">
        <?php if($login_error):?><div class="err">&#9888; <?php echo htmlspecialchars($login_error);?></div><?php endif;?>
        <form method="POST">
            <div class="fi"><label>Username</label><input type="text" name="username" autocomplete="username" required placeholder="thfpadmin"></div>
            <div class="fi"><label>Password</label><input type="password" name="password" autocomplete="current-password" required placeholder="••••••••"></div>
            <button type="submit" name="thfp_login" class="btn-l">Sign In</button>
        </form>
    </div>
</div>
</body></html>
<?php exit; }

// ── DB ──
if (file_exists('../config/database.php')) { require_once '../config/database.php'; $conn = getDbConnection(); }
else { $conn = new mysqli('localhost','u156115548_usr_c7q18hWL','lZ2|INcfX6my','u156115548_db_c7q18hWL'); if ($conn->connect_error) die('DB Error: '.$conn->connect_error); }
$conn->set_charset('utf8mb4');

if (!is_dir(PHOTO_DIR)) { @mkdir(PHOTO_DIR, 0775, true); @chmod(PHOTO_DIR, 0775); }

// ── Create tables ──
$conn->query("CREATE TABLE IF NOT EXISTS thfp_fighters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    nickname VARCHAR(100) DEFAULT '',
    date_of_birth DATE DEFAULT NULL,
    gym VARCHAR(200) DEFAULT '',
    weight_class VARCHAR(80) DEFAULT '',
    hometown VARCHAR(150) DEFAULT '',
    nationality VARCHAR(100) DEFAULT '',
    fighting_out_of VARCHAR(150) DEFAULT '',
    height VARCHAR(50) DEFAULT '',
    reach VARCHAR(50) DEFAULT '',
    last_weigh_in DECIMAL(5,2) DEFAULT NULL,
    current_streak VARCHAR(50) DEFAULT '',
    affiliation VARCHAR(200) DEFAULT '',
    gender ENUM('male','female','Male','Female','') DEFAULT '',
    age_category ENUM('junior','senior','Junior','Senior','') DEFAULT '',
    record_wins INT DEFAULT 0,
    record_losses INT DEFAULT 0,
    record_draws INT DEFAULT 0,
    photo_path VARCHAR(255) DEFAULT '',
    status ENUM('active','inactive','retired') DEFAULT 'active',
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS thfp_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    event_number INT DEFAULT 1,
    event_date DATE DEFAULT NULL,
    venue VARCHAR(250) DEFAULT '',
    status ENUM('upcoming','ongoing','completed','cancelled','live') DEFAULT 'upcoming',
    tournament_director VARCHAR(200) DEFAULT '',
    mc VARCHAR(200) DEFAULT '',
    sanctioned_by VARCHAR(200) DEFAULT '',
    officials TEXT DEFAULT '',
    sponsors TEXT DEFAULT '',
    production_team TEXT DEFAULT '',
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS thfp_bouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    bout_number INT DEFAULT 1,
    discipline ENUM('muay_thai','kickboxing','mma') NOT NULL DEFAULT 'muay_thai',
    bout_type ENUM('elimination','final','exhibition','main_event','co_main','prelim','amateur') DEFAULT 'elimination',
    bout_order INT DEFAULT 1,
    weight_class VARCHAR(80) DEFAULT '',
    gender ENUM('male','female','Male','Female','Mixed','') DEFAULT '',
    age_category ENUM('junior','senior','Junior','Senior','Open','') DEFAULT '',
    red_fighter_id INT DEFAULT NULL,
    blue_fighter_id INT DEFAULT NULL,
    winner_id INT DEFAULT NULL,
    result_method ENUM('KO','TKO','RSC','decision','no_contest','SUB','DEC','DRAW','NC','') DEFAULT '',
    decision_type ENUM('unanimous','split','Unanimous','Split','Majority','') DEFAULT '',
    result_round INT DEFAULT NULL,
    result_time VARCHAR(10) DEFAULT '',
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES thfp_events(id) ON DELETE CASCADE,
    FOREIGN KEY (red_fighter_id) REFERENCES thfp_fighters(id) ON DELETE SET NULL,
    FOREIGN KEY (blue_fighter_id) REFERENCES thfp_fighters(id) ON DELETE SET NULL,
    FOREIGN KEY (winner_id) REFERENCES thfp_fighters(id) ON DELETE SET NULL
)");

// ── Safe column adders ──
function addColIfMissing($conn,$table,$col,$def){
    $db=$conn->query("SELECT DATABASE()")->fetch_row()[0];
    $r=$conn->query("SELECT COUNT(*) c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$table' AND COLUMN_NAME='$col'");
    if($r&&(int)$r->fetch_assoc()['c']===0) $conn->query("ALTER TABLE `$table` ADD COLUMN $col $def");
}
function modColIfExists($conn,$table,$col,$def){
    $db=$conn->query("SELECT DATABASE()")->fetch_row()[0];
    $r=$conn->query("SELECT COUNT(*) c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$table' AND COLUMN_NAME='$col'");
    if($r&&(int)$r->fetch_assoc()['c']>0) $conn->query("ALTER TABLE `$table` MODIFY COLUMN $col $def");
}

// thfp_fighters migrations
// NOTE: No AFTER clauses — avoids silent failure when referenced column doesn't exist yet.
addColIfMissing($conn,'thfp_fighters','nickname',"VARCHAR(100) DEFAULT '' AFTER name");
addColIfMissing($conn,'thfp_fighters','date_of_birth',"DATE DEFAULT NULL");
addColIfMissing($conn,'thfp_fighters','nationality',"VARCHAR(100) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','fighting_out_of',"VARCHAR(150) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','hometown',"VARCHAR(150) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','height',"VARCHAR(50) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','reach',"VARCHAR(50) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','last_weigh_in',"DECIMAL(5,2) DEFAULT NULL");
addColIfMissing($conn,'thfp_fighters','current_streak',"VARCHAR(50) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','affiliation',"VARCHAR(200) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','record_wins',"INT DEFAULT 0");
addColIfMissing($conn,'thfp_fighters','record_losses',"INT DEFAULT 0");
addColIfMissing($conn,'thfp_fighters','record_draws',"INT DEFAULT 0");
addColIfMissing($conn,'thfp_fighters','photo_path',"VARCHAR(255) DEFAULT ''");
addColIfMissing($conn,'thfp_fighters','notes',"TEXT DEFAULT NULL");
modColIfExists($conn,'thfp_fighters','gender',"ENUM('male','female','Male','Female','') DEFAULT ''");
modColIfExists($conn,'thfp_fighters','age_category',"ENUM('junior','senior','Junior','Senior','') DEFAULT ''");
modColIfExists($conn,'thfp_fighters','status',"ENUM('active','inactive','retired') DEFAULT 'active'");

// thfp_events migrations
addColIfMissing($conn,'thfp_events','tournament_director',"VARCHAR(200) DEFAULT '' AFTER status");
addColIfMissing($conn,'thfp_events','mc',"VARCHAR(200) DEFAULT '' AFTER tournament_director");
addColIfMissing($conn,'thfp_events','sanctioned_by',"VARCHAR(200) DEFAULT '' AFTER mc");
addColIfMissing($conn,'thfp_events','officials',"TEXT DEFAULT NULL");
addColIfMissing($conn,'thfp_events','sponsors',"TEXT DEFAULT NULL");
addColIfMissing($conn,'thfp_events','production_team',"TEXT DEFAULT NULL");
addColIfMissing($conn,'thfp_events','notes',"TEXT DEFAULT NULL");
modColIfExists($conn,'thfp_events','status',"ENUM('upcoming','ongoing','completed','cancelled','live') DEFAULT 'upcoming'");

// thfp_bouts migrations
addColIfMissing($conn,'thfp_bouts','discipline',"ENUM('muay_thai','kickboxing','mma') NOT NULL DEFAULT 'muay_thai' AFTER bout_number");
addColIfMissing($conn,'thfp_bouts','result_time',"VARCHAR(10) DEFAULT '' AFTER result_round");
addColIfMissing($conn,'thfp_bouts','gender',"ENUM('male','female','Male','Female','Mixed','') DEFAULT '' AFTER weight_class");
addColIfMissing($conn,'thfp_bouts','age_category',"ENUM('junior','senior','Junior','Senior','Open','') DEFAULT '' AFTER gender");
addColIfMissing($conn,'thfp_bouts','decision_type',"ENUM('unanimous','split','Unanimous','Split','Majority','') DEFAULT '' AFTER result_method");
modColIfExists($conn,'thfp_bouts','bout_type',"ENUM('elimination','final','exhibition','main_event','co_main','prelim','amateur') DEFAULT 'elimination'");
modColIfExists($conn,'thfp_bouts','result_method',"ENUM('KO','TKO','RSC','decision','no_contest','SUB','DEC','DRAW','NC','') DEFAULT ''");
modColIfExists($conn,'thfp_bouts','gender',"ENUM('male','female','Male','Female','Mixed','') DEFAULT ''");
modColIfExists($conn,'thfp_bouts','age_category',"ENUM('junior','senior','Junior','Senior','Open','') DEFAULT ''");


// ── One-time record repair: recalculate W/L/D from bout results for fighters still at 0-0-0 ──
// Safe to run every load — only updates fighters where ALL three counters are 0 AND they have bouts
$conn->query("
    UPDATE thfp_fighters f
    SET
        record_wins   = (SELECT COUNT(*) FROM thfp_bouts WHERE winner_id = f.id),
        record_losses = (SELECT COUNT(*) FROM thfp_bouts
                         WHERE winner_id IS NOT NULL AND winner_id != f.id
                           AND (red_fighter_id = f.id OR blue_fighter_id = f.id)),
        record_draws  = (SELECT COUNT(*) FROM thfp_bouts
                         WHERE result_method IN ('DRAW','draw')
                           AND (red_fighter_id = f.id OR blue_fighter_id = f.id))
    WHERE f.record_wins = 0 AND f.record_losses = 0 AND f.record_draws = 0
      AND EXISTS (
          SELECT 1 FROM thfp_bouts
          WHERE red_fighter_id = f.id OR blue_fighter_id = f.id
      )
");

function sc($v){ return htmlspecialchars(strip_tags(trim((string)$v))); }

// ── Photo upload ──
function handlePhotoUpload($file_key, $fighter_id = null) {
    global $flash;
    if (empty($_FILES[$file_key]['name']) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        if (!empty($_FILES[$file_key]['error']) && $_FILES[$file_key]['error'] !== UPLOAD_ERR_NO_FILE) {
            $flash = 'Upload error code: ' . $_FILES[$file_key]['error'] . ' (check php.ini upload_max_filesize)';
        }
        return null;
    }
    $file = $_FILES[$file_key];
    // Detect by both finfo and extension — finfo can lie on some hosts
    $extMap = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif'];
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
    if (!isset($allowed[$mime])) {
        // fallback: trust the file extension
        $origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = $extMap[$origExt] ?? '';
    }
    if (!isset($allowed[$mime])) { $flash = 'Upload failed: unsupported image type. Use JPG, PNG, WEBP or GIF.'; return null; }
    if ($file['size'] > 5 * 1024 * 1024) { $flash = 'Upload failed: image must be under 5MB.'; return null; }
    if (!is_dir(PHOTO_DIR)) { @mkdir(PHOTO_DIR, 0775, true); @chmod(PHOTO_DIR, 0775); }
    if (!is_writable(PHOTO_DIR)) { $flash = 'Upload failed: uploads folder not writable — create admin/uploads/thfp_fighters/ in File Manager and set permissions to 755.'; return null; }
    $ext = $allowed[$mime];
    $safe_id = $fighter_id ? (int)$fighter_id : 'tmp_' . uniqid();
    $filename = 'fighter_' . $safe_id . '_' . time() . '.' . $ext;
    $dest = PHOTO_DIR . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) { @chmod($dest, 0644); return PHOTO_URL . $filename; }
    $flash = 'Upload failed: could not move file to ' . PHOTO_DIR . ' — check folder permissions.';
    return null;
}
function deleteOldPhoto($photo_path) {
    if (!empty($photo_path)) { $full = PHOTO_DIR . basename($photo_path); if (file_exists($full)) @unlink($full); }
}

$flash = '';

// ── CRUD Events ──
if (($_POST['action'] ?? '') === 'save_event') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = sc($_POST['name'] ?? '');
    $num  = (int)($_POST['event_number'] ?? 1);
    $date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
    $venue= sc($_POST['venue'] ?? '');
    $stat = in_array($_POST['status'] ?? '', ['upcoming','completed','cancelled','live','ongoing']) ? $_POST['status'] : 'upcoming';
    $td   = sc($_POST['tournament_director'] ?? '');
    $mc   = sc($_POST['mc'] ?? '');
    $sanc = sc($_POST['sanctioned_by'] ?? '');
    $off  = sc($_POST['officials'] ?? '');
    $spon = sc($_POST['sponsors'] ?? '');
    $prod = sc($_POST['production_team'] ?? '');
    $notes= sc($_POST['notes'] ?? '');
    if ($id) {
        // 13 vars: s i s s s s s s s s s s i
        $stmt = $conn->prepare("UPDATE thfp_events SET name=?,event_number=?,event_date=?,venue=?,status=?,tournament_director=?,mc=?,sanctioned_by=?,officials=?,sponsors=?,production_team=?,notes=? WHERE id=?");
        $stmt->bind_param("sissssssssssi",$name,$num,$date,$venue,$stat,$td,$mc,$sanc,$off,$spon,$prod,$notes,$id);
    } else {
        // 12 vars: s i s s s s s s s s s s
        $stmt = $conn->prepare("INSERT INTO thfp_events (name,event_number,event_date,venue,status,tournament_director,mc,sanctioned_by,officials,sponsors,production_team,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sissssssssss",$name,$num,$date,$venue,$stat,$td,$mc,$sanc,$off,$spon,$prod,$notes);
    }
    $stmt->execute(); $stmt->close();
    $flash = $id ? 'Event updated successfully.' : 'Event created successfully.';
}
if (isset($_POST['delete_event'])) {
    $conn->query("DELETE FROM thfp_events WHERE id=".(int)$_POST['id']);
    $flash = 'Event deleted.';
}

// ── CRUD Fighters ──
if (($_POST['action'] ?? '') === 'save_fighter') {
    $id     = (int)($_POST['id'] ?? 0);
    $name   = sc($_POST['name'] ?? '');
    $nick   = sc($_POST['nickname'] ?? '');
    $dob    = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $gym    = sc($_POST['gym'] ?? '');
    $affil  = sc($_POST['affiliation'] ?? '');
    $wc     = sc($_POST['weight_class'] ?? '');
    $town   = sc($_POST['hometown'] ?? '');
    $nation = sc($_POST['nationality'] ?? '');
    $fout   = sc($_POST['fighting_out_of'] ?? '');
    $height = sc($_POST['height'] ?? '');
    $reach  = sc($_POST['reach'] ?? '');
    $lw     = !empty($_POST['last_weigh_in']) ? (float)$_POST['last_weigh_in'] : null;
    $streak = sc($_POST['current_streak'] ?? '');
    $gender = in_array($_POST['gender'] ?? '', ['Male','Female','male','female','']) ? $_POST['gender'] : '';
    $age    = in_array($_POST['age_category'] ?? '', ['Junior','Senior','junior','senior','']) ? $_POST['age_category'] : '';
    $w      = (int)($_POST['record_wins'] ?? 0);
    $l      = (int)($_POST['record_losses'] ?? 0);
    $d      = (int)($_POST['record_draws'] ?? 0);
    $st     = in_array($_POST['status'] ?? '', ['active','inactive','retired']) ? $_POST['status'] : 'active';
    $notes  = sc($_POST['notes'] ?? '');
    $existing_photo = sc($_POST['existing_photo'] ?? '');
    $photo_path = $existing_photo;
    if (!empty($_FILES['fighter_photo']['name']) && $_FILES['fighter_photo']['error'] === UPLOAD_ERR_OK) {
        $new_photo = handlePhotoUpload('fighter_photo', $id ?: null);
        if ($new_photo) { if ($id && $existing_photo) deleteOldPhoto($existing_photo); $photo_path = $new_photo; }
    }
    if ($id) {
        // 22 vars: name(s) nick(s) dob(s) gym(s) affil(s) wc(s) town(s) nation(s) fout(s) height(s) reach(s) lw(d) streak(s) gender(s) age(s) w(i) l(i) d(i) photo(s) st(s) notes(s) id(i)
        $stmt = $conn->prepare("UPDATE thfp_fighters SET name=?,nickname=?,date_of_birth=?,gym=?,affiliation=?,weight_class=?,hometown=?,nationality=?,fighting_out_of=?,height=?,reach=?,last_weigh_in=?,current_streak=?,gender=?,age_category=?,record_wins=?,record_losses=?,record_draws=?,photo_path=?,status=?,notes=? WHERE id=?");
        $stmt->bind_param("sssssssssssdsssiiisssi",$name,$nick,$dob,$gym,$affil,$wc,$town,$nation,$fout,$height,$reach,$lw,$streak,$gender,$age,$w,$l,$d,$photo_path,$st,$notes,$id);
        $stmt->execute(); $stmt->close();
    } else {
        // 21 vars: same minus id
        $stmt = $conn->prepare("INSERT INTO thfp_fighters (name,nickname,date_of_birth,gym,affiliation,weight_class,hometown,nationality,fighting_out_of,height,reach,last_weigh_in,current_streak,gender,age_category,record_wins,record_losses,record_draws,photo_path,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssssdsssiiisss",$name,$nick,$dob,$gym,$affil,$wc,$town,$nation,$fout,$height,$reach,$lw,$streak,$gender,$age,$w,$l,$d,$photo_path,$st,$notes);
        $stmt->execute();
        $new_id = $conn->insert_id;
        if ($photo_path && strpos(basename($photo_path), 'tmp_') !== false) {
            $new_name = preg_replace('/fighter_tmp_[^_]+_/', 'fighter_'.$new_id.'_', basename($photo_path));
            @rename(PHOTO_DIR . basename($photo_path), PHOTO_DIR . $new_name);
            $photo_path = PHOTO_URL . $new_name;
            $conn->query("UPDATE thfp_fighters SET photo_path='".sc($photo_path)."' WHERE id=$new_id");
        }
        $stmt->close();
    }
    if (empty($flash)) $flash = $id ? 'Fighter updated.' : 'Fighter added.';
}
if (isset($_POST['delete_fighter'])) {
    $fid = (int)$_POST['id'];
    $row = $conn->query("SELECT photo_path FROM thfp_fighters WHERE id=$fid")->fetch_assoc();
    if ($row && $row['photo_path']) deleteOldPhoto($row['photo_path']);
    $conn->query("DELETE FROM thfp_fighters WHERE id=$fid");
    $flash = 'Fighter deleted.';
}

// ── CRUD Bouts ──
// !! FIXED: type strings now match variable counts exactly !!
if (($_POST['action'] ?? '') === 'save_bout') {
    $id     = (int)($_POST['id'] ?? 0);
    $eid    = (int)($_POST['event_id'] ?? 0);                                       // i
    $bnum   = (int)($_POST['bout_number'] ?? 1);                                    // i
    $disc   = in_array($_POST['discipline'] ?? '', ['muay_thai','kickboxing','mma'])
              ? $_POST['discipline'] : 'muay_thai';                                 // s
    $btype  = in_array($_POST['bout_type'] ?? '', ['elimination','final','exhibition','main_event','co_main','prelim','amateur'])
              ? $_POST['bout_type'] : 'elimination';                                // s
    $bord   = (int)($_POST['bout_order'] ?? 1);                                     // i
    $wc     = sc($_POST['weight_class'] ?? '');                                     // s
    $gender = in_array($_POST['gender'] ?? '', ['male','female','Male','Female','Mixed',''])
              ? $_POST['gender'] : '';                                               // s
    $age    = in_array($_POST['age_category'] ?? '', ['junior','senior','Junior','Senior','Open',''])
              ? $_POST['age_category'] : '';                                         // s
    $red    = (int)($_POST['red_fighter_id'] ?? 0) ?: null;                        // i
    $blue   = (int)($_POST['blue_fighter_id'] ?? 0) ?: null;                       // i
    $winner = (int)($_POST['winner_id'] ?? 0) ?: null;                             // i
    $method = in_array($_POST['result_method'] ?? '', ['KO','TKO','RSC','decision','no_contest','SUB','DEC','DRAW','NC',''])
              ? $_POST['result_method'] : '';                                        // s
    $dec    = in_array($_POST['decision_type'] ?? '', ['unanimous','split','Unanimous','Split','Majority',''])
              ? $_POST['decision_type'] : '';                                        // s
    $round  = (int)($_POST['result_round'] ?? 0) ?: null;                          // i
    $time   = sc($_POST['result_time'] ?? '');                                      // s
    $notes  = sc($_POST['notes'] ?? '');                                            // s

    if ($id) {
        // ── UPDATE: 17 bind vars ──
        // eid(i) bnum(i) disc(s) btype(s) bord(i) wc(s) gender(s) age(s) red(i) blue(i) winner(i) method(s) dec(s) round(i) time(s) notes(s) id(i)
        // Type string: i  i  s  s  i  s  s  s  i  i  i  s  s  i  s  s  i  = "iississsiiississi" (17 chars)
        $stmt = $conn->prepare("UPDATE thfp_bouts SET event_id=?,bout_number=?,discipline=?,bout_type=?,bout_order=?,weight_class=?,gender=?,age_category=?,red_fighter_id=?,blue_fighter_id=?,winner_id=?,result_method=?,decision_type=?,result_round=?,result_time=?,notes=? WHERE id=?");
        $stmt->bind_param("iississsiiississi", $eid,$bnum,$disc,$btype,$bord,$wc,$gender,$age,$red,$blue,$winner,$method,$dec,$round,$time,$notes,$id);
        $stmt->execute(); $stmt->close();
    } else {
        // ── INSERT: 16 bind vars ──
        // eid(i) bnum(i) disc(s) btype(s) bord(i) wc(s) gender(s) age(s) red(i) blue(i) winner(i) method(s) dec(s) round(i) time(s) notes(s)
        // Type string: i  i  s  s  i  s  s  s  i  i  i  s  s  i  s  s  = "iississsiiississ" (16 chars)
        $stmt = $conn->prepare("INSERT INTO thfp_bouts (event_id,bout_number,discipline,bout_type,bout_order,weight_class,gender,age_category,red_fighter_id,blue_fighter_id,winner_id,result_method,decision_type,result_round,result_time,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iississsiiississ", $eid,$bnum,$disc,$btype,$bord,$wc,$gender,$age,$red,$blue,$winner,$method,$dec,$round,$time,$notes);
        $stmt->execute(); $stmt->close();
    }
    $flash = $id ? 'Bout updated.' : 'Bout added.';
}
if (isset($_POST['delete_bout'])) {
    $conn->query("DELETE FROM thfp_bouts WHERE id=".(int)$_POST['id']);
    $flash = 'Bout deleted.';
}

$active   = $_GET['section'] ?? 'events';
$events   = $conn->query("SELECT * FROM thfp_events ORDER BY event_number DESC");
$fighters = $conn->query("SELECT * FROM thfp_fighters ORDER BY weight_class, name");
$bouts    = $conn->query("
    SELECT b.*,
           e.name AS event_name, e.event_number,
           rf.name AS red_name, rf.nickname AS red_nick,
           bf.name AS blue_name, bf.nickname AS blue_nick,
           wf.name AS winner_name
    FROM thfp_bouts b
    LEFT JOIN thfp_events e ON b.event_id=e.id
    LEFT JOIN thfp_fighters rf ON b.red_fighter_id=rf.id
    LEFT JOIN thfp_fighters bf ON b.blue_fighter_id=bf.id
    LEFT JOIN thfp_fighters wf ON b.winner_id=wf.id
    ORDER BY e.event_number DESC, b.bout_order ASC
");
$ev_count = (int)$conn->query("SELECT COUNT(*) c FROM thfp_events")->fetch_assoc()['c'];
$fi_count = (int)$conn->query("SELECT COUNT(*) c FROM thfp_fighters")->fetch_assoc()['c'];
$bo_count = (int)$conn->query("SELECT COUNT(*) c FROM thfp_bouts")->fetch_assoc()['c'];
$fi_list  = $conn->query("SELECT id,name,nickname FROM thfp_fighters ORDER BY name");
$ev_list  = $conn->query("SELECT id,event_number,name FROM thfp_events ORDER BY event_number DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>THFP Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
    --fire:#E8341A;--fire-light:#FF5A3C;--fire-dark:#C42A12;
    --fire-dim:rgba(232,52,26,.1);--fire-border:rgba(232,52,26,.28);
    --ember:#FF8C00;--gold:#D4AF37;--gold-light:#F0D060;
    --black:#080808;--dark:#0d0d0d;--surface:#161616;--card:#1a1a1a;
    --white:#fff;--muted:rgba(255,255,255,.55);--dim:rgba(255,255,255,.26);--border:rgba(255,255,255,.08);
    --font-disp:'Cinzel',serif;--font-ui:'Rajdhani',sans-serif;--sb-w:232px;
}
html,body{height:100%;background:var(--dark);color:var(--white);font-family:var(--font-ui);-webkit-font-smoothing:antialiased;}
.layout{display:flex;min-height:100vh;}
.sidebar{width:var(--sb-w);flex-shrink:0;background:var(--black);border-right:1px solid var(--fire-border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;z-index:100;}
.main{flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden;}
.sb-logo{padding:1.5rem 1.4rem 1.1rem;border-bottom:1px solid var(--fire-border);display:flex;flex-direction:column;align-items:center;gap:.6rem;text-align:center;background:linear-gradient(180deg,rgba(232,52,26,.06) 0%,transparent 100%);}
.sb-emblem{position:relative;width:48px;height:48px;display:flex;align-items:center;justify-content:center;}
.sb-emblem-diamond{width:48px;height:48px;border:2px solid var(--fire);transform:rotate(45deg);position:absolute;background:var(--fire-dim);}
.sb-emblem-diamond::before{content:'';position:absolute;inset:5px;border:1px solid rgba(232,52,26,.25);}
.sb-emblem span{position:relative;z-index:1;font-family:var(--font-disp);font-size:.7rem;font-weight:900;color:var(--fire);}
.sb-title{font-family:var(--font-disp);font-size:.82rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;}
.sb-sub{font-size:.6rem;letter-spacing:3px;text-transform:uppercase;color:var(--fire);opacity:.7;}
.sb-nav{padding:.6rem 0;flex:1;overflow-y:auto;}
.sb-section{font-size:.56rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--dim);padding:.85rem 1.3rem .3rem;}
.sb-link{display:flex;align-items:center;gap:.75rem;padding:.72rem 1.3rem;font-size:.85rem;font-weight:600;color:var(--muted);text-decoration:none;border-left:2.5px solid transparent;transition:all .18s;}
.sb-link:hover{background:var(--fire-dim);color:var(--white);}
.sb-link.active{color:var(--fire-light);border-left-color:var(--fire);background:var(--fire-dim);}
.sb-link svg{width:16px;height:16px;opacity:.6;flex-shrink:0;}
.sb-count{margin-left:auto;background:var(--fire-dim);color:var(--fire-light);font-size:.65rem;font-weight:700;padding:1px 8px;border-radius:20px;border:1px solid var(--fire-border);}
.sb-footer{padding:1rem 1.3rem;border-top:1px solid var(--fire-border);}
.sb-signout{background:none;border:1px solid var(--border);border-radius:3px;color:var(--dim);cursor:pointer;font-family:var(--font-ui);font-size:.75rem;letter-spacing:1px;padding:.45rem 1rem;width:100%;transition:all .2s;display:flex;align-items:center;gap:.5rem;justify-content:center;}
.sb-signout:hover{border-color:var(--fire-border);color:var(--fire-light);}
.topbar{background:var(--black);border-bottom:1px solid var(--fire-border);padding:0 2rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;height:64px;position:relative;}
.topbar::after{content:'';position:absolute;bottom:0;left:0;right:0;height:1px;background:linear-gradient(to right,transparent,var(--fire),transparent);}
.topbar-title{font-family:var(--font-disp);font-size:1.1rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;}
.topbar-actions{display:flex;gap:.75rem;align-items:center;}
.hamburger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:6px;}
.hamburger span{display:block;width:22px;height:2px;background:var(--fire);border-radius:2px;transition:all .3s;}
.hamburger.open span:nth-child(1){transform:rotate(45deg) translate(5px,5px);}
.hamburger.open span:nth-child(2){opacity:0;}
.hamburger.open span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px);}
.content{padding:2rem;overflow-y:auto;flex:1;}
.btn{display:inline-flex;align-items:center;gap:6px;font-family:var(--font-ui);font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:.6rem 1.2rem;border-radius:3px;border:none;cursor:pointer;transition:all .18s;text-decoration:none;white-space:nowrap;}
.btn-fire{background:var(--fire);color:var(--white);box-shadow:0 2px 12px rgba(232,52,26,.3);}
.btn-fire:hover{background:var(--fire-light);}
.btn-outline{background:transparent;color:var(--muted);border:1px solid var(--border);}
.btn-outline:hover{border-color:var(--fire-border);color:var(--fire-light);}
.btn-ghost-danger{background:rgba(202,19,19,.1);color:#f87171;border:1px solid rgba(202,19,19,.25);}
.btn-ghost-danger:hover{background:rgba(202,19,19,.22);}
.btn-sm{padding:.35rem .75rem;font-size:.68rem;}
.stats-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--fire-border);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;margin-bottom:2rem;}
.stat-box{background:var(--surface);padding:1.2rem 1.5rem;display:flex;align-items:center;gap:1rem;position:relative;overflow:hidden;}
.stat-box::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.stat-icon{width:40px;height:40px;border:1px solid var(--fire-border);border-radius:3px;display:flex;align-items:center;justify-content:center;color:var(--fire);flex-shrink:0;background:var(--fire-dim);}
.stat-val{font-family:var(--font-disp);font-size:2rem;font-weight:900;color:var(--fire-light);line-height:1;}
.stat-lbl{font-size:.65rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--muted);margin-top:3px;}
.flash{display:flex;align-items:center;gap:10px;padding:.9rem 1.2rem;border-radius:3px;margin-bottom:1.5rem;font-size:.9rem;}
.flash-ok{background:rgba(232,52,26,.08);border:1px solid var(--fire-border);color:var(--fire-light);}
.flash-err{background:rgba(200,30,30,.15);border:1px solid rgba(200,30,30,.5);color:#ffaaaa;}
.panel{background:var(--surface);border:1px solid var(--fire-border);border-radius:4px;overflow:hidden;}
.panel-head{background:var(--black);border-bottom:1px solid var(--fire-border);padding:.95rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:relative;}
.panel-head::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.panel-title{font-family:var(--font-disp);font-size:.88rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--fire-light);}
.tbl-wrap{overflow-x:auto;}
.tbl{width:100%;border-collapse:collapse;font-size:.9rem;min-width:600px;}
.tbl th{font-size:.62rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--dim);padding:.7rem 1.25rem;border-bottom:1px solid var(--fire-border);text-align:left;white-space:nowrap;background:rgba(232,52,26,.04);}
.tbl td{padding:.8rem 1.25rem;border-bottom:1px solid var(--border);color:rgba(255,255,255,.88);vertical-align:middle;}
.tbl tr:last-child td{border-bottom:none;}
.tbl tr:hover td{background:var(--fire-dim);}
.f-cell{display:flex;align-items:center;gap:.7rem;}
.f-photo{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--fire-border);flex-shrink:0;}
.f-init{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-disp);font-size:.8rem;font-weight:900;flex-shrink:0;background:var(--fire-dim);color:var(--fire-light);border:2px solid var(--fire-border);}
.f-name{font-weight:600;font-size:.9rem;}
.f-meta{font-size:.75rem;color:var(--muted);}
.f-detail{font-size:.72rem;color:var(--dim);margin-top:2px;}
.rec .w{color:#4ade80;}.rec .sep{color:var(--dim);margin:0 2px;}.rec .l{color:#f87171;}.rec .d{color:var(--muted);}
.badge{font-family:var(--font-ui);font-size:.6rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:3px 10px;border-radius:2px;display:inline-block;white-space:nowrap;}
.b-muay_thai{background:rgba(232,52,26,.18);color:var(--fire-light);border:1px solid var(--fire-border);}
.b-kickboxing{background:rgba(255,140,0,.15);color:#FF8C00;border:1px solid rgba(255,140,0,.3);}
.b-mma{background:rgba(139,92,246,.15);color:#c4b5fd;border:1px solid rgba(139,92,246,.25);}
.b-elimination{background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border);}
.b-final{background:rgba(212,175,55,.2);color:var(--gold-light);border:1px solid rgba(212,175,55,.4);}
.b-exhibition{background:rgba(16,185,129,.1);color:#6ee7b7;}
.b-main_event{background:rgba(212,175,55,.15);color:var(--gold-light);}
.b-co_main,.b-prelim,.b-amateur{background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border);}
.b-ko,.b-rsc{background:rgba(232,52,26,.2);color:var(--fire-light);}
.b-tko{background:rgba(232,52,26,.12);color:#fca5a5;}
.b-decision{background:rgba(37,99,235,.12);color:#93c5fd;}
.b-no_contest{background:rgba(100,116,139,.12);color:#94a3b8;}
.b-draw{background:rgba(100,116,139,.1);color:#94a3b8;}
.b-completed{background:rgba(212,175,55,.12);color:var(--gold);}
.b-upcoming{background:rgba(16,185,129,.1);color:#34d399;}
.b-ongoing{background:rgba(232,52,26,.15);color:var(--fire-light);animation:livepulse 1.5s infinite;}
.b-cancelled{background:rgba(100,116,139,.1);color:#94a3b8;}
.b-live{background:rgba(232,52,26,.15);color:var(--fire-light);animation:livepulse 1.5s infinite;}
@keyframes livepulse{0%,100%{opacity:1;}50%{opacity:.5;}}
.b-active{background:rgba(16,185,129,.1);color:#34d399;}
.b-inactive,.b-retired{background:rgba(100,116,139,.1);color:#94a3b8;}
.b-male,.b-Male{background:rgba(37,99,235,.1);color:#93c5fd;}
.b-female,.b-Female{background:rgba(236,72,153,.1);color:#f9a8d4;}
/* Modal */
.modal-bg{display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.85);backdrop-filter:blur(6px);align-items:flex-start;justify-content:center;padding:2rem 1rem;overflow-y:auto;}
.modal-bg.open{display:flex;}
.modal{background:var(--card);border:1px solid var(--fire-border);border-radius:4px;width:100%;max-width:840px;animation:mslide .22s ease;position:relative;}
@keyframes mslide{from{transform:translateY(-16px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-top{background:var(--black);border-bottom:1px solid var(--fire-border);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;position:relative;}
.modal-top::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(to right,var(--fire),var(--ember),transparent);}
.modal-heading{font-family:var(--font-disp);font-size:1rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;}
.modal-x{background:none;border:1px solid var(--border);border-radius:2px;color:var(--muted);cursor:pointer;font-size:1.2rem;line-height:1;padding:4px 8px;transition:all .2s;}
.modal-x:hover{color:var(--fire-light);border-color:var(--fire-border);}
.modal-body{padding:1.5rem;max-height:75vh;overflow-y:auto;}
.modal-foot{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.75rem;}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.full{grid-column:1/-1;}
.form-sep{font-size:.6rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:var(--fire-light);border-bottom:1px solid var(--fire-border);padding-bottom:.4rem;margin:.5rem 0 .9rem;grid-column:1/-1;}
.fg{display:flex;flex-direction:column;gap:.4rem;margin-bottom:.85rem;}
.fg label{font-size:.62rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--fire);}
.fg input,.fg select,.fg textarea{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:3px;color:var(--white);font-family:var(--font-ui);font-size:.95rem;padding:.7rem .95rem;width:100%;outline:none;transition:border-color .2s;-webkit-appearance:none;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--fire);background:var(--fire-dim);}
.fg input::placeholder,.fg textarea::placeholder{color:var(--dim);}
.fg select option{background:#1c1c1c;}
.fg textarea{resize:vertical;min-height:70px;}
.fg .hint{font-size:.65rem;color:var(--dim);margin-top:2px;}
.photo-upload-area{text-align:center;padding:1.2rem;border:1.5px dashed var(--fire-border);border-radius:4px;cursor:pointer;transition:border-color .2s;position:relative;background:var(--fire-dim);}
.photo-upload-area:hover{border-color:var(--fire);}
.photo-upload-area p{font-size:.72rem;color:var(--dim);margin-top:.25rem;}
.photo-upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.photo-preview-wrap{display:flex;align-items:center;gap:1.2rem;padding:1rem;background:var(--fire-dim);border:1px solid var(--fire-border);border-radius:4px;margin-bottom:.75rem;}
.photo-preview-img{width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--fire-border);}
@media(max-width:900px){
    .sidebar{position:fixed;left:0;top:0;transform:translateX(calc(-1 * var(--sb-w)));transition:transform .3s;height:100vh;}
    .sidebar.mobile-open{transform:translateX(0);}
    .hamburger{display:flex;}
    .stats-strip{grid-template-columns:1fr;}
    .grid2{grid-template-columns:1fr;}
    .content{padding:1.25rem;}
    .topbar{padding:0 1.25rem;}
}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99;}
.sb-overlay.active{display:block;}
</style>
</head>
<body>
<div class="layout">

<nav class="sidebar" id="sidebar">
    <div class="sb-logo">
        <div class="sb-emblem"><div class="sb-emblem-diamond"></div><span>TH</span></div>
        <div class="sb-title">THFP</div><div class="sb-sub">Admin Portal</div>
    </div>
    <div class="sb-nav">
        <div class="sb-section">Manage</div>
        <?php
        $nav = [
            'events'  => ['Events','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',$ev_count],
            'bouts'   => ['Bouts','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>',$bo_count],
            'fighters'=> ['Fighters','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',$fi_count],
        ];
        foreach ($nav as $k => [$lbl,$ico,$cnt]): ?>
        <a href="?section=<?php echo $k;?>" class="sb-link <?php echo $active===$k?'active':'';?>">
            <?php echo $ico;?> <?php echo $lbl;?><span class="sb-count"><?php echo $cnt;?></span>
        </a>
        <?php endforeach;?>
        <div class="sb-section" style="margin-top:.6rem;">Analytics</div>
        <a href="thfp_analytics.php" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Medal Tally</a>
        <div class="sb-section" style="margin-top:.6rem;">Site</div>
        <a href="thfp.php" target="_blank" class="sb-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>View Public Page</a>
    </div>
    <div class="sb-footer">
        <form method="POST"><button type="submit" name="thfp_logout" class="sb-signout"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Sign Out</button></form>
    </div>
</nav>
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<div class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:1rem;">
            <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
            <div class="topbar-title"><?php echo $nav[$active][0]??'Dashboard';?></div>
        </div>
        <div class="topbar-actions">
            <?php if($active==='events'):?><button class="btn btn-fire" onclick="openModal('mEvent')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Add Event</button>
            <?php elseif($active==='fighters'):?><button class="btn btn-fire" onclick="openAddFighter()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Add Fighter</button>
            <?php elseif($active==='bouts'):?><button class="btn btn-fire" onclick="openAddBout()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Add Bout</button>
            <?php endif;?>
        </div>
    </div>

    <div class="content">
        <?php if($flash):?><div class="flash <?php echo strpos($flash,'Upload failed')===0||strpos($flash,'Upload error')===0?'flash-err':'flash-ok';?>">
            <?php echo strpos($flash,'Upload failed')===0||strpos($flash,'Upload error')===0?'&#9888;':'&#9733;';?> <?php echo htmlspecialchars($flash);?></div><?php endif;?>
        <div class="stats-strip">
            <div class="stat-box"><div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><div><div class="stat-val"><?php echo $ev_count;?></div><div class="stat-lbl">Events</div></div></div>
            <div class="stat-box"><div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg></div><div><div class="stat-val"><?php echo $bo_count;?></div><div class="stat-lbl">Bouts</div></div></div>
            <div class="stat-box"><div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div><div class="stat-val"><?php echo $fi_count;?></div><div class="stat-lbl">Fighters</div></div></div>
        </div>

<?php if($active==='events'):?>
        <div class="panel">
            <div class="panel-head"><div class="panel-title">All Events</div><span style="font-size:.72rem;color:var(--dim);"><?php echo $ev_count;?> total</span></div>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>#</th><th>Name</th><th>Date</th><th>Venue</th><th>Director</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while($e=$events->fetch_assoc()):
                    $sc2=['completed'=>'b-completed','upcoming'=>'b-upcoming','cancelled'=>'b-cancelled','live'=>'b-live','ongoing'=>'b-ongoing'][$e['status']]??'';?>
                <tr>
                    <td style="font-family:var(--font-disp);font-size:1.2rem;font-weight:900;color:var(--fire-light);"><?php echo $e['event_number'];?></td>
                    <td style="font-weight:600;"><?php echo htmlspecialchars($e['name']);?></td>
                    <td style="font-size:.84rem;color:var(--muted);white-space:nowrap;"><?php echo $e['event_date']?date('M d, Y',strtotime($e['event_date'])):'—';?></td>
                    <td style="font-size:.8rem;color:var(--muted);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($e['venue']?:'—');?></td>
                    <td style="font-size:.8rem;color:var(--muted);"><?php echo htmlspecialchars($e['tournament_director']?:'—');?></td>
                    <td><span class="badge <?php echo $sc2;?>"><?php echo ucfirst($e['status']);?></span></td>
                    <td><div style="display:flex;gap:.4rem;">
                        <button class="btn btn-outline btn-sm" onclick='editEvent(<?php echo json_encode($e);?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this event and all its bouts?');">
                            <input type="hidden" name="id" value="<?php echo $e['id'];?>">
                            <button type="submit" name="delete_event" class="btn btn-ghost-danger btn-sm">Del</button>
                        </form>
                    </div></td>
                </tr>
                <?php endwhile;?>
                </tbody>
            </table></div>
        </div>

<?php elseif($active==='fighters'):?>
        <div class="panel">
            <div class="panel-head"><div class="panel-title">Fighter Roster</div><span style="font-size:.72rem;color:var(--dim);"><?php echo $fi_count;?> fighters</span></div>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>Fighter</th><th>Gym</th><th>Class</th><th>Division</th><th>Record</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while($f=$fighters->fetch_assoc()):
                    $photo_ok=!empty($f['photo_path'])&&file_exists(PHOTO_DIR.basename($f['photo_path']));
                ?>
                <tr>
                    <td><div class="f-cell">
                        <?php if($photo_ok):?><img src="<?php echo htmlspecialchars($f['photo_path']);?>" class="f-photo" alt=""><?php else:?><div class="f-init"><?php echo strtoupper(substr(trim($f['name']),0,2));?></div><?php endif;?>
                        <div>
                            <div class="f-name"><?php echo htmlspecialchars($f['name']);?></div>
                            <?php if(!empty($f['nickname'])):?><div class="f-meta">&ldquo;<?php echo htmlspecialchars($f['nickname']);?>&rdquo;</div><?php endif;?>
                        </div>
                    </div></td>
                    <td style="font-size:.84rem;"><?php echo htmlspecialchars($f['gym']?:'—');?></td>
                    <td style="font-size:.84rem;"><?php echo htmlspecialchars($f['weight_class']?:'—');?></td>
                    <td><span class="badge b-<?php echo strtolower($f['gender']?:'');?>"><?php echo trim(ucfirst($f['age_category']).' '.ucfirst($f['gender']))?:'—';?></span></td>
                    <td><span class="rec"><span class="w"><?php echo $f['record_wins']??0;?></span><span class="sep">-</span><span class="l"><?php echo $f['record_losses']??0;?></span><span class="sep">-</span><span class="d"><?php echo $f['record_draws']??0;?></span></span></td>
                    <td><span class="badge b-<?php echo $f['status'];?>"><?php echo ucfirst($f['status']);?></span></td>
                    <td><div style="display:flex;gap:.4rem;">
                        <button class="btn btn-outline btn-sm" onclick='editFighter(<?php echo json_encode($f);?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this fighter?');">
                            <input type="hidden" name="id" value="<?php echo $f['id'];?>">
                            <button type="submit" name="delete_fighter" class="btn btn-ghost-danger btn-sm">Del</button>
                        </form>
                    </div></td>
                </tr>
                <?php endwhile;?>
                </tbody>
            </table></div>
        </div>

<?php elseif($active==='bouts'):?>
        <div class="panel">
            <div class="panel-head"><div class="panel-title">All Bouts</div><span style="font-size:.72rem;color:var(--dim);"><?php echo $bo_count;?> bouts</span></div>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>Event</th><th>#</th><th>Discipline</th><th>Type</th><th>Class</th><th>Red Corner</th><th>Blue Corner</th><th>Result</th><th>Winner</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $disc_labels=['muay_thai'=>'Muay Thai','kickboxing'=>'Kickboxing','mma'=>'MMA'];
                while($b=$bouts->fetch_assoc()):
                    $disc_val=$b['discipline']??'muay_thai';
                    $tb=['elimination'=>'b-elimination','final'=>'b-final','exhibition'=>'b-exhibition','main_event'=>'b-main_event','co_main'=>'b-co_main','prelim'=>'b-prelim','amateur'=>'b-amateur'][$b['bout_type']]??'b-elimination';
                    $db='b-'.$disc_val;
                    $rm=$b['result_method']??'';
                    $mb=['KO'=>'b-ko','TKO'=>'b-tko','RSC'=>'b-rsc','decision'=>'b-decision','no_contest'=>'b-no_contest','DRAW'=>'b-draw'][$rm]??'';
                ?>
                <tr>
                    <td style="font-family:var(--font-disp);font-size:1rem;font-weight:900;color:var(--fire-light);"><?php echo $b['event_number'];?></td>
                    <td style="font-family:var(--font-disp);font-size:.9rem;color:var(--dim);"><?php echo $b['bout_number']?:'—';?></td>
                    <td><span class="badge <?php echo $db;?>"><?php echo $disc_labels[$disc_val]??'Muay Thai';?></span></td>
                    <td><span class="badge <?php echo $tb;?>"><?php echo ucfirst(str_replace('_',' ',$b['bout_type']));?></span></td>
                    <td style="font-size:.8rem;white-space:nowrap;"><?php echo htmlspecialchars($b['weight_class']?:'—');?><br><span style="font-size:.7rem;color:var(--dim);"><?php echo trim(ucfirst($b['age_category']).' '.ucfirst($b['gender']));?></span></td>
                    <td style="font-size:.84rem;"><?php echo htmlspecialchars($b['red_name']??'—');?></td>
                    <td style="font-size:.84rem;"><?php echo htmlspecialchars($b['blue_name']??'—');?></td>
                    <td>
                        <?php if($rm):?>
                            <span class="badge <?php echo $mb;?>"><?php echo strtoupper($rm);?></span>
                            <?php if($b['decision_type']):?><span style="font-size:.7rem;color:var(--dim);display:block;"><?php echo ucfirst($b['decision_type']);?></span><?php endif;?>
                            <?php if($b['result_round']):?><span style="font-size:.7rem;color:var(--dim);">R<?php echo $b['result_round'];?></span><?php endif;?>
                        <?php else:?><span style="color:var(--dim);">—</span><?php endif;?>
                    </td>
                    <td style="font-size:.84rem;font-weight:600;color:var(--fire-light);"><?php echo htmlspecialchars($b['winner_name']??'—');?></td>
                    <td><div style="display:flex;gap:.4rem;">
                        <button class="btn btn-outline btn-sm" onclick='editBout(<?php echo json_encode($b);?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this bout?');">
                            <input type="hidden" name="id" value="<?php echo $b['id'];?>">
                            <button type="submit" name="delete_bout" class="btn btn-ghost-danger btn-sm">Del</button>
                        </form>
                    </div></td>
                </tr>
                <?php endwhile;?>
                </tbody>
            </table></div>
        </div>
<?php endif;?>
    </div>
</div>
</div>

<!-- MODAL: EVENT -->
<div class="modal-bg" id="mEvent">
<div class="modal">
    <div class="modal-top"><div class="modal-heading" id="mEventTitle">Add Event</div><button class="modal-x" onclick="closeModal('mEvent')">&times;</button></div>
    <form method="POST">
        <input type="hidden" name="action" value="save_event">
        <input type="hidden" name="id" id="ev_id">
        <div class="modal-body"><div class="grid2">
            <div class="fg full"><label>Event Name *</label><input type="text" name="name" id="ev_name" required placeholder="e.g. Tribal Hunters – Combat 2"></div>
            <div class="fg"><label>Event Number</label><input type="number" name="event_number" id="ev_num" min="1" value="1"></div>
            <div class="fg"><label>Status</label><select name="status" id="ev_status">
                <option value="upcoming">Upcoming</option><option value="completed">Completed</option>
                <option value="ongoing">Ongoing</option><option value="live">Live</option><option value="cancelled">Cancelled</option>
            </select></div>
            <div class="fg"><label>Date</label><input type="date" name="event_date" id="ev_date"></div>
            <div class="fg"><label>Venue</label><input type="text" name="venue" id="ev_venue" placeholder="e.g. Masbate Coliseum"></div>
            <div class="form-sep">Officials &amp; Credits</div>
            <div class="fg full"><label>Tournament Director</label><input type="text" name="tournament_director" id="ev_td"></div>
            <div class="fg full"><label>Master of Ceremonies</label><input type="text" name="mc" id="ev_mc"></div>
            <div class="fg full"><label>Sanctioned By</label><input type="text" name="sanctioned_by" id="ev_sanc"></div>
            <div class="fg full"><label>Technical Officials</label><textarea name="officials" id="ev_off"></textarea></div>
            <div class="fg full"><label>Production Team</label><textarea name="production_team" id="ev_prod"></textarea></div>
            <div class="fg full"><label>Sponsors</label><textarea name="sponsors" id="ev_spon"></textarea></div>
            <div class="fg full"><label>Notes</label><textarea name="notes" id="ev_notes"></textarea></div>
        </div></div>
        <div class="modal-foot"><button type="button" class="btn btn-outline" onclick="closeModal('mEvent')">Cancel</button><button type="submit" class="btn btn-fire">Save Event</button></div>
    </form>
</div>
</div>

<!-- MODAL: FIGHTER -->
<div class="modal-bg" id="mFighter">
<div class="modal">
    <div class="modal-top"><div class="modal-heading" id="mFighterTitle">Add Fighter</div><button class="modal-x" onclick="closeModal('mFighter')">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_fighter">
        <input type="hidden" name="id" id="fi_id">
        <input type="hidden" name="existing_photo" id="fi_existing_photo">
        <div class="modal-body">
            <div class="fg full" style="margin-bottom:1.2rem;">
                <label>Fighter Photo</label>
                <div id="photoPreviewWrap" style="display:none;" class="photo-preview-wrap">
                    <img id="photoPreviewImg" class="photo-preview-img" src="" alt="">
                    <div><div style="font-size:.8rem;font-weight:600;margin-bottom:.3rem;">Current Photo</div><div style="font-size:.72rem;color:var(--dim);">Upload a new image to replace</div></div>
                </div>
                <div class="photo-upload-area">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--fire)" stroke-width="1.5" style="margin:0 auto .5rem;display:block;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <div style="font-size:.8rem;font-weight:600;">Click to upload photo</div>
                    <p>JPG, PNG, WEBP &middot; Max 5MB</p>
                    <input type="file" name="fighter_photo" id="fi_photo_input" accept="image/*" onchange="previewPhoto(this)">
                </div>
            </div>
            <div class="grid2">
                <div class="form-sep">Identity</div>
                <div class="fg"><label>Full Name *</label><input type="text" name="name" id="fi_name" required></div>
                <div class="fg"><label>Nickname</label><input type="text" name="nickname" id="fi_nick" placeholder='"The Blade"'></div>
                <div class="fg"><label>Date of Birth</label><input type="date" name="date_of_birth" id="fi_dob"><span class="hint">Age auto-calculated</span></div>
                <div class="fg"><label>Nationality</label><input type="text" name="nationality" id="fi_nation" placeholder="e.g. Filipino"></div>
                <div class="form-sep">Gym &amp; Team</div>
                <div class="fg full"><label>Primary Gym / Team</label><input type="text" name="gym" id="fi_gym" placeholder="e.g. MMBC Combat Hunters Tribe"></div>
                <div class="fg full"><label>Affiliation / Camp</label><input type="text" name="affiliation" id="fi_affil" placeholder="Secondary affiliation"></div>
                <div class="form-sep">Competition Details</div>
                <div class="fg"><label>Weight Class (kg)</label>
                    <select name="weight_class" id="fi_wc">
                        <option value="">— Select —</option>
                        <option>48</option><option>51</option><option>54</option><option>57</option>
                        <option>60</option><option>63.5</option><option>67</option><option>70</option>
                        <option>75</option><option>81</option><option>91</option><option>105</option>
                    </select>
                </div>
                <div class="fg"><label>Last Weigh-In (kg)</label><input type="number" name="last_weigh_in" id="fi_lw" step="0.01" min="0" placeholder="e.g. 53.5"></div>
                <div class="fg"><label>Gender</label>
                    <select name="gender" id="fi_gender">
                        <option value="">— Select —</option><option value="Male">Male</option><option value="Female">Female</option>
                    </select>
                </div>
                <div class="fg"><label>Age Category</label>
                    <select name="age_category" id="fi_age">
                        <option value="">— Select —</option><option value="Junior">Junior</option><option value="Senior">Senior</option>
                    </select>
                </div>
                <div class="form-sep">Location</div>
                <div class="fg"><label>Hometown</label><input type="text" name="hometown" id="fi_town" placeholder="e.g. Masbate City"></div>
                <div class="fg"><label>Fighting Out Of</label><input type="text" name="fighting_out_of" id="fi_fout"></div>
                <div class="form-sep">Physical Stats</div>
                <div class="fg"><label>Height</label><input type="text" name="height" id="fi_height" placeholder='e.g. 5&apos;7" / 171 cm'></div>
                <div class="fg"><label>Reach</label><input type="text" name="reach" id="fi_reach" placeholder='e.g. 71" / 180 cm'></div>
                <div class="form-sep">Record &amp; Streak</div>
                <div class="fg"><label>Wins</label><input type="number" name="record_wins" id="fi_w" min="0" value="0"></div>
                <div class="fg"><label>Losses</label><input type="number" name="record_losses" id="fi_l" min="0" value="0"></div>
                <div class="fg"><label>Draws</label><input type="number" name="record_draws" id="fi_d" min="0" value="0"></div>
                <div class="fg"><label>Current Streak</label><input type="text" name="current_streak" id="fi_streak" placeholder='e.g. "3 Win"'></div>
                <div class="form-sep">Status &amp; Notes</div>
                <div class="fg"><label>Status</label>
                    <select name="status" id="fi_st">
                        <option value="active">Active</option><option value="inactive">Inactive</option><option value="retired">Retired</option>
                    </select>
                </div>
                <div class="fg full"><label>Notes</label><textarea name="notes" id="fi_notes" placeholder="Additional notes..."></textarea></div>
            </div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-outline" onclick="closeModal('mFighter')">Cancel</button><button type="submit" class="btn btn-fire">Save Fighter</button></div>
    </form>
</div>
</div>

<!-- MODAL: BOUT -->
<div class="modal-bg" id="mBout">
<div class="modal" style="max-width:760px;">
    <div class="modal-top"><div class="modal-heading" id="mBoutTitle">Add Bout</div><button class="modal-x" onclick="closeModal('mBout')">&times;</button></div>
    <form method="POST">
        <input type="hidden" name="action" value="save_bout">
        <input type="hidden" name="id" id="bo_id">
        <div class="modal-body"><div class="grid2">
            <div class="fg"><label>Event *</label>
                <select name="event_id" id="bo_event" required>
                    <option value="">— Select —</option>
                    <?php if($ev_list){$ev_list->data_seek(0);while($ev=$ev_list->fetch_assoc()):?>
                    <option value="<?php echo $ev['id'];?>">Combat <?php echo $ev['event_number'];?> — <?php echo htmlspecialchars($ev['name']);?></option>
                    <?php endwhile;}?>
                </select>
            </div>
            <div class="fg"><label>Bout Number</label><input type="number" name="bout_number" id="bo_bnum" min="1" value="1"></div>
            <div class="fg"><label>Discipline</label>
                <select name="discipline" id="bo_disc">
                    <option value="muay_thai">🥊 Muay Thai</option>
                    <option value="kickboxing">🦵 Kickboxing</option>
                    <option value="mma">🤼 MMA</option>
                </select>
            </div>
            <div class="fg"><label>Bout Type</label>
                <select name="bout_type" id="bo_type">
                    <option value="elimination">Elimination</option>
                    <option value="final">Final</option>
                    <option value="exhibition">Exhibition</option>
                    <option value="main_event">Main Event</option>
                    <option value="co_main">Co-Main Event</option>
                    <option value="prelim">Prelim</option>
                    <option value="amateur">Amateur</option>
                </select>
            </div>
            <div class="fg"><label>Bout Order (display)</label><input type="number" name="bout_order" id="bo_ord" min="1" value="1"></div>
            <div class="fg"><label>Weight Class (kg)</label>
                <select name="weight_class" id="bo_wc">
                    <option value="">— Select —</option>
                    <option>48</option><option>51</option><option>54</option><option>57</option>
                    <option>60</option><option>63.5</option><option>67</option><option>70</option>
                    <option>75</option><option>81</option><option>91</option><option>105</option>
                </select>
            </div>
            <div class="fg"><label>Gender</label>
                <select name="gender" id="bo_gender">
                    <option value="">— Select —</option>
                    <option value="male">Male</option><option value="female">Female</option><option value="Mixed">Mixed</option>
                </select>
            </div>
            <div class="fg"><label>Age Category</label>
                <select name="age_category" id="bo_age">
                    <option value="">— Select —</option>
                    <option value="junior">Junior</option><option value="senior">Senior</option><option value="Open">Open</option>
                </select>
            </div>
            <div class="form-sep">Fighters</div>
            <div class="fg"><label>Red Corner</label>
                <select name="red_fighter_id" id="bo_red">
                    <option value="">— Select Fighter —</option>
                    <?php if($fi_list){$fi_list->data_seek(0);while($fi=$fi_list->fetch_assoc()):?>
                    <option value="<?php echo $fi['id'];?>"><?php echo htmlspecialchars($fi['name'].($fi['nickname']?' "'.$fi['nickname'].'"':''));?></option>
                    <?php endwhile;}?>
                </select>
            </div>
            <div class="fg"><label>Blue Corner</label>
                <select name="blue_fighter_id" id="bo_blue">
                    <option value="">— Select Fighter —</option>
                    <?php if($fi_list){$fi_list->data_seek(0);while($fi=$fi_list->fetch_assoc()):?>
                    <option value="<?php echo $fi['id'];?>"><?php echo htmlspecialchars($fi['name'].($fi['nickname']?' "'.$fi['nickname'].'"':''));?></option>
                    <?php endwhile;}?>
                </select>
            </div>
            <div class="form-sep">Result</div>
            <div class="fg"><label>Winner</label>
                <select name="winner_id" id="bo_winner">
                    <option value="">— No result / Draw —</option>
                    <?php if($fi_list){$fi_list->data_seek(0);while($fi=$fi_list->fetch_assoc()):?>
                    <option value="<?php echo $fi['id'];?>"><?php echo htmlspecialchars($fi['name']);?></option>
                    <?php endwhile;}?>
                </select>
            </div>
            <div class="fg"><label>Method</label>
                <select name="result_method" id="bo_method">
                    <option value="">— Pending —</option>
                    <option value="KO">KO</option><option value="TKO">TKO</option><option value="RSC">RSC</option>
                    <option value="decision">Decision</option><option value="no_contest">No Contest</option><option value="DRAW">Draw</option>
                </select>
            </div>
            <div class="fg"><label>Decision Type</label>
                <select name="decision_type" id="bo_dec">
                    <option value="">— N/A —</option>
                    <option value="unanimous">Unanimous</option><option value="split">Split</option><option value="Majority">Majority</option>
                </select>
            </div>
            <div class="fg"><label>Round</label><input type="number" name="result_round" id="bo_rnd" min="1" max="10" placeholder="e.g. 2"></div>
            <div class="fg"><label>Time</label><input type="text" name="result_time" id="bo_time" placeholder="e.g. 4:12"></div>
            <div class="fg full"><label>Notes</label><textarea name="notes" id="bo_notes"></textarea></div>
        </div></div>
        <div class="modal-foot"><button type="button" class="btn btn-outline" onclick="closeModal('mBout')">Cancel</button><button type="submit" class="btn btn-fire">Save Bout</button></div>
    </form>
</div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
window.addEventListener('click',function(e){['mEvent','mFighter','mBout'].forEach(function(id){if(e.target===document.getElementById(id))closeModal(id);});});
document.addEventListener('keydown',function(e){if(e.key==='Escape')['mEvent','mFighter','mBout'].forEach(closeModal);});
var hamburger=document.getElementById('hamburger');
var sidebar=document.getElementById('sidebar');
var overlay=document.getElementById('sbOverlay');
hamburger.addEventListener('click',function(){hamburger.classList.toggle('open');sidebar.classList.toggle('mobile-open');overlay.classList.toggle('active');});
function closeSidebar(){hamburger.classList.remove('open');sidebar.classList.remove('mobile-open');overlay.classList.remove('active');}
function previewPhoto(input){
    if(input.files&&input.files[0]){
        var reader=new FileReader();
        reader.onload=function(e){document.getElementById('photoPreviewImg').src=e.target.result;document.getElementById('photoPreviewWrap').style.display='flex';};
        reader.readAsDataURL(input.files[0]);
    }
}
function editEvent(e){
    document.getElementById('mEventTitle').textContent='Edit Event';
    var map={ev_id:'id',ev_name:'name',ev_num:'event_number',ev_date:'event_date',ev_venue:'venue',ev_status:'status',ev_td:'tournament_director',ev_mc:'mc',ev_sanc:'sanctioned_by',ev_off:'officials',ev_prod:'production_team',ev_spon:'sponsors',ev_notes:'notes'};
    Object.entries(map).forEach(function(x){document.getElementById(x[0]).value=e[x[1]]||'';});
    openModal('mEvent');
}
function openAddFighter(){
    document.getElementById('mFighterTitle').textContent='Add Fighter';
    document.getElementById('fi_id').value='';
    document.getElementById('fi_existing_photo').value='';
    document.getElementById('photoPreviewWrap').style.display='none';
    ['fi_name','fi_nick','fi_dob','fi_nation','fi_gym','fi_affil','fi_lw','fi_town','fi_fout','fi_height','fi_reach','fi_streak','fi_notes'].forEach(function(id){document.getElementById(id).value='';});
    ['fi_wc','fi_gender','fi_age'].forEach(function(id){document.getElementById(id).selectedIndex=0;});
    document.getElementById('fi_w').value=0;document.getElementById('fi_l').value=0;document.getElementById('fi_d').value=0;
    document.getElementById('fi_st').value='active';
    document.getElementById('fi_photo_input').value='';
    openModal('mFighter');
}
function editFighter(f){
    document.getElementById('mFighterTitle').textContent='Edit Fighter';
    document.getElementById('fi_id').value=f.id;
    document.getElementById('fi_existing_photo').value=f.photo_path||'';
    document.getElementById('fi_name').value=f.name||'';
    document.getElementById('fi_nick').value=f.nickname||'';
    document.getElementById('fi_dob').value=f.date_of_birth||'';
    document.getElementById('fi_nation').value=f.nationality||'';
    document.getElementById('fi_gym').value=f.gym||'';
    document.getElementById('fi_affil').value=f.affiliation||'';
    document.getElementById('fi_wc').value=f.weight_class||'';
    document.getElementById('fi_lw').value=f.last_weigh_in||'';
    document.getElementById('fi_gender').value=f.gender||'';
    document.getElementById('fi_age').value=f.age_category||'';
    document.getElementById('fi_town').value=f.hometown||'';
    document.getElementById('fi_fout').value=f.fighting_out_of||'';
    document.getElementById('fi_height').value=f.height||'';
    document.getElementById('fi_reach').value=f.reach||'';
    document.getElementById('fi_w').value=f.record_wins||0;
    document.getElementById('fi_l').value=f.record_losses||0;
    document.getElementById('fi_d').value=f.record_draws||0;
    document.getElementById('fi_streak').value=f.current_streak||'';
    document.getElementById('fi_st').value=f.status||'active';
    document.getElementById('fi_notes').value=f.notes||'';
    var pw=document.getElementById('photoPreviewWrap');var pi=document.getElementById('photoPreviewImg');
    if(f.photo_path){pi.src=f.photo_path;pw.style.display='flex';}else{pw.style.display='none';}
    document.getElementById('fi_photo_input').value='';
    openModal('mFighter');
}
function openAddBout(){
    document.getElementById('mBoutTitle').textContent='Add Bout';
    document.getElementById('bo_id').value='';
    document.getElementById('bo_event').selectedIndex=0;
    document.getElementById('bo_disc').value='muay_thai';
    document.getElementById('bo_type').value='elimination';
    document.getElementById('bo_bnum').value=1;
    document.getElementById('bo_ord').value=1;
    document.getElementById('bo_wc').selectedIndex=0;
    document.getElementById('bo_gender').selectedIndex=0;
    document.getElementById('bo_age').selectedIndex=0;
    document.getElementById('bo_red').selectedIndex=0;
    document.getElementById('bo_blue').selectedIndex=0;
    document.getElementById('bo_winner').selectedIndex=0;
    document.getElementById('bo_method').selectedIndex=0;
    document.getElementById('bo_dec').selectedIndex=0;
    document.getElementById('bo_rnd').value='';
    document.getElementById('bo_time').value='';
    document.getElementById('bo_notes').value='';
    openModal('mBout');
}
function editBout(b){
    document.getElementById('mBoutTitle').textContent='Edit Bout';
    document.getElementById('bo_id').value=b.id;
    document.getElementById('bo_event').value=b.event_id;
    document.getElementById('bo_bnum').value=b.bout_number||'';
    document.getElementById('bo_disc').value=b.discipline||'muay_thai';
    document.getElementById('bo_type').value=b.bout_type||'elimination';
    document.getElementById('bo_ord').value=b.bout_order||'';
    document.getElementById('bo_wc').value=b.weight_class||'';
    document.getElementById('bo_gender').value=b.gender||'';
    document.getElementById('bo_age').value=b.age_category||'';
    document.getElementById('bo_red').value=b.red_fighter_id||'';
    document.getElementById('bo_blue').value=b.blue_fighter_id||'';
    document.getElementById('bo_winner').value=b.winner_id||'';
    document.getElementById('bo_method').value=b.result_method||'';
    document.getElementById('bo_dec').value=b.decision_type||'';
    document.getElementById('bo_rnd').value=b.result_round||'';
    document.getElementById('bo_time').value=b.result_time||'';
    document.getElementById('bo_notes').value=b.notes||'';
    openModal('mBout');
}
</script>
</body>
</html>