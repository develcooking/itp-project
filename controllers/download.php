<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT fileName, fileType, fileSize, fileData FROM postAttachments WHERE attachmentId = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$file = $result->fetch_assoc();

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