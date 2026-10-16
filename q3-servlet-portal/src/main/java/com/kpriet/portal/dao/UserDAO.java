package com.kpriet.portal.dao;

import com.kpriet.portal.model.User;
import com.kpriet.portal.util.DBConnection;
import com.kpriet.portal.util.PasswordUtil;
import jakarta.servlet.ServletContext;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * File    : UserDAO.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Checks login details against the users table.
 */
public class UserDAO {

    private final ServletContext ctx;

    public UserDAO(ServletContext ctx) {
        this.ctx = ctx;
    }

    /** Returns the User when username + password are correct, else null. */
    public User authenticate(String username, String password) throws SQLException {
        String sql = "SELECT id, username, password_hash, full_name "
                   + "FROM users WHERE username = ?";

        // try-with-resources closes ResultSet, Statement and Connection automatically
        try (Connection con = DBConnection.getConnection(ctx);
             PreparedStatement ps = con.prepareStatement(sql)) {

            ps.setString(1, username);          // PreparedStatement blocks SQL injection
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next() && PasswordUtil.matches(password, rs.getString("password_hash"))) {
                    return new User(rs.getInt("id"), rs.getString("username"),
                                    rs.getString("full_name"));
                }
            }
        }
        return null;
    }
}
