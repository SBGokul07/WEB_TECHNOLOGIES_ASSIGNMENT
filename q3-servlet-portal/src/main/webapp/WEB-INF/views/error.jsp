<%--
    File    : WEB-INF/views/error.jsp
    Author  : S B GOKUL (24CS067)
    Purpose : One friendly page for 400 / 403 / 404 / 500 errors
              (configured in web.xml). Technical details are not shown.
--%>
<%@ page contentType="text/html;charset=UTF-8" pageEncoding="UTF-8"
         isErrorPage="true" session="false" %>
<%@ page import="com.kpriet.portal.util.HtmlUtil" %>
<%
    Object codeObj = request.getAttribute("jakarta.servlet.error.status_code");
    int code = (codeObj instanceof Integer) ? (Integer) codeObj : 500;
    String message = (String) request.getAttribute("jakarta.servlet.error.message");

    // Never show internal error text (stack traces) for server errors
    if (code == 500 || message == null || message.isEmpty()) {
        message = (code == 404) ? "The page you requested was not found."
                                : "Something went wrong. Please try again later.";
    }
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error <%= code %> | KPRIET Student Profile Portal</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/portal.css">
</head>
<body class="login-page">
    <main class="login-card">
        <h1><%= code %></h1>
        <p class="subtitle"><%= HtmlUtil.escape(message) %></p>
        <a href="<%= request.getContextPath() %>/students" class="btn btn-primary">Go to Home</a>
    </main>
</body>
</html>
