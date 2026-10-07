import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

export function initializeEditors() {
    document.querySelectorAll('textarea[data-rich-text]').forEach((textarea) => {
        const shell = document.createElement('div');
        shell.className = 'rich-text';
        const toolbar = document.createElement('div');
        toolbar.className = 'rich-text-toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Format teks');
        const surface = document.createElement('div');
        shell.append(toolbar, surface);
        textarea.before(shell);

        // Preserve line breaks in legacy plain text without interpreting it as HTML.
        let content = textarea.value;
        if (content && ! /<\/?[a-z][\s\S]*>/i.test(content)) {
            const paragraph = document.createElement('p');
            content.split(/\r?\n/).forEach((line, index) => {
                if (index) paragraph.append(document.createElement('br'));
                paragraph.append(document.createTextNode(line));
            });
            content = paragraph.outerHTML;
        }

        const editor = new Editor({
            element: surface,
            extensions: [StarterKit.configure({
                heading: { levels: [2, 3, 4] },
                code: false,
                codeBlock: false,
                link: { openOnClick: false, protocols: ['http', 'https', 'mailto'] },
            }), Image.configure({ allowBase64: false })],
            content,
            editorProps: { attributes: { id: `${textarea.id}-editor`, role: 'textbox', 'aria-multiline': 'true', 'aria-label': textarea.labels?.[0]?.textContent || 'Isi konten' } },
            onUpdate: ({ editor }) => { textarea.value = editor.isEmpty ? '' : editor.getHTML(); },
        });
        const required = textarea.required;
        textarea.hidden = true;
        textarea.required = false;
        textarea.labels?.[0]?.setAttribute('for', `${textarea.id}-editor`);

        const actions = [
            ['Paragraf', () => editor.chain().focus().setParagraph().run()],
            ...[2, 3, 4].map((level) => [`H${level}`, () => editor.chain().focus().toggleHeading({ level }).run()]),
            ['Tebal', () => editor.chain().focus().toggleBold().run(), 'bold'],
            ['Miring', () => editor.chain().focus().toggleItalic().run(), 'italic'],
            ['Garis bawah', () => editor.chain().focus().toggleUnderline().run(), 'underline'],
            ['Coret', () => editor.chain().focus().toggleStrike().run(), 'strike'],
            ['Daftar', () => editor.chain().focus().toggleBulletList().run(), 'bulletList'],
            ['Nomor', () => editor.chain().focus().toggleOrderedList().run(), 'orderedList'],
            ['Kutipan', () => editor.chain().focus().toggleBlockquote().run(), 'blockquote'],
            ['Tautan', () => {
                const url = window.prompt('URL tautan (https://, http://, atau mailto:)', editor.getAttributes('link').href || 'https://');
                if (url === null) return;
                if (! url.trim()) { editor.chain().focus().extendMarkRange('link').unsetLink().run(); return; }
                if (! /^(https?:\/\/|mailto:)/i.test(url.trim())) { window.alert('Gunakan URL http, https, atau mailto.'); return; }
                editor.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run();
            }, 'link'],
            ['Lepas tautan', () => editor.chain().focus().unsetLink().run()],
            ['Garis pemisah', () => editor.chain().focus().setHorizontalRule().run()],
            ['Urungkan', () => editor.chain().focus().undo().run()],
            ['Ulangi', () => editor.chain().focus().redo().run()],
        ];
        actions.forEach(([label, action, mark]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.addEventListener('click', action);
            if (mark) {
                const update = () => button.setAttribute('aria-pressed', String(editor.isActive(mark)));
                editor.on('transaction', update);
                update();
            }
            toolbar.append(button);
        });
        textarea.form?.addEventListener('submit', (event) => {
            textarea.value = editor.isEmpty ? '' : editor.getHTML();
            if (required && editor.isEmpty) {
                event.preventDefault();
                editor.commands.focus();
                window.alert('Isi konten wajib diisi.');
            }
        });
    });
}
