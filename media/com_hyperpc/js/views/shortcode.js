/**
 * @copyright  HYPERPC
 * @author     Artem Vyshnevskiy
 */

jQuery(function ($) {
    $('#positions-submit').on('click', function (e) {
        const
            fieldKeys = {
                common: [
                    'type',
                    'order',
                    'price-range',
                    'initial-amount',
                    'limit'
                ],
                product: [
                    'platform[]',
                    'instock',
                    'position-product-ids[]',
                    'config',
                    'game',
                    'show-fps',
                    'product-layout',
                    'load-unavailable'
                ],
                part: [
                    'product-folder-ids[]',
                    'position-part-ids[]',
                    'field',
                    'field-value'
                ],
                service: [
                    'product-folder-ids[]',
                    'position-service-ids[]'
                ]
            },
            editor = Joomla.getOptions('xtd-shortcode').editor,
            $button = $(this),
            $form = $button.closest('form'),
            data = $form.serializeArray(),
            type = data.find((item) => item.name === 'type')?.value || null;

        if (['product', 'part', 'service'].includes(type)) {
            const typeFields = fieldKeys.common.concat(fieldKeys[type]),
                    attrs = {},
                    filteredData = data.filter((item) => {
                        return typeFields.includes(item.name) && item.value.trim() !== '';
                    });

            filteredData.forEach((item) => {
                if (item.name.match(/position-.+-ids\[\]$/)) {
                    attrs.ids = attrs.ids || [];
                    attrs.ids.push(item.value);
                } else if (item.name.match(/(.+)\[\]$/)) {
                    const prop = item.name.match(/(.+)\[\]$/)[1];
                    attrs[prop] = attrs[prop] || [];
                    attrs[prop].push(item.value);
                } else {
                    if (item.name === 'product-layout') {
                        if (item.value !== 'default') {
                            attrs.layout = item.value;
                        }
                        return;
                    }

                    if (item.name === 'order') {
                        if (item.value !== 'a.price ASC') {
                            attrs.order = item.value;
                        }
                        return;
                    }

                    attrs[item.name] = item.value;
                }
            });

            const stringValues = [];
            for (const [key, value] of Object.entries(attrs)) {
                stringValues.push(key + '=' + (Array.isArray(value) ? value.join(',') : value));
            }

            window.parent.jInsertEditorText(
                '{positions ' + stringValues.join('; ') + '}',
                editor
            );
        } else {
            alert('Shortcode error. Unexpected type.');
        }

        window.parent.Joomla.Modal.getCurrent().close();
    });
});
