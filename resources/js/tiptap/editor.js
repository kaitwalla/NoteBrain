import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'

export function createEditor(element, content = '', onUpdate = null) {
  return new Editor({
    element,
    extensions: [
      StarterKit,
      Link.configure({
        openOnClick: false,
      }),
      Image,
    ],
    content,
    onUpdate: ({ editor }) => {
      if (onUpdate) {
        onUpdate(editor);
      }
    },
  });
}

export function createToolbar(editor, container) {
  const toolbar = document.createElement('div');
  toolbar.className = 'flex flex-wrap gap-2 p-2 bg-gray-100 border-b border-gray-300 rounded-t-md';

  // Bold button
  const boldButton = document.createElement('button');
  boldButton.type = 'button';
  boldButton.className = 'p-1 rounded hover:bg-gray-200';
  boldButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>';
  boldButton.addEventListener('click', () => {
    editor.chain().focus().toggleBold().run();
  });
  toolbar.appendChild(boldButton);

  // Italic button
  const italicButton = document.createElement('button');
  italicButton.type = 'button';
  italicButton.className = 'p-1 rounded hover:bg-gray-200';
  italicButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="19" y1="4" x2="10" y2="4"></line><line x1="14" y1="20" x2="5" y2="20"></line><line x1="15" y1="4" x2="9" y2="20"></line></svg>';
  italicButton.addEventListener('click', () => {
    editor.chain().focus().toggleItalic().run();
  });
  toolbar.appendChild(italicButton);

  // Heading buttons
  for (let level = 1; level <= 3; level++) {
    const headingButton = document.createElement('button');
    headingButton.type = 'button';
    headingButton.className = 'p-1 rounded hover:bg-gray-200';
    headingButton.textContent = 'H' + level;
    headingButton.addEventListener('click', () => {
      editor.chain().focus().toggleHeading({ level }).run();
    });
    toolbar.appendChild(headingButton);
  }

  // Bullet list button
  const bulletListButton = document.createElement('button');
  bulletListButton.type = 'button';
  bulletListButton.className = 'p-1 rounded hover:bg-gray-200';
  bulletListButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>';
  bulletListButton.addEventListener('click', () => {
    editor.chain().focus().toggleBulletList().run();
  });
  toolbar.appendChild(bulletListButton);

  // Ordered list button
  const orderedListButton = document.createElement('button');
  orderedListButton.type = 'button';
  orderedListButton.className = 'p-1 rounded hover:bg-gray-200';
  orderedListButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path></svg>';
  orderedListButton.addEventListener('click', () => {
    editor.chain().focus().toggleOrderedList().run();
  });
  toolbar.appendChild(orderedListButton);

  // Link button
  const linkButton = document.createElement('button');
  linkButton.type = 'button';
  linkButton.className = 'p-1 rounded hover:bg-gray-200';
  linkButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
  linkButton.addEventListener('click', () => {
    const url = window.prompt('URL');
    if (url) {
      editor.chain().focus().setLink({ href: url }).run();
    } else {
      editor.chain().focus().unsetLink().run();
    }
  });
  toolbar.appendChild(linkButton);

  // Insert the toolbar before the editor
  container.insertBefore(toolbar, element);

  // Adjust the editor's border radius to match with the toolbar
  element.style.borderTopLeftRadius = '0';
  element.style.borderTopRightRadius = '0';

  return toolbar;
}

export function initTipTap(editorSelector, contentSelector, formSelector) {
  const editorElement = document.querySelector(editorSelector);
  const contentElement = document.querySelector(contentSelector);

  if (!editorElement || !contentElement) {
    console.error('Editor or content element not found');
    return;
  }

  const editor = createEditor(
    editorElement,
    contentElement.value || '',
    (editor) => {
      // Update the hidden textarea with the editor's HTML content
      contentElement.value = editor.getHTML();
    }
  );

  // Create toolbar
  createToolbar(editor, editorElement.parentNode);

  // Update the hidden textarea before form submission
  if (formSelector) {
    const form = document.querySelector(formSelector);
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        contentElement.value = editor.getHTML();

        // Add CSRF token to form if not already present
        if (!form.querySelector('input[name="_token"]')) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const csrfInput = document.createElement('input');
          csrfInput.type = 'hidden';
          csrfInput.name = '_token';
          csrfInput.value = csrfToken;
          form.appendChild(csrfInput);
        }

        // Submit the form programmatically
        form.submit();
      });
    }
  }

  return editor;
}
