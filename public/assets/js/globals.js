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

window.fancybox = Fancybox;
window.fancyCarousel = Carousel;
window.fancyCarouselPlugins = { Arrows, Dots };
window.tags = Tags;
window.notyf = notyf;
