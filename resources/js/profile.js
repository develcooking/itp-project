// Source - https://stackoverflow.com/a/65996386
// Posted by Simon Dehaut, modified by community. See post 'Timeline' for change history
// Retrieved 2026-03-21, License - CC BY-SA 4.0
// This is needed to copy on http only connections

async function copyToClipboard(textToCopy) {
    // Navigator clipboard api needs a secure context (https)
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(textToCopy);
    } else {
        // Use the 'out of viewport hidden text area' trick
        const textArea = document.createElement("textarea");
        textArea.value = textToCopy;

        // Move textarea out of the viewport so it's not visible
        textArea.style.position = "absolute";
        textArea.style.left = "-999999px";

        document.body.prepend(textArea);
        textArea.select();

        try {
            document.execCommand('copy');
        } catch (error) {
            console.error(error);
        } finally {
            textArea.remove();
        }
    }
}

async function copyApiToken() {
    const copyText = document.getElementById("apiTokenInput");
    await copyToClipboard(copyText.value);
    alert("Token in die Zwischenablage kopiert!");
}

document.addEventListener('DOMContentLoaded', function () {
    const profileImageInput = document.getElementById('profileImage');
    const profileImageFileName = document.getElementById('profileImageFileName');
    const profileImagePreview = document.getElementById('profileImagePreview');
    const profileImagePlaceholder = document.getElementById('profileImagePlaceholder');
    const removeProfileImageInput = document.getElementById('removeProfileImage');
    const removeProfileImageButton = document.getElementById('removeProfileImageButton');
    let currentPreviewObjectUrl = null;

    if (!profileImageInput || !profileImageFileName || !profileImagePreview || !profileImagePlaceholder || !removeProfileImageInput) {
        return;
    }

    profileImageInput.addEventListener('change', function () {
        if (profileImageInput.files && profileImageInput.files.length > 0) {
            const selectedFile = profileImageInput.files[0];
            removeProfileImageInput.value = '0';

            profileImageFileName.textContent = selectedFile.name;
            profileImageFileName.classList.remove('text-muted');

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
            }

            currentPreviewObjectUrl = URL.createObjectURL(selectedFile);
            profileImagePreview.src = currentPreviewObjectUrl;
            profileImagePreview.classList.remove('d-none');
            profileImagePlaceholder.classList.add('d-none');
        } else {
            profileImageFileName.textContent = 'Profilbild hochladen (JPG, PNG, WEBP, max. 2 MB)';
            profileImageFileName.classList.add('text-muted');

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
                currentPreviewObjectUrl = null;
            }
        }
    });

    if (removeProfileImageButton) {
        removeProfileImageButton.addEventListener('click', function () {
            removeProfileImageInput.value = '1';
            profileImageInput.value = '';

            if (currentPreviewObjectUrl) {
                URL.revokeObjectURL(currentPreviewObjectUrl);
                currentPreviewObjectUrl = null;
            }

            profileImagePreview.classList.add('d-none');
            profileImagePlaceholder.classList.remove('d-none');
            profileImageFileName.textContent = 'Profilbild wird beim Speichern gelöscht';
            profileImageFileName.classList.remove('text-muted');
        });
    }
});