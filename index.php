
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION["user_id"]) || empty($_SESSION["owner_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

$userName = $_SESSION["full_name"] ?? "User";
$userOwnerId = $_SESSION["owner_id"];

try {
    $stmt = $pdo->prepare(
        "SELECT full_name, owner_id FROM users WHERE id = :user_id LIMIT 1"
    );

    $stmt->execute([":user_id" => $_SESSION["user_id"]]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $currentUser &&
        (string)$currentUser["owner_id"] === (string)$_SESSION["owner_id"]
    ) {
        $userName = $currentUser["full_name"];
        $userOwnerId = $currentUser["owner_id"];
        $_SESSION["full_name"] = $userName;
    }

} catch (PDOException $e) {
    error_log("RIZA User Name Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>RIZA AI - Premium Assistant</title>

<link rel="stylesheet" href="assets/css/style.css">

<style>

/* =====================================
   TEACHER AVATARS
===================================== */

.teacher-avatar {
    width:150px;
    height:150px;
    margin:0 auto 18px;
    border-radius:50%;
    overflow:hidden;
    position:relative;
    border:3px solid #2585ff;
    box-shadow:0 0 22px rgba(37,133,255,.25);
    transition:.3s ease;
}

.teacher-avatar img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.teacher-card.girl .teacher-avatar {
    border-color:#ec4899;
    box-shadow:0 0 22px rgba(236,72,153,.25);
}

.teacher-card.selected .teacher-avatar {
    transform:scale(1.05);
}

.teacher-avatar.speaking {
    animation:avatarGlow 1.2s infinite alternate;
}

@keyframes avatarGlow {
    from {transform:scale(1);}
    to {transform:scale(1.04);}
}

.teacher-avatar.speaking img {
    animation:avatarTalking .25s infinite alternate;
}

@keyframes avatarTalking {
    from {transform:scale(1);}
    to {transform:scale(1.025);}
}

/* =====================================
   CHAT HISTORY
===================================== */

.history-section {
    margin-top:25px;
    padding:0 10px;
}

.history-heading {
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:#6b7280;
    font-size:12px;
    font-weight:bold;
    letter-spacing:1px;
    margin-bottom:12px;
}

.history-heading button {
    background:transparent;
    border:none;
    color:#2585ff;
    cursor:pointer;
    font-size:12px;
}

.history-list {
    display:flex;
    flex-direction:column;
    gap:8px;
    max-height:300px;
    overflow-y:auto;
}

.history-item {
    width:100%;
    text-align:left;
    padding:12px;
    border:1px solid #e5e7eb;
    border-radius:9px;
    background:#fff;
    color:#1f2937;
    cursor:pointer;
    transition:.2s;
}

.history-item:hover {
    background:#eff6ff;
    border-color:#2585ff;
}

.history-item strong {
    display:block;
    font-size:13px;
    margin-bottom:5px;
}

.history-item small {
    display:block;
    color:#6b7280;
    font-size:11px;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.history-empty {
    color:#6b7280;
    font-size:12px;
    padding:10px 3px;
}

.history-row {
    display:flex;
    align-items:center;
    gap:6px;
    width:100%;
    margin-bottom:8px;
}

.history-row .history-item {
    flex:1;
    min-width:0;
}

.history-delete {
    background:#fff1f2;
    color:#e11d48;
    border:1px solid #fecdd3;
    border-radius:8px;
    padding:10px;
    cursor:pointer;
}

.history-delete:hover {
    background:#e63946;
    color:white;
}

/* =====================================
   USER ACCOUNT
===================================== */

.user-account {
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
    margin-left:auto;
}

.user-account span {
    color:#374151;
    font-size:13px;
}

.logout-button {
    background:#fff1f2;
    color:#e11d48;
    border:1px solid #fecdd3;
    border-radius:8px;
    padding:7px 11px;
    text-decoration:none;
    font-size:12px;
}

.logout-button:hover {
    background:#e63946;
    color:white;
}

/* =====================================
   LANGUAGE SELECTOR
===================================== */

.language-control {
    display:flex;
    align-items:center;
    gap:6px;
}

.language {
    background:#eff6ff;
    color:#2563eb;
    border:1px solid #bfdbfe;
    border-radius:8px;
    padding:7px 10px;
    cursor:pointer;
    font-size:12px;
    font-weight:600;
}

.language-select {
    padding:7px 9px;
    border:1px solid #d1d5db;
    border-radius:8px;
    background:#fff;
    color:#1f2937;
    font-size:12px;
    max-width:135px;
}

/* =====================================
   HOME AND CHAT
===================================== */

html, body {
    background:#fff;
    color:#1f2937;
}

.chat-page {
    display:none;
    min-height:calc(100vh - 75px);
    padding:25px;
    background:#f8faff;
}

.chat-page .chat-container {
    width:100%;
    max-width:1100px;
    min-height:75vh;
    margin:0 auto;
    display:flex;
    flex-direction:column;
}

.chat-page .chat-messages {
    flex:1;
    min-height:350px;
}

.chat-back-button {
    background:#f3f4f6;
    color:#1f2937;
    border:1px solid #e5e7eb;
    border-radius:9px;
    padding:9px 13px;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
}

/* =====================================
   SETTINGS PAGE
===================================== */

.settings-page {
    display:none;
    min-height:calc(100vh - 75px);
    padding:35px;
    background:#f8faff;
}

.settings-content {
    max-width:750px;
    margin:0 auto;
}

.settings-title {
    font-size:30px;
    margin-bottom:8px;
}

.settings-description {
    color:#6b7280;
    margin-bottom:30px;
}

.settings-card {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 4px 15px rgba(0,0,0,.04);
}

.settings-card h3 {
    margin-top:0;
}

.theme-option {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    flex-wrap:wrap;
}

.theme-info {
    display:flex;
    align-items:center;
    gap:14px;
}

.theme-icon {
    width:45px;
    height:45px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    background:#f3f4f6;
}

.theme-info strong {
    display:block;
    margin-bottom:5px;
}

.theme-info small {
    color:#6b7280;
}

/* THEME SWITCH */

.theme-switch {
    position:relative;
    width:58px;
    height:31px;
    display:inline-block;
    flex-shrink:0;
}

.theme-switch input {
    opacity:0;
    width:0;
    height:0;
}

.theme-slider {
    position:absolute;
    inset:0;
    cursor:pointer;
    background:#d1d5db;
    border-radius:30px;
    transition:.3s;
}

.theme-slider:before {
    content:"";
    position:absolute;
    width:23px;
    height:23px;
    left:4px;
    bottom:4px;
    background:#fff;
    border-radius:50%;
    transition:.3s;
    box-shadow:0 1px 4px rgba(0,0,0,.2);
}

.theme-switch input:checked + .theme-slider {
    background:#2585ff;
}

.theme-switch input:checked + .theme-slider:before {
    transform:translateX(27px);
}

.theme-switch input:focus-visible + .theme-slider {
    outline:3px solid rgba(37,133,255,.3);
    outline-offset:3px;
}

/* =====================================
   DARK THEME
===================================== */

body.dark-theme,
body.dark-theme html {
    background:#111827;
    color:#f9fafb;
}

body.dark-theme .app,
body.dark-theme .main {
    background:#111827;
    color:#f9fafb;
}

body.dark-theme .sidebar {
    background:#171717;
    color:#f9fafb;
    border-color:#303030;
}

body.dark-theme .sidebar nav a {
    color:#d1d5db;
}

body.dark-theme .sidebar nav a:hover,
body.dark-theme .sidebar nav a.active {
    background:#292929;
    color:#fff;
}

body.dark-theme .logo {
    color:#fff;
}

body.dark-theme .topbar {
    background:#171717;
    color:#fff;
    border-color:#303030;
}

body.dark-theme .topbar span,
body.dark-theme .user-account span {
    color:#f3f4f6;
}

body.dark-theme .welcome,
body.dark-theme #homePage {
    background-color:#111827;
    color:#f9fafb;
}
.welcome h1 {
    color: #2585ff;
}



body.dark-theme .teacher-card {
    background:#1f2937;
    color:#f9fafb;
    border-color:#374151;
}

body.dark-theme .teacher-card h2,
body.dark-theme .teacher-card h3,
body.dark-theme .teacher-card p {
    color:#f9fafb;
}

body.dark-theme .chat-page,
body.dark-theme .settings-page {
    background:#111827;
    color:#f9fafb;
}

body.dark-theme .chat-container {
    background:#111827;
    color:#f9fafb;
}

body.dark-theme .chat-header {
    background:#1f2937;
    color:#f9fafb;
}

body.dark-theme .chat-messages {
    background:#172033;
    color:#f9fafb;
}

body.dark-theme .chat-messages p,
body.dark-theme .chat-messages h1,
body.dark-theme .chat-messages h2,
body.dark-theme .chat-messages h3,
body.dark-theme .chat-messages li {
    color:#f9fafb;
}

body.dark-theme .chat-box {
    background:#1f2937;
    border-color:#374151;
}

body.dark-theme #messageInput {
    background:#1f2937;
    color:#fff;
}

body.dark-theme #messageInput::placeholder {
    color:#9ca3af;
}

body.dark-theme #teacherStatus {
    color:#d1d5db;
}

body.dark-theme .chat-back-button {
    background:#292929;
    color:#fff;
    border-color:#444;
}

body.dark-theme .settings-card {
    background:#1f2937;
    border-color:#374151;
    color:#f9fafb;
}

body.dark-theme .settings-description,
body.dark-theme .theme-info small {
    color:#cbd5e1;
}

body.dark-theme .theme-icon {
    background:#374151;
}

body.dark-theme .history-item {
    background:#1f2937;
    color:#f9fafb;
    border-color:#374151;
}

body.dark-theme .history-item small {
    color:#cbd5e1;
}

body.dark-theme .history-item:hover {
    background:#374151;
}

body.dark-theme .history-heading {
    color:#cbd5e1;
}

body.dark-theme .new-chat {
    background:#2563eb;
    color:white;
}

body.dark-theme .language-select {
    background:#1f2937;
    color:#fff;
    border-color:#374151;
}

body.dark-theme .language {
    background:#1e3a8a;
    color:#fff;
    border-color:#1d4ed8;
}

/* =====================================
   FULL SCREEN CHAT
===================================== */

.chat-container.fullscreen-chat {
    position:fixed !important;
    inset:0 !important;
    width:100vw !important;
    height:100vh !important;
    height:100dvh !important;
    max-width:none !important;
    margin:0 !important;
    z-index:99999 !important;
    background:#fff !important;
    border-radius:0 !important;
    box-shadow:none !important;
    display:flex !important;
    flex-direction:column !important;
    padding:16px 22px !important;
    box-sizing:border-box !important;
    overflow:hidden !important;
}

.chat-container.fullscreen-chat .chat-header {
    width:100% !important;
    max-width:900px !important;
    margin:0 auto !important;
    flex-shrink:0 !important;
    background:#fff !important;
    color:#111827 !important;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}

.chat-container.fullscreen-chat .chat-messages {
    flex:1 !important;
    min-height:0 !important;
    width:100% !important;
    max-width:900px !important;
    margin:15px auto !important;
    overflow-y:auto !important;
    background:#fff !important;
    color:#1f2937 !important;
    padding:15px !important;
    box-sizing:border-box !important;
}

.chat-container.fullscreen-chat .chat-box {
    width:100% !important;
    max-width:900px !important;
    margin:10px auto !important;
    flex-shrink:0 !important;
    background:#fff !important;
    border:1px solid #d1d5db !important;
    border-radius:18px !important;
    padding:10px !important;
    box-shadow:0 4px 20px rgba(0,0,0,.07);
    box-sizing:border-box !important;
}

.chat-container.fullscreen-chat #messageInput {
    background:#fff !important;
    color:#111827 !important;
    border:none !important;
    outline:none !important;
    flex:1 !important;
    min-width:0 !important;
}

.chat-container.fullscreen-chat #teacherStatus {
    flex-shrink:0 !important;
    text-align:center !important;
    color:#6b7280 !important;
    margin:0 auto 5px !important;
}

/* DARK FULLSCREEN OVERRIDES */

body.dark-theme .chat-container.fullscreen-chat,
body.dark-theme .chat-container.fullscreen-chat .chat-header,
body.dark-theme .chat-container.fullscreen-chat .chat-messages,
body.dark-theme .chat-container.fullscreen-chat .chat-box {
    background:#111827 !important;
    color:#f9fafb !important;
}

body.dark-theme .chat-container.fullscreen-chat .chat-header {
    background:#1f2937 !important;
    color:#fff !important;
}

body.dark-theme .chat-container.fullscreen-chat .chat-messages {
    background:#172033 !important;
    color:#f9fafb !important;
}

body.dark-theme .chat-container.fullscreen-chat .chat-box {
    background:#1f2937 !important;
    border-color:#374151 !important;
}

body.dark-theme .chat-container.fullscreen-chat #messageInput {
    background:#1f2937 !important;
    color:#fff !important;
}

body.dark-theme .chat-container.fullscreen-chat #teacherStatus {
    color:#cbd5e1 !important;
}

body.chat-fullscreen {
    overflow:hidden !important;
}

/* =====================================
   IMAGE UPLOAD
===================================== */

.image-upload-row {
    display:flex;
    align-items:center;
    gap:10px;
    width:100%;
    max-width:900px;
    margin:12px auto 0;
}

.image-upload-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:10px 15px;
    background:#eff6ff;
    color:#2563eb;
    border:1px solid #bfdbfe;
    border-radius:12px;
    cursor:pointer;
    font-size:13px;
    font-weight:600;
    transition:.2s;
}

.image-upload-button:hover {
    background:#dbeafe;
}

#imageInput {
    display:none;
}

.image-preview {
    display:none;
    width:100%;
    max-width:900px;
    margin:12px auto 0;
    padding:12px;
    border:1px solid #bfdbfe;
    border-radius:13px;
    background:#eff6ff;
    box-sizing:border-box;
    align-items:center;
    gap:12px;
}

.image-preview img {
    width:75px;
    height:75px;
    object-fit:cover;
    border-radius:9px;
    border:1px solid #d1d5db;
}

.image-preview-info {
    flex:1;
    min-width:0;
}

.image-preview-info strong {
    display:block;
    font-size:13px;
    color:#1f2937;
}

.image-preview-info small {
    display:block;
    color:#6b7280;
    font-size:11px;
    margin-top:5px;
    overflow-wrap:anywhere;
}

.remove-image-button {
    background:#fff1f2;
    color:#e11d48;
    border:1px solid #fecdd3;
    border-radius:8px;
    padding:8px 11px;
    cursor:pointer;
    font-size:12px;
}

.remove-image-button:hover {
    background:#e11d48;
    color:white;
}

body.dark-theme .image-upload-button {
    background:#1e3a8a;
    color:#fff;
    border-color:#1d4ed8;
}

body.dark-theme .image-preview {
    background:#1f2937;
    border-color:#374151;
}

body.dark-theme .image-preview-info strong {
    color:#fff;
}

body.dark-theme .image-preview-info small {
    color:#cbd5e1;
}

/* =====================================
   INNOVATIVE COLLAPSIBLE SIDEBAR
===================================== */

.app {
    display:flex;
    min-height:100vh;
}

.sidebar {
    width:260px;
    min-width:260px;
    flex-shrink:0;
    position:relative;
    transition:width .3s ease, min-width .3s ease;
    overflow-x:hidden;
    box-sizing:border-box;
}

.main {
    flex:1;
    min-width:0;
    transition:margin .3s ease;
}

/* SIDEBAR HEADER */

.sidebar-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    padding:0 10px;
    margin-bottom:15px;
}

.sidebar .logo {
    white-space:nowrap;
    transition:.3s ease;
}

/* TOGGLE BUTTON */

.sidebar-toggle {
    width:38px;
    height:38px;
    min-width:38px;
    display:flex;
    align-items:center;
    justify-content:center;
    border:1px solid rgba(148,163,184,.2);
    background:rgba(59,130,246,.12);
    color:#60a5fa;
    border-radius:11px;
    cursor:pointer;
    font-size:19px;
    transition:.25s ease;
}

.sidebar-toggle:hover {
    background:#2563eb;
    color:white;
    transform:scale(1.05);
}

/* SIDEBAR NAVIGATION */

.sidebar nav {
    padding:0 7px;
}

.sidebar nav a {
    display:flex;
    align-items:center;
    gap:13px;
    min-height:44px;
    padding:11px 13px;
    margin:5px 0;
    border-radius:12px;
    text-decoration:none;
    white-space:nowrap;
    transition:background .25s ease, color .25s ease;
}

.nav-icon {
    width:23px;
    min-width:23px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
}

.nav-label {
    white-space:nowrap;
    overflow:hidden;
    transition:opacity .2s ease;
}

/* NEW CHAT */

.new-chat {
    width:calc(100% - 20px);
    margin:0 10px 14px;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:12px;
    white-space:nowrap;
    overflow:hidden;
    box-sizing:border-box;
}

.new-chat-icon {
    font-size:20px;
    min-width:22px;
    text-align:center;
}

.new-chat-label {
    transition:opacity .2s ease;
}

/* COLLAPSED STATE */

.sidebar.collapsed {
    width:76px;
    min-width:76px;
}

.sidebar.collapsed .sidebar-header {
    justify-content:center;
    padding:0;
}

.sidebar.collapsed .sidebar-header .logo {
    display:none;
}

.sidebar.collapsed .sidebar-toggle {
    margin:auto;
}

.sidebar.collapsed nav {
    padding:0 8px;
}

.sidebar.collapsed nav a {
    justify-content:center;
    padding:11px 5px;
    gap:0;
}

.sidebar.collapsed .nav-label {
    display:none;
}

.sidebar.collapsed .new-chat {
    width:46px;
    height:44px;
    margin:0 auto 14px;
    padding:8px !important;
    justify-content:center;
    gap:0;
    font-size:0;
}

.sidebar.collapsed .new-chat-icon {
    font-size:21px;
}

.sidebar.collapsed .new-chat-label {
    display:none;
}

/* HIDE HISTORY WHEN COLLAPSED */

.sidebar.collapsed .history-section,
.sidebar.collapsed .sidebar-footer {
    display:none !important;
}

/* DARK SIDEBAR */

body.dark-theme .sidebar-toggle {
    background:rgba(59,130,246,.15);
    color:#93c5fd;
    border-color:#374151;
}

body.dark-theme .sidebar-toggle:hover {
    background:#2563eb;
    color:#fff;
}

/* MOBILE SIDEBAR */

@media(max-width:768px) {

    .sidebar {
        width:76px;
        min-width:76px;
    }

    .sidebar .sidebar-header {
        justify-content:center;
        padding:0;
    }

    .sidebar .sidebar-header .logo {
        display:none;
    }

    .sidebar nav {
        padding:0 8px;
    }

    .sidebar nav a {
        justify-content:center;
        padding:11px 5px;
    }

    .sidebar .nav-label {
        display:none;
    }

    .sidebar .new-chat {
        width:46px;
        height:44px;
        margin:0 auto 14px;
        padding:8px !important;
        justify-content:center;
        font-size:0;
    }

    .sidebar .new-chat-label {
        display:none;
    }

    .sidebar.mobile-expanded {
        width:250px;
        min-width:250px;
    }

    .sidebar.mobile-expanded .sidebar-header {
        justify-content:space-between;
        padding:0 10px;
    }

    .sidebar.mobile-expanded .sidebar-header .logo {
        display:block;
    }

    .sidebar.mobile-expanded nav {
        padding:0 7px;
    }

    .sidebar.mobile-expanded nav a {
        justify-content:flex-start;
        padding:11px 13px;
    }

    .sidebar.mobile-expanded .nav-label {
        display:inline;
    }

    .sidebar.mobile-expanded .new-chat {
        width:calc(100% - 20px);
        margin:0 10px 14px;
        justify-content:flex-start;
        font-size:14px;
    }

    .sidebar.mobile-expanded .new-chat-label {
        display:inline;
    }

    .sidebar.mobile-expanded .history-section,
    .sidebar.mobile-expanded .sidebar-footer {
        display:block !important;
    }

}

/* RESPONSIVE */

@media(max-width:600px) {

    .teacher-avatar {
        width:120px;
        height:120px;
    }

    .user-account {
        gap:5px;
    }

    .user-account span {
        font-size:11px;
    }

    .logout-button,
    .language {
        padding:6px 8px;
        font-size:11px;
    }

    .language-select {
        max-width:110px;
        font-size:11px;
    }

    .chat-page {
        padding:10px;
    }

    .settings-page {
        padding:15px;
    }

    .chat-container.fullscreen-chat {
        padding:10px !important;
    }

    .chat-container.fullscreen-chat .chat-messages {
        padding:8px !important;
    }

    .image-preview {
        gap:8px;
        padding:8px;
    }

    .image-preview img {
        width:60px;
        height:60px;
    }

    .image-upload-button {
        padding:9px 11px;
    }

}


/* =====================================
   SMART AI ASSISTANT PROFILE
===================================== */

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.smart-ai-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.smart-ai-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #2585ff;
    box-shadow: 0 0 14px rgba(37,133,255,.25);
}

.smart-ai-title {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.smart-ai-name {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

.smart-ai-title small {
    font-size: 11px;
    color: #2585ff;
}

body.dark-theme .smart-ai-name {
    color: #ffffff;
}

@media(max-width:600px) {
    .smart-ai-avatar {
        width: 38px;
        height: 38px;
    }

    .smart-ai-name {
        font-size: 13px;
    }

    .smart-ai-title small {
        font-size: 10px;
    }
}

/* =================================
   RIZA AI - KIGALI CITY BACKGROUND
================================= */

body {
    background-image:
        linear-gradient(
            rgba(8, 15, 30, 0.55),
            rgba(8, 15, 30, 0.55)
        ),
        url("assets/images/Kigali_city.jpeg") !important;

    background-size: cover !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    background-color: #101827;
}
</style>
</head>

<body>

<div class="app">

<!-- =====================================
     SIDEBAR
===================================== -->

<aside class="sidebar" id="rizaSidebar">

    <div class="sidebar-header">

        <div class="logo">RIZA<span> AI</span></div>

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            title="Collapse sidebar"
            aria-label="Collapse sidebar"
            aria-expanded="true"
        >
            ☰
        </button>

    </div>

    <button type="button" class="new-chat">

        <span class="new-chat-icon">＋</span>
        <span class="new-chat-label">New Chat</span>

    </button>

    <nav>

        <a class="active" href="#" id="homeNav" title="Home">
            <span class="nav-icon">⌂</span>
            <span class="nav-label">Home</span>
        </a>

        <a href="#" id="chatNav" title="Chat">
            <span class="nav-icon">◉</span>
            <span class="nav-label">Chat</span>
        </a>

        <a href="#teachers" id="boyNav" title="RIZA BOY">
            <span class="nav-icon">👨</span>
            <span class="nav-label">RIZA BOY</span>
        </a>

        <a href="#teachers" id="girlNav" title="RIZA GIRL">
            <span class="nav-icon">👩</span>
            <span class="nav-label">RIZA GIRL</span>
        </a>

        <a href="#" title="Lessons">
            <span class="nav-icon">▤</span>
            <span class="nav-label">Lessons</span>
        </a>

        <a href="#" id="settingsNav" title="Settings">
            <span class="nav-icon">⚙</span>
            <span class="nav-label">Settings</span>
        </a>

        <a href="about.php" id="aboutNav" title="About Us">
            <span class="nav-icon">ⓘ</span>
            <span class="nav-label">About Us</span>
        </a>

    </nav>

    <!-- RECENT CHATS -->

    <div class="history-section">

        <div class="history-heading">
            <span>RECENT CHATS</span>
            <button type="button" id="refreshHistory">Refresh</button>
        </div>

        <div class="history-list" id="chatHistoryList">
            <p class="history-empty">Loading conversations...</p>
        </div>

    </div>

    <div class="sidebar-footer">
        <span>RWANDA 🇷🇼</span>
        <p>RIZA AI V2.0</p>
    </div>

</aside>

<!-- =====================================
     MAIN CONTENT
===================================== -->

<main class="main">
<!-- =====================================
     SMART AI ASSISTANT TOPBAR
===================================== -->

<header class="topbar">

    <div class="smart-ai-brand">

        <img
            src="assets/images/images.png"
            alt="Smart AI Assistant"
            class="smart-ai-avatar"
        >

        <div class="smart-ai-title">
            <span class="smart-ai-name">Smart AI Assistant</span>
            <small>Powered by RIZA AI</small>
        </div>

    </div>

    <div class="user-account">

        <span>
            Welcome,
            <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
        </span>

        <a href="auth/logout.php" class="logout-button">Logout</a>

        <div class="language-control">

            <button type="button" class="language" id="languageButton">
                EN / RW
            </button>

            <select id="languageSelect" class="language-select"
                aria-label="Choose language">

                <option value="en">English 🇬🇧</option>
                <option value="rw">Kinyarwanda 🇷🇼</option>

            </select>

        </div>

    </div>

</header>

    

<!-- =====================================
     HOME PAGE
===================================== -->

<section class="welcome" id="homePage">

    <div class="status">● AI SYSTEM ONLINE</div>

    <h1>WELCOME TO <span>RIZA AI</span></h1>

    <p>
        Your intelligent learning partner.
        Learn, create, discover and grow with AI.
    </p>

    <div class="teachers" id="teachers">

        <!-- RIZA BOY -->

        <div class="teacher-card boy">

            <div class="teacher-avatar">
                <img src="assets/images/jack.jpg" alt="RIZA BOY">
            </div>

            <h2>RIZA BOY</h2>

            <p>Technology & Coding Teacher</p>

            <button type="button" class="choose-teacher" data-teacher="RIZA BOY">
                Choose RIZA BOY
            </button>

        </div>

        <!-- RIZA GIRL -->

        <div class="teacher-card girl">

            <div class="teacher-avatar animated-avatar girl-avatar" id="rizaGirlAvatar">
                <img src="assets/images/avators%20girl.png" alt="RIZA GIRL Avatar">
            </div>

            <h2>RIZA GIRL</h2>

            <p>Languages & General Education</p>

            <button type="button" class="choose-teacher" data-teacher="RIZA GIRL">
                Choose RIZA GIRL
            </button>

        </div>

    </div>

    <div class="powered">
        POWERED BY ARTIFICIAL INTELLIGENCE JSCKXON
    </div>

</section>

<!-- =====================================
     CHAT PAGE
===================================== -->

<section class="chat-page" id="chatPage">

    <div class="chat-container" id="chat">

        <div class="chat-header">

            <button type="button" class="chat-back-button" id="chatBackButton">
                ← Back to Home
            </button>

            <h2>RIZA AI CHAT</h2>

            <span id="chatTeacher">No teacher selected</span>

        </div>

        <div class="chat-messages" id="chatMessages">

            <div class="welcome-message">
                <h3>Murakaza neza kuri RIZA AI!</h3>
                <p>Hitamo umwarimu, ubaze ikibazo icyo ari cyo cyose.</p>
            </div>

        </div>

        <!-- IMAGE PREVIEW -->

        <div class="image-preview" id="imagePreview">

            <img src="" alt="Selected image preview" id="previewImage">

            <div class="image-preview-info">
                <strong>Image selected</strong>
                <small id="imageFileName">Selected image</small>
            </div>

            <button type="button" class="remove-image-button" id="removeImageButton">
                ✕ Remove
            </button>

        </div>

        <!-- IMAGE UPLOAD BUTTON -->

        <div class="image-upload-row">

            <input
                type="file"
                id="imageInput"
                accept="image/jpeg,image/png,image/webp,image/gif"
            >

            <button
                type="button"
                class="image-upload-button"
                id="imageButton"
                title="Upload an image"
            >
                📎 Upload Image
            </button>

        </div>

        <!-- CHAT INPUT -->

        <div class="chat-box">

            <input
                type="text"
                id="messageInput"
                placeholder="Ask RIZA AI anything..."
                autocomplete="off"
            >

            <button type="button" id="voiceButton" title="Speak to RIZA AI">
                🎙
            </button>

            <button type="button" id="sendButton" title="Send message">
                ➤
            </button>

        </div>

        <p id="teacherStatus">
            Choose your AI teacher to start learning.
        </p>

    </div>

</section>

<!-- =====================================
     SETTINGS PAGE
===================================== -->

<section class="settings-page" id="settingsPage">

    <div class="settings-content">

        <h1 class="settings-title">Settings ⚙</h1>

        <p class="settings-description">
            Customize your RIZA AI experience.
        </p>

        <div class="settings-card">

            <h3>Theme Settings</h3>

            <div class="theme-option">

                <div class="theme-info">

                    <div class="theme-icon" id="themeIcon">☀️</div>

                    <div>
                        <strong id="themeName">White Theme</strong>

                        <small id="themeDescription">
                            Light appearance is currently active.
                        </small>
                    </div>

                </div>

                <label class="theme-switch"
                    aria-label="Switch between light and dark themes">

                    <input type="checkbox" id="themeToggle">

                    <span class="theme-slider"></span>

                </label>

            </div>

        </div>

    </div>

</section>

</main>

</div>

<!-- =====================================
     LIBRARIES
===================================== -->

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@latest/dist/purify.min.js"></script>

<script>

window.RIZA_OWNER_ID = <?= json_encode(
    (string)$userOwnerId,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>;

window.RIZA_USER_NAME = <?= json_encode(
    $userName,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
) ?>;

</script>

<script src="assets/js/language.js"></script>
<script src="assets/js/app.js"></script>

<!-- =====================================
     IMAGE SELECTION AND PREVIEW
===================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const imageInput = document.getElementById("imageInput");
    const imageButton = document.getElementById("imageButton");
    const imagePreview = document.getElementById("imagePreview");
    const previewImage = document.getElementById("previewImage");
    const imageFileName = document.getElementById("imageFileName");
    const removeImageButton = document.getElementById("removeImageButton");

    window.RIZA_SELECTED_IMAGE_FILE = null;
    window.RIZA_SELECTED_IMAGE_DATA = null;

    imageButton.addEventListener("click", function () {
        imageInput.click();
    });

    imageInput.addEventListener("change", function () {

        const file = imageInput.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/gif"
        ];

        if (!allowedTypes.includes(file.type)) {
            alert("Please select a JPG, PNG, WEBP or GIF image.");
            imageInput.value = "";
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert("Image is too large. Maximum size is 10 MB.");
            imageInput.value = "";
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            window.RIZA_SELECTED_IMAGE_FILE = file;
            window.RIZA_SELECTED_IMAGE_DATA = event.target.result;

            previewImage.src = event.target.result;
            imageFileName.textContent = file.name;

            imagePreview.style.display = "flex";

        };

        reader.onerror = function () {
            alert("Unable to read this image. Please try again.");
        };

        reader.readAsDataURL(file);

    });

    removeImageButton.addEventListener("click", function () {

        imageInput.value = "";

        window.RIZA_SELECTED_IMAGE_FILE = null;
        window.RIZA_SELECTED_IMAGE_DATA = null;

        previewImage.src = "";
        imageFileName.textContent = "";

        imagePreview.style.display = "none";

    });

});

</script>

<!-- =====================================
     NAVIGATION AND THEME SYSTEM
===================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const homePage = document.getElementById("homePage");
    const chatPage = document.getElementById("chatPage");
    const settingsPage = document.getElementById("settingsPage");

    const chat = document.getElementById("chat");
    const input = document.getElementById("messageInput");

    const homeNav = document.getElementById("homeNav");
    const chatNav = document.getElementById("chatNav");
    const settingsNav = document.getElementById("settingsNav");

    const boyNav = document.getElementById("boyNav");
    const girlNav = document.getElementById("girlNav");

    const backButton = document.getElementById("chatBackButton");
    const newChatButton = document.querySelector(".new-chat");

    const themeToggle = document.getElementById("themeToggle");
    const themeName = document.getElementById("themeName");
    const themeDescription = document.getElementById("themeDescription");
    const themeIcon = document.getElementById("themeIcon");

    const themeStorageKey = "riza_theme_" + String(window.RIZA_OWNER_ID || "guest");

    /* =====================================
       COLLAPSIBLE SIDEBAR SYSTEM
    ===================================== */

    const sidebar = document.getElementById("rizaSidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");

    const sidebarStorageKey =
        "riza_sidebar_collapsed_" + String(window.RIZA_OWNER_ID || "guest");

    function applySidebarState(collapsed, save = true) {

        sidebar.classList.toggle("collapsed", collapsed);

        if (window.innerWidth <= 768) {
            sidebar.classList.remove("mobile-expanded");
        }

        const isMobile = window.innerWidth <= 768;

        const isExpanded = isMobile
            ? sidebar.classList.contains("mobile-expanded")
            : !collapsed;

        sidebarToggle.setAttribute(
            "aria-expanded",
            isExpanded ? "true" : "false"
        );

        sidebarToggle.title = isExpanded
            ? "Collapse sidebar"
            : "Expand sidebar";

        sidebarToggle.setAttribute(
            "aria-label",
            isExpanded ? "Collapse sidebar" : "Expand sidebar"
        );

        sidebarToggle.textContent = isExpanded ? "☰" : "☷";

        if (save) {
            localStorage.setItem(
                sidebarStorageKey,
                collapsed ? "true" : "false"
            );
        }
    }

    const savedSidebarState = localStorage.getItem(sidebarStorageKey);

    applySidebarState(savedSidebarState === "true", false);

    sidebarToggle.addEventListener("click", function () {

        if (window.innerWidth <= 768) {

            sidebar.classList.toggle("mobile-expanded");

            const expanded =
                sidebar.classList.contains("mobile-expanded");

            sidebarToggle.setAttribute(
                "aria-expanded",
                expanded ? "true" : "false"
            );

            sidebarToggle.title = expanded
                ? "Collapse sidebar"
                : "Expand sidebar";

            sidebarToggle.textContent = expanded ? "☰" : "☷";

            return;
        }

        const collapsed = !sidebar.classList.contains("collapsed");

        applySidebarState(collapsed);

    });

    window.addEventListener("resize", function () {

        if (window.innerWidth > 768) {

            sidebar.classList.remove("mobile-expanded");

            applySidebarState(
                localStorage.getItem(sidebarStorageKey) === "true",
                false
            );

        } else {

            sidebar.classList.remove("mobile-expanded");

            applySidebarState(
                localStorage.getItem(sidebarStorageKey) === "true",
                false
            );

        }

    });

    /* =====================================
       NAVIGATION
    ===================================== */

    function setActiveNav(activeElement) {

        [homeNav, chatNav, settingsNav, boyNav, girlNav].forEach(function (item) {
            item.classList.remove("active");
        });

        if (activeElement) {
            activeElement.classList.add("active");
        }

    }

    function closeFullscreen() {

        chat.classList.remove("fullscreen-chat");
        document.body.classList.remove("chat-fullscreen");

    }

    function showHome() {

        homePage.style.display = "block";
        chatPage.style.display = "none";
        settingsPage.style.display = "none";

        setActiveNav(homeNav);
        closeFullscreen();

        window.scrollTo(0, 0);

    }

    function showChat() {

        homePage.style.display = "none";
        chatPage.style.display = "block";
        settingsPage.style.display = "none";

        setActiveNav(chatNav);
        closeFullscreen();

        window.scrollTo(0, 0);

    }

    function showSettings() {

        homePage.style.display = "none";
        chatPage.style.display = "none";
        settingsPage.style.display = "block";

        setActiveNav(settingsNav);
        closeFullscreen();

        window.scrollTo(0, 0);

    }

    homeNav.addEventListener("click", function (event) {
        event.preventDefault();
        showHome();
    });

    chatNav.addEventListener("click", function (event) {
        event.preventDefault();
        showChat();
    });

    settingsNav.addEventListener("click", function (event) {
        event.preventDefault();
        showSettings();
    });

    backButton.addEventListener("click", function () {

        if (input) {
            input.blur();
        }

        showHome();

    });

    newChatButton.addEventListener("click", function () {
        showChat();
    });

    document.querySelectorAll(".choose-teacher").forEach(function (button) {

        button.addEventListener("click", function () {
            showChat();
        });

    });

    boyNav.addEventListener("click", function (event) {

        event.preventDefault();
        showHome();

        document.getElementById("teachers").scrollIntoView({
            behavior: "smooth"
        });

    });

    girlNav.addEventListener("click", function (event) {

        event.preventDefault();
        showHome();

        document.getElementById("teachers").scrollIntoView({
            behavior: "smooth"
        });

    });

    /* =====================================
       FULLSCREEN CHAT
    ===================================== */

    function openFullScreenChat() {

        if (chatPage.style.display === "none") {
            return;
        }

        chat.classList.add("fullscreen-chat");
        document.body.classList.add("chat-fullscreen");

    }

    input.addEventListener("focus", openFullScreenChat);

    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            closeFullscreen();

            if (input) {
                input.blur();
            }

        }

    });

    /* =====================================
       THEME SYSTEM
    ===================================== */

    function applyTheme(isDark, save = true) {

        document.body.classList.toggle("dark-theme", isDark);

        themeToggle.checked = isDark;

        if (isDark) {

            themeName.textContent = "Black Theme";
            themeDescription.textContent = "Dark appearance is currently active.";
            themeIcon.textContent = "🌙";

        } else {

            themeName.textContent = "White Theme";
            themeDescription.textContent = "Light appearance is currently active.";
            themeIcon.textContent = "☀️";

        }

        if (save) {
            localStorage.setItem(themeStorageKey, isDark ? "dark" : "light");
        }

    }

    const savedTheme = localStorage.getItem(themeStorageKey);

    applyTheme(savedTheme === "dark", false);

    themeToggle.addEventListener("change", function () {

        applyTheme(themeToggle.checked);

    });

    showHome();

});

</script>

</body>
</html>
