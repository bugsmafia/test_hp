export default {
    props: {
        label: {
            type: String,
            required: false,
            default: ''
        },
        labelPosition: {
            type: String,
            default: 'inside', // 'inside' | 'outside'
            validator: (value) => ['inside', 'outside'].includes(value)
        }
    },
    computed: {
        computedWrapperClass() {
            return {
                'tm-label-infield': this.labelPosition === 'inside',
                'tm-label-outside': this.labelPosition === 'outside'
            };
        }
    },
    methods: {
        focusInput() {
            if (!this.inputAttributes.disabled && !this.focus) {
                this.$refs.input.focus();
            }
        }
    }
};
