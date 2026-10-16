package com.kpriet.portal.model;

import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

/**
 * File    : Student.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : One student together with all of his / her uploaded files.
 */
public class Student {

    private int id;
    private String name;
    private String rollNo;
    private String email;
    private String phone;
    private String course;
    private int yearOfStudy;
    private Timestamp createdAt;
    private final List<StudentFile> files = new ArrayList<>();

    // ---------- Getters and setters ----------
    public int getId()                     { return id; }
    public void setId(int id)              { this.id = id; }
    public String getName()                { return name; }
    public void setName(String v)          { this.name = v; }
    public String getRollNo()              { return rollNo; }
    public void setRollNo(String v)        { this.rollNo = v; }
    public String getEmail()               { return email; }
    public void setEmail(String v)         { this.email = v; }
    public String getPhone()               { return phone; }
    public void setPhone(String v)         { this.phone = v; }
    public String getCourse()              { return course; }
    public void setCourse(String v)        { this.course = v; }
    public int getYearOfStudy()            { return yearOfStudy; }
    public void setYearOfStudy(int v)      { this.yearOfStudy = v; }
    public Timestamp getCreatedAt()        { return createdAt; }
    public void setCreatedAt(Timestamp v)  { this.createdAt = v; }
    public List<StudentFile> getFiles()    { return files; }

    /** Returns the profile image, or null when the student has none. */
    public StudentFile getProfileImage() {
        for (StudentFile file : files) {
            if (file.isProfileImage()) {
                return file;
            }
        }
        return null;
    }

    /** All files except the profile image (ID proofs and certificates). */
    public List<StudentFile> getDocuments() {
        List<StudentFile> documents = new ArrayList<>();
        for (StudentFile file : files) {
            if (!file.isProfileImage()) {
                documents.add(file);
            }
        }
        return documents;
    }
}
