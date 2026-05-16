'use strict';

// Global app namespace
const App = {
    baseUrl: '/DELOX',

    api(endpoint, options = {}) {
        return fetch(this.baseUrl + endpoint, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            ...options,
        }).then(res => res.json());
    },
};
