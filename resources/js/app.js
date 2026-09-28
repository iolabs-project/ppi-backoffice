import './bootstrap';

import * as NumberUtils from './utils/number';
import * as FormValidation from './utils/form-validation';
import * as ExportUtils from './utils/export';

window.NumberUtils = NumberUtils;
window.FormValidation = FormValidation;
window.ExportUtils = ExportUtils;

// Field-level feedback for every form: drop stale marks when something is submitted,
// and mark the fields named in a 422 response
window.axios.interceptors.request.use((config) => {
    if (['post', 'put', 'patch', 'delete'].includes((config.method || '').toLowerCase())) {
        FormValidation.clearErrors();
    }
    return config;
});

window.axios.interceptors.response.use((response) => response, (error) => {
    if (error.response?.status === 422 && error.response.data?.errors) {
        FormValidation.showServerErrors(error.response.data.errors);
    }
    return Promise.reject(error);
});

// Tab bars scroll sideways on phones; keep the active tab in view (no-op when every tab fits)
const revealActiveTab = (bar) => {
    const active = bar.querySelector('.utab-active');
    if (!active || bar.scrollWidth <= bar.clientWidth) {
        return;
    }
    const offset = active.getBoundingClientRect().left - bar.getBoundingClientRect().left + bar.scrollLeft;
    bar.scrollLeft = offset - (bar.clientWidth - active.offsetWidth) / 2;
};
document.addEventListener('alpine:initialized', () => document.querySelectorAll('.utab').forEach(revealActiveTab));
document.addEventListener('click', (event) => {
    const bar = event.target.closest('.utab');
    if (bar) {
        requestAnimationFrame(() => revealActiveTab(bar));
    }
});
