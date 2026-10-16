/* =============================================================
 * File    : validation.js
 * Author  : S B GOKUL (24CS067)
 * Purpose : Client-side validation for the registration form (Q1)
 *           - required fields, email / roll no / phone format
 *           - password strength meter and confirm password
 *           - file type and file size checks with photo preview
 * ============================================================= */

"use strict";

// Run only after the whole HTML document has been loaded
document.addEventListener("DOMContentLoaded", function () {

    // ---------- 1. References to form elements ----------
    const form        = document.getElementById("registrationForm");
    const formAlert   = document.getElementById("formAlert");
    const successCard = document.getElementById("successCard");

    const fullName    = document.getElementById("fullName");
    const rollNo      = document.getElementById("rollNo");
    const email       = document.getElementById("email");
    const phone       = document.getElementById("phone");
    const course      = document.getElementById("course");
    const year        = document.getElementById("year");
    const password    = document.getElementById("password");
    const confirmPwd  = document.getElementById("confirmPassword");
    const photo       = document.getElementById("photo");
    const idProof     = document.getElementById("idProof");
    const declaration = document.getElementById("declaration");
    const genderGroup = document.getElementById("genderGroup");

    // ---------- 2. Validation rules ----------
    const PATTERNS = {
        name:  /^[A-Za-z][A-Za-z .]{2,59}$/,        // letters, spaces and dots
        roll:  /^[0-9]{2}[A-Z]{2,4}[0-9]{3}$/,       // e.g. 24CS067
        email: /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/,    // user@domain.com
        phone: /^[6-9][0-9]{9}$/                     // 10-digit Indian mobile
    };

    const MB = 1024 * 1024;
    const PHOTO_RULE = { types: ["image/jpeg", "image/png"], maxSize: 2 * MB };
    const DOC_RULE   = { types: ["application/pdf", "image/jpeg", "image/png"],
                         maxSize: 5 * MB };

    // ---------- 3. Helpers to show / clear messages ----------

    // Marks a field as invalid and shows the message under it
    function setError(element, message) {
        const group = element.closest(".form-group");
        group.classList.add("has-error");
        group.classList.remove("has-success");
        group.querySelector(".error-msg").textContent = message;
        return false;
    }

    // Marks a field as valid and clears its message
    function setSuccess(element) {
        const group = element.closest(".form-group");
        group.classList.remove("has-error");
        group.classList.add("has-success");
        group.querySelector(".error-msg").textContent = "";
        return true;
    }

    // Generic check: required + regular expression pattern
    function checkPattern(input, pattern, label, formatHint) {
        const value = input.value.trim();
        if (value === "") {
            return setError(input, label + " is required.");
        }
        if (!pattern.test(value)) {
            return setError(input, formatHint);
        }
        return setSuccess(input);
    }

    // ---------- 4. Individual field validators ----------
    function validateName() {
        return checkPattern(fullName, PATTERNS.name, "Full name",
            "Use 3-60 letters; only spaces and dots are allowed.");
    }

    function validateRoll() {
        return checkPattern(rollNo, PATTERNS.roll, "Roll number",
            "Format must be like 24CS067 (2 digits, 2-4 letters, 3 digits).");
    }

    function validateEmail() {
        return checkPattern(email, PATTERNS.email, "Email",
            "Enter a valid email address, e.g. name@example.com");
    }

    function validatePhone() {
        return checkPattern(phone, PATTERNS.phone, "Mobile number",
            "Enter a valid 10-digit number starting with 6, 7, 8 or 9.");
    }

    function validateGender() {
        const checked = form.querySelector('input[name="gender"]:checked');
        const firstRadio = genderGroup.querySelector("input");
        return checked ? setSuccess(firstRadio)
                       : setError(firstRadio, "Please select your gender.");
    }

    function validateSelect(select, label) {
        return select.value === "" ? setError(select, "Please select your " + label + ".")
                                   : setSuccess(select);
    }

    // Returns which password rules are satisfied (true / false each)
    function getPasswordRules(pwd) {
        return {
            length:  pwd.length >= 8,
            upper:   /[A-Z]/.test(pwd),
            lower:   /[a-z]/.test(pwd),
            digit:   /[0-9]/.test(pwd),
            special: /[^A-Za-z0-9]/.test(pwd)
        };
    }

    // Updates the coloured bar, the strength label and the rule checklist
    function updateStrengthMeter() {
        const rules = getPasswordRules(password.value);
        const score = Object.values(rules).filter(Boolean).length;  // 0 to 5

        const levels = [
            { text: "-",           color: "#e5e7eb" },
            { text: "Very Weak",   color: "#d93025" },
            { text: "Weak",        color: "#e8710a" },
            { text: "Medium",      color: "#f2a900" },
            { text: "Strong",      color: "#7cb342" },
            { text: "Very Strong", color: "#188038" }
        ];

        const fill = document.getElementById("strengthFill");
        fill.style.width = (score * 20) + "%";
        fill.style.backgroundColor = levels[score].color;
        document.getElementById("strengthText").textContent =
            "Strength: " + levels[score].text;

        // Tick or cross each rule in the checklist
        document.querySelectorAll("#passRules li").forEach(function (item) {
            item.classList.toggle("met", rules[item.dataset.rule]);
        });
        return score;
    }

    function validatePassword() {
        const score = updateStrengthMeter();
        if (password.value === "") {
            return setError(password, "Password is required.");
        }
        if (score < 5) {
            return setError(password, "Password is not strong enough - meet all 5 rules.");
        }
        return setSuccess(password);
    }

    function validateConfirm() {
        if (confirmPwd.value === "") {
            return setError(confirmPwd, "Please confirm your password.");
        }
        if (confirmPwd.value !== password.value) {
            return setError(confirmPwd, "Passwords do not match.");
        }
        return setSuccess(confirmPwd);
    }

    // Checks that a file is chosen, has an allowed type and size
    function validateFile(input, rule, label) {
        if (input.files.length === 0) {
            return setError(input, label + " is required.");
        }
        const file = input.files[0];
        if (!rule.types.includes(file.type)) {
            return setError(input, "Invalid file type (" + file.name + ").");
        }
        if (file.size > rule.maxSize) {
            const sizeMb = (file.size / MB).toFixed(2);
            return setError(input, "File is " + sizeMb + " MB; limit is "
                + (rule.maxSize / MB) + " MB.");
        }
        return setSuccess(input);
    }

    function validatePhoto() {
        const preview = document.getElementById("photoPreview");
        const isValid = validateFile(photo, PHOTO_RULE, "Profile photo");

        // Show a round preview of a valid photo
        if (isValid) {
            preview.src = URL.createObjectURL(photo.files[0]);
            preview.hidden = false;
        } else {
            preview.hidden = true;
        }
        return isValid;
    }

    function validateDeclaration() {
        return declaration.checked
            ? setSuccess(declaration)
            : setError(declaration, "You must accept the declaration.");
    }

    // ---------- 5. Live (real-time) validation ----------
    // Each entry: [element, validator, event that triggers it]
    const liveChecks = [
        [fullName, validateName, "blur"],
        [rollNo, validateRoll, "blur"],
        [email, validateEmail, "blur"],
        [phone, validatePhone, "blur"],
        [course, function () { return validateSelect(course, "course"); }, "change"],
        [year, function () { return validateSelect(year, "year of study"); }, "change"],
        [password, validatePassword, "input"],
        [confirmPwd, validateConfirm, "input"],
        [photo, validatePhoto, "change"],
        [idProof, function () { return validateFile(idProof, DOC_RULE, "ID proof"); },
            "change"],
        [declaration, validateDeclaration, "change"]
    ];

    liveChecks.forEach(function (entry) {
        const element = entry[0], validator = entry[1], eventName = entry[2];
        element.addEventListener(eventName, validator);

        // Once a field shows an error, re-check it while the user types
        element.addEventListener("input", function () {
            if (element.closest(".form-group").classList.contains("has-error")) {
                validator();
            }
        });
    });

    genderGroup.addEventListener("change", validateGender);

    // Roll number is always stored in capital letters
    rollNo.addEventListener("input", function () {
        rollNo.value = rollNo.value.toUpperCase();
    });

    // Allow only digits in the phone field
    phone.addEventListener("input", function () {
        phone.value = phone.value.replace(/[^0-9]/g, "");
    });

    // Show / hide password buttons
    document.querySelectorAll(".toggle-pass").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const input = document.getElementById(btn.dataset.target);
            const hidden = input.type === "password";
            input.type = hidden ? "text" : "password";
            btn.textContent = hidden ? "Hide" : "Show";
        });
    });

    // ---------- 6. Form submission ----------
    form.addEventListener("submit", function (event) {
        event.preventDefault();   // stop the browser from submitting

        // Run EVERY validator (not just until the first failure)
        // so that all error messages are shown together
        const results = liveChecks.map(function (entry) { return entry[1](); });
        results.push(validateGender());
        const errorCount = results.filter(function (ok) { return !ok; }).length;

        if (errorCount > 0) {
            formAlert.textContent = "Please correct the " + errorCount
                + " highlighted field(s) before submitting.";
            formAlert.hidden = false;
            // Move the cursor to the first invalid field
            const firstError = form.querySelector(".has-error input, .has-error select");
            if (firstError) { firstError.focus(); }
            window.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }

        // All fields are valid: show the success card.
        // (In the full system, form.submit() would send data to the server.)
        formAlert.hidden = true;
        document.getElementById("successName").textContent = fullName.value.trim();
        document.getElementById("successRoll").textContent = rollNo.value.trim();
        document.getElementById("successCourse").textContent = course.value;
        form.hidden = true;
        successCard.hidden = false;
    });

    // ---------- 7. Reset handling ----------
    function clearValidationState() {
        form.querySelectorAll(".form-group").forEach(function (group) {
            group.classList.remove("has-error", "has-success");
            const msg = group.querySelector(".error-msg");
            if (msg) { msg.textContent = ""; }
        });
        formAlert.hidden = true;
        document.getElementById("photoPreview").hidden = true;
    }

    form.addEventListener("reset", function () {
        clearValidationState();
        // Wait for the browser to clear the inputs, then refresh the meter
        setTimeout(updateStrengthMeter, 0);
    });

    document.getElementById("newEntryBtn").addEventListener("click", function () {
        form.reset();
        successCard.hidden = true;
        form.hidden = false;
    });
});
