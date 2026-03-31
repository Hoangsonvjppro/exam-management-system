import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Image from '@tiptap/extension-image'
import Placeholder from '@tiptap/extension-placeholder'

document.addEventListener('DOMContentLoaded', () => {
    // 1. Khởi tạo Tiptap cho câu hỏi chính
    const editorElement = document.querySelector('#tiptap-editor');
    if (editorElement) {
        const editor = new Editor({
            element: editorElement,
            extensions: [
                StarterKit,
                Underline,
                Image.configure({ HTMLAttributes: { class: 'max-w-full h-auto rounded-lg mx-auto shadow-sm my-4' } }),
                Placeholder.configure({ placeholder: 'Nhập nội dung câu hỏi chi tiết tại đây...' }),
            ],
            onUpdate: ({ editor }) => {
                const contentInput = document.getElementById('question_content_input');
                if (contentInput) {
                    contentInput.value = editor.getHTML();
                }
            },
        });

        // Toolbar chính
        document.querySelectorAll('#main-editor-toolbar [data-tiptap]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const action = button.getAttribute('data-tiptap');
                if (action === 'bold') editor.chain().focus().toggleBold().run();
                if (action === 'italic') editor.chain().focus().toggleItalic().run();
                if (action === 'underline') editor.chain().focus().toggleUnderline().run();
                if (action === 'image') document.getElementById('editor-image-upload').click();
                if (action === 'latex') editor.chain().focus().insertContent('$formula$').run();
                if (action === 'bulletList') editor.chain().focus().toggleBulletList().run();
                if (action === 'orderedList') editor.chain().focus().toggleOrderedList().run();
                if (action === 'codeBlock') editor.chain().focus().toggleCodeBlock().run();
            });
        });

        // Xử lý upload ảnh (giả lập hoặc thực tế tùy cấu hình)
        const imageUpload = document.getElementById('editor-image-upload');
        if (imageUpload) {
            imageUpload.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (readerEvent) => {
                        editor.chain().focus().setImage({ src: readerEvent.target.result }).run();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // 2. Logic Thêm Phương án
    const container = document.getElementById('answer-options-container');
    const template = document.getElementById('option-template');
    const addBtn = document.querySelector('.add-option-btn');
    const labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    function updateLabels() {
        const rows = container.querySelectorAll('.option-row');
        rows.forEach((row, index) => {
            const label = labels[index];
            row.querySelector('.option-label').textContent = label;
            row.querySelector('.correct-radio').value = index;
        });
    }

    function addOption() {
        if (!container || !template) return;
        
        const currentCount = container.querySelectorAll('.option-row').length;
        if (currentCount >= labels.length) return;

        const label = labels[currentCount];
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.option-row');
        
        row.querySelector('.option-label').textContent = label;
        row.querySelector('.correct-radio').value = currentCount;

        const editorTarget = row.querySelector('.option-editor-target');
        const hiddenInput = row.querySelector('.option-hidden-input');
        
        // Khởi tạo Editor riêng cho phương án này
        const optionEditor = new Editor({
            element: editorTarget,
            extensions: [
                StarterKit,
                Underline,
                Placeholder.configure({ placeholder: `Nội dung phương án ${label}...` }),
            ],
            onUpdate: ({ editor }) => {
                hiddenInput.value = editor.getHTML();
            },
        });

        // Xử lý Toolbar của phương án
        row.querySelectorAll('[data-tiptap]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = btn.getAttribute('data-tiptap');
                if (action === 'bold') optionEditor.chain().focus().toggleBold().run();
                if (action === 'italic') optionEditor.chain().focus().toggleItalic().run();
                if (action === 'underline') optionEditor.chain().focus().toggleUnderline().run();
                if (action === 'latex') optionEditor.chain().focus().insertContent('$formula$').run();
            });
        });

        // Xử lý Xóa
        row.querySelector('.remove-option-btn').addEventListener('click', () => {
            optionEditor.destroy();
            row.remove();
            updateLabels();
        });

        container.appendChild(row);
    }

    if (addBtn) {
        addBtn.addEventListener('click', (e) => {
            e.preventDefault();
            addOption();
        });
        
        // Thêm sẵn 4 phương án mặc định A, B, C, D
        for(let i=0; i<4; i++) addOption();
    }
    
    // 3. Logic lấy Chapter qua API
    let idSuject = document.getElementById('add_subject');
    if (idSuject) {
        idSuject.addEventListener('change', function() {
            let subjectId = this.value;
            let chapterSelect = document.getElementById('add_chapter');
            if (!chapterSelect || !subjectId) return;
            
            chapterSelect.innerHTML = '<option>⌛ Đang lấy dữ liệu...</option>';
            fetch(`/lecturer/api/questions/add/${subjectId}`)
            .then(response => response.json())
            .then(data => {
                chapterSelect.innerHTML = '<option value="">Chọn chương từ danh sách...</option>';
                data.forEach(chapter => {
                    let option = document.createElement('option');
                    option.value = chapter.id;
                    option.textContent = chapter.name;
                    chapterSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching chapters:', error);
                chapterSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
            });
        });
    }
});