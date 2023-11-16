(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.taxonomy-term-select').forEach(function (el) {
            const settings = {
                valueField: 'o:id',
                labelField: 'o:title',
                searchField: [],
                load: function(query, callback) {
                    this.clearOptions();
                    const url = new URL(el.getAttribute('data-search-url'));
                    if (el.hasAttribute('data-taxonomy-id')) {
                        url.searchParams.set('taxonomy_id', el.getAttribute('data-taxonomy-id'));
                    }
                    url.searchParams.set('q', query);
                    fetch(url)
                        .then(response => response.json())
                        .then(items => {
                            callback(items);
                        }).catch(()=>{
                            callback();
                        });
                },
            };
            new TomSelect(el, settings);
        });
    });
})();
