package com.kpriet.portal.servlet;

import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;

/**
 * File    : LogoutServlet.java   (URL: /logout)
 * Author  : S B GOKUL (24CS067)
 * Purpose : Destroys the session and returns to the login page.
 */
public class LogoutServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);
        if (session != null) {
            session.invalidate();               // removes the "user" attribute and more
        }
        resp.sendRedirect(req.getContextPath() + "/login?logout=1");
    }
}
