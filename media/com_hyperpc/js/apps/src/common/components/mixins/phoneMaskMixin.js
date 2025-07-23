import {IMaskDirective} from 'vue-imask';
// TODO пофиксить ввод букв в маску
// TODO сделать maskData computed, на события стораджа/документа апдейтить маску и ставить начальное значение
// TODO разобраться с вводом через onInput
// TODO
export default {
    props: {
        maskData: {
            type: String,
            default: ''
        },
        maskClass: {
            type: String,
            default: ''
        },
    },
    directives: {
        imask: IMaskDirective,
    },
    data() {
        return {};
    },
    mounted() {
        this.setDefaultValue();
        this.setMaskAttr(this.mask)
    },
    computed: {
        mask() {
            return this.maskData ? this.maskData.mask.replaceAll('0', '_') : '';
        },
        format() {
            return this.maskData ? this.maskData.format.replaceAll('x', '0').replace(this.defaultValue, '') : '';
        },
        defaultValue() {
            return this.maskData ? this.maskData.default : '';
        }
    },
    watch: {
        internalValue(newVal, oldVal) {
            if (this.maskData) {
                if (this.$refs.input.selectionEnd < this.defaultValue.length) this.onInput({target: {value: oldVal}});
                if (newVal.length > this.mask.length) this.onInput({target: {value: oldVal}});
            }
        },
        maskData() {
            this.onAccept()
        }
    },
    methods: {
        setDefaultValue() {
            if (this.maskData) {
                this.onInput({target: {value: this.defaultValue}})
            }
        },
        setMaskAttr(newVal) {
            this.$refs.formControls.dataset.mask = newVal + this.mask.substring(newVal.length);
        },
        onAccept(e) {
            if (!e?.detail._rawInputValue || e.detail._rawInputValue.indexOf(this.defaultValue) === -1) {
                this.setDefaultValue()
            } else {
                this.onInput({target: {value: e.detail._value}})
            }
            this.setMaskAttr(this.internalValue);
        },
        onkeydown(e) {
            switch (e.keyCode) {
                case 36: // home
                case 38: // arrow up
                    e.preventDefault()
                    this.$refs.input.selectionEnd = this.defaultValue.length
                    break;
                case 37: // arrow left
                    if (this.defaultValue.length && this.$refs.input.selectionStart === this.defaultValue.length) {
                        e.preventDefault()
                    }
                    break;
                case 39: // arrow right
                case 46: // delete
                case 8: // backspace
                    if (this.defaultValue.length && this.$refs.input.selectionStart < this.defaultValue.length) {
                        e.preventDefault()
                    }
                    break;
            }
        }
    }
};