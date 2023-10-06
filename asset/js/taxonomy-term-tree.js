(function () {
    'use strict';

    $(document).ready(function() {
        $('.taxonomy-term-tree').each(function () {
            const taxonomyId = this.getAttribute('data-taxonomy-id');
            $(this).jstree({
                core: {
                    data: {
                        url: `/admin/taxonomy/${taxonomyId}/term-children`,
                        data: function (node) {
                            const params = {};
                            if (node.id !== '#') {
                                params.id = node.id;
                            }

                            return params;
                        }
                    },
                },
            });
        });

        // Make clicks on taxonomy term title and edit icon work as expected
        $('.taxonomy-term-tree').on('changed.jstree', function (event, data) {
            const target = data.event.target;
            const href = target.getAttribute('href');
            if (href && href !== '#') {
                location.href = href
            }
        });
    });
})();
