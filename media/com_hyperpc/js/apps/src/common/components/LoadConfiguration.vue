<template>
    <div class="uk-flex uk-flex-column">
        <div class="uk-modal-title tm-margin-16-bottom">
            {{ text.loadConfiguration }}
        </div>
        <div class="tm-text-size-14 uk-text-default@s tm-margin-24-bottom tm-color-gray-100">
            {{ text.loadConfigurationDescription }}
        </div>
        <form novalidate class="uk-flex uk-flex-column tm-margin-24-bottom" :action="formAction" @submit.prevent="submitHandler">
            <div class="uk-flex uk-flex-top uk-flex-column uk-flex-row@s">
                <LabelInfield
                    class="uk-width-1-1"
                    :label="text.configurationNumber"
                    :input-attributes="inputAttributes"
                    :max-length="7"
                    :input-classes="'uk-width-1-1'"
                    :message="v$.configurationId.$errors[0] ? v$.configurationId.$errors[0].$message : error"
                    :is-dirty="v$.configurationId.$dirty"
                    @labelInfieldInputChange="configurationId = $event.value"
                    @blur="v$.configurationId.$validate()"
                />
            </div>
        </form>
        <div class="uk-flex uk-flex-column uk-flex-row@s uk-flex-middle">
            <button
                class="uk-width-1-1 uk-width-1-3@s uk-button uk-position-relative uk-button-primary uk-button-large tm-margin-24-bottom uk-margin-remove-bottom@s"
                type="submit" :disabled="isLoading" @click="submitHandler">
                <span v-if="isLoading" uk-spinner="ratio: 0.7"></span>
                {{ text.load }}
            </button>
            <div class="uk-text-center uk-width-1-1 uk-width-2-3@s uk-text-left@s">
                <a :href="configuratorLink"
                   class="tm-margin-24-left@s tm-text-size-14 uk-text-default@s">
                    {{ text.createNewConfiguration }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import {triggerEvent} from "../../utilities/helpers";
import LabelInfield from "./LabelInfield.vue";
import {useVuelidate} from '@vuelidate/core';
import {required, minLength, helpers} from '@vuelidate/validators';

export default {
    name: "LoadConfiguration",
    components: {
        LabelInfield
    },
    props: {
        configuratorLink: {
            type: String
        },
        formAction: {
            type: String,
            default: Joomla.getOptions('ajaxBase', '/index.php') + '?option=com_hyperpc&task=configurator.find_configuration'
        },
        checkConfigUrl: {
            type: String,
            default: Joomla.getOptions('ajaxBase', '/index.php') + '?option=com_hyperpc&task=configurator.check_configuration&format=raw&tmpl=component'
        }
    },
    data() {
        return {
            v$: useVuelidate(),
            text: {
                loadConfiguration: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION', 'Load configuration'),
                loadConfigurationDescription: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_DESCRIPTION', 'To load an existing configuration, enter its number in the field below. Please note: due to changes in assortment and prices, the configuration number may become invalid.'),
                load: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD', 'Load'),
                configurationNumber: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_NUMBER', 'Configuration number'),
                createNewConfiguration: Joomla.Text._('MOD_HP_NAVBAR_USER_CREATE_NEW_CONFIGURATION', 'Create new configuration'),
                lengthError: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION_LENGTH_ERROR', 'Field should be at least %s characters long').replace('%s', '3')
            },
            error: '',
            configurationId: '',
            isLoading: false,
        }
    },
    computed: {
        inputAttributes() {
            return {
                id: 'load-configuration-modal-input',
                name: "configuration_id",
                pattern: /[^0-9]/g,
                inputmode: "numeric",
                required: 'required'
            }
        }
    },
    methods: {
        submitHandler() {
            this.isLoading = true;
            this.error = '';

            fetch(`${this.checkConfigUrl}&configuration_id=${this.configurationId}`)
                .then((response) => response.json())
                .then((result) => {
                    if (result.result === 'success') {
                        window.location.href = `${this.formAction}&configuration_id=${this.configurationId}`;
                        triggerEvent('goToConfigurator', document);
                    } else {
                        this.error = result.msg;
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                })
        }
    },
    validations() {
        return {
            configurationId: {
                required: helpers.withMessage(this.text.lengthError, required),
                minLength: helpers.withMessage(this.text.lengthError, minLength('3'))
            }
        }
    },
}
</script>