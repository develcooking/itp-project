document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Initialer Editor (Topic Creation) ---
    const elInitial = document.getElementById('quillEditorInitial');
    if (elInitial) {
        const quillInitial = new Quill('#quillEditorInitial', {
            theme: 'snow',
            placeholder: 'Schreiben Sie hier Ihre Nachricht...',
            modules: { toolbar: [[{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link'], ['clean']] }
        });
        const createTopicForm = document.getElementById('createTopicForm');
        if (createTopicForm) {
            createTopicForm.onsubmit = function() {
                document.getElementById('postContentHiddenInitial').value = quillInitial.root.innerHTML;
                if (quillInitial.getText().trim().length === 0) { alert('Bitte Nachricht eingeben.'); return false; }
            };
        }
    }

    // --- 2. Standard Post Editor (Post Creation) ---
    const elPost = document.getElementById('quillEditor');
    if (elPost) {
        const quill = new Quill('#quillEditor', {
            theme: 'snow',
            modules: { toolbar: [[{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] }
        });
        const createPostForm = document.getElementById('createPostForm');
        if (createPostForm) {
            createPostForm.onsubmit = function() {
                document.getElementById('postContentHidden').value = quill.root.innerHTML;
                if (quill.getText().trim().length === 0) { alert('Bitte Nachricht eingeben.'); return false; }
            };
        }
    }

    // --- 3. Comment Editor (Comment Creation) ---
    const elComment = document.getElementById('quillEditorComment');
    if (elComment) {
        const quillComment = new Quill('#quillEditorComment', {
            theme: 'snow',
            modules: { toolbar: [['bold', 'italic', 'underline', 'strike'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote'], ['link']] }
        });
        const createCommentForm = document.getElementById('createCommentForm');
        if (createCommentForm) {
            createCommentForm.onsubmit = function() {
                document.getElementById('commentContentHidden').value = quillComment.root.innerHTML;
                if (quillComment.getText().trim().length === 0) { alert('Bitte Kommentar eingeben.'); return false; }
            };
        }
    }

    // --- 4. Edit post editor ---
    const elEdit = document.getElementById('quillEditorEdit');
    let quillEdit;
    if (elEdit) {
        quillEdit = new Quill('#quillEditorEdit', {
            theme: 'snow',
            modules: { toolbar: [[{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] }
        });
    }

    const editPostModalElem = document.getElementById('editPostModal');
    const editPostModal = editPostModalElem ? new bootstrap.Modal(editPostModalElem) : null;

    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const contentDiv = document.getElementById('post-content-' + postId);
            if (contentDiv && quillEdit) {
                quillEdit.root.innerHTML = contentDiv.innerHTML.trim();
                document.getElementById('editPostId').value = postId;
                if (editPostModal) editPostModal.show();
            }
        });
    });

    const editPostForm = document.getElementById('editPostForm');
    if (editPostForm && quillEdit) {
        editPostForm.onsubmit = function() {
            document.getElementById('editPostContentHidden').value = quillEdit.root.innerHTML;
            if (quillEdit.getText().trim().length === 0) { alert('Inhalt darf nicht leer sein.'); return false; }
        };
    }

    // --- 5. Edit comment editor ---
    const elCommentEdit = document.getElementById('quillEditorCommentEdit');
    let quillCommentEdit;
    if (elCommentEdit) {
        quillCommentEdit = new Quill('#quillEditorCommentEdit', {
            theme: 'snow',
            modules: { toolbar: [['bold', 'italic', 'underline', 'strike'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['blockquote'], ['link']] }
        });
    }

    const editCommentModalElem = document.getElementById('editCommentModal');
    const editCommentModal = editCommentModalElem ? new bootstrap.Modal(editCommentModalElem) : null;

    document.querySelectorAll('.edit-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const contentDiv = document.getElementById('comment-content-' + commentId);
            if (contentDiv && quillCommentEdit) {
                quillCommentEdit.root.innerHTML = contentDiv.innerHTML.trim();
                document.getElementById('editCommentId').value = commentId;
                if (editCommentModal) editCommentModal.show();
            }
        });
    });

    const editCommentForm = document.getElementById('editCommentForm');
    if (editCommentForm && quillCommentEdit) {
        editCommentForm.onsubmit = function() {
            document.getElementById('editCommentContentHidden').value = quillCommentEdit.root.innerHTML;
            if (quillCommentEdit.getText().trim().length === 0) { alert('Inhalt darf nicht leer sein.'); return false; }
        };
    }

    // --- 6. Deletion ---
    document.querySelectorAll('.delete-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Beitrag wirklich löschen?')) {
                const postId = this.getAttribute('data-post-id');
                const form = document.getElementById('deletePostForm');
                const input = document.getElementById('deletePostId');
                if (form && input) {
                    input.value = postId;
                    form.submit();
                }
            }
        });
    });

    document.querySelectorAll('.delete-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Kommentar wirklich löschen?')) {
                const commentId = this.getAttribute('data-comment-id');
                const form = document.getElementById('deleteCommentForm');
                const input = document.getElementById('deleteCommentId');
                if (form && input) {
                    input.value = commentId;
                    form.submit();
                }
            }
        });
    });

    // --- 7. Voting (AJAX) ---
    document.querySelectorAll('.vote-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const button = this.querySelector('button');
            const icon = button.querySelector('i');
            const countSpan = button.querySelector('span');

            const formData = new FormData(this);
            
            // Send AJAX request
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                // Since the controller currently just redirects, we might need to 
                // either reload the page or update the controller for JSON response.
                // For now, simple reload or manual update if possible.
                window.location.reload(); 
            })
            .catch(err => console.error(err));
        });
    });
});
