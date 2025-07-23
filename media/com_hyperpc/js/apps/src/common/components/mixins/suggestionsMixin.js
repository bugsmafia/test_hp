export default {
    props: {
        suggestionsType: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            showSuggestions: false,
            query: ''
        };
    },
    watch: {
        internalValue(newVal) {
            if (this.suggestionsType) {
                this.query = newVal;
            }
        },
        focus() {
            this.focus ? this.showSuggestions = true : setTimeout(()=>{
                this.showSuggestions = false;
            }, 100)
        }
    },
    methods: {
        toggleSuggestions() {
            this.showSuggestions = !this.showSuggestions;
        },
        suggestionBlurHandler(e) {
            if (e && this.$refs.suggestions?.$el.contains(e.explicitOriginalTarget)) return;
            if (this.suggestionsType) {
                setTimeout(() => {
                    this.showSuggestions = false;
                }, 100);
            }
        },
        selectSuggestion(suggestion) {
            this.query = suggestion.target.value
            this.$refs.input.value = suggestion.target.value
            this.onInput(suggestion)
        }
    }
};