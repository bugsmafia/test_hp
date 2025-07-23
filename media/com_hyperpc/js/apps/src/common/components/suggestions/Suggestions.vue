<template>
    <div class="suggestions-wrapper" v-if="showSuggestions && filteredSuggestions.length">
        <div class="suggestions-suggestions">
            <div class="suggestions-hint">{{ suggestionsHint }}</div>
            <div class="suggestions-suggestion">
                <div
                    v-for="(suggestion, index) in filteredSuggestions"
                    :key="index"
                    @click="selectSuggestion(suggestion.value)"
                    class="suggestions-value"
                    :class="{ highlighted: index === highlightedIndex }"
                    v-html="highlightQuery(suggestion.value)"
                />
            </div>
        </div>
    </div>
</template>

<script>
import {locales} from "./locales";

export default {
    name: "Suggestions",
    data() {
        return {
            language: 'en',
            filteredSuggestions: [],
            highlightedIndex: -1,
            timeout: null // для debounce
        }
    },
    props: {
        suggestionsType: {
            type: String,
            required: true
        },
        query: {
            type: String,
            required: true
        },
        debounceDelay: {
            type: Number,
            default: 300
        },
        showSuggestions: {
            type: Boolean,
            default: false
        },
        suggestionsCount: {
            type: Number,
            default: 5
        },
        additionalFetchBodyOptions: {
            type: Object
        }
    },
    watch: {
        query() {
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                if (this.query.length > 0) {
                    this.fetchSuggestions(this.query);
                } else {
                    this.filteredSuggestions = [];
                }
            }, this.debounceDelay);
        }
    },
    mounted() {
        this.defineLanguage();
    },
    computed: {
        suggestionsHint() {
            return locales[this.language].SUGGESTIONS_HINT;
        }
    },
    methods: {
        async fetchSuggestions(query) {
            const fetchOptions = {
                method: 'POST',
                mode: 'cors',
                headers: {
                    //TODO сделать файл для api, токенов итд
                    Authorization: 'Token 7745c8a3490454e6e14d0109f139d490e5ab3c29',
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify({query: query, count: this.suggestionsCount, ...this.additionalFetchBodyOptions})
            }

            try {
                const response = await fetch(`https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/${this.suggestionsType}`, fetchOptions);
                const data = await response.json();
                this.filteredSuggestions = data.suggestions;
            } catch (error) {
                console.error('Ошибка при загрузке подсказок:', error);
                this.filteredSuggestions = [];
            }
        },
        selectSuggestion(suggestion) {
            this.$emit('selectSuggestion', suggestion);
        },
        highlightQuery(suggestion) {
            const query = this.query.trim();
            return suggestion.replace(query, `<strong>${query}</strong>`);
        },
        defineLanguage() {
            this.language = document.querySelector("html").lang;
        }
    }
}
</script>

<style scoped>

</style>