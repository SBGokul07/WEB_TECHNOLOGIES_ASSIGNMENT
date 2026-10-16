<?php
/**
 * File    : config/db.php
 * Author  : S B GOKUL (24CS067)
 * Purpose : Opens ONE MySQLi connection ($conn) that every page uses.
 *           Change the constants below to match your MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');                 // XAMPP default password is empty
define('DB_NAME', 'student_portal');

// Make MySQLi throw exceptions instead of silent warnings,
// so every database error can be caught with try / catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log the real reason for the developer; show a safe message to the user
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('<h2 style="font-family:sans-serif;color:#b00020">'
        . 'Could not connect to the database. Please check config/db.php '
        . 'and make sure MySQL is running.</h2>');
}
