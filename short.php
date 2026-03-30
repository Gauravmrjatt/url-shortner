<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/UrlService.php';

$password = $_GET['password'] ?? $_POST['password'] ?? '';
$isAuthorized = $password === ADMIN_PASSWORD;

if (!$isAuthorized) {
    http_response_code(401);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - Osm-it</title>
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'DM Sans', sans-serif; background: #ede8e1; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
            .card { background: #faf8f5; padding: 40px; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            h1 { font-family: 'DM Serif Display', serif; color: #2a1e10; margin-bottom: 16px; }
            p { color: #9a8470; margin-bottom: 20px; }
            input { padding: 12px 16px; border: 2px solid #dfd0bc; border-radius: 8px; font-size: 14px; width: 200px; }
            button { padding: 12px 24px; background: #7b5c3c; color: #fff; border: none; border-radius: 8px; cursor: pointer; margin-left: 8px; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Osm-it</h1>
            <p>Enter password to access</p>
            <form method="GET">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Go</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$password = $_GET['password'] ?? '';
$passwordParam = '&password=' . urlencode($password);

$message = '';
$messageType = '';
$shortUrl = '';
$shortCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'] ?? '';
    $customAlias = $_POST['custom_alias'] ?? '';
    
    if (!empty($url)) {
        try {
            $urlService = new UrlService();
            $result = $urlService->createShortUrl($url, $customAlias ?: null);
            
            if ($result['success']) {
                $shortUrl = $result['data']['short_url'];
                $shortCode = $result['data']['short_code'];
                $message = 'success';
                $messageType = 'success';
            } else {
                $message = $result['error'];
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'An error occurred. Please try again.';
            $messageType = 'error';
        }
    }
}

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Osm-it - Short URL</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #ede8e1;
    --white: #faf8f5;
    --sand: #c9a98a;
    --sand-light: #e8d8c6;
    --sand-mid: #d6bfa6;
    --brown: #7b5c3c;
    --brown-dark: #5a4028;
    --text: #2a1e10;
    --text-muted: #9a8470;
    --border: #dfd0bc;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
  }

  .scene {
    position: relative;
    width: 820px;
    height: 580px;
  }

  .page-create {
    position: absolute;
    bottom: 0; left: 0;
    width: 800px; height: 530px;
    background: var(--white);
    border-radius: 18px;
    box-shadow: 0 14px 56px rgba(80,50,20,0.20);
    overflow: hidden;
    animation: revealFront 0.85s 0.1s cubic-bezier(.22,1,.36,1) both;
  }

  @keyframes revealFront {
    from { opacity: 0; transform: translateY(26px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .wave-bg {
    position: absolute;
    bottom: 0; left: 0; width: 100%; height: 180px;
    pointer-events: none; z-index: 0;
  }

  .create-nav {
    position: relative; z-index: 2;
    padding: 24px 44px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .create-logo {
    font-family: 'DM Serif Display', serif;
    font-size: 23px; color: var(--text); letter-spacing: -0.3px;
  }

  .back-btn {
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    color: var(--text-muted);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    transition: background 0.2s;
  }
  .back-btn:hover {
    background: var(--sand-light);
    color: var(--text);
  }

  .create-body {
    position: relative; z-index: 2;
    display: flex; align-items: center;
    padding: 38px 44px 0;
    gap: 24px;
  }

  .form-panel { flex: 0 0 300px; }

  .field-block { margin-bottom: 20px; }

  .field-label {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; font-weight: 600;
    color: var(--text); margin-bottom: 7px; opacity: 0.85;
  }

  .f-input {
    width: 100%;
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    padding: 10px 13px;
    border: 1.5px solid var(--border); border-radius: 9px;
    background: rgba(255,255,255,0.9);
    color: var(--text); outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
  }
  .f-input:focus {
    border-color: var(--sand);
    box-shadow: 0 0 0 3px rgba(201,169,138,0.18);
  }
  .f-input::placeholder { color: var(--sand-mid); }

  .custom-row { display: flex; }
  .prefix-box {
    font-size: 11px; font-weight: 500; color: var(--text-muted);
    background: #f0e8de;
    border: 1.5px solid var(--border); border-right: none;
    padding: 10px 11px;
    border-radius: 9px 0 0 9px;
    white-space: nowrap;
  }
  .alias-box {
    flex: 1;
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    padding: 10px 13px;
    border: 1.5px solid var(--border); border-left: none;
    border-radius: 0 9px 9px 0;
    background: rgba(255,255,255,0.9);
    color: var(--text); outline: none;
    transition: border-color 0.18s;
  }
  .alias-box:focus { border-color: var(--sand); }
  .alias-box::placeholder { color: var(--sand-mid); }

  .form-footer {
    display: flex; align-items: center; gap: 18px; margin-top: 6px;
  }

  .my-url-btn {
    font-size: 12px; font-weight: 500;
    color: var(--text);
    text-decoration: underline; text-underline-offset: 3px;
    background: none; border: none; cursor: pointer;
    font-family: 'DM Sans', sans-serif;
  }

  .bub-btn {
    font-family: 'DM Serif Display', serif;
    font-size: 15px;
    padding: 11px 34px;
    border-radius: 9px; border: none;
    background: var(--brown); color: #fff;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(90,55,20,0.26);
    transition: background 0.18s, transform 0.13s;
  }
  .bub-btn:hover { background: var(--brown-dark); transform: translateY(-1px); }

  .illus-area {
    flex: 1;
    display: flex; align-items: flex-end; justify-content: flex-end;
  }

  .result-success {
    background: #e8f5e9;
    border: 1.5px solid #4caf50;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
  }
  .result-label {
    font-size: 11px; color: #2e7d32;
    margin-bottom: 8px;
    font-weight: 600;
  }
  .result-link {
    display: block;
    font-size: 14px;
    color: var(--brown);
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 12px;
    word-break: break-all;
  }
  .result-link:hover {
    text-decoration: underline;
  }
  .copy-btn {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; font-weight: 500;
    padding: 6px 14px;
    border: none; border-radius: 7px;
    background: var(--brown); color: #fff;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px;
  }
  .copy-btn:hover { background: var(--brown-dark); }

  .alert-error {
    background: #ffebee;
    border: 1.5px solid #ef5350;
    color: #c62828;
    padding: 12px 16px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 12px;
  }

  /* Mobile Responsive */
  @media (max-width: 600px) {
    body {
      padding: 20px 12px;
      align-items: flex-start;
    }

    .scene {
      width: 100%;
      height: auto;
      position: relative;
    }

    .page-create {
      position: relative;
      width: 100%;
      height: auto;
      min-height: 100vh;
      border-radius: 0;
      padding-bottom: 40px;
    }

    .wave-bg {
      display: none;
    }

    .create-nav {
      padding: 20px 16px;
    }

    .create-logo {
      font-size: 18px;
    }

    .back-btn {
      font-size: 11px;
      padding: 6px 12px;
    }

    .create-body {
      flex-direction: column;
      padding: 20px 16px;
      gap: 24px;
    }

    .form-panel {
      width: 100%;
      flex: none;
    }

    .field-block {
      margin-bottom: 16px;
    }

    .field-label {
      font-size: 11px;
    }

    .f-input {
      font-size: 14px;
      padding: 12px 14px;
    }

    .custom-row {
      flex-wrap: wrap;
    }

    .prefix-box {
      font-size: 10px;
      padding: 12px 10px;
      width: 100%;
      border-radius: 9px 9px 0 0;
      border-right: 1.5px solid var(--border);
      text-align: center;
    }

    .alias-box {
      font-size: 14px;
      padding: 12px 14px;
      border-radius: 0 0 9px 9px;
      border-left: 1.5px solid var(--border);
      width: 100%;
    }

    .form-footer {
      flex-direction: column;
      gap: 12px;
      align-items: stretch;
    }

    .my-url-btn {
      text-align: center;
      padding: 8px;
    }

    .bub-btn {
      width: 100%;
      padding: 14px;
      font-size: 14px;
    }

    .illus-area {
      display: none;
    }

    .result-success {
      padding: 14px;
    }

    .result-label {
      font-size: 10px;
    }

    .result-link {
      font-size: 13px;
    }

    .copy-btn {
      width: 100%;
      justify-content: center;
      padding: 10px;
    }
  }

  /* Tablet */
  @media (min-width: 601px) and (max-width: 900px) {
    body {
      padding: 30px 20px;
    }

    .scene {
      width: 100%;
      max-width: 700px;
    }

    .page-create {
      width: 100%;
      max-width: 700px;
    }

    .create-nav {
      padding: 20px 24px;
    }

    .create-body {
      padding: 24px;
      gap: 20px;
    }

    .form-panel {
      flex: 0 0 280px;
    }

    .illus-area {
      flex: 1;
    }

    .illus-area svg {
      width: 250px;
      height: 200px;
    }
  }
</style>
</head>
<body>

<div class="scene">

  <div class="page-create">

    <svg class="wave-bg" viewBox="0 0 800 180" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,90 C120,50 240,130 380,80 C500,38 640,110 800,70 L800,180 L0,180 Z" fill="#c9a98a" opacity="0.25"/>
      <path d="M0,120 C140,80 280,150 430,110 C560,75 690,140 800,110 L800,180 L0,180 Z" fill="#c9a98a" opacity="0.45"/>
    </svg>

    <div class="create-nav">
      <span class="create-logo">Osm-it</span>
      <a href="<?= $baseUrl ?>/my-links.php?password=<?= urlencode($password) ?>" class="back-btn">← My URLs</a>
    </div>

    <div class="create-body">

      <div class="form-panel">

        <?php if ($messageType === 'error'): ?>
          <div class="alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($messageType === 'success' && $shortUrl): ?>
          <div class="result-success">
            <div class="result-label">Your shortened URL is ready!</div>
            <a href="<?= htmlspecialchars($shortUrl) ?>" target="_blank" class="result-link">
              <?= htmlspecialchars($shortUrl) ?>
            </a>
            <button type="button" class="copy-btn" onclick="copyUrl()">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              Copy
            </button>
          </div>
        <?php endif; ?>

        <?php if (!$shortUrl || $messageType === 'error'): ?>
        <form method="POST" action="">

          <div class="field-block">
            <div class="field-label">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
              Enter your long URL here
            </div>
            <input class="f-input" type="url" name="url" placeholder="https://your-very-long-url.com/goes/here" required value="<?= htmlspecialchars($_POST['url'] ?? '') ?>">
          </div>

          <div class="field-block">
            <div class="field-label">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Customize your link
            </div>
            <div class="custom-row">
              <span class="prefix-box"><?= str_replace(['https://', 'http://'], '', $baseUrl) ?: 'osmarmy.com' ?></span>
              <input class="alias-box" type="text" name="custom_alias" placeholder="alias" pattern="[a-zA-Z0-9-]{0,50}" value="<?= htmlspecialchars($_POST['custom_alias'] ?? '') ?>">
            </div>
          </div>

          <div class="form-footer">
            <button type="button" class="my-url-btn" onclick="window.location.href='<?= $baseUrl ?>/my-links.php?password=<?= urlencode($password) ?>'">My URLs</button>
            <button type="submit" class="bub-btn">Osm It</button>
          </div>

        </form>
        <?php else: ?>
        <div class="form-footer">
          <button type="button" class="my-url-btn" onclick="window.location.href='<?= $baseUrl ?>/'">View All URLs</button>
          <button type="button" class="bub-btn" onclick="window.location.href='<?= $baseUrl ?>/short.php?password=<?= urlencode($password) ?>'">Create Another</button>
        </div>
        <?php endif; ?>

      </div>

      <div class="illus-area">
        <svg width="310" height="250" viewBox="0 0 310 250" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="10" y="40" width="185" height="150" rx="13" fill="#ede7df" stroke="#cfc0ab" stroke-width="1.5" opacity="0.6"/>
          <rect x="10" y="40" width="185" height="26" rx="13" fill="#e0d5c8" opacity="0.6"/>
          <rect x="10" y="54" width="185" height="12" fill="#e0d5c8" opacity="0.6"/>

          <rect x="28" y="18" width="200" height="168" rx="13" fill="#f0ebe2" stroke="#cec0ac" stroke-width="2"/>
          <rect x="28" y="18" width="200" height="28" rx="13" fill="#e3d8cc"/>
          <rect x="28" y="32" width="200" height="14" fill="#e3d8cc"/>
          <circle cx="46" cy="32" r="4.5" fill="#e8b4b4"/>
          <circle cx="61" cy="32" r="4.5" fill="#e8d9b4"/>
          <circle cx="76" cy="32" r="4.5" fill="#b4d9b4"/>
          <rect x="44" y="54" width="88" height="7" rx="3.5" fill="#d6bfa6" opacity=".5"/>
          <rect x="44" y="67" width="58" height="6" rx="3" fill="#d6bfa6" opacity=".4"/>
          <rect x="44" y="80" width="38" height="28" rx="6" stroke="#cec0ac" stroke-width="1.5"/>
          <rect x="89" y="80" width="38" height="28" rx="6" stroke="#cec0ac" stroke-width="1.5"/>
          <rect x="134" y="80" width="38" height="28" rx="6" stroke="#cec0ac" stroke-width="1.5"/>
          <rect x="44" y="118" width="72" height="6" rx="3" fill="#d6bfa6" opacity=".45"/>
          <rect x="44" y="130" width="68" height="24" rx="6" fill="#e8d8c6"/>
          <text x="78" y="146" font-family="monospace" font-size="9" fill="#7b5c3c" font-weight="700" text-anchor="middle">{<?= $shortCode ?: '9a' ?>} →</text>
          <rect x="44" y="160" width="52" height="6" rx="3" fill="#d6bfa6" opacity=".4"/>
          <rect x="44" y="172" width="30" height="20" rx="5" stroke="#cec0ac" stroke-width="1.5"/>
          <rect x="80" y="172" width="30" height="20" rx="5" stroke="#cec0ac" stroke-width="1.5"/>

          <circle cx="272" cy="75" r="16" stroke="#2a1e10" stroke-width="2.2" fill="none"/>
          <path d="M256 68 Q272 58 288 68" stroke="#2a1e10" stroke-width="2" fill="none" stroke-linecap="round"/>
          <path d="M257 104 Q272 94 287 104 L290 152 L280 152 L278 130 L272 136 L266 130 L264 152 L254 152 Z" stroke="#2a1e10" stroke-width="2" fill="none" stroke-linejoin="round"/>
          <path d="M258 108 L228 126" stroke="#2a1e10" stroke-width="2.2" stroke-linecap="round"/>
          <path d="M228 126 L219 120" stroke="#2a1e10" stroke-width="2" stroke-linecap="round"/>
          <path d="M287 106 L298 122" stroke="#2a1e10" stroke-width="2.2" stroke-linecap="round"/>
          <path d="M264 152 L261 186" stroke="#2a1e10" stroke-width="2.2" stroke-linecap="round"/>
          <path d="M280 152 L283 186" stroke="#2a1e10" stroke-width="2.2" stroke-linecap="round"/>
          <path d="M261 186 L252 190" stroke="#2a1e10" stroke-width="2" stroke-linecap="round"/>
          <path d="M283 186 L292 190" stroke="#2a1e10" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>

    </div>
  </div>

</div>

<script>
function copyUrl() {
  const url = document.querySelector('.result-link').textContent.trim();
  navigator.clipboard.writeText(url).then(() => {
    const btn = document.querySelector('.copy-btn');
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
    setTimeout(() => {
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy';
    }, 2000);
  });
}
</script>

</body>
</html>
