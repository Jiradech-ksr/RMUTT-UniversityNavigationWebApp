<?php
/**
 * Safely handles file uploads, checks/creates directories,
 * and returns the relative path for the database.
 */
function uploadFileSafely($fileArray, $targetSubFolder, $basePath = "../")
{
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $target_dir = $basePath . "uploads/" . $targetSubFolder . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $filename    = time() . "_" . basename($fileArray["name"]);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($fileArray["tmp_name"], $target_file)) {
        return "uploads/" . $targetSubFolder . "/" . $filename;
    }
    return null;
}

/**
 * Like uploadFileSafely, but enforces:
 *   - Max size : 5 MB
 *   - Allowed  : JPG / PNG only (checked via MIME, not extension)
 *
 * Returns the relative path on success.
 * Returns null  if no file was selected (UPLOAD_ERR_NO_FILE).
 * Returns "ERR:…message…" string on validation failure.
 */
function uploadRoomImageValidated($fileArray, $targetSubFolder, $basePath = "../")
{
    if (!isset($fileArray) || $fileArray['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($fileArray['error'] !== UPLOAD_ERR_OK) {
        return "ERR:Upload error code " . $fileArray['error'];
    }

    // 5 MB limit
    if ($fileArray['size'] > 5 * 1024 * 1024) {
        return "ERR:ไฟล์ \"" . htmlspecialchars(basename($fileArray['name'])) . "\" ขนาดเกิน 5MB";
    }

    // JPG / PNG only — check real MIME type, not just extension
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $fileArray['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png'])) {
        return "ERR:ไฟล์ \"" . htmlspecialchars(basename($fileArray['name'])) . "\" ต้องเป็น JPG หรือ PNG เท่านั้น";
    }

    $target_dir = $basePath . "uploads/" . $targetSubFolder . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $ext         = ($mime === 'image/png') ? '.png' : '.jpg';
    $filename    = time() . "_" . bin2hex(random_bytes(4)) . $ext;
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($fileArray['tmp_name'], $target_file)) {
        return "uploads/" . $targetSubFolder . "/" . $filename;
    }
    return "ERR:ไม่สามารถบันทึกไฟล์ได้";
}
?>