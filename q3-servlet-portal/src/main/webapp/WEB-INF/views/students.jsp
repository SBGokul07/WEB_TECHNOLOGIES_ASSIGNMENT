<%--
    File    : WEB-INF/views/students.jsp
    Author  : S B GOKUL (24CS067)
    Purpose : Lists every student as a card. Uses LOOPS (for) and
              CONDITIONS (if / else) on the data from StudentListServlet.
              Images come from FileStreamServlet:  <img src=".../file?id=N">
--%>
<%@ page contentType="text/html;charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List" %>
<%@ page import="com.kpriet.portal.model.Student, com.kpriet.portal.model.StudentFile" %>
<%@ page import="com.kpriet.portal.util.HtmlUtil" %>
<%
    String ctx = request.getContextPath();
    @SuppressWarnings("unchecked")
    List<Student> students = (List<Student>) request.getAttribute("students");
    String search = (String) request.getAttribute("search");
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | KPRIET Student Profile Portal</title>
    <link rel="stylesheet" href="<%= ctx %>/css/portal.css">
</head>
<body>
<%@ include file="navbar.jspf" %>

<main class="container">
    <div class="page-head">
        <h2>Student Profiles <span class="count"><%= students.size() %></span></h2>
        <form method="get" action="<%= ctx %>/students" class="search">
            <input type="search" name="q" placeholder="Search by name, roll no, course"
                   value="<%= HtmlUtil.escape(search) %>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <% if (students.isEmpty()) { %>
        <div class="card empty">No students found for "<%= HtmlUtil.escape(search) %>".</div>
    <% } else { %>
        <div class="student-grid">
        <%-- LOOP 1: one card for every student --%>
        <% for (Student s : students) {
               StudentFile photo = s.getProfileImage();
               List<StudentFile> documents = s.getDocuments(); %>
            <article class="card student-card">

                <%-- CONDITION: real photo from the servlet, or initials --%>
                <% if (photo != null) { %>
                    <img src="<%= ctx %>/file?id=<%= photo.getId() %>" class="avatar"
                         alt="Photo of <%= HtmlUtil.escape(s.getName()) %>">
                <% } else { %>
                    <div class="avatar placeholder">
                        <%= HtmlUtil.escape(HtmlUtil.initials(s.getName())) %></div>
                <% } %>

                <h3><%= HtmlUtil.escape(s.getName()) %></h3>
                <p class="roll"><%= HtmlUtil.escape(s.getRollNo()) %></p>
                <p class="muted"><%= HtmlUtil.escape(s.getCourse()) %><br>
                    <%= HtmlUtil.yearLabel(s.getYearOfStudy()) %></p>

                <div class="chips">
                    <% if (documents.isEmpty()) { %>
                        <span class="muted small">No documents uploaded</span>
                    <% } else { %>
                        <%-- LOOP 2 (nested): one link for every document --%>
                        <% for (StudentFile d : documents) { %>
                            <a href="<%= ctx %>/file?id=<%= d.getId() %>" target="_blank"
                               class="chip <%= d.isPdf() ? "chip-pdf" : "chip-img" %>"
                               title="<%= HtmlUtil.escape(d.getOriginalName()) %>">
                                <%= d.getCategoryLabel() %></a>
                        <% } %>
                    <% } %>
                </div>

                <a href="<%= ctx %>/profile?id=<%= s.getId() %>" class="btn btn-primary btn-block">
                    View Profile</a>
            </article>
        <% } %>
        </div>
    <% } %>
</main>

<footer class="footer">U21CS501 Web Technologies &middot; S B GOKUL (24CS067)</footer>
</body>
</html>
