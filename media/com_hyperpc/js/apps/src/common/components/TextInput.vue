<template>
    <div :class="computedWrapperClass">
        <label class="uk-form-label" @click="focusInput">{{ label }}</label>
        <div class="uk-form-controls" :class="maskClass" :data-mask="mask" ref="formControls">
            <input
                class="uk-input"
                v-imask="maskData ? { mask: `{${defaultValue}${maskData.mask.substring(defaultValue.length)}` } : ''"
                :class="`${inputClasses} ${validationState}`"
                v-bind="inputAttributes"
                :value="internalValue"
                @focus="focusHandler"
                @focusout="focus = false"
                @input="onInput"
                @blur="blurHandler"
                @accept="onAccept"
                @keydown="onkeydown"
                ref="input"
            >
            <suggestions ref="suggestions" :suggestions-type="suggestionsType" :query="query" :show-suggestions="showSuggestions"
                         @selectSuggestion="onInput"/>
        </div>
        <div class="uk-form-danger" v-if="message">
            {{ message }}
        </div>
    </div>
</template>

<script>

import labelMixin from './mixins/labelMixin';
import textInputMixin from './mixins/textInputMixin';
import validationMixin from './mixins/validationMixin';
import suggestionsMixin from './mixins/suggestionsMixin';
import phoneMaskMixin from "./mixins/phoneMaskMixin";
import Suggestions from "./suggestions/Suggestions.vue";

export default {
    name: "TextInput",
    components: {
        Suggestions
    },
    mixins: [labelMixin, textInputMixin, validationMixin, suggestionsMixin, phoneMaskMixin],
    props: {
        inputClasses: {
            type: String,
            default: ''
        },
    },
};
</script>
