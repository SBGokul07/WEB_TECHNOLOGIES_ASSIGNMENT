package com.kpriet.portal.servlet;

import com.kpriet.portal.dao.StudentDAO;
import com.kpriet.portal.model.StudentFile;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.sql.SQLException;

/**
 * File    : FileStreamServlet.java   (URL: /file?id=N  or  /file?id=N&download=1)
 * Author  : S B GOKUL (24CS067)
 * Purpose : Reads an uploaded image / document from the upload folder and
 *           STREAMS it to the browser. JSP pages use it as the src of
 *           <img> tags, e.g.  <img src="file?id=5">
 */
public class FileStreamServlet extends HttpServlet {

    private static final long serialVersionUID = 1L;
    private static final int BUFFER_SIZE = 8192;          // 8 KB per read

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // 1. Find the file's metadata (path, type) in MySQL
        int id = StudentProfileServlet.parseId(req.getParameter("id"));
        if (id <= 0) {
            resp.sendError(HttpServletResponse.SC_BAD_REQUEST, "A valid file id is required.");
            return;
        }
        StudentFile meta;
        try {
            meta = new StudentDAO(getServletContext()).findFileById(id);
        } catch (SQLException e) {
            throw new ServletException("Could not read file details", e);
        }
        if (meta == null) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File record not found.");
            return;
        }

        // 2. Build the real path: <uploadBaseDir>/<file_path from database>
        File baseDir = new File(getServletContext().getInitParameter("uploadBaseDir"));
        File uploadsDir = new File(baseDir, "uploads").getCanonicalFile();
        File target = new File(baseDir, meta.getFilePath()).getCanonicalFile();

        // 3. Security: the file must be INSIDE the uploads folder (blocks ../ tricks)
        if (!target.toPath().startsWith(uploadsDir.toPath())) {
            resp.sendError(HttpServletResponse.SC_FORBIDDEN, "Access denied.");
            return;
        }
        if (!target.isFile()) {
            resp.sendError(HttpServletResponse.SC_NOT_FOUND, "File is missing on the server.");
            return;
        }

        // 4. Response headers: type, size and inline / download
        String mime = meta.getFileType();
        if (mime == null || mime.isEmpty()) {
            mime = getServletContext().getMimeType(target.getName());
        }
        resp.setContentType(mime != null ? mime : "application/octet-stream");
        resp.setContentLengthLong(target.length());
        resp.setHeader("X-Content-Type-Options", "nosniff");

        boolean download = "1".equals(req.getParameter("download"));
        String safeName = meta.getOriginalName().replaceAll("[\"\\\\\\r\\n]", "_");
        resp.setHeader("Content-Disposition",
                (download ? "attachment" : "inline") + "; filename=\"" + safeName + "\"");

        // 5. Stream the bytes in small chunks (the whole file is never in memory)
        try (InputStream in = new BufferedInputStream(new FileInputStream(target));
             OutputStream out = resp.getOutputStream()) {
            byte[] buffer = new byte[BUFFER_SIZE];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        }
    }
}
