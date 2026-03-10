document.addEventListener("DOMContentLoaded", () => {
    /* ===========================
       FORM + INPUT REFERENCES
    ============================ */

    /** @type {HTMLFormElement|null} */
    const form = document.querySelector("form[action='../controllers/createNewUser.php']");

    /**
     * Helper function to get input elements by ID
     * @param {string} id - DOM element ID
     * @returns {HTMLElement|null}
     */
    const getInput = id => document.getElementById(id);

    /** @type {HTMLInputElement|null} */
    const username = getInput("userName");
    const firstName = getInput("firstName");
    const lastName = getInput("lastName");
    const email = getInput("email");
    const password = getInput("password");
    const confirmpassword = getInput("confirmPassword");
    const securityanswer = getInput("securityAnswer");
    const emaillogin = getInput("emailLogin");
    const emailforgot = getInput("emailForgot");
    const passwordReset = getInput("passwordReset");
    const confirmPasswordReset = getInput("confirmPasswordReset");

    /* ===========================
       VALIDATION RULES
    ============================ */

    /**
     * Validation config object
     * el = input element
     * min = min length
     * max = max length
     * msg = error message
     */
    const rules = {
        username: { el: username, min: 3, max: 20, msg: "3–20 Zeichen" },
        firstName: { el: firstName, min: 2, max: 30, msg: "Min. 2 Zeichen" },
        lastName: { el: lastName, min: 2, max: 30, msg: "Min. 2 Zeichen" },
        securityanswer: { el: securityanswer, min: 2, max: 50, msg: "Min. 2 Zeichen" }
    };

    /** Password rule config */
    const passwordRule = { el: password, min: 8, max: 40 };

    /** Regex patterns for validation */
    const regex = {
        upper: /[A-Z]/,                 // at least one uppercase
        lower: /[a-z]/,                 // at least one lowercase
        special: /[^A-Za-z0-9]/,        // at least one special char
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ // email format
    };

    /* ===========================
       UI FEEDBACK FUNCTIONS
    ============================ */

    /**
     * Sets error state + message on input
     * @param {HTMLElement} el - input element
     * @param {string} msg - error message
     */
    function setError(el, msg) {
        if (!el) return;

        el.classList.add("is-invalid");
        el.classList.remove("is-valid");

        let feedback = el.parentElement.querySelector(".invalid-feedback");

        // Create feedback div if not existing
        if (!feedback) {
            feedback = document.createElement("div");
            feedback.className = "invalid-feedback";
            el.parentElement.appendChild(feedback);
        }

        feedback.innerText = msg;
    }

    /**
     * Sets success (valid) state on input
     * @param {HTMLElement} el - input element
     */
    function setSuccess(el) {
        if (!el) return;

        el.classList.remove("is-invalid");
        el.classList.add("is-valid");

        const feedback = el.parentElement.querySelector(".invalid-feedback");
        if (feedback) feedback.remove();
    }

    /* ===========================
       VALIDATION FUNCTIONS
    ============================ */

    /**
     * Generic field validator (length-based)
     * @param {{el:HTMLElement, min:number, max:number, msg:string}} rule
     * @returns {boolean}
     */
    function validateField({ el, min, max, msg }) {
        if (!el) return true;

        const val = el.value.trim();

        if (val.length < min) {
            setError(el, msg);
            return false;
        }

        if (val.length > max) {
            setError(el, `Maximal ${max} Zeichen`);
            return false;
        }

        setSuccess(el);
        return true;
    }

    /**
     * Validates email format
     * @returns {boolean}
     */
    function validateEmail(input) {
    if (!input) return true;

    const val = input.value.trim();

    if (!regex.email.test(val)) {
        setError(input, "Ungültige E-Mail-Adresse");
        return false;
    }

    setSuccess(input);
    return true;
}


    /**
     * Validates password strength
     * @param {HTMLInputElement} pw - password input
     * @returns {boolean}
     */
    function validatePassword(pw = password) {
        if (!pw) return true;

        const val = pw.value;

        if (val.length < passwordRule.min) return setError(pw, "Mindestens 8 Zeichen"), false;
        if (val.length > passwordRule.max) return setError(pw, "Maximal 40 Zeichen"), false;
        if (!regex.upper.test(val)) return setError(pw, "Mindestens 1 Großbuchstabe"), false;
        if (!regex.lower.test(val)) return setError(pw, "Mindestens 1 Kleinbuchstabe"), false;
        if (!regex.special.test(val)) return setError(pw, "Mindestens 1 Sonderzeichen"), false;

        setSuccess(pw);
        return true;
    }

    /**
     * Checks if password and confirm password match
     * @returns {boolean}
     */
    function validatePasswordMatch(pw, confirm) {
    if (!pw || !confirm) return true;

    if (pw.value !== confirm.value) {
        setError(confirm, "Passwörter stimmen nicht überein");
        return false;
    }

    setSuccess(confirm);
    return true;
}

    /* ===========================
       LIVE VALIDATION EVENTS
    ============================ */

    // Text fields
    Object.values(rules).forEach(rule => {
        if (!rule.el) return;
        rule.el.addEventListener("input", () => validateField(rule));
    });

    // Password fields
    if (password) {
    password.addEventListener("input", () => {
        validatePassword(password);
        validatePasswordMatch(password, confirmpassword);
    });
}

if (passwordReset) {
    passwordReset.addEventListener("input", () => {
        validatePassword(passwordReset);
        validatePasswordMatch(passwordReset, confirmPasswordReset);
    });
}

if (confirmpassword)
    confirmpassword.addEventListener("input", () =>
        validatePasswordMatch(password, confirmpassword)
    );

if (confirmPasswordReset)
    confirmPasswordReset.addEventListener("input", () =>
        validatePasswordMatch(passwordReset, confirmPasswordReset)
    );
    // Email fields
    if (email) email.addEventListener("input", () => validateEmail(email));
    if (emaillogin) emaillogin.addEventListener("input", () => validateEmail(emaillogin));
    if (emailforgot) emailforgot.addEventListener("input", () => validateEmail(emailforgot));

    /* ===========================
       FORM SUBMIT VALIDATION
    ============================ */

    if (form) {
        form.addEventListener("submit", (e) => {
            let valid = true;

            Object.values(rules).forEach(rule => {
                if (!validateField(rule)) valid = false;
            });

            if (!validateEmail()) valid = false;
            if (!validatePassword()) valid = false;
            if (!validatePasswordMatch()) valid = false;

            // Prevent submit if invalid
            if (!valid) e.preventDefault();
        });
    }

    /* ===========================
       PASSWORD VISIBILITY TOGGLE
    ============================ */

document.querySelectorAll(".password-container").forEach(container => {

    const input = container.querySelector("input");
    const eyeOpen = container.querySelector("img[id^='eyeOpen']");
    const eyeSlash = container.querySelector("img[id^='eyeSlash']");
    const toggle = container.querySelector(".password-eye");

    if (!input || !eyeOpen || !eyeSlash) return;

    toggle.addEventListener("click", () => {

        const isPassword = input.type === "password";

        input.type = isPassword ? "text" : "password";
        eyeOpen.style.display = isPassword ? "none" : "inline";
        eyeSlash.style.display = isPassword ? "inline" : "none";

    });

});


});
