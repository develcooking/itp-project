document.addEventListener("DOMContentLoaded", function() {
    const quillInitial = new Quill('#quillEditorInitial', {
        theme: 'snow',
        placeholder: 'Schreiben Sie hier Ihre Nachricht...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
     document.getElementById('createTopicForm').onsubmit = function() {
        const postContentHiddenInitial = document.getElementById('postContentHiddenInitial');
        postContentHiddenInitial.value = quillInitial.root.innerHTML;
        if (quillInitial.getText().trim().length === 0) {
            alert('Bitte geben Sie eine Nachricht ein.');
            return false;
        }
    };
    const quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Schreiben Sie hier Ihre Nachricht...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
     document.getElementById('createPostForm').onsubmit = function() {
        const postContentHidden = document.getElementById('postContentHidden');
        postContentHidden.value = quill.root.innerHTML;

            if (quill.getText().trim().length === 0) {
                alert('Bitte geben Sie eine Nachricht ein.');
                return false;
            }
        };
    }

    // Edit Post Quill
    const quillEdit = new Quill('#quillEditorEdit', {
        theme: 'snow',
        placeholder: 'Schreiben Sie hier Ihre Nachricht...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Handle Edit Button Click
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const contentDiv = document.getElementById('post-content-' + postId);
            if (contentDiv) {
                quillEdit.root.innerHTML = contentDiv.innerHTML;
                document.getElementById('editPostId').value = postId;
                const editModalElem = document.getElementById('editPostModal');
                const editModal = new bootstrap.Modal(editModalElem);
                editModal.show();
            }
        });
    });

    // Handle Edit Form Submit
    const editPostForm = document.getElementById('editPostForm');
    if (editPostForm) {
        editPostForm.onsubmit = function() {
            const postContentHidden = document.getElementById('editPostContentHidden');
            postContentHidden.value = quillEdit.root.innerHTML;

            if (quillEdit.getText().trim().length === 0) {
                alert('Bitte geben Sie eine Nachricht ein.');
                return false;
            }
        };
    }

    // Handle Delete Button Click
    document.querySelectorAll('.delete-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Sind Sie sicher, dass Sie diesen Beitrag löschen möchten?')) {
                const postId = this.getAttribute('data-post-id');
                document.getElementById('deletePostId').value = postId;
                document.getElementById('deletePostForm').submit();
            }
        });
    });

    document.querySelectorAll('.vote-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // prevent page reload

            const button = this.querySelector('.vote-btn');
            const icon = button.querySelector('.thumb-icon');
            const countSpan = button.querySelector('.vote-count');

            const formData = new FormData(this);
            const postId = formData.get('postId');
            const action = formData.get('action'); // voteUp or voteDown

            // Immediate visual feedback
            if (icon) {
                icon.style.color = (action === 'voteUp') ? 'green' : 'red';
            }

                  // Send AJAX request
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        // Update counts
                        if(action === 'voteUp') countSpan.textContent = data.newCountUp;
                        if(action === 'voteDown') countSpan.textContent = data.newCountDown;
                    } else {
                        alert('Fehler beim Abstimmen!');
                        // Reset color if failed
                        icon.style.color = '';
                    }
                })
                .catch(err => {
                    console.error(err);
                    icon.style.color = '';
                });
        });
    });

    // =============================
    //  DRAG & DROP UPLOAD
    // =============================

    // 🚫 STOP browser opening files
    ['dragenter','dragover','dragleave','drop'].forEach(eventName => {
        document.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    const dropArea = document.getElementById("drop-area");
    const fileInput = document.getElementById("fileElem");
    const fileList = document.getElementById("fileList");

    if (!dropArea || !fileInput) return;

    let filesArray = [];

    // Click = open file picker
    dropArea.addEventListener("click", () => fileInput.click());

    // Highlight
    dropArea.addEventListener("dragover", () => {
        dropArea.classList.add("dragover");
    });

    dropArea.addEventListener("dragleave", () => {
        dropArea.classList.remove("dragover");
    });

    // DROP
    dropArea.addEventListener("drop", (e) => {
        dropArea.classList.remove("dragover");
        addFiles(e.dataTransfer.files);
    });

    // SELECT
    fileInput.addEventListener("change", () => {
        addFiles(fileInput.files);
    });

    function addFiles(files) {
        for (let file of files) {

            // 5MB limit
            if (file.size > 5 * 1024 * 1024) {
                alert(file.name + " ist zu groß");
                continue;
            }

            filesArray.push(file);
        }
        renderFiles();
    }

    function renderFiles() {
        fileList.innerHTML = "";

        const dt = new DataTransfer();

        filesArray.forEach((file, i) => {
            dt.items.add(file);

            const li = document.createElement("li");
            li.innerHTML = `
                ${file.name}
                <button type="button" onclick="removeFile(${i})">❌</button>
            `;
            fileList.appendChild(li);
        });

        fileInput.files = dt.files;
    }

    window.removeFile = function(i) {
        filesArray.splice(i, 1);
        renderFiles();
    };

});