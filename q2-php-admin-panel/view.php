<?php
/**
 * File    : view.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : READ (single) - shows one student's details together with the
 *           profile image and every uploaded document (view / download).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id      = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$student = $id ? get_student($conn, $id) : null;

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('index.php');
}

// Split the files into the profile photo and the other documents
$photo     = null;
$documents = [];
foreach (get_student_files($conn, $id) as $file) {
    if ($file['file_category'] === 'profile_image') {
        $photo = $file;
    } else {
        $documents[] = $file;
    }
}

$pageTitle = $student['name'];
require __DIR__ . '/includes/header.php';
?>
<div class="profile-layout">
    <!-- Left: photo and actions -->
    <aside class="card profile-card">
        <?php if ($photo): ?>
            <img src="<?= e($photo['file_path']) ?>" class="avatar-lg"
                 alt="Profile photo of <?= e($student['name']) ?>">
        <?php else: ?>
            <div class="avatar-lg placeholder"><?= e(initials($student['name'])) ?></div>
        <?php endif; ?>

        <h2><?= e($student['name']) ?></h2>
        <p class="muted"><?= e($student['roll_no']) ?></p>

        <div class="actions center">
            <a href="edit.php?id=<?= $id ?>" class="btn btn-primary">Edit</a>
            <form method="post" action="delete.php" class="inline"
                  onsubmit="return confirm('Delete this student and all files?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </aside>

    <!-- Right: details table -->
    <section class="card">
        <h3 class="section-title">Student Details</h3>
        <table class="details">
            <tr><th>Roll Number</th><td><?= e($student['roll_no']) ?></td></tr>
            <tr><th>Email</th><td><?= e($student['email']) ?></td></tr>
            <tr><th>Phone</th><td><?= e($student['phone'] ?: '-') ?></td></tr>
            <tr><th>Course</th><td><?= e($student['course']) ?></td></tr>
            <tr><th>Year of Study</th>
                <td><?= year_label((int) $student['year_of_study']) ?></td></tr>
            <tr><th>Registered On</th>
                <td><?= e(date('d M Y, h:i A', strtotime($student['created_at']))) ?></td></tr>
            <tr><th>Last Updated</th>
                <td><?= e(date('d M Y, h:i A', strtotime($student['updated_at']))) ?></td></tr>
        </table>
    </section>
</div>

<!-- Uploaded documents with their stored metadata -->
<section class="card">
    <h3 class="section-title">Uploaded Documents (<?= count($documents) ?>)</h3>

    <?php if (empty($documents)): ?>
        <p class="empty">No documents have been uploaded for this student.</p>
    <?php else: ?>
        <div class="doc-grid">
        <?php foreach ($documents as $doc): ?>
            <div class="doc-card">
                <?php if (is_image_type($doc['file_type'])): ?>
                    <img src="<?= e($doc['file_path']) ?>" alt="<?= e($doc['original_name']) ?>">
                <?php else: ?>
                    <div class="doc-icon">PDF</div>
                <?php endif; ?>

                <span class="badge"><?= e(CATEGORY_LABELS[$doc['file_category']]) ?></span>
                <p class="doc-name" title="<?= e($doc['original_name']) ?>">
                    <?= e($doc['original_name']) ?></p>
                <p class="muted small">
                    <?= e($doc['file_type']) ?> &middot;
                    <?= format_size((int) $doc['file_size']) ?><br>
                    <?= e(date('d M Y, h:i A', strtotime($doc['uploaded_at']))) ?>
                </p>
                <div class="doc-links">
                    <a href="<?= e($doc['file_path']) ?>" target="_blank">View</a>
                    <a href="<?= e($doc['file_path']) ?>"
                       download="<?= e($doc['original_name']) ?>">Download</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
