document.addEventListener("DOMContentLoaded", function() {
    
    // --- 1. Initialer Editor (nur wenn vorhanden) ---
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

    // --- 2. Standard Post Editor (nur wenn vorhanden) ---
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

    // --- 3. Edit post editor ---
    const elEdit = document.getElementById('quillEditorEdit');
    let quillEdit; // Globaler im Scope definieren
    if (elEdit) {
        quillEdit = new Quill('#quillEditorEdit', {
            theme: 'snow',
            modules: { toolbar: [[{ 'header': [1, 2, 3, false] }], ['bold', 'italic', 'underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] }
        });
    }

    // Bearbeiten-Button Click Handler
    document.querySelectorAll('.edit-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const contentDiv = document.getElementById('post-content-' + postId);
            const editModalElem = document.getElementById('editPostModal');
            
            if (contentDiv && quillEdit && editModalElem) {
                // Inhalt in den Editor laden
                quillEdit.root.innerHTML = contentDiv.innerHTML;
                // ID ins versteckte Feld
                document.getElementById('editPostId').value = postId;
                
                // Modal öffnen (Bootstrap 5)
                const modal = new bootstrap.Modal(editModalElem);
                modal.show();
            }
        });
    });

    // Formular-Submit für Edit
    const editPostForm = document.getElementById('editPostForm');
    if (editPostForm && quillEdit) {
        editPostForm.onsubmit = function() {
            document.getElementById('editPostContentHidden').value = quillEdit.root.innerHTML;
            if (quillEdit.getText().trim().length === 0) { alert('Inhalt darf nicht leer sein.'); return false; }
        };
    }

    // --- 4. Löschen & Votes ---
    document.querySelectorAll('.delete-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Sind Sie sicher?')) {
                document.getElementById('deletePostId').value = this.getAttribute('data-post-id');
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
            icon.style.color = (action === 'voteUp') ? 'green' : 'red';

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
    // 1. Editor für Kommentar-Bearbeitung initialisieren
    const elCommentEdit = document.getElementById('quillEditorCommentEdit');
    let quillCommentEdit;
    if (elCommentEdit) {
        quillCommentEdit = new Quill('#quillEditorCommentEdit', {
            theme: 'snow',
            modules: { toolbar: [['bold', 'italic', 'underline'], ['link', 'clean']] }
        });
    }

    // 2. Edit Button Handler
    document.querySelectorAll('.edit-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const contentDiv = document.getElementById('comment-content-' + commentId);

            if (contentDiv && quillCommentEdit) {
                quillCommentEdit.root.innerHTML = contentDiv.innerHTML;
                document.getElementById('editCommentId').value = commentId;
                new bootstrap.Modal(document.getElementById('editCommentModal')).show();
            }
        });
    });

    // 3. Submit Handler
    const editCommentForm = document.getElementById('editCommentForm');
    if (editCommentForm && quillCommentEdit) {
        editCommentForm.onsubmit = function() {
            document.getElementById('editCommentContentHidden').value = quillCommentEdit.root.innerHTML;
        };
    }

    // 4. Delete Button Handler
    document.querySelectorAll('.delete-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Kommentar wirklich löschen?')) {
                document.getElementById('deleteCommentId').value = this.getAttribute('data-comment-id');
                document.getElementById('deleteCommentForm').submit();
            }
        });
    });
});