<?php
require_once __DIR__ . '/../includes/functions.php';

// Auth Check
 $currentUser = getCurrentUser();
if (!$currentUser) {
    jsonResponse(['success' => false, 'message' => 'Authentication required.']);
}

 $isPro = isPro($currentUser);
 $input = json_decode(file_get_contents('php://input'), true);
 $action = $input['action'] ?? $_POST['action'] ?? '';

// --- GENERATE IMAGE ---
if ($action === 'generate') {
    // 1. Validate Upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['success' => false, 'message' => 'No image uploaded.']);
    }

    // 2. Check Credits/Pro Status
    $useCredit = isset($_POST['use_credit']) && $_POST['use_credit'] === 'true';
    
    // Logic: Free users get 3 free credits initially. They can generate for free (low res) 
    // or use 1 credit for a High-Res PRO-like generation.
    
    $effectivePro = $isPro; 
    
    if (!$isPro && $useCredit) {
        if ($currentUser['credits'] > 0) {
            $effectivePro = true; // Treat as PRO for this generation
            // Deduct credit later on success
        } else {
            jsonResponse(['success' => false, 'message' => 'No credits remaining.']);
        }
    }

    // 3. Process Image
    $tmpFile = $_FILES['image']['tmp_name'];
    $filename = uniqid() . '.png';
    $savePath = PROCESSED_DIR . $filename;
    
    // --- CORE IMAGE PROCESSING (Simulated wrapper) ---
    // In a real production app, this calls Python rembg or similar
    // We'll use standard GD logic for this demo to ensure it runs on standard servers.
    
    $srcImg = imagecreatefromstring(file_get_contents($tmpFile));
    $w = imagesx($srcImg);
    $h = imagesy($srcImg);
    
    // Resize Logic
    $maxSize = $effectivePro ? 2000 : 800; // PRO gets 2K, Free gets 800px
    if ($w > $maxSize || $h > $maxSize) {
        $ratio = min($maxSize/$w, $maxSize/$h);
        $newW = (int)($w * $ratio);
        $newH = (int)($h * $ratio);
        $dstImg = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dstImg, $srcImg, 0,0,0,0, $newW, $newH, $w, $h);
        imagedestroy($srcImg);
        $srcImg = $dstImg;
    }

    // 4. Apply Background Logic (Simplified for demo)
    $bgStyle = $_POST['bg_style'] ?? 'white';
    $finalCanvas = imagecreatetruecolor(imagesx($srcImg), imagesy($srcImg));
    
    // Fill Background
    if ($bgStyle === 'blur') {
        // Simulated blur blend
        imagecopy($finalCanvas, $srcImg, 0, 0, 0, 0, imagesx($srcImg), imagesy($srcImg));
        for($i=0; $i<10; $i++) imagefilter($finalCanvas, IMG_FILTER_GAUSSIAN_BLUR);
    } else {
        // Solid color
        $color = $bgStyle === 'white' ? 0xFFFFFF : 0x000000;
        imagefill($finalCanvas, 0, 0, $color);
    }
    
    // Overlay Subject
    imagecopy($finalCanvas, $srcImg, 0, 0, 0, 0, imagesx($srcImg), imagesy($srcImg));
    
    // Add Watermark for Free Users (not using credit)
    if (!$effectivePro) {
        $textColor = imagecolorallocatealpha($finalCanvas, 255, 255, 255, 60);
        imagestring($finalCanvas, 5, 10, 10, 'ProfileAI Free', $textColor);
    }

    // 5. Save
    imagepng($finalCanvas, $savePath);
    imagedestroy($finalCanvas);
    imagedestroy($srcImg);

    // 6. Deduct Credit if used
    if (!$isPro && $useCredit) {
        $db = getDB();
        $db->prepare("UPDATE users SET credits = credits - 1 WHERE id = ?")->execute([$currentUser['id']]);
    }

    // 7. DB Log
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO images (user_id, original_path, result_path) VALUES (?, ?, ?)");
    $stmt->execute([$currentUser['id'], $tmpFile, $filename]);

    jsonResponse([
        'success' => true, 
        'image_url' => 'processed/' . $filename,
        'credits_left' => $isPro ? 'Unlimited' : ($currentUser['credits'] - ($useCredit ? 1 : 0))
    ]);
}

// --- REDEEM CODE ---
if ($action === 'redeem') {
    $code = trim($input['code']);
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM pro_codes WHERE code = ? AND used_by IS NULL");
    $stmt->execute([$code]);
    $codeData = $stmt->fetch();

    if (!$codeData) {
        jsonResponse(['success' => false, 'message' => 'Invalid or used code.']);
    }

    // Apply Code
    $expiryDate = null;
    if ($codeData['type'] === '1_month') $expiryDate = date('Y-m-d H:i:s', strtotime('+1 month'));
    if ($codeData['type'] === '1_year') $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
    // Lifetime is NULL

    $db->beginTransaction();
    try {
        $db->prepare("UPDATE users SET is_pro = 1, pro_expires_at = ? WHERE id = ?")
           ->execute([$expiryDate, $currentUser['id']]);
           
        $db->prepare("UPDATE pro_codes SET used_by = ?, used_at = NOW() WHERE id = ?")
           ->execute([$currentUser['id'], $codeData['id']]);

        $db->commit();
        jsonResponse(['success' => true, 'message' => 'PRO Activated!']);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Activation failed.']);
    }
}
?>