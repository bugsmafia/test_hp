export default {
    props: {
        value: {
            type: [String, Number],
            default: ''
        },
        inputAttributes: {
            type: Object,
            default: () => ({
                type: 'text',
            })
        }
    },
    data() {
        return {
            internalValue: this.value,
            focus: false
        };
    },
    computed: {
        isEmpty() {
            return !this.internalValue ? 'isEmpty' : '';
        },
        isFocused() {
            return this.focus ? 'isFocused' : '';
        }
    },
    watch: {
        value(newValue) {
            this.internalValue = newValue;
        },
        internalValue(newValue) {
            this.$emit('input', newValue);
        }
    },
    methods: {
        onInput(event) {
            const pattern = this.inputAttributes?.pattern ? new RegExp(this.inputAttributes.pattern) : ''
            let value = event.target.value.replace(pattern, '');
            this.$refs.input.value = value;
            this.internalValue = value;
            this.$emit('textInputChange', { value: this.internalValue });
        },
        focusHandler() {
            this.focus = true;
        },
        blurHandler() {
            this.focus = false;
            if (this.suggestionsType) this.suggestionBlurHandler()
            this.$emit('blur');
        }
    }
};