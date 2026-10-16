package com.kpriet.portal.model;

import java.io.Serializable;

/**
 * File    : User.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Logged-in user, stored in the HttpSession after login.
 *           Serializable so Tomcat can save sessions on restart.
 */
public class User implements Serializable {

    private static final long serialVersionUID = 1L;

    private final int id;
    private final String username;
    private final String fullName;

    public User(int id, String username, String fullName) {
        this.id = id;
        this.username = username;
        this.fullName = fullName;
    }

    public int getId()            { return id; }
    public String getUsername()   { return username; }
    public String getFullName()   { return fullName; }
}
