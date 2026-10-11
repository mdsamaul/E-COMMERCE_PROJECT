import axios from 'axios';
import * as bootstrap from 'bootstrap';

window.axios = axios;
window.bootstrap = bootstrap;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head ? document.head.querySelector('meta[name="csrf-token"]') : null;
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

