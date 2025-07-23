<template>
    <div class="tm-label-infield" :class="computedClasses">
        <label class="uk-form-label" @click="labelClick">{{ label }}</label>
        <div class="uk-form-controls" :class="maskClass" ref="formControls">
            <input
                :disabled="isDisabled"
                class="uk-input"
                :maxlength="maxLength"
                :class="`${inputClasses} ${isError}`"
                :type="inputType"
                v-bind="inputAttributes"
                @focus="focusHandler"
                @focusout="focus = false"
                @input="onInput"
                @blur="blurHandler"
                ref="input"
            >
            <suggestions ref="suggestions" :suggestions-type="suggestionsType" :query="query" :show-suggestions="showSuggestions"
                         @selectSuggestion="selectSuggestion"/>
        </div>
        <div class="uk-form-danger" v-if="message">
            {{ message }}
        </div>
    </div>
</template>

<script>
import Suggestions from "./suggestions/Suggestions.vue";

export default {
    name: "LabelInfield",
    components: {
        Suggestions
    },
    props: {
        inputType: {
            type: String,
            default: 'text'
        },
        inputAttributes: {
          type: Object,
          default: {}
        },
        inputClasses: {
            type: String,
            default: ''
        },
        maskClass: {
            type: String,
            default: ''
        },
        maxLength: {
            type: Number,
            default: Infinity
        },
        label: {
            type: String,
            default: 'Label'
        },
        message: {
            type: String,
            default: ''
        },
        isDisabled: {
            type: Boolean,
            default: false
        },
        isDirty: {
            type: Boolean,
            default: false
        },
        suggestionsType: {
            type: String
        },
        additionalFetchBodyOptions: {
            type: Object
        }
    },
    data() {
        return {
            value: '',
            focus: false,
            showSuggestions: false,
            query: ''
        };
    },
    watch: {
        value(e) {
            this.suggestionsType ? this.query = e : '';
        }
    },
    computed: {
        isEmpty() {
            return !this.value ? 'isEmpty' : '';
        },
        isFocused() {
            return this.focus ? 'isFocused' : '';
        },
        isError() {
            if (this.isDirty && this.message) {
                return 'uk-form-danger';
            }
            if (this.isDirty && !this.message && this.value) {
                return 'uk-form-success';
            }

            return ''
        },
        computedClasses() {
            return [this.isEmpty, this.isFocused].join(' ');
        }
    },
    methods: {
        labelClick() {
            if (!this.isDisabled && !this.focus) {
                this.$refs.input.focus();
            }
        },
        focusHandler() {
            this.focus = true;
            this.showSuggestions = true;
        },
        blurHandler(e) {
            if (e && this.$refs.suggestions?.$el.contains(e.explicitOriginalTarget)) return;
            this.$emit('blur');
            if (this.suggestionsType) {
                setTimeout(() => {
                    this.showSuggestions = false;
                }, 100);
            }
        },
        onInput(event) {
            const pattern = this.inputAttributes?.pattern ? new RegExp(this.inputAttributes.pattern) : ''
            let value = event.target.value.replace(pattern, '');
            event.target.value = value;
            this.value = value;
            this.$emit('labelInfieldInputChange', { value: this.value });
        },
        selectSuggestion(value) {
            this.value = value;
            this.$refs.input.value = value;
            this.$emit('labelInfieldInputChange', {value: this.value});
            this.blurHandler();
        },
    }
};
</script>
