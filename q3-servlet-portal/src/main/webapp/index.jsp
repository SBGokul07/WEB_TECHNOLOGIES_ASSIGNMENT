<%--
    File    : index.jsp  (welcome page)
    Purpose : Sends the user straight to the student list. AuthFilter
              redirects to the login page first if nobody is logged in.
--%>
<%@ page session="false" %>
<% response.sendRedirect(request.getContextPath() + "/students"); %>
