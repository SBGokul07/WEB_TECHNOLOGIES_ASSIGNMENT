<?php
/**
 * File    : includes/header.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : Common page top - <head>, navigation bar and flash message.
 *           Each page sets $pageTitle before including this file.
 */
$pageTitle = $pageTitle ?? 'Student Admin Panel';
$flash     = get_flash();
$current   = basename($_SERVER['PHP_SELF']);   // highlights the active menu
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | KPRIET Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<header class="topbar">
    <div class="brand">KPRIET <span>Student Admin Panel</span></div>
    <nav>
        <a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">
            All Students</a>
        <a href="add.php" class="<?= $current === 'add.php' ? 'active' : '' ?>">
            + Add Student</a>
    </nav>
</header>

<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
