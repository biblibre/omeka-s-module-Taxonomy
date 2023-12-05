'use strict';

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.taxonomy-linked-values-grid').forEach(el => {
        fetch(el.getAttribute('data-config-url'))
            .then(res => res.json())
            .then(config => {
                if (config.server) {
                    config.server.then = data => data.values.map(v => v.map(c => gridjs.html(c)));
                    config.server.total = data => data.total;
                }
                if (config.pagination && config.server) {
                    config.pagination.server = {
                        url: (prev, page, limit) => `${prev}?per_page=${limit}&page=${page + 1}`,
                    };
                }
                new gridjs.Grid(config).render(el);
            })
    })
});
