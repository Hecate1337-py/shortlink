<?php
/**
 * TWITTER STYLE SHORTLINK SYSTEM (Single File Auto-Installer)
 * Features: Twitter-like loading screen, Clean URLs, Auto .htaccess creation, Modern Admin Panel.
 * Author: [Your Name/GitHub Username]
 */

// --- CONFIGURATION ---
$admin_password   = "12345";                 // Admin Login Password
$secret_path      = "panel";                 // Admin Access: domain.com/v/?panel
$fallback_url     = "https://videqlix.live"; // Redirect destination if link is invalid/direct access
$db_filename      = "database.json";         // JSON Database filename
$loading_duration = 3;                       // Loading duration in seconds

// =============================================================
// PART 1: AUTO-INSTALLER (.HTACCESS)
// =============================================================
// This block ensures clean URLs (e.g., domain.com/v/AbCd1) work automatically.
$htaccess_content = "RewriteEngine On
<Files \"$db_filename\">
    Order Allow,Deny
    Deny from all
</Files>
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9-]+)$ index.php?v=$1 [L,QSA]";

if (!file_exists('.htaccess')) {
    @file_put_contents('.htaccess', $htaccess_content);
    // Refresh page to apply server settings immediately
    echo "<meta http-equiv='refresh' content='0'>";
    exit();
}

// =============================================================
// PART 2: SYSTEM LOGIC
// =============================================================
session_start();
error_reporting(0);

// Helper: Get clean base URL
function getBaseUrl() {
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $path = str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
    return $protocol . $_SERVER['HTTP_HOST'] . $path;
}

// Database Initialization
if (!file_exists($db_filename)) file_put_contents($db_filename, json_encode([]));
$links = json_decode(file_get_contents($db_filename), true);
if (!is_array($links)) $links = [];

// =============================================================
// PART 3: VISITOR VIEW (TWITTER STYLE LOADER)
// =============================================================
if (isset($_GET['v']) && $_GET['v'] != $secret_path) {
    $code = $_GET['v'];
    
    if (isset($links[$code])) {
        $target_url = $links[$code]['url'];
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
          <title>Checking browser Wait...</title>
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
          <style>
            * { margin:0; padding:0; }
            body, html { height:100%; background:#ffffff; overflow:hidden; }
            .loader { position:fixed; inset:0; display:flex; flex-direction:column; justify-content:center; align-items:center; color:#fff; font-family:Arial,Helvetica,sans-serif; background:#000; transition:opacity .4s ease; z-index:9999; }
            .spinner { position: relative; width:60px; height:60px; border:6px solid #222; border-top-color:#00ccff; border-radius:50%; animation:spin 1s linear infinite; margin-bottom:20px; }
            .spinner i { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 28px; color: #00ccff; }
            @keyframes spin { to { transform:rotate(360deg); } }
            .text { font-size:1.1rem; }
            .fade { opacity:0; }
          </style>
        </head>
        <body>
        <div class="loader" id="loader">
          <div class="spinner"><i class="fa-brands fa-x-twitter"></i></div>
          <div class="text">Checking browser Wait...</div>
        </div>
        <script>
          setTimeout(() => {
            const loader = document.getElementById('loader');
            loader.classList.add('fade');
            setTimeout(() => { window.location = '<?php echo $target_url; ?>'; }, 400);
          }, <?php echo $loading_duration * 1000; ?>);
        </script>
        </body>
        </html>
        <?php
        exit();
    } else {
        // Invalid code -> Redirect to fallback
        header("Location: " . $fallback_url);
        exit();
    }
}

// =============================================================
// PART 4: ADMIN PANEL (MODERN UI)
// =============================================================
elseif (isset($_GET[$secret_path])) {
    $msg = "";
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Login Logic
        if (isset($_POST['auth_pass'])) {
            if ($_POST['auth_pass'] === $admin_password) $_SESSION['admin_logged'] = true;
            else $msg = "Invalid Password!";
        }
        // Logout Logic
        if (isset($_POST['logout'])) { session_destroy(); header("Refresh:0"); exit(); }
        
        // CRUD Logic
        if (isset($_SESSION['admin_logged'])) {
            if (isset($_POST['long_url'])) {
                $url = trim($_POST['long_url']);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $id = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
                    $links[$id] = ['url' => $url, 'created_at' => date('M d, H:i')]; 
                    file_put_contents($db_filename, json_encode($links));
                    $generated_link = getBaseUrl() . $id;
                }
            }
            if (isset($_POST['delete_id'])) {
                unset($links[$_POST['delete_id']]);
                file_put_contents($db_filename, json_encode($links));
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Link Manager</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
        <style>
            :root { --bg: #121212; --card: #1e1e1e; --text: #e2e8f0; --accent: #0ea5e9; --danger: #ef4444; }
            body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--text); margin: 0; display: flex; justify-content: center; min-height: 100vh; padding: 20px; box-sizing: border-box; }
            .container { width: 100%; max-width: 500px; }
            .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
            .header h2 { margin: 0; font-size: 1.5rem; font-weight: 600; color: #fff; }
            .logout-btn { background: transparent; border: 1px solid #333; color: #888; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; font-size: 0.85rem;}
            .logout-btn:hover { background: #333; color: #fff; }
            .login-card { background: var(--card); padding: 30px; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3); border: 1px solid #333; }
            .login-input { width: 100%; padding: 12px; margin-bottom: 15px; background: #2a2a2a; border: 1px solid #444; color: white; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.2s; }
            .login-input:focus { border-color: var(--accent); }
            .action-box { background: var(--card); padding: 20px; border-radius: 16px; border: 1px solid #333; margin-bottom: 20px; }
            .input-group { display: flex; gap: 10px; }
            .main-input { flex: 1; background: #2a2a2a; border: 1px solid #444; color: #fff; padding: 12px; border-radius: 8px; outline: none; }
            .main-input:focus { border-color: var(--accent); }
            .btn-primary { background: var(--accent); color: #000; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; white-space: nowrap;}
            .btn-primary:hover { background: #38bdf8; }
            .result-box { background: rgba(14, 165, 233, 0.1); border: 1px solid var(--accent); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; animation: fadeIn 0.3s ease; }
            .result-link { color: var(--accent); font-weight: 600; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-right: 10px; }
            .copy-btn-main { background: var(--accent); color: #000; border: none; padding: 8px 15px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 5px; }
            .list-header { font-size: 0.9rem; color: #888; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
            .link-item { background: var(--card); padding: 15px; border-radius: 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #2a2a2a; transition: 0.2s; }
            .link-item:hover { border-color: #444; transform: translateY(-2px); }
            .link-info { overflow: hidden; flex: 1; margin-right: 10px; }
            .short-url { color: #fff; font-weight: 600; font-size: 1rem; display: block; margin-bottom: 4px; cursor: pointer; transition: 0.2s; }
            .short-url:hover { color: var(--accent); }
            .original-url { color: #666; font-size: 0.8rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .actions { display: flex; gap: 8px; }
            .btn-icon { width: 36px; height: 36px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; font-size: 1rem; }
            .btn-copy { background: #333; color: #aaa; }
            .btn-copy:hover { background: #444; color: #fff; }
            .btn-del { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
            .btn-del:hover { background: var(--danger); color: #fff; }
            #toast { visibility: hidden; min-width: 200px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 12px; position: fixed; z-index: 1; left: 50%; bottom: 30px; transform: translateX(-50%); border: 1px solid #555; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
            #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
            @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
            @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
            @keyframes fadeIn { from {opacity:0; transform: translateY(-10px);} to {opacity:1; transform: translateY(0);} }
        </style>
    </head>
    <body>
        <div id="toast"><i class="fa-solid fa-check-circle" style="color: #4ade80;"></i> Link Copied!</div>

        <div class="container">
            <?php if (!isset($_SESSION['admin_logged'])): ?>
                <div class="login-card">
                    <div style="font-size: 3rem; color: var(--accent); margin-bottom: 15px;"><i class="fa-solid fa-shield-halved"></i></div>
                    <h2 style="margin-top: 0; color: #fff;">Admin Access</h2>
                    <p style="color: #888; margin-bottom: 20px;">Enter security key to continue.</p>
                    <form method="POST">
                        <input type="password" name="auth_pass" class="login-input" placeholder="Password..." autofocus required>
                        <button class="btn-primary" style="width:100%">LOGIN</button>
                    </form>
                    <?php if($msg) echo "<p style='color:#ef4444; margin-top:15px; font-size:0.9rem;'>$msg</p>"; ?>
                </div>
            <?php else: ?>
                <div class="header">
                    <h2><i class="fa-solid fa-rocket" style="color: var(--accent); margin-right: 8px;"></i>Shortlink</h2>
                    <form method="POST" style="margin:0"><input type="hidden" name="logout"><button class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</button></form>
                </div>

                <?php if(isset($generated_link)): ?>
                    <div class="result-box">
                        <a href="<?php echo $generated_link; ?>" target="_blank" class="result-link"><?php echo $generated_link; ?></a>
                        <button class="copy-btn-main" onclick="copyText('<?php echo $generated_link; ?>')"><i class="fa-regular fa-copy"></i> COPY</button>
                    </div>
                <?php endif; ?>

                <div class="action-box">
                    <form method="POST" style="margin:0">
                        <label style="display:block; color:#aaa; font-size:0.85rem; margin-bottom:8px;">Paste Original URL:</label>
                        <div class="input-group">
                            <input type="text" name="long_url" class="main-input" placeholder="https://..." required autocomplete="off">
                            <button class="btn-primary"><i class="fa-solid fa-plus"></i> Create</button>
                        </div>
                    </form>
                </div>
                
                <div class="list-header">History (<?php echo count($links); ?>)</div>
                <div class="link-list">
                    <?php 
                    $reversed = array_reverse($links, true);
                    foreach($reversed as $id => $val): $full_short = getBaseUrl() . $id; ?>
                        <div class="link-item">
                            <div class="link-info">
                                <span class="short-url" onclick="copyText('<?php echo $full_short; ?>')">/<?php echo $id; ?></span>
                                <span class="original-url"><?php echo $val['url']; ?></span>
                                <span style="font-size:0.7rem; color:#444;"><?php echo isset($val['created_at']) ? $val['created_at'] : ''; ?></span>
                            </div>
                            <div class="actions">
                                <button class="btn-icon btn-copy" onclick="copyText('<?php echo $full_short; ?>')"><i class="fa-regular fa-copy"></i></button>
                                <form method="POST" style="margin:0" onsubmit="return confirm('Delete this link?')">
                                    <input type="hidden" name="delete_id" value="<?php echo $id; ?>">
                                    <button class="btn-icon btn-del"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($links)): ?>
                        <div style="text-align:center; color:#555; padding:20px; border: 1px dashed #333; border-radius:10px;">No links generated yet.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            function copyText(text) {
                navigator.clipboard.writeText(text).then(function() { showToast(); }, function(err) {
                    var textArea = document.createElement("textarea"); textArea.value = text; document.body.appendChild(textArea); textArea.select(); document.execCommand("Copy"); textArea.remove(); showToast();
                });
            }
            function showToast() {
                var x = document.getElementById("toast"); x.className = "show"; setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
            }
        </script>
    </body>
    </html>
    <?php
    exit();
}

// =============================================================
// PART 5: DIRECT ACCESS PROTECTION
// =============================================================
else {
    header("Location: " . $fallback_url);
    exit();
}
?>
