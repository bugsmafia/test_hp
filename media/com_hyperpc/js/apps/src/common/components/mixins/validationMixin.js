export default {
    props: {
        isDirty: {
            type: Boolean,
            default: false
        },
        message: {
            type: String,
            default: ''
        }
    },
    computed: {
        validationState() {
            if (this.isDirty && this.message) return 'uk-form-danger';
            if (this.isDirty && !this.message) return 'uk-form-success';
            return '';
        }
    }
};