<?php
/**
 * File    : edit.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : UPDATE - edits student details, uploads more files,
 *           replaces the profile image and deletes single files.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$student = $id ? get_student($conn, $id) : null;

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

$errors     = [];
$formErrors = [];

if (post_too_large()) {
    $formErrors[] = 'The selected files are too large in total. Please choose smaller files.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // $id is passed so the student's own roll no / email is not a "duplicate"
    [$data, $errors] = validate_student($conn, $_POST, $id);

    if (empty($errors)) {
        $result = save_student($conn, $data, $id);        // UPDATE + uploads
        if (empty($result['errors'])) {
            set_flash('success', 'Student details were updated successfully.');
            redirect('view.php?id=' . $id);
        }
        $formErrors = $result['errors'];
    } else {
        $formErrors[] = 'Some details are invalid. Please check the highlighted fields.';
    }
    $student = array_merge($student, $data);   // keep what the user typed
}

$files       = get_student_files($conn, $id);
$pageTitle   = 'Edit Student';
$submitLabel = 'Update Student';
$isEdit      = true;
require __DIR__ . '/includes/header.php';
?>
<h2 class="page-title">Edit Student &ndash; <?= e($student['name']) ?></h2>
<?php require __DIR__ . '/includes/student_form.php'; ?>

<!-- Existing files (kept outside the main form: HTML forms cannot be nested) -->
<section class="card">
    <h3 class="section-title">Current Files</h3>
    <?php if (empty($files)): ?>
        <p class="empty">No files uploaded yet.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Category</th><th>File</th><th>Size</th><th>Uploaded On</th>
                    <th class="right">Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($files as $f): ?>
                <tr>
                    <td><span class="badge">
                        <?= e(CATEGORY_LABELS[$f['file_category']]) ?></span></td>
                    <td><a href="<?= e($f['file_path']) ?>" target="_blank">
                        <?= e($f['original_name']) ?></a></td>
                    <td><?= format_size((int) $f['file_size']) ?></td>
                    <td><?= e(date('d M Y, h:i A', strtotime($f['uploaded_at']))) ?></td>
                    <td class="right">
                        <form method="post" action="delete_file.php" class="inline"
                              onsubmit="return confirm('Delete this file permanently?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="file_id" value="<?= (int) $f['id'] ?>">
                            <input type="hidden" name="student_id" value="<?= $id ?>">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
