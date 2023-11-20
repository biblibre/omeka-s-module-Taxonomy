(function () {
    'use strict';

    $(document).ready(function (event) {
        $('#content').on('click', '.taxonomy-tree-view-toggle', function (event) {
            $(this).closest('.sidebar').toggleClass('taxonomy-tree-view');
        });
    });

    $(document).on('o:sidebar-content-loaded', function (event) {
        initTaxonomyTermTree(event.target);
    });

    $(document).ready(function (event) {
        initTaxonomyTermTree(event.target);
    });

    function initTaxonomyTermTree (context) {
        $('.taxonomy-term-tree', context).each(function () {
            const childrenUrl = this.getAttribute('data-children-url');
            $(this).jstree({
                core: {
                    data: {
                        url: childrenUrl,
                        data: function (node) {
                            const params = {};
                            if (node.id !== '#') {
                                params.parent_id = node.id;
                            }

                            return params;
                        }
                    },
                },
            });
        });

        // Make clicks on taxonomy term title and edit icon work as expected
        $('.taxonomy-term-tree', context).on('changed.jstree', function (event, data) {
            const target = data.event.target;
            const link = target.closest('a[href]');
            if (link) {
                const href = link.getAttribute('href');
                if (href && href !== '#') {
                    location.href = href
                }
            }
        });
    }
})();
