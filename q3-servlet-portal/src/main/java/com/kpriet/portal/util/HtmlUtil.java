package com.kpriet.portal.util;

/**
 * File    : HtmlUtil.java
 * Author  : S B GOKUL (24CS067)
 * Purpose : Small helpers used by the JSP pages.
 */
public final class HtmlUtil {

    private HtmlUtil() {
    }

    /**
     * Escapes special HTML characters so that data from the database is
     * shown as text and can never run as HTML / JavaScript (prevents XSS).
     */
    public static String escape(Object value) {
        if (value == null) {
            return "";
        }
        String text = value.toString();
        StringBuilder out = new StringBuilder(text.length() + 16);
        for (char c : text.toCharArray()) {
            switch (c) {
                case '&':  out.append("&amp;");  break;
                case '<':  out.append("&lt;");   break;
                case '>':  out.append("&gt;");   break;
                case '"':  out.append("&quot;"); break;
                case '\'': out.append("&#39;");  break;
                default:   out.append(c);
            }
        }
        return out.toString();
    }

    /** "S B Gokul" -> "SG" (shown when a student has no photo). */
    public static String initials(String name) {
        if (name == null || name.trim().isEmpty()) {
            return "?";
        }
        String[] parts = name.trim().split("\\s+");
        String first = parts[0].substring(0, 1);
        String last  = parts.length > 1 ? parts[parts.length - 1].substring(0, 1) : "";
        return (first + last).toUpperCase();
    }

    /** 3 -> "III Year" */
    public static String yearLabel(int year) {
        String[] roman = {"", "I", "II", "III", "IV"};
        return (year >= 1 && year <= 4 ? roman[year] : String.valueOf(year)) + " Year";
    }
}
