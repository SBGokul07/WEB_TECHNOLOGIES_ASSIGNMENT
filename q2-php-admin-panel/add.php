<?php
/**
 * File    : add.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : CREATE - adds a new student together with uploaded files.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Empty values for a fresh form
$student = ['name' => '', 'roll_no' => '', 'email' => '', 'phone' => '',
            'course' => '', 'year_of_study' => 1];
$errors     = [];      // field name => message
$formErrors = [];      // general and file upload messages

if (post_too_large()) {
    $formErrors[] = 'The selected files are too large in total. Please choose smaller files.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$student, $errors] = validate_student($conn, $_POST);

    if (empty($errors)) {
        $result = save_student($conn, $student);          // INSERT + uploads
        if (empty($result['errors'])) {
            set_flash('success', 'Student "' . $student['name'] . '" was added successfully.');
            redirect('view.php?id=' . $result['id']);
        }
        $formErrors = $result['errors'];
    } else {
        $formErrors[] = 'Some details are invalid. Please check the highlighted fields.';
    }
}

$pageTitle   = 'Add Student';
$submitLabel = 'Save Student';
$isEdit      = false;
require __DIR__ . '/includes/header.php';
?>
<h2 class="page-title">Add New Student</h2>
<?php require __DIR__ . '/includes/student_form.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
