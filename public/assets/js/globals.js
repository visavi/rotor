import bootbox from 'bootbox';
import { Fancybox } from '@fancyapps/ui';
import Tags from 'bootstrap5-tags';
import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

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

window.bootbox = bootbox;
window.fancybox = Fancybox;
window.tags = Tags;
window.notyf = notyf;
