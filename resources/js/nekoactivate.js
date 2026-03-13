let neko = null;

function initNeko() {
    if (!neko) {
        console.log("Neko: Initializing instance...");
        neko = createNeko({
            fps: 30,
            allowBehaviorChange: false,
        });
    }
}

const invisibleButton = document.getElementById('invisibleButton');

function updateCookie(isVisible) {
    document.cookie = `cat=${isVisible}; path=/; max-age=31536000`;
    console.log("Neko: Cookie updated -> cat=" + isVisible);
}

function toggleCat() {
    const isActive = invisibleButton.getAttribute('data-active') === 'true';
    console.log("Neko: Button clicked. Current state (isActive):", isActive);

    if (!isActive) {
        // Button was visible -> Hide it and START Neko
        console.log("Neko: Hiding button and starting cat...");
        invisibleButton.style.opacity = 1;
        initNeko();
        neko.start();

        // Ensure sprite is visible if it was previously set to display:none
        const nekoEl = document.querySelector('.neko');
        if (nekoEl) nekoEl.style.display = 'block';

        invisibleButton.setAttribute('data-active', 'true');
        updateCookie(true);
    } else {
        // Button was hidden -> Show it and STOP Neko
        console.log("Neko: Showing button and stopping cat...");
        invisibleButton.style.opacity = 0;
        if (neko) {
            neko.stop();
            const nekoEl = document.querySelector('.neko');
            if (nekoEl) nekoEl.style.display = 'none';
        }
        invisibleButton.setAttribute('data-active', 'false');
        updateCookie(false);
    }
}

invisibleButton.addEventListener("click", toggleCat);

// Initial load state check
const catcookie = document.cookie.split('; ').find(row => row.startsWith('cat='));
const isCatActive = catcookie && catcookie.split('=')[1] === 'true';
console.log("Neko: Initial load check. Cookie active:", isCatActive);

if (isCatActive) {
    invisibleButton.style.opacity = 1;
    invisibleButton.setAttribute('data-active', 'true');
    initNeko();
    neko.start();
} else {
    invisibleButton.style.opacity = 0;
    invisibleButton.setAttribute('data-active', 'false');
}