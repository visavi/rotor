import 'js/jquery.js';
import 'jquery-mask-plugin';
import 'bootstrap';

import bootbox from 'bootbox';
window.bootbox = bootbox;

import toastr from 'toastr';
window.toastr = toastr;

import { Fancybox } from '@fancyapps/ui';
window.fancybox = Fancybox;

import Tags from 'bootstrap5-tags';
window.tags = Tags;



import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
//import TextStyle from '@tiptap/extension-text-style'

// Инициализация редактора
const editor = new Editor({
    element: document.querySelector('.element'),
    extensions: [StarterKit/*, TextStyle*/],
    content: '<p>Начните писать...</p>',

    // Обновляем меню при любом изменении выделения или содержимого
    onSelectionUpdate: updateMenuBar,
    onUpdate: ({ editor }) => {
        document.getElementById('contentInput').value = editor.getHTML()
        updateMenuBar()
    }
})

// Функция обновления состояния меню
function updateMenuBar() {
    const $buttons = $('#menuBar button')

    $buttons.each(function () {
        const $btn = $(this)
        const action = $btn.data('action')
        const level = $btn.data('level')

        let isActive = false
        let canDo = true

        // Проверяем активность и возможность выполнения
        switch (action) {
            case 'bold':
                isActive = editor.isActive('bold')
                canDo = editor.can().chain().toggleBold().run()
                break
            case 'italic':
                isActive = editor.isActive('italic')
                canDo = editor.can().chain().toggleItalic().run()
                break
            case 'strike':
                isActive = editor.isActive('strike')
                canDo = editor.can().chain().toggleStrike().run()
                break
            case 'code':
                isActive = editor.isActive('code')
                canDo = editor.can().chain().toggleCode().run()
                break
            case 'paragraph':
                isActive = editor.isActive('paragraph')
                break
            case 'heading':
                isActive = editor.isActive('heading', { level: level })
                break
            case 'bulletList':
                isActive = editor.isActive('bulletList')
                break
            case 'orderedList':
                isActive = editor.isActive('orderedList')
                break
            case 'codeBlock':
                isActive = editor.isActive('codeBlock')
                break
            case 'blockquote':
                isActive = editor.isActive('blockquote')
                break
            case 'undo':
                canDo = editor.can().undo()
                break
            case 'redo':
                canDo = editor.can().redo()
                break
            // clearMarks, clearNodes, horizontalRule, hardBreak — не имеют состояния активности
            default:
                canDo = true
        }

        // Обновляем класс и disabled
        $btn.toggleClass('is-active', isActive)
        $btn.prop('disabled', !canDo)
    })
}

// Инициализация меню
updateMenuBar()

// Обработчик кликов по кнопкам
$('#menuBar').on('click', 'button', function () {
    const action = $(this).data('action')
    const level = $(this).data('level')

    editor.chain().focus()

    switch (action) {
        case 'bold':
            editor.chain().toggleBold().run()
            break
        case 'italic':
            editor.chain().toggleItalic().run()
            break
        case 'strike':
            editor.chain().toggleStrike().run()
            break
        case 'code':
            editor.chain().toggleCode().run()
            break
        case 'clearMarks':
            editor.chain().unsetAllMarks().run()
            break
        case 'clearNodes':
            editor.chain().clearNodes().run()
            break
        case 'paragraph':
            editor.chain().setParagraph().run()
            break
        case 'heading':
            editor.chain().toggleHeading({ level: level }).run()
            break
        case 'bulletList':
            editor.chain().toggleBulletList().run()
            break
        case 'orderedList':
            editor.chain().toggleOrderedList().run()
            break
        case 'codeBlock':
            editor.chain().toggleCodeBlock().run()
            break
        case 'blockquote':
            editor.chain().toggleBlockquote().run()
            break
        case 'horizontalRule':
            editor.chain().setHorizontalRule().run()
            break
        case 'hardBreak':
            editor.chain().setHardBreak().run()
            break
        case 'undo':
            editor.chain().undo().run()
            break
        case 'redo':
            editor.chain().redo().run()
            break
    }
})


// Локальные скрипты
import 'js/translate.js';
import 'js/jquery.caret.min.js';
import 'js/markitup-set.js';
import 'js/markitup.js';
import 'js/prettify.js';
import 'js/main.js';
import './sidebar.js';
