<?php
/**
 * File    : index.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : READ (list) - shows all students with photo thumbnail,
 *           document count, search box and View / Edit / Delete actions.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Search text from ?q=...  (% and _ are escaped so they are matched literally)
$search = trim($_GET['q'] ?? '');
$like   = '%' . addcslashes($search, '%_\\') . '%';

$sql = "SELECT s.id, s.name, s.roll_no, s.email, s.course, s.year_of_study,
               (SELECT f.file_path FROM student_files f
                 WHERE f.student_id = s.id AND f.file_category = 'profile_image'
                 LIMIT 1) AS photo,
               (SELECT COUNT(*) FROM student_files d
                 WHERE d.student_id = s.id
                   AND d.file_category <> 'profile_image') AS doc_count
          FROM students s
         WHERE s.name LIKE ? OR s.roll_no LIKE ? OR s.course LIKE ?
         ORDER BY s.roll_no";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'All Students';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h2 class="page-title">Student Records <span class="count"><?= count($students) ?></span></h2>
    <form method="get" class="search">
        <input type="search" name="q" placeholder="Search students..."
               value="<?= e($search) ?>">
        <button class="btn btn-primary">Search</button>
        <?php if ($search !== ''): ?>
            <a href="index.php" class="btn btn-light">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card table-wrap">
<?php if (empty($students)): ?>
    <p class="empty">No students found. <a href="add.php">Add the first student</a>.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Photo</th><th>Roll No</th><th>Name</th><th>Course</th>
                <th>Year</th><th>Documents</th><th class="right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td>
                    <?php if ($s['photo']): ?>
                        <img src="<?= e($s['photo']) ?>" alt="Photo" class="avatar-sm">
                    <?php else: ?>
                        <span class="avatar-sm placeholder"><?= e(initials($s['name'])) ?></span>
                    <?php endif; ?>
                </td>
                <td><strong><?= e($s['roll_no']) ?></strong></td>
                <td><?= e($s['name']) ?><br><small class="muted"><?= e($s['email']) ?></small></td>
                <td><?= e($s['course']) ?></td>
                <td><?= year_label((int) $s['year_of_study']) ?></td>
                <td><span class="badge"><?= (int) $s['doc_count'] ?> file(s)</span></td>
                <td class="right nowrap">
                    <a href="view.php?id=<?= (int) $s['id'] ?>"
                       class="btn btn-sm btn-light">View</a>
                    <a href="edit.php?id=<?= (int) $s['id'] ?>"
                       class="btn btn-sm btn-light">Edit</a>
                    <!-- Delete uses POST + confirmation, never a plain link -->
                    <form method="post" action="delete.php" class="inline"
                          onsubmit="return confirm('Delete this student and all files?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
