document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("form[action='../controllers/createNewUser.php']");
    const getInput = id => document.getElementById(id);

    const username = getInput("username");
    const firstName = getInput("firstName");
    const lastName = getInput("lastName");
    const email = getInput("email");
    const password = getInput("password");
    const password2 = getInput("password2");
    const securityAnswer = getInput("securityAnswer");


    const rules = {
        username: { el: username, min: 3, max: 20, msg: "3–20 Zeichen" },
        firstName: { el: firstName, min: 2, max: 30, msg: "Min. 2 Zeichen" },
        lastName: { el: lastName, min: 2, max: 30, msg: "Min. 2 Zeichen" },
        securityAnswer: { el: securityAnswer, min: 2, max: 50, msg: "Min. 2 Zeichen" }
    };

    const passwordRule = { el: password, min: 8, max: 40 };

    const regex = {
        upper: /[A-Z]/,
        lower: /[a-z]/,
        special: /[^A-Za-z0-9]/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    };

    function setError(el, msg) {
        if (!el) return;
        el.classList.add("is-invalid");
        el.classList.remove("is-valid");

        let feedback = el.parentElement.querySelector(".invalid-feedback");
        if (!feedback) {
        feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        el.parentElement.appendChild(feedback);
        }
        feedback.innerText = msg;
    }

    function setSuccess(el) {
        if (!el) return;
        el.classList.remove("is-invalid");
        el.classList.add("is-valid");

        const feedback = el.parentElement.querySelector(".invalid-feedback");
        if (feedback) feedback.remove();
    }

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

    function validateEmail() {
        if (!email) return true;
        const val = email.value.trim();
        if (!regex.email.test(val)) {
        setError(email, "Ungültige E-Mail-Adresse");
        return false;
        }
        setSuccess(email);
        return true;
    }

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

    function validatePasswordMatch() {
        if (!password || !password2) return true;
        if (password.value !== password2.value) {
        setError(password2, "Passwörter stimmen nicht überein");
        return false;
        }
        setSuccess(password2);
        return true;
    }

    Object.values(rules).forEach(rule => {
        if (!rule.el) return;
        rule.el.addEventListener("input", () => validateField(rule));
    });

    if (password) password.addEventListener("input", () => { validatePassword(); validatePasswordMatch(); });
    if (password2) password2.addEventListener("input", validatePasswordMatch);
    if (email) email.addEventListener("input", validateEmail);


    if (form) {
        form.addEventListener("submit", (e) => {
        let valid = true;

        Object.values(rules).forEach(rule => { if (!validateField(rule)) valid = false; });
        if (!validateEmail()) valid = false;
        if (!validatePassword()) valid = false;
        if (!validatePasswordMatch()) valid = false;

        if (!valid) e.preventDefault();
        });
    }

    // Toggle function für Eye-icon
    const passwordContainers = document.querySelectorAll(".password-container");

    passwordContainers.forEach(container => {
        const input = container.querySelector("input[type='password'], input[type='text']");
        const eyeOpen = container.querySelector(".password-eye svg:first-child");
        const eyeSlash = container.querySelector(".password-eye svg:last-child");

        if (!input || !eyeOpen || !eyeSlash) return;

        container.querySelector(".password-eye").addEventListener("click", () => {
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
    });

});
