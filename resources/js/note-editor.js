import { initTipTap } from './tiptap/editor';

document.addEventListener('DOMContentLoaded', function() {
  // Check if we're on a note create or edit page
  const editor = document.getElementById('editor');
  if (editor) {
    initTipTap('#editor', '#content', 'form');
  }
});
