-- =============================================================
-- File        : schema.sql
-- Project     : Student Admission & Management System
-- Author      : S B GOKUL (24CS067)
-- Course      : U21CS501 - Web Technologies (Assignment II)
-- Purpose     : Creates the database, tables and sample data
--               shared by the PHP admin panel (Q2) and the
--               Servlet portal (Q3).
-- Run         : mysql -u root -p < schema.sql
--               (or paste into phpMyAdmin > SQL tab)
-- =============================================================

DROP DATABASE IF EXISTS student_portal;

CREATE DATABASE student_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_portal;

-- -------------------------------------------------------------
-- Table 1 : students
-- Holds one row per student. Used for CRUD operations in Q2.
-- -------------------------------------------------------------
CREATE TABLE students (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name           VARCHAR(100)     NOT NULL,
    roll_no        VARCHAR(20)      NOT NULL,
    email          VARCHAR(120)     NOT NULL,
    phone          VARCHAR(15)      NULL,
    course         VARCHAR(80)      NOT NULL,
    year_of_study  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_roll  (roll_no),
    UNIQUE KEY uq_students_email (email)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- Table 2 : student_files
-- Metadata of every uploaded file (path, type, size, date).
-- Each row is linked to a student; deleting the student
-- automatically deletes its file rows (ON DELETE CASCADE).
-- -------------------------------------------------------------
CREATE TABLE student_files (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    student_id     INT UNSIGNED  NOT NULL,
    file_category  ENUM('profile_image', 'id_proof', 'certificate') NOT NULL,
    original_name  VARCHAR(255)  NOT NULL,   -- name on the user's PC
    stored_name    VARCHAR(255)  NOT NULL,   -- unique name on the server
    file_path      VARCHAR(500)  NOT NULL,   -- relative path, e.g. uploads/images/x.png
    file_type      VARCHAR(100)  NOT NULL,   -- MIME type, e.g. image/png
    file_size      INT UNSIGNED  NOT NULL,   -- size in bytes
    uploaded_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_files_stored_name (stored_name),
    KEY idx_files_student (student_id),
    CONSTRAINT fk_files_student
        FOREIGN KEY (student_id) REFERENCES students (id)
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- Table 3 : users
-- Login accounts for the Servlet portal (Q3).
-- Passwords are never stored in plain text: SHA-256 hex digest.
-- -------------------------------------------------------------
CREATE TABLE users (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username       VARCHAR(50)   NOT NULL,
    password_hash  CHAR(64)      NOT NULL,
    full_name      VARCHAR(100)  NOT NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- Sample data
-- -------------------------------------------------------------

-- Default portal login  ->  username: admin   password: Admin@123
INSERT INTO users (username, password_hash, full_name)
VALUES ('admin', SHA2('Admin@123', 256), 'Portal Administrator');

INSERT INTO students (name, roll_no, email, phone, course, year_of_study)
VALUES
    ('S B Gokul',         '24CS067', 'gokul.24cs067@example.com',  '9876543210',
     'B.E. Computer Science and Engineering', 3),
    ('Priya Dharshini R', '24CS071', 'priya.24cs071@example.com',  '9123456780',
     'B.E. Computer Science and Engineering', 3),
    ('Arun Kumar M',      '24IT015', 'arun.24it015@example.com',   '9988776655',
     'B.Tech. Information Technology', 3),
    ('Kavya S',           '24AD032', 'kavya.24ad032@example.com',  '9445566778',
     'B.Tech. Artificial Intelligence and Data Science', 3);

-- Files are added through the PHP admin panel (Q2), so the
-- student_files table starts empty.
