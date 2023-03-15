(function() {
    $(document).on('o:prepare-value', function(e, dataType, value, valueObj) {
        // Prepare the markup for the resource data types.
        if (valueObj && dataType.startsWith('resource:taxonomy')) {
            value.find('span.default').hide();
            var resource = value.find('.selected-resource');
            if (typeof valueObj['display_title'] === 'undefined') {
                valueObj['display_title'] = Omeka.jsTranslate('[Untitled]');
            }
            resource.find('.o-title')
                .removeClass() // remove all classes
                .addClass('o-title ' + valueObj['value_resource_name'])
                .html($('<a>', {href: valueObj['url'], text: valueObj['display_title']}));
            if (typeof valueObj['thumbnail_url'] !== 'undefined') {
                resource.find('.o-title')
                    .prepend($('<img>', {src: valueObj['thumbnail_url']}));
            }
        }
    });

    $(document).on('o:prepare-value-annotation', function(e, dataTypeName, valueAnnotation, value) {
        // Set the display title for resource types.
        if (dataTypeName.startsWith('resource:taxonomy')) {
            let thumbnail = '';
            if (value.thumbnail_url) {
                thumbnail = $('<img>', {src: value.thumbnail_url});
            }
            const resourceLink = $('<a>', {
                text: value.display_title,
                href: value.url,
                target: '_blank',
            });
            if (value.value_resource_id !== undefined) {
                valueAnnotation.find('.default').hide();
            }
            valueAnnotation.find('.o-title').append(thumbnail, resourceLink);
            valueAnnotation.find('.display_title').val(value.display_title);
            valueAnnotation.find('.url').val(value.url);
        }
    });
})();
