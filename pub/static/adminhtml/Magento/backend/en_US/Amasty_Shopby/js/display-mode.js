define([
    'underscore',
    'jquery'
], function (_, $) {
    return function (config) {

        function isEditable() {
            return config.inputTypeMap && $('#frontend_input').prop( "disabled") == false;
        }

        function onDisplayModeChanged(){
            $('#display_mode-note').html("");
            if (isEditable() &&
                _.has(config.inputTypeMap, this.value) &&
                $('#display_mode').attr('skip-trigger') != 1
            ){
                $('#frontend_input').val(config.inputTypeMap[this.value]);
                $('#frontend_input').attr('skip-trigger', 1);
                $('#frontend_input').trigger('change');
                $('#frontend_input').attr('skip-trigger', 0);

                if (_.has(config.notices, this.value)){
                    $('#display_mode-note').html(config.notices[this.value]);
                }
            }

            showIsMultiselect();
            showProductQuantities();
            showNumberUnfoldedOptions();
        }

        function onFrontendInputChanged(){
            if (isEditable() &&
                $('#frontend_input').attr('skip-trigger') != 1
            ){

                var displayMode = _.findKey(config.inputTypeMap, function(value, index){
                    return this.value === value;
                }.bind(this));

                if (displayMode !== -1){
                    $('#display_mode').val(displayMode);
                    $('#display_mode').attr('skip-trigger', 1);
                    $('#display_mode').trigger('change');
                    $('#display_mode').attr('skip-trigger', 0);
                }
            }
        }

        function hide(selector){
            $(selector).hide();
            $(selector).find('select').each(function(){
                $(this).hide();
            });
            $(selector).find('input').each(function(){
                $(this).hide();
            });
        }

        function show(selector){
            $(selector).show();
            $(selector).find('select').each(function(){
                $(this).show();
            });
            $(selector).find('input').each(function(){
                $(this).show();
            });
        }

        function showIsMultiselect()
        {
            if (_.indexOf(config.isMultiselectConfig, $('#display_mode').val()) !== -1)  {
                show('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-is-use-and-logic]');
                show('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-is-multiselect]');
            } else {
                hide('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-is-use-and-logic]');
                hide('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-is-multiselect]');
            }
        }

        function showProductQuantities()
        {
            if (_.indexOf(config.showProductQuantitiesConfig, $('#display_mode').val()) !== -1)  {
                show('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-show-product-quantities]');
            } else {
                hide('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-show-product-quantities]');
            }
        }

        function showNumberUnfoldedOptions()
        {
            if (_.indexOf(config.numberUnfoldedOptionsConfig, $('#display_mode').val()) !== -1)  {
                show('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-number-unfolded-options]');
            } else {
                hide('[data-ui-id=attribute-edit-content-form-fieldset-element-form-field-number-unfolded-options]');
            }
        }

        $('#display_mode').on('change', onDisplayModeChanged);
        $('#frontend_input').on('change', onFrontendInputChanged);

        onDisplayModeChanged();
    }
});