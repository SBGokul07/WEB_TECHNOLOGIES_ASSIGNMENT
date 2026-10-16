<?php
/**
 * File    : delete.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : DELETE - removes a student. The related rows in student_files
 *           are removed automatically (ON DELETE CASCADE) and the physical
 *           files are deleted from the uploads folder.
 *           Only POST requests with a valid CSRF token are accepted.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$id      = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$student = $id ? get_student($conn, $id) : null;

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

// Remember the file paths BEFORE the rows are deleted
$files = get_student_files($conn, $id);

try {
    $stmt = $conn->prepare('DELETE FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    foreach ($files as $file) {
        delete_physical_file($file['file_path']);
    }
    set_flash('success', sprintf('Student "%s" and %d file(s) were deleted.',
        $student['name'], count($files)));
} catch (mysqli_sql_exception $ex) {
    error_log('Delete failed: ' . $ex->getMessage());
    set_flash('error', 'The student could not be deleted. Please try again.');
}
redirect('index.php');
