import './bootstrap';

import * as NumberUtils from './utils/number';
import * as FormValidation from './utils/form-validation';

window.NumberUtils = NumberUtils;
window.FormValidation = FormValidation;

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
