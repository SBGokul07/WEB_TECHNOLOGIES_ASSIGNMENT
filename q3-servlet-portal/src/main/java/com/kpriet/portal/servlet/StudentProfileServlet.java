package com.kpriet.portal.servlet;

import com.kpriet.portal.dao.StudentDAO;
import com.kpriet.portal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.SQLException;

/**
 * File    : StudentProfileServlet.java   (URL: /profile?id=N)
 * Author  : S B GOKUL (24CS067)
 * Purpose : Loads ONE student with all files and shows profile.jsp.
 */
public class StudentProfileServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        int id = parseId(req.getParameter("id"));
        if (id <= 0) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "A valid student id is required.");
            return;
        }

        try {
            Student student = new StudentDAO(getServletContext()).findById(id);
            if (student == null) {
                resp.sendError(HttpServletResponse.SC_NOT_FOUND, "Student not found.");
                return;
            }
            req.setAttribute("student", student);
            req.getRequestDispatcher("/WEB-INF/views/profile.jsp").forward(req, resp);
        } catch (SQLException e) {
            throw new ServletException("Could not load the student profile", e);
        }
    }

    /** Converts the id parameter to a number; returns -1 if it is invalid. */
    static int parseId(String value) {
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException e) {
            return -1;
        }
    }
}
