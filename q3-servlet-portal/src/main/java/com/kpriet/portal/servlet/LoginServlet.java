package com.kpriet.portal.servlet;

import com.kpriet.portal.dao.UserDAO;
import com.kpriet.portal.model.User;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;
import java.sql.SQLException;

/**
 * File    : LoginServlet.java   (URL: /login)
 * Author  : S B GOKUL (24CS067)
 * Purpose : GET  - shows the login form
 *           POST - checks the username / password and creates the session
 */
public class LoginServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;
    private static final String VIEW = "/WEB-INF/views/login.jsp";
    private static final int SESSION_TIMEOUT_SECONDS = 15 * 60;   // 15 minutes

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        HttpSession session = req.getSession(false);
        if (session != null && session.getAttribute("user") != null) {
            resp.sendRedirect(req.getContextPath() + "/students");   // already logged in
            return;
        }
        req.getRequestDispatcher(VIEW).forward(req, resp);
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {
        req.setCharacterEncoding("UTF-8");
        String username = req.getParameter("username");
        String password = req.getParameter("password");
        username = (username == null) ? "" : username.trim();

        // 1. Required field check
        if (username.isEmpty() || password == null || password.isEmpty()) {
            showError(req, resp, username, "Please enter both username and password.");
            return;
        }

        try {
            // 2. Verify the credentials against the database
            User user = new UserDAO(getServletContext()).authenticate(username, password);
            if (user == null) {
                showError(req, resp, username, "Invalid username or password.");
                return;
            }

            // 3. Session fixation protection: discard any old session first
            HttpSession oldSession = req.getSession(false);
            if (oldSession != null) {
                oldSession.invalidate();
            }

            // 4. Create a fresh session and remember the user in it
            HttpSession session = req.getSession(true);
            session.setAttribute("user", user);
            session.setMaxInactiveInterval(SESSION_TIMEOUT_SECONDS);

            resp.sendRedirect(req.getContextPath() + "/students");
        } catch (SQLException e) {
            throw new ServletException("Login failed because of a database error", e);
        }
    }

    private void showError(HttpServletRequest req, HttpServletResponse resp,
                           String username, String message)
            throws ServletException, IOException {
        req.setAttribute("error", message);
        req.setAttribute("username", username);         // keep the typed username
        req.getRequestDispatcher(VIEW).forward(req, resp);
    }
}
