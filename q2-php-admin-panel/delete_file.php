<?php
/**
 * File    : delete_file.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : Deletes ONE uploaded file (database row + physical file).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$fileId    = filter_input(INPUT_POST, 'file_id', FILTER_VALIDATE_INT) ?: 0;
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?: 0;

// The file must belong to the given student
$stmt = $conn->prepare('SELECT * FROM student_files WHERE id = ? AND student_id = ?');
$stmt->bind_param('ii', $fileId, $studentId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file) {
    set_flash('error', 'File not found.');
    redirect('index.php');
}

$stmt = $conn->prepare('DELETE FROM student_files WHERE id = ?');
$stmt->bind_param('i', $fileId);
$stmt->execute();
$stmt->close();

delete_physical_file($file['file_path']);

set_flash('success', 'File "' . $file['original_name'] . '" was deleted.');
redirect('edit.php?id=' . $studentId);
