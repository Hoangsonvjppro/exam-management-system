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
            content: window.INITIAL_QUESTION_CONTENT || '',
            onUpdate: ({ editor }) => {
                const contentInput = document.getElementById('question_content_input');
                if (contentInput) {
                    contentInput.value = editor.getHTML();
                }
            },
        });
        
        // Set initial hidden input value if editing
        const contentInput = document.getElementById('question_content_input');
        if (contentInput && window.INITIAL_QUESTION_CONTENT) {
            contentInput.value = window.INITIAL_QUESTION_CONTENT;
        }

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

    const questionTypeSelect = document.getElementById('question_type_id');
    const form = document.getElementById('add-question-form');

    function updateLabels() {
        const rows = container.querySelectorAll('.option-row');
        rows.forEach((row, index) => {
            const label = labels[index];
            row.querySelector('.option-label').textContent = label;
            row.querySelector('.correct-input').value = index;
            row.querySelector('.option-hidden-input').name = `options[${index}][content]`;
        });
    }

    function updateInputTypes() {
        if (!questionTypeSelect) return;
        
        // Lấy data-code của option đang được chọn một cách an toàn hơn
        const selectedOption = questionTypeSelect.options[questionTypeSelect.selectedIndex];
        let typeCode = 'single_choice';
        if (selectedOption) {
            typeCode = selectedOption.getAttribute('data-code') || 'single_choice';
        } else if (questionTypeSelect.value) {
            // Fallback: Tìm option có value khớp
            const activeOpt = Array.from(questionTypeSelect.options).find(opt => opt.value == questionTypeSelect.value);
            if (activeOpt) typeCode = activeOpt.getAttribute('data-code');
        }

        const isMultiple = (typeCode === 'multiple_choice');
        const rows = container.querySelectorAll('.option-row');
        
        rows.forEach(row => {
            const input = row.querySelector('.correct-input');
            if (input) {
                const targetType = isMultiple ? 'checkbox' : 'radio';
                const targetName = isMultiple ? 'option_selector[]' : 'option_selector';
                
                // Chỉ thay thế nếu type hoặc name bị sai
                if (input.type !== targetType || input.name !== targetName) {
                    const wasChecked = input.checked;
                    const newInput = document.createElement('input');
                    
                    // Copy toàn bộ class và attributes quan trọng
                    newInput.className = input.className;
                    newInput.value = input.value;
                    newInput.type = targetType;
                    newInput.name = targetName;
                    newInput.checked = wasChecked;
                    
                    // Thay thế trong DOM
                    input.parentNode.replaceChild(newInput, input);
                    
                    // Trigger lại event để các hệ thống khác (nếu có) nhận biết
                    newInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    }

    if (questionTypeSelect) {
        questionTypeSelect.addEventListener('change', updateInputTypes);
    }

    function addOption(initialContent = '', isCorrect = false) {
        if (!container || !template) return;
        
        const currentCount = container.querySelectorAll('.option-row').length;
        if (currentCount >= labels.length) return;

        const label = labels[currentCount];
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.option-row');
        
        row.querySelector('.option-label').textContent = label;
        const correctInput = row.querySelector('.correct-input');
        correctInput.value = currentCount;
        correctInput.checked = isCorrect;
        
        row.querySelector('.option-hidden-input').name = `options[${currentCount}][content]`;

        const editorTarget = row.querySelector('.option-editor-target');
        const hiddenInput = row.querySelector('.option-hidden-input');
        hiddenInput.value = initialContent;
        
        // Khởi tạo Editor riêng cho phương án này
        const optionEditor = new Editor({
            element: editorTarget,
            extensions: [
                StarterKit,
                Underline,
                Placeholder.configure({ placeholder: `Nội dung phương án ${label}...` }),
            ],
            content: initialContent,
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
            updateInputTypes();
        });

        container.appendChild(row);
        updateInputTypes();
    }

    if (addBtn) {
        addBtn.addEventListener('click', (e) => {
            e.preventDefault();
            addOption();
        });
        
        if (window.INITIAL_OPTIONS && window.INITIAL_OPTIONS.length > 0) {
            window.INITIAL_OPTIONS.forEach(opt => {
                addOption(opt.content, opt.is_correct);
            });
        } else {
            // Thêm sẵn 4 phương án mặc định A, B, C, D
            for(let i=0; i<4; i++) addOption();
        }
        
        updateInputTypes(); // init input types
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            // Build explicit correct_options[] payload
            // Remove existing hidden correct_options inputs
            form.querySelectorAll('.hidden-correct-option').forEach(el => el.remove());
            
            const checkedInputs = container.querySelectorAll('.correct-input:checked');
            if (checkedInputs.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất 1 đáp án đúng!');
                return;
            }

            checkedInputs.forEach(input => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'correct_options[]';
                hidden.value = input.value;
                hidden.className = 'hidden-correct-option';
                form.appendChild(hidden);
            });
        });
    }
    
    // 3. Logic lấy Chapter qua API
    let idSuject = document.getElementById('add_subject');
    
    function loadChapters(subjectId, initialChapterId = null) {
        let chapterSelect = document.getElementById('add_chapter');
        if (!chapterSelect || !subjectId) return;
        
        chapterSelect.innerHTML = '<option>⌛ Đang lấy dữ liệu...</option>';
        fetch(`/lecturer/api/questions/add/${subjectId}`)
        .then(response => response.json())
        .then(data => {
            chapterSelect.innerHTML = '<option value="">Chọn chương tương ứng...</option>';
            data.forEach(chapter => {
                let option = document.createElement('option');
                option.value = chapter.id;
                option.textContent = chapter.name;
                if (initialChapterId && parseInt(initialChapterId) === parseInt(chapter.id)) {
                    option.selected = true;
                }
                chapterSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching chapters:', error);
            chapterSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        });
    }

    if (idSuject) {
        idSuject.addEventListener('change', function() {
            loadChapters(this.value);
        });
        
        // Initial load for edit page
        if (window.INITIAL_SUBJECT_ID) {
            const addChapterEl = document.getElementById('add_chapter');
            const initialChapterId = addChapterEl ? addChapterEl.getAttribute('data-initial') : null;
            loadChapters(window.INITIAL_SUBJECT_ID, initialChapterId);
        }
    }
});