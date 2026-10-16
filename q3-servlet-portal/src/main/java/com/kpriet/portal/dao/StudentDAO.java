package com.kpriet.portal.dao;

import com.kpriet.portal.model.Student;
import com.kpriet.portal.model.StudentFile;
import com.kpriet.portal.util.DBConnection;
import jakarta.servlet.ServletContext;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * File    : StudentDAO.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Data Access Object - all JDBC queries for students and
 *           their files are kept here, away from the servlets.
 */
public class StudentDAO {

    /*
     * LEFT JOIN returns one row for every (student, file) pair.
     * A student without files still appears once, with NULL file columns.
     */
    private static final String SELECT_WITH_FILES =
          "SELECT s.id, s.name, s.roll_no, s.email, s.phone, s.course, "
        + "       s.year_of_study, s.created_at, "
        + "       f.id AS file_id, f.file_category, f.original_name, f.file_path, "
        + "       f.file_type, f.file_size, f.uploaded_at "
        + "FROM students s "
        + "LEFT JOIN student_files f ON f.student_id = s.id ";

    private final ServletContext ctx;

    public StudentDAO(ServletContext ctx) {
        this.ctx = ctx;
    }

    /** All students (optionally filtered by name / roll no / course). */
    public List<Student> findAll(String search) throws SQLException {
        String sql = SELECT_WITH_FILES
                   + "WHERE s.name LIKE ? OR s.roll_no LIKE ? OR s.course LIKE ? "
                   + "ORDER BY s.roll_no, f.file_category, f.uploaded_at";
        String like = "%" + escapeLike(search) + "%";

        try (Connection con = DBConnection.getConnection(ctx);
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setString(1, like);
            ps.setString(2, like);
            ps.setString(3, like);
            try (ResultSet rs = ps.executeQuery()) {
                return mapRows(rs);
            }
        }
    }

    /** One student with files, or null if the id does not exist. */
    public Student findById(int id) throws SQLException {
        String sql = SELECT_WITH_FILES
                   + "WHERE s.id = ? ORDER BY f.file_category, f.uploaded_at";

        try (Connection con = DBConnection.getConnection(ctx);
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                List<Student> list = mapRows(rs);
                return list.isEmpty() ? null : list.get(0);
            }
        }
    }

    /** Metadata of a single file (used by FileStreamServlet). */
    public StudentFile findFileById(int fileId) throws SQLException {
        String sql = "SELECT id, student_id, file_category, original_name, file_path, "
                   + "file_type, file_size, uploaded_at FROM student_files WHERE id = ?";

        try (Connection con = DBConnection.getConnection(ctx);
             PreparedStatement ps = con.prepareStatement(sql)) {
            ps.setInt(1, fileId);
            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) {
                    return null;
                }
                StudentFile file = readFile(rs, "id");
                file.setStudentId(rs.getInt("student_id"));
                return file;
            }
        }
    }

    /*
     * Converts the joined rows into Student objects.
     *  - LOOP over every row of the ResultSet
     *  - IF the student id is new, create a Student object
     *  - IF the row contains a file (not NULL), add it to that student
     * LinkedHashMap keeps the ORDER BY order of the SQL query.
     */
    private List<Student> mapRows(ResultSet rs) throws SQLException {
        Map<Integer, Student> students = new LinkedHashMap<>();

        while (rs.next()) {
            int id = rs.getInt("id");
            Student student = students.get(id);

            if (student == null) {                       // first row for this student
                student = new Student();
                student.setId(id);
                student.setName(rs.getString("name"));
                student.setRollNo(rs.getString("roll_no"));
                student.setEmail(rs.getString("email"));
                student.setPhone(rs.getString("phone"));
                student.setCourse(rs.getString("course"));
                student.setYearOfStudy(rs.getInt("year_of_study"));
                student.setCreatedAt(rs.getTimestamp("created_at"));
                students.put(id, student);
            }

            if (rs.getObject("file_id") != null) {       // this row has a file
                student.getFiles().add(readFile(rs, "file_id"));
            }
        }
        return new ArrayList<>(students.values());
    }

    /** Reads the file columns of the current row into a StudentFile. */
    private StudentFile readFile(ResultSet rs, String idColumn) throws SQLException {
        StudentFile file = new StudentFile();
        file.setId(rs.getInt(idColumn));
        file.setCategory(rs.getString("file_category"));
        file.setOriginalName(rs.getString("original_name"));
        file.setFilePath(rs.getString("file_path"));
        file.setFileType(rs.getString("file_type"));
        file.setFileSize(rs.getLong("file_size"));
        file.setUploadedAt(rs.getTimestamp("uploaded_at"));
        return file;
    }

    /** Makes % and _ in the search text match literally in LIKE. */
    private static String escapeLike(String text) {
        if (text == null) {
            return "";
        }
        return text.trim()
                   .replace("\\", "\\\\")
                   .replace("%", "\\%")
                   .replace("_", "\\_");
    }
}
