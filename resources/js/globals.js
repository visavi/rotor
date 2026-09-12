// Сторонние библиотеки в одном месте: настроенные экземпляры отдаются экспортом,
// в window их выставляет main.js — там же, где остальные публичные функции

import { Fancybox } from '@fancyapps/ui';
import { Carousel } from '@fancyapps/ui/dist/carousel/carousel.js';
import { Arrows } from '@fancyapps/ui/dist/carousel/carousel.arrows.js';
import { Dots } from '@fancyapps/ui/dist/carousel/carousel.dots.js';
import Tags from 'bootstrap5-tags';
import { Notyf } from 'notyf';

const notyf = new Notyf({
    duration: 4000,
    position: { x: 'right', y: 'top' },
    dismissible: true,
    ripple: true,
    types: [
      { type: 'success', background: '#28a745' },
      { type: 'error', background: '#dc3545' },
      { type: 'warning', background: '#ffc107' }
    ],
});

const fancyCarouselPlugins = { Arrows, Dots };

export {
    notyf,
    Tags as tags,
    Fancybox as fancybox,
    Carousel as fancyCarousel,
    fancyCarouselPlugins,
};
