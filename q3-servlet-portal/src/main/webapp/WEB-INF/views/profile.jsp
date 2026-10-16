<%--
    File    : WEB-INF/views/profile.jsp
    Author  : S B GOKUL (24CS067)
    Purpose : Full profile of one student with photo and all documents.
--%>
<%@ page contentType="text/html;charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List, java.text.SimpleDateFormat" %>
<%@ page import="com.kpriet.portal.model.Student, com.kpriet.portal.model.StudentFile" %>
<%@ page import="com.kpriet.portal.util.HtmlUtil" %>
<%
    String ctx = request.getContextPath();
    Student s = (Student) request.getAttribute("student");
    StudentFile photo = s.getProfileImage();
    List<StudentFile> documents = s.getDocuments();
    SimpleDateFormat fmt = new SimpleDateFormat("dd MMM yyyy, hh:mm a");
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><%= HtmlUtil.escape(s.getName()) %> | KPRIET Student Profile Portal</title>
    <link rel="stylesheet" href="<%= ctx %>/css/portal.css">
</head>
<body>
<%@ include file="navbar.jspf" %>

<main class="container">
    <a href="<%= ctx %>/students" class="back-link">&larr; Back to all students</a>

    <div class="profile-layout">
        <aside class="card profile-card">
            <% if (photo != null) { %>
                <img src="<%= ctx %>/file?id=<%= photo.getId() %>" class="avatar avatar-lg"
                     alt="Photo of <%= HtmlUtil.escape(s.getName()) %>">
            <% } else { %>
                <div class="avatar avatar-lg placeholder">
                    <%= HtmlUtil.escape(HtmlUtil.initials(s.getName())) %></div>
            <% } %>
            <h2><%= HtmlUtil.escape(s.getName()) %></h2>
            <p class="roll"><%= HtmlUtil.escape(s.getRollNo()) %></p>
        </aside>

        <section class="card">
            <h3 class="section-title">Student Details</h3>
            <table class="details">
                <tr><th>Roll Number</th><td><%= HtmlUtil.escape(s.getRollNo()) %></td></tr>
                <tr><th>Email</th><td><%= HtmlUtil.escape(s.getEmail()) %></td></tr>
                <tr><th>Phone</th>
                    <td><%= s.getPhone() != null ? HtmlUtil.escape(s.getPhone()) : "-" %></td></tr>
                <tr><th>Course</th><td><%= HtmlUtil.escape(s.getCourse()) %></td></tr>
                <tr><th>Year of Study</th>
                    <td><%= HtmlUtil.yearLabel(s.getYearOfStudy()) %></td></tr>
                <tr><th>Registered On</th><td><%= fmt.format(s.getCreatedAt()) %></td></tr>
            </table>
        </section>
    </div>

    <section class="card">
        <h3 class="section-title">Documents (<%= documents.size() %>)</h3>

        <% if (documents.isEmpty()) { %>
            <p class="empty">No documents have been uploaded for this student.</p>
        <% } else { %>
            <div class="doc-grid">
            <% for (StudentFile d : documents) {
                   String url = ctx + "/file?id=" + d.getId(); %>
                <div class="doc-card">
                    <% if (d.isImage()) { %>
                        <img src="<%= url %>" alt="<%= HtmlUtil.escape(d.getOriginalName()) %>">
                    <% } else if (d.isPdf()) { %>
                        <div class="doc-icon">PDF</div>
                    <% } else { %>
                        <div class="doc-icon">FILE</div>
                    <% } %>
                    <span class="badge"><%= d.getCategoryLabel() %></span>
                    <p class="doc-name"><%= HtmlUtil.escape(d.getOriginalName()) %></p>
                    <p class="muted small"><%= HtmlUtil.escape(d.getFileType()) %> &middot;
                        <%= d.getReadableSize() %><br><%= fmt.format(d.getUploadedAt()) %></p>
                    <div class="doc-links">
                        <a href="<%= url %>" target="_blank">View</a>
                        <a href="<%= url %>&amp;download=1">Download</a>
                    </div>
                </div>
            <% } %>
            </div>
        <% } %>
    </section>
</main>

<footer class="footer">U21CS501 Web Technologies &middot; S B GOKUL (24CS067)</footer>
</body>
</html>
