<?php
/**
 * File    : includes/functions.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : Shared helpers used by every page:
 *           output escaping, flash messages, CSRF protection,
 *           form validation, secure file upload and CRUD helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================================
// 1. Configuration constants
// =============================================================
const APP_ROOT = __DIR__ . '/../';      // project folder on disk
const MB       = 1048576;               // 1 MB in bytes

const COURSES = [
    'B.E. Computer Science and Engineering',
    'B.E. Electronics and Communication Engineering',
    'B.E. Electrical and Electronics Engineering',
    'B.E. Mechanical Engineering',
    'B.Tech. Information Technology',
    'B.Tech. Artificial Intelligence and Data Science',
];

const CATEGORY_LABELS = [
    'profile_image' => 'Profile Image',
    'id_proof'      => 'ID Proof',
    'certificate'   => 'Certificate',
];

// Upload rules per category: target folder, size limit and the
// allowed extensions mapped to the MIME type their content must have
const DOC_TYPES = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];

const UPLOAD_RULES = [
    'profile_image' => [
        'folder'   => 'uploads/images/',
        'max_size' => 2 * MB,
        'types'    => ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'],
    ],
    'id_proof' => [
        'folder'   => 'uploads/documents/',
        'max_size' => 5 * MB,
        'types'    => DOC_TYPES,
    ],
    'certificate' => [
        'folder'   => 'uploads/documents/',
        'max_size' => 5 * MB,
        'types'    => DOC_TYPES,
    ],
];

// =============================================================
// 2. General helpers
// =============================================================

/** Escapes text before printing it in HTML (prevents XSS attacks). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Sends the browser to another page and stops this script. */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Stores a one-time message that is shown on the next page. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Returns the stored flash message once, then removes it. */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/** Converts bytes into a readable size such as "1.25 MB". */
function format_size(int $bytes): string
{
    return $bytes >= MB
        ? round($bytes / MB, 2) . ' MB'
        : round($bytes / 1024, 1) . ' KB';
}

function is_image_type(string $mime): bool
{
    return strpos($mime, 'image/') === 0;
}

/** "S B Gokul" -> "SG" (used when a student has no profile photo). */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $first = $parts[0][0] ?? '';
    $last  = count($parts) > 1 ? $parts[count($parts) - 1][0] : '';
    return strtoupper($first . $last);
}

/** 3 -> "III Year" */
function year_label(int $year): string
{
    $roman = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
    return ($roman[$year] ?? $year) . ' Year';
}

/** Error message HTML for one form field (empty string if no error). */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<small class="field-error">' . e($errors[$field]) . '</small>'
        : '';
}

// =============================================================
// 3. CSRF protection (stops forged form submissions)
// =============================================================
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

/**
 * When a request is bigger than "post_max_size" in php.ini, PHP silently
 * empties $_POST and $_FILES. This detects that case.
 */
function post_too_large(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && empty($_POST) && empty($_FILES)
        && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

// =============================================================
// 4. Read helpers (prepared statements everywhere)
// =============================================================
function get_student(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/** All files of a student, optionally only one category. */
function get_student_files(mysqli $conn, int $studentId, ?string $category = null): array
{
    $sql = 'SELECT * FROM student_files WHERE student_id = ?';
    if ($category !== null) {
        $sql .= ' AND file_category = ?';
    }
    $sql .= ' ORDER BY file_category, uploaded_at DESC';

    $stmt = $conn->prepare($sql);
    if ($category !== null) {
        $stmt->bind_param('is', $studentId, $category);
    } else {
        $stmt->bind_param('i', $studentId);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// =============================================================
// 5. Server-side validation of the student form
//    (never trust the browser - JavaScript can be switched off)
// =============================================================

/** Returns [cleanData, errors]. $excludeId is the record being edited. */
function validate_student(mysqli $conn, array $input, int $excludeId = 0): array
{
    $data = [
        'name'          => trim($input['name'] ?? ''),
        'roll_no'       => strtoupper(trim($input['roll_no'] ?? '')),
        'email'         => strtolower(trim($input['email'] ?? '')),
        'phone'         => trim($input['phone'] ?? ''),
        'course'        => trim($input['course'] ?? ''),
        'year_of_study' => (int) ($input['year_of_study'] ?? 0),
    ];
    $errors = [];

    if (!preg_match('/^[A-Za-z][A-Za-z .]{2,99}$/', $data['name'])) {
        $errors['name'] = 'Name must be 3-100 letters (spaces and dots allowed).';
    }
    if (!preg_match('/^[0-9]{2}[A-Z]{2,4}[0-9]{3}$/', $data['roll_no'])) {
        $errors['roll_no'] = 'Roll number must look like 24CS067.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($data['phone'] !== '' && !preg_match('/^[6-9][0-9]{9}$/', $data['phone'])) {
        $errors['phone'] = 'Phone must be a valid 10-digit mobile number.';
    }
    if (!in_array($data['course'], COURSES, true)) {
        $errors['course'] = 'Please select a valid course.';
    }
    if ($data['year_of_study'] < 1 || $data['year_of_study'] > 4) {
        $errors['year_of_study'] = 'Year of study must be between 1 and 4.';
    }

    // Duplicate checks run only when the format is already correct
    if (!isset($errors['roll_no'])
        && value_exists($conn, 'roll_no', $data['roll_no'], $excludeId)) {
        $errors['roll_no'] = 'This roll number is already registered.';
    }
    if (!isset($errors['email'])
        && value_exists($conn, 'email', $data['email'], $excludeId)) {
        $errors['email'] = 'This email is already registered.';
    }
    return [$data, $errors];
}

/** True if another student already uses this roll number / email. */
function value_exists(mysqli $conn, string $column, string $value, int $excludeId): bool
{
    // Column names cannot be bound as parameters, so allow only known ones
    if (!in_array($column, ['roll_no', 'email'], true)) {
        throw new InvalidArgumentException('Unknown column');
    }
    $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE $column = ? AND id <> ?");
    $stmt->bind_param('si', $value, $excludeId);
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count > 0;
}

// =============================================================
// 6. File upload handling
// =============================================================

/** Turns a multi-file input (name="x[]") into a simple list of files. */
function normalize_files(array $files): array
{
    if (!is_array($files['name'])) {
        return [$files];                       // single file input
    }
    $list = [];
    foreach ($files['name'] as $i => $name) {
        $list[] = [
            'name'     => $name,
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
    return $list;
}

/** Readable text for PHP's built-in upload error codes. */
function upload_error_message(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'is larger than the server upload limit';
        case UPLOAD_ERR_PARTIAL:
            return 'was only partially uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'could not be saved (missing temporary folder)';
        case UPLOAD_ERR_CANT_WRITE:
            return 'could not be written to disk';
        default:
            return 'failed to upload';
    }
}

/** Real MIME type read from the file content, not the browser's claim. */
function detect_mime(array $file): string
{
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return (string) $finfo->file($file['tmp_name']);
    }
    // Fallback when the fileinfo extension is disabled in php.ini
    return (string) $file['type'];
}

/**
 * Validates ONE uploaded file and moves it into the uploads folder.
 * Returns: null            -> no file was chosen
 *          ['error' => ..] -> validation failed
 *          metadata array  -> file saved successfully
 */
function store_upload(array $file, string $category, string $rollNo): ?array
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $rule  = UPLOAD_RULES[$category];
    $label = CATEGORY_LABELS[$category];
    $name  = basename($file['name']);          // remove any folder path

    // (a) PHP-level upload errors
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => "$label \"$name\" " . upload_error_message($file['error']) . '.'];
    }

    // (b) File size
    if ($file['size'] > $rule['max_size']) {
        return ['error' => sprintf('%s "%s" is %s - the limit is %s.', $label, $name,
            format_size($file['size']), format_size($rule['max_size']))];
    }

    // (c) Extension must be in the allowed list
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!isset($rule['types'][$ext])) {
        $allowed = strtoupper(implode(', ', array_keys($rule['types'])));
        return ['error' => "$label \"$name\": only $allowed files are allowed."];
    }

    // (d) Content must match the extension (stops renamed .exe/.php files)
    $mime = detect_mime($file);
    if ($mime !== $rule['types'][$ext]) {
        return ['error' => "$label \"$name\": file content does not match its type."];
    }

    // (e) Unique stored name, so an existing file is NEVER overwritten
    $folder = APP_ROOT . $rule['folder'];
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        return ['error' => 'Upload folder could not be created.'];
    }
    do {
        $storedName = sprintf('%s_%s_%s_%s.%s', strtolower($rollNo), $category,
            date('YmdHis'), bin2hex(random_bytes(4)), $ext);
    } while (file_exists($folder . $storedName));

    // (f) Move from PHP's temporary folder to our uploads folder
    if (!move_uploaded_file($file['tmp_name'], $folder . $storedName)) {
        return ['error' => "$label \"$name\" could not be saved on the server."];
    }

    return [
        'category'      => $category,
        'original_name' => $name,
        'stored_name'   => $storedName,
        'file_path'     => $rule['folder'] . $storedName,
        'file_type'     => $mime,
        'file_size'     => (int) $file['size'],
    ];
}

/** Inserts the metadata row that links a saved file to a student. */
function save_file_record(mysqli $conn, int $studentId, array $meta): void
{
    $stmt = $conn->prepare(
        'INSERT INTO student_files (student_id, file_category, original_name,
             stored_name, file_path, file_type, file_size)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('isssssi', $studentId, $meta['category'], $meta['original_name'],
        $meta['stored_name'], $meta['file_path'], $meta['file_type'], $meta['file_size']);
    $stmt->execute();
    $stmt->close();
}

/**
 * Processes every file input of the student form.
 * $moved    receives paths of new files (deleted again if saving fails)
 * $replaced receives old profile images (deleted after a successful save)
 * Returns a list of error messages; an empty list means success.
 */
function process_student_uploads(mysqli $conn, int $studentId, string $rollNo,
                                 array &$moved, array &$replaced): array
{
    // form input name => file category
    $inputs = [
        'profile_image' => 'profile_image',
        'id_proof'      => 'id_proof',
        'certificates'  => 'certificate',
    ];
    $errors = [];

    foreach ($inputs as $field => $category) {
        if (empty($_FILES[$field])) {
            continue;
        }
        foreach (normalize_files($_FILES[$field]) as $file) {
            $result = store_upload($file, $category, $rollNo);
            if ($result === null) {
                continue;                              // field left empty
            }
            if (isset($result['error'])) {
                $errors[] = $result['error'];
                continue;
            }
            $moved[] = $result['file_path'];

            // Only ONE profile image per student: the new one replaces the old
            if ($category === 'profile_image') {
                foreach (get_student_files($conn, $studentId, 'profile_image') as $old) {
                    $replaced[] = $old['file_path'];
                }
                $stmt = $conn->prepare("DELETE FROM student_files
                    WHERE student_id = ? AND file_category = 'profile_image'");
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                $stmt->close();
            }
            save_file_record($conn, $studentId, $result);
        }
    }
    return $errors;
}

/** Deletes a file from disk, but only if it is inside the uploads folder. */
function delete_physical_file(string $relativePath): void
{
    $uploadsDir = realpath(APP_ROOT . 'uploads');
    $target     = realpath(APP_ROOT . $relativePath);

    if ($uploadsDir && $target && is_file($target)
        && strpos($target, $uploadsDir . DIRECTORY_SEPARATOR) === 0) {
        unlink($target);
    }
}

// =============================================================
// 7. Create / Update in ONE transaction (record + files together)
// =============================================================

/**
 * Inserts ($id = 0) or updates ($id > 0) a student and stores the
 * uploaded files. If anything fails, the database changes are rolled
 * back and newly saved files are removed, so data stays consistent.
 * Returns ['id' => studentId, 'errors' => [messages]].
 */
function save_student(mysqli $conn, array $data, int $id = 0): array
{
    $moved    = [];
    $replaced = [];
    $errors   = [];
    $phone    = $data['phone'] !== '' ? $data['phone'] : null;

    $conn->begin_transaction();
    try {
        if ($id === 0) {
            $stmt = $conn->prepare('INSERT INTO students
                (name, roll_no, email, phone, course, year_of_study)
                VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssi', $data['name'], $data['roll_no'], $data['email'],
                $phone, $data['course'], $data['year_of_study']);
        } else {
            $stmt = $conn->prepare('UPDATE students SET name = ?, roll_no = ?, email = ?,
                phone = ?, course = ?, year_of_study = ? WHERE id = ?');
            $stmt->bind_param('sssssii', $data['name'], $data['roll_no'], $data['email'],
                $phone, $data['course'], $data['year_of_study'], $id);
        }
        $stmt->execute();
        if ($id === 0) {
            $id = (int) $conn->insert_id;
        }
        $stmt->close();

        $errors = process_student_uploads($conn, $id, $data['roll_no'], $moved, $replaced);
        if (!empty($errors)) {
            throw new RuntimeException('File validation failed');
        }

        $conn->commit();
        foreach ($replaced as $path) {
            delete_physical_file($path);           // remove old profile image
        }
        return ['id' => $id, 'errors' => []];

    } catch (Throwable $ex) {
        $conn->rollback();
        foreach ($moved as $path) {
            delete_physical_file($path);           // undo files saved in this request
        }
        if (empty($errors)) {
            error_log('Save failed: ' . $ex->getMessage());
            $errors[] = 'The record could not be saved. Please try again.';
        }
        return ['id' => 0, 'errors' => $errors];
    }
}
