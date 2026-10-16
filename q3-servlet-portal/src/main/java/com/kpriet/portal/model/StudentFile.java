package com.kpriet.portal.model;

import java.sql.Timestamp;

/**
 * File    : StudentFile.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : One row of the student_files table (file metadata).
 */
public class StudentFile {

    private int id;
    private int studentId;
    private String category;        // profile_image | id_proof | certificate
    private String originalName;
    private String filePath;        // relative path, e.g. uploads/images/x.png
    private String fileType;        // MIME type, e.g. image/png
    private long fileSize;          // bytes
    private Timestamp uploadedAt;

    // ---------- Getters and setters ----------
    public int getId()                      { return id; }
    public void setId(int id)               { this.id = id; }
    public int getStudentId()               { return studentId; }
    public void setStudentId(int v)         { this.studentId = v; }
    public String getCategory()             { return category; }
    public void setCategory(String v)       { this.category = v; }
    public String getOriginalName()         { return originalName; }
    public void setOriginalName(String v)   { this.originalName = v; }
    public String getFilePath()             { return filePath; }
    public void setFilePath(String v)       { this.filePath = v; }
    public String getFileType()             { return fileType; }
    public void setFileType(String v)       { this.fileType = v; }
    public long getFileSize()               { return fileSize; }
    public void setFileSize(long v)         { this.fileSize = v; }
    public Timestamp getUploadedAt()        { return uploadedAt; }
    public void setUploadedAt(Timestamp v)  { this.uploadedAt = v; }

    // ---------- Helper methods used by the JSP pages ----------
    public boolean isProfileImage() {
        return "profile_image".equals(category);
    }

    public boolean isImage() {
        return fileType != null && fileType.startsWith("image/");
    }

    public boolean isPdf() {
        return "application/pdf".equals(fileType);
    }

    public String getCategoryLabel() {
        if ("profile_image".equals(category)) {
            return "Profile Image";
        } else if ("id_proof".equals(category)) {
            return "ID Proof";
        } else {
            return "Certificate";
        }
    }

    /** 81078 -> "79.2 KB" */
    public String getReadableSize() {
        if (fileSize >= 1024 * 1024) {
            return String.format("%.2f MB", fileSize / (1024.0 * 1024.0));
        }
        return String.format("%.1f KB", fileSize / 1024.0);
    }
}
