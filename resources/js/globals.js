// Сторонние библиотеки в одном месте: настроенные экземпляры отдаются экспортом,
// в window их выставляет main.js — там же, где остальные публичные функции

import { Fancybox } from '@fancyapps/ui';
import { Carousel } from '@fancyapps/ui/dist/carousel/carousel.js';
import { Arrows } from '@fancyapps/ui/dist/carousel/carousel.arrows.js';
import { Dots } from '@fancyapps/ui/dist/carousel/carousel.dots.js';
import Tags from 'bootstrap5-tags';
import { Notyf } from 'notyf';

const notyfInstance = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    dismissible: true,
    ripple: true,
    types: [
      { type: 'success', background: '#28a745' },
      { type: 'error', background: '#dc3545' },
      { type: 'warning', background: '#ffc107' },
      { type: 'info', background: '#17a2b8' }
    ],
});

/* Notyf вставляет сообщение как HTML, а в него попадает ввод пользователя
 * из ответа сервера (логин в «не найден», имя файла). Экранируем здесь, для
 * всех вызовов сразу; перенос строки становится <br>.
 * warning и info — свои типы: у самого Notyf методов для них нет */
function escapeMessage(text) {
    const div = document.createElement('div')
    div.textContent = String(text ?? '')

    return div.innerHTML.replace(/\n/g, '<br>')
}

function notifyOptions(type, options) {
    const opts = typeof options === 'object' && options !== null ? options : { message: options }

    return { ...opts, type: opts.type ?? type, message: escapeMessage(opts.message) }
}

const notyf = {
    open: options => notyfInstance.open(notifyOptions('info', options)),
    success: options => notyfInstance.open(notifyOptions('success', options)),
    error: options => notyfInstance.open(notifyOptions('error', options)),
    warning: options => notyfInstance.open(notifyOptions('warning', options)),
    info: options => notyfInstance.open(notifyOptions('info', options)),
    dismiss: notification => notyfInstance.dismiss(notification),
    dismissAll: () => notyfInstance.dismissAll(),
}

const fancyCarouselPlugins = { Arrows, Dots };

export {
    notyf,
    Tags as tags,
    Fancybox as fancybox,
    Carousel as fancyCarousel,
    fancyCarouselPlugins,
};
