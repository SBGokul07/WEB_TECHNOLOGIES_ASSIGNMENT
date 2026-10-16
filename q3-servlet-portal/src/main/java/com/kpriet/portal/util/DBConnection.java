package com.kpriet.portal.util;

import jakarta.servlet.ServletContext;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * File    : DBConnection.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Opens JDBC connections to the MySQL database.
 *           The driver, URL, user and password are read from the
 *           context-param entries in WEB-INF/web.xml, so they can be
 *           changed without recompiling the code.
 */
public final class DBConnection {

    private DBConnection() {
        // Utility class - objects are not needed
    }

    public static Connection getConnection(ServletContext ctx) throws SQLException {
        String driver = ctx.getInitParameter("jdbcDriver");
        try {
            Class.forName(driver);                      // Step 1: load the JDBC driver
        } catch (ClassNotFoundException e) {
            throw new SQLException("JDBC driver " + driver + " not found. Copy "
                    + "mysql-connector-j-x.x.x.jar into WEB-INF/lib.", e);
        }
        // Step 2: open the connection (closed by try-with-resources in the DAO)
        return DriverManager.getConnection(
                ctx.getInitParameter("jdbcUrl"),
                ctx.getInitParameter("jdbcUser"),
                ctx.getInitParameter("jdbcPassword"));
    }
}
