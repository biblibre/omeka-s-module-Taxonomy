'use strict';

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.taxonomy-linked-resources').forEach(el => {
        let page = 1;

        const sortSelect = el.querySelector('.taxonomy-linked-resources__sort-select');
        const searchInput = el.querySelector('.taxonomy-linked-resources__search-input');
        const resourcesContainer = el.querySelector('.taxonomy-linked-resources__resources');
        const paginationContainer = el.querySelector('.taxonomy-linked-resources__pagination');

        if (!resourcesContainer) {
            return;
        }

        function refresh () {
            const url = new URL(el.dataset.url, location.origin);
            url.searchParams.set('page', page);

            if (searchInput) {
                url.searchParams.set('search', searchInput.value);
            }

            if (sortSelect) {
                const [sortBy, sortOrder] = sortSelect.value.split(' ');
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_order', sortOrder);
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const html = data.values.map(v => `<div class="taxonomy-linked-resources__resource">${v}</div>`).join('');
                    resourcesContainer.innerHTML = html;

                    if (data.pages > 1) {
                        const prevButton = document.createElement('button');
                        prevButton.innerHTML = Omeka.jsTranslate('Previous');
                        if (page <= 1) {
                            prevButton.setAttribute('disabled', true);
                        } else {
                            prevButton.addEventListener('click', () => { page--; refresh() });
                        }

                        const nextButton = document.createElement('button');
                        nextButton.innerHTML = Omeka.jsTranslate('Next');
                        if (page >= data.pages) {
                            nextButton.setAttribute('disabled', true);
                        } else {
                            nextButton.addEventListener('click', () => { page++; refresh() });
                        }
                        paginationContainer.replaceChildren(prevButton, ' ', nextButton)
                    } else {
                        paginationContainer.replaceChildren()
                    }
                })
        }

        refresh();

        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', () => {
                console.log('foo');
                clearTimeout(timeoutId);
                timeoutId = setTimeout(refresh, 250);
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', () => { refresh(); });
        }
    });
});
