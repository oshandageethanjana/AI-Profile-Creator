<?php require_once 'includes/functions.php'; $user = getCurrentUser(); $isPro = isPro($user); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    //index_php
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ProfileAI Pro — AI Profile Generator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="https://unpkg.com/lucide@latest"></script> <!-- Icons -->
</head>
<body>

    <!-- Ambient Background -->
    <div class="ambient-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon"><i data-lucide="sparkles"></i></div>
                    ProfileAI
                </div>
            </div>

            <!-- Controls -->
            <div style="flex:1; overflow-y:auto;">
                <!-- Step 1: Upload -->
                <div class="panel-section" id="step-upload">
                    <div class="section-title">Upload Photo</div>
                    <label class="opt-btn" style="padding:40px; border-style:dashed; display:block;">
                        <input type="file" id="imageInput" accept="image/*" style="display:none">
                        <i data-lucide="upload-cloud" style="width:24px; height:24px; margin-bottom:8px;"></i>
                        <div style="font-size:14px;">Drop Photo Here</div>
                    </label>
                </div>

                <!-- Step 2: Background -->
                <div class="panel-section">
                    <div class="section-title">Background</div>
                    <div class="grid-options">
                        <div class="opt-btn active" onclick="setBg('white')">
                            <i data-lucide="sun"></i><div>White</div>
                        </div>
                        <div class="opt-btn" onclick="setBg('blur')">
                            <i data-lucide="droplet"></i><div>Blur</div>
                        </div>
                        <div class="opt-btn" onclick="setBg('black')">
                            <i data-lucide="moon"></i><div>Black</div>
                        </div>
                        <div class="opt-btn" onclick="setBg('office')">
                            <i data-lucide="building-2"></i><div>Office</div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Effects -->
                <div class="panel-section">
                    <div class="section-title">Enhance</div>
                    <div class="grid-options">
                        <div class="opt-btn" id="btn-upscale" onclick="toggleProFeature('upscale')">
                            <i data-lucide="maximize-2"></i><div>Upscale</div>
                            <span class="badge-pro">PRO</span>
                        </div>
                        <div class="opt-btn" id="btn-enhance" onclick="toggleProFeature('enhance')">
                            <i data-lucide="wand-2"></i><div>AI Enhance</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div style="padding:20px; border-top:1px solid var(--border-glass);">
                <?php if ($user): ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:13px; color:var(--text-secondary);">
                        <span><?= htmlspecialchars($user['name']) ?></span>
                        <span><?= $isPro ? '👑 PRO' : 'Credits: ' . $user['credits'] ?></span>
                    </div>
                    <button class="btn-primary" onclick="generateImage()">
                        <i data-lucide="sparkles" style="width:16px; height:16px; margin-right:8px; vertical-align:middle;"></i>
                        Generate HD Photo
                    </button>
                <?php else: ?>
                    <button class="btn-primary" onclick="openAuthModal('login')">Sign In to Generate</button>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main View -->
        <main class="main-view">
            <div class="canvas-wrapper">
                <img id="previewImage" src="https://via.placeholder.com/600x600/0A0A0A/333?text=Upload+Photo" alt="Preview">
            </div>
        </main>
    </div>

    <!-- Modals (Auth, Pro) -->
    <div id="modal-overlay" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="background:var(--bg-surface); padding:32px; border-radius:20px; border:1px solid var(--border-glass); width:400px; max-width:90%;">
            <!-- Dynamic Content Injected Here -->
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
