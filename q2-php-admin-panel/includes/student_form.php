<?php
/**
 * File    : includes/student_form.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : The student form shared by add.php and edit.php.
 * Expects : $student     - current field values
 *           $errors      - field name => error message
 *           $formErrors  - general / file upload error messages
 *           $submitLabel - text of the submit button
 *           $isEdit      - true on the edit page
 */
?>
<?php if (!empty($formErrors)): ?>
    <div class="alert alert-error">
        <strong>Please correct the following:</strong>
        <ul>
            <?php foreach ($formErrors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
<form method="post" enctype="multipart/form-data" class="card">
    <?= csrf_field() ?>

    <h3 class="section-title">Student Details</h3>
    <div class="grid-2">
        <div class="field <?= isset($errors['name']) ? 'invalid' : '' ?>">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" maxlength="100" required
                   value="<?= e($student['name']) ?>">
            <?= field_error($errors, 'name') ?>
        </div>

        <div class="field <?= isset($errors['roll_no']) ? 'invalid' : '' ?>">
            <label for="roll_no">Roll Number *</label>
            <input type="text" id="roll_no" name="roll_no" maxlength="20" required
                   placeholder="e.g. 24CS067" value="<?= e($student['roll_no']) ?>">
            <?= field_error($errors, 'roll_no') ?>
        </div>

        <div class="field <?= isset($errors['email']) ? 'invalid' : '' ?>">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" maxlength="120" required
                   value="<?= e($student['email']) ?>">
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="field <?= isset($errors['phone']) ? 'invalid' : '' ?>">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" maxlength="10"
                   value="<?= e($student['phone'] ?? '') ?>">
            <?= field_error($errors, 'phone') ?>
        </div>

        <div class="field <?= isset($errors['course']) ? 'invalid' : '' ?>">
            <label for="course">Course *</label>
            <select id="course" name="course" required>
                <option value="">-- Select Course --</option>
                <?php foreach (COURSES as $course): ?>
                    <option value="<?= e($course) ?>"
                        <?= $student['course'] === $course ? 'selected' : '' ?>>
                        <?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'course') ?>
        </div>

        <div class="field <?= isset($errors['year_of_study']) ? 'invalid' : '' ?>">
            <label for="year_of_study">Year of Study *</label>
            <select id="year_of_study" name="year_of_study">
                <?php for ($y = 1; $y <= 4; $y++): ?>
                    <option value="<?= $y ?>"
                        <?= (int) $student['year_of_study'] === $y ? 'selected' : '' ?>>
                        <?= year_label($y) ?></option>
                <?php endfor; ?>
            </select>
            <?= field_error($errors, 'year_of_study') ?>
        </div>
    </div>

    <h3 class="section-title">Documents</h3>
    <div class="grid-2">
        <div class="field">
            <label for="profile_image">
                Profile Image <?= $isEdit ? '(choose a file to replace)' : '' ?></label>
            <input type="file" id="profile_image" name="profile_image"
                   accept=".jpg,.jpeg,.png">
            <small class="hint">JPG / PNG only &middot; max 2 MB</small>
        </div>

        <div class="field">
            <label for="id_proof">ID Proof</label>
            <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png">
            <small class="hint">PDF / JPG / PNG &middot; max 5 MB</small>
        </div>

        <div class="field full">
            <label for="certificates">Certificates (you may select several files)</label>
            <!-- name ends with [] so PHP receives an array of files -->
            <input type="file" id="certificates" name="certificates[]" multiple
                   accept=".pdf,.jpg,.jpeg,.png">
            <small class="hint">PDF / JPG / PNG &middot; max 5 MB each</small>
        </div>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="index.php" class="btn btn-light">Cancel</a>
    </div>
</form>
