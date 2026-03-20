<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/models/PostAttachment.php";

$id = intval($_GET['id'] ?? 0);

$attachmentModel = new PostAttachment($conn);
$file = $attachmentModel->getById($id);

if (!$file) {
    die("File not found");
}

header("Content-Type: " . $file['fileType']);
// Show inline for images, download for others
if (strpos($file['fileType'], 'image/') === 0) {
    header("Content-Disposition: inline");
} else {
    header("Content-Disposition: attachment; filename=\"" . $file['fileName'] . "\"");
}header("Content-Length: " . $file['fileSize']);

echo $file['fileData'];
exit;