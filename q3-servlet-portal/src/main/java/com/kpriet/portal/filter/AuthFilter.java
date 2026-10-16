package com.kpriet.portal.filter;

import jakarta.servlet.Filter;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;

/**
 * File    : AuthFilter.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : SESSION HANDLING - runs before EVERY request (mapped to /*
 *           in web.xml). Users who are not logged in are sent to the
 *           login page, so no servlet, JSP or file can be reached
 *           without a valid session.
 */
public class AuthFilter implements Filter {

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest req = (HttpServletRequest) request;
        HttpServletResponse resp = (HttpServletResponse) response;

        // Path inside this web application, e.g. "/students"
        String path = req.getRequestURI().substring(req.getContextPath().length());

        // Pages that may be opened without logging in
        boolean isPublic = path.equals("/login") || path.equals("/logout")
                        || path.startsWith("/css/");

        // getSession(false) returns the existing session or null (never creates one)
        HttpSession session = req.getSession(false);
        boolean loggedIn = session != null && session.getAttribute("user") != null;

        if (isPublic) {
            chain.doFilter(request, response);
        } else if (loggedIn) {
            // Do not let the browser cache private pages. After logout the
            // Back button will then not show the old pages again.
            resp.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
            resp.setHeader("Pragma", "no-cache");
            resp.setDateHeader("Expires", 0);
            chain.doFilter(request, response);
        } else {
            resp.sendRedirect(req.getContextPath() + "/login?required=1");
        }
    }
}
