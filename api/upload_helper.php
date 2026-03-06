<?php
/**
 * Safely handles file uploads, checks/creates directories, 
 * and returns the relative path for the database.
 *
 * @param array $fileArray The specific $_FILES array (e.g., $_FILES['room_image'])
 * @param string $targetSubFolder The folder inside 'uploads' (e.g., 'rooms', 'layouts', 'reports')
 * @param string $basePath The path to step back to the root (e.g., '../' or '')
 * @return string|null Returns the relative path on success, or null on failure.
 */
function uploadFileSafely($fileArray, $targetSubFolder, $basePath = "../")
{
    // 1. Check if file was actually uploaded and has no errors
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // 2. Define the target directory (e.g., ../uploads/layouts/)
    $target_dir = $basePath . "uploads/" . $targetSubFolder . "/";

    // 3. Issue #4 Fix: Create the directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 4. Generate a unique filename to prevent overwriting
    $filename = time() . "_" . basename($fileArray["name"]);
    $target_file = $target_dir . $filename;

    // 5. Move the file and return the relative path (Issue #3 Fix)
    if (move_uploaded_file($fileArray["tmp_name"], $target_file)) {
        return "uploads/" . $targetSubFolder . "/" . $filename;
    }

    return null; // Failsafe
}
?>