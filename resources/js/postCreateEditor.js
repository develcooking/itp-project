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
