const customFields = [
    {
        label: 'Signature',
        type: 'signature',
        name: 'signature-' + (Math.floor(Math.random() * 1e13).toString().padStart(13, '0')) + '-0',
        icon: '✍️',
        attrs: {
            type: 'hidden'
        }
    }
];

const customFieldsTemplates = {
    signature: function(fieldData) {
        var attrs = fieldData.attrs || {};
        var name = fieldData.name || 'signature';
        var value = fieldData.value || '';
        
        return {
            field: '<input type="hidden" name="' + name + '" value="' + value + '" class="form-control custom-hidden-field">',
            onRender: function() {

            }
        };
    }
};

const fieldsOption = {
    fields: customFields,
    templates: customFieldsTemplates,
    disableFields: [], 
    controlOrder: [
        "radio-group", 
        "file", 
        "checkbox-group", 
        "checkbox", 
        "hidden", 
        "select", 
        "number", 
        "date", 
        "text", 
        "textarea", 
        "button", 
        "autocomplete", 
        "paragraph", 
        "header", 
        "signature"
    ],
    typeUserAttrs: {
        signature: {
            value: {
                label: '',
                type: 'text',
                description: 'Signature'
            }
        }
    },
    i18n: {
        locale: 'en-US',
        extension: {
            'signature': 'Signature'
        }
    }
};