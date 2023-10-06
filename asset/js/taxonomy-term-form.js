(function ($) {
    $(document).ready(function () {
        var selectingForm = null;
        var sidebar = $('<div class="sidebar" id="taxonomy-term-sidebar"><div class="sidebar-content"></div></div>');
        sidebar.appendTo('#content');

        function searchResources() {
            var sidebarResourceSearch = sidebar.find('.resource-search');
            Omeka.populateSidebarContent(
                sidebarResourceSearch.closest('.sidebar'),
                sidebarResourceSearch.data('search-url'),
                sidebarResourceSearch.find(':input').serialize()
            );
        }

        sidebar.on('click', '.resource-search .o-icon-search', function () {
            searchResources();
        });

        sidebar.on('keydown', '.resource-search input[name="search"]', function(e) {
            if ((e.keycode || e.which) == '13') {
                e.preventDefault();
                searchResources();
            }
        });

        $('#content').on('click', '.taxonomy-term-form-select', function () {
            Omeka.openSidebar(sidebar);
            Omeka.populateSidebarContent(sidebar, $(this).data('sidebar-content-url'));
            selectingForm = $(this).closest('.taxonomy-term-form-element');
        });

        $('#content').on('click', '.taxonomy-term-form-clear', function () {
            $(this).closest('.taxonomy-term-form-element')
                .addClass('empty')
                .find('input[type=hidden]').val('').end()
                .find('.selected-taxonomy-term').hide();
        });

        $('#content').on('click', '.taxonomy-terms.resource-list .select-resource', function (e) {
            e.preventDefault();
            selectingForm.find('input[type=hidden]').val($(this).data('taxonomyTermId'));

            selectingForm.find('.selected-taxonomy-term').html(this.outerHTML);

            selectingForm.find('.selected-taxonomy-term').show();
            selectingForm.removeClass('empty');
            Omeka.closeSidebar(sidebar);
            selectingForm = null;
        });
    });
})(jQuery);
