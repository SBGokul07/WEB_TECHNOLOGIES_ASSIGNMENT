package com.kpriet.portal.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * File    : PasswordUtil.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Hashes passwords with SHA-256. The result is the same
 *           64-character hex string that MySQL's SHA2(text, 256) returns,
 *           so it can be compared with the users.password_hash column.
 */
public final class PasswordUtil {

    private PasswordUtil() {
    }

    public static String sha256(String text) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            byte[] hash = md.digest(text.getBytes(StandardCharsets.UTF_8));

            StringBuilder hex = new StringBuilder(64);
            for (byte b : hash) {
                hex.append(String.format("%02x", b));   // each byte -> 2 hex digits
            }
            return hex.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalStateException("SHA-256 is not available", e);
        }
    }

    /** Compares two hashes in constant time (prevents timing attacks). */
    public static boolean matches(String plainPassword, String storedHash) {
        if (plainPassword == null || storedHash == null) {
            return false;
        }
        byte[] given  = sha256(plainPassword).getBytes(StandardCharsets.UTF_8);
        byte[] stored = storedHash.getBytes(StandardCharsets.UTF_8);
        return MessageDigest.isEqual(given, stored);
    }
}
