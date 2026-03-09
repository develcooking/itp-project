document.addEventListener("DOMContentLoaded", () => {
    const userNameInput = document.getElementById('userName');
    const emailInput = document.getElementById('email');

    if (userNameInput) {
        userNameInput.addEventListener('input', function() {
            const errorDiv = this.parentElement.querySelector('.invalidUserName');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
            this.classList.remove('is-invalid');
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const errorDiv = this.parentElement.querySelector('.invalidUserName');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
            this.classList.remove('is-invalid');
        });
    }
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
    const emailLogin = getInput("emailLogin");

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
    function validatePasswordMatch() {
        if (!password || !confirmpassword) return true;

        if (password.value !== confirmpassword.value) {
            setError(confirmpassword, "Passwörter stimmen nicht überein");
            return false;
        }

        setSuccess(confirmpassword);
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
    if (password) password.addEventListener("input", () => {
        validatePassword();
        validatePasswordMatch();
    });

    if (confirmpassword) confirmpassword.addEventListener("input", validatePasswordMatch);

    // Email fields
    if (email) email.addEventListener("input", validateEmail);
    if (emailLogin) emailLogin.addEventListener("input", () => validateEmail(emailLogin));

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

    /**
     * Setup show/hide password toggle
     * @param {string} inputId - Password input ID
     * @param {string} eyeOpenId - Open eye icon ID
     * @param {string} eyeSlashId - Slash eye icon ID
     */
    function setupPasswordToggle(inputId, eyeOpenId, eyeSlashId) {
        const input = document.getElementById(inputId);
        const eyeOpen = document.getElementById(eyeOpenId);
        const eyeSlash = document.getElementById(eyeSlashId);

        if (!input || !eyeOpen || !eyeSlash) return;

        // Click container (icon wrapper)
        const toggleContainer = eyeOpen.parentElement;

        toggleContainer.addEventListener("click", () => {
            if (input.type === "password") {
                input.type = "text";
                eyeOpen.style.display = "none";
                eyeSlash.style.display = "inline";
            } else {
                input.type = "password";
                eyeOpen.style.display = "inline";
                eyeSlash.style.display = "none";
            }
        });
    }

    // Init toggles
    setupPasswordToggle("password", "eyeOpen", "eyeSlash");
    setupPasswordToggle("confirmPassword", "eyeOpenConfirm", "eyeSlashConfirm");
    setupPasswordToggle("passwordLogin", "eyeOpenLogin", "eyeSlashLogin");

});
