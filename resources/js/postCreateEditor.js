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
});

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
