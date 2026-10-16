<%--
    File    : WEB-INF/views/login.jsp
    Author  : S B GOKUL (24CS067)
    Purpose : Login form. Kept inside WEB-INF so it can only be reached
              through LoginServlet. session="false" means opening this
              page does not create an empty session.
--%>
<%@ page contentType="text/html;charset=UTF-8" pageEncoding="UTF-8" session="false" %>
<%@ page import="com.kpriet.portal.util.HtmlUtil" %>
<%
    String ctx      = request.getContextPath();
    String error    = (String) request.getAttribute("error");
    String username = (String) request.getAttribute("username");

    // Information messages passed in the URL by other pages
    String info = null;
    if (request.getParameter("logout") != null) {
        info = "You have been logged out successfully.";
    } else if (request.getParameter("required") != null) {
        info = "Please log in to access the student portal.";
    }
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KPRIET Student Profile Portal</title>
    <link rel="stylesheet" href="<%= ctx %>/css/portal.css">
</head>
<body class="login-page">
    <main class="login-card">
        <h1>KPRIET</h1>
        <p class="subtitle">Student Profile Portal</p>

        <% if (error != null) { %>
            <div class="alert alert-error"><%= HtmlUtil.escape(error) %></div>
        <% } else if (info != null) { %>
            <div class="alert alert-info"><%= info %></div>
        <% } %>

        <form method="post" action="<%= ctx %>/login">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus
                   value="<%= HtmlUtil.escape(username) %>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
        <p class="hint">Demo account: <strong>admin</strong> / <strong>Admin@123</strong></p>
    </main>
</body>
</html>
