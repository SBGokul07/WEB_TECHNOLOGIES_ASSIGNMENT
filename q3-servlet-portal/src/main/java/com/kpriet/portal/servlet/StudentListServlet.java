package com.kpriet.portal.servlet;

import com.kpriet.portal.dao.StudentDAO;
import com.kpriet.portal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.SQLException;
import java.util.List;

/**
 * File    : StudentListServlet.java   (URL: /students)
 * Author  : S B GOKUL (24CS067)
 * Purpose : Fetches all students and their files using JDBC and forwards
 *           them to students.jsp, which loops over the list.
 */
public class StudentListServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.setCharacterEncoding("UTF-8");
        String search = req.getParameter("q");

        try {
            List<Student> students = new StudentDAO(getServletContext()).findAll(search);

            // Data for the JSP page (MVC: servlet = controller, JSP = view)
            req.setAttribute("students", students);
            req.setAttribute("search", search == null ? "" : search.trim());
            req.getRequestDispatcher("/WEB-INF/views/students.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException("Could not load the student list", e);
        }
    }
}
