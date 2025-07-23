<template>
    <div>
        <div class="uk-grid uk-grid-medium">
            <div v-for="(digit, index) in codeLength"
                 :key="index">
                <input
                    :ref="el => inputRefs[index] = el"
                    type="text"
                    maxlength="1"
                    class="uk-input uk-form-width-xsmall"
                    v-model="code[index]"
                    @input="handleInput($event, index)"
                    @keydown="keyDownHandler($event, index)"
                    @paste="handlePaste"
                />
            </div>
        </div>
        <p v-if="errorMessage" class="uk-padding-remove-left uk-form-danger uk-text-center uk-text-small tm-margin-8-top">{{ errorMessage }}</p>
        <p v-if="timer" class="tm-color-lime-600 tm-margin-32-top uk-text-center">Оставшееся время: 00:<span v-if="timer < 10">0</span>{{ timer }}</p>
        <p v-else class="uk-link tm-margin-32-top uk-text-center" @click="sendCode">Отправить новый код</p>
    </div>
</template>

<script>
export default {
    name: "CodeInput",
    props:{
        codeLength: {
            type: Number,
            default: 4
        },
        timer: {
            type: Number,
            default: 30
        }
    },
    data() {
        return {
            code: Array(this.codeLength).fill(""),
            errorMessage: null,
            timerInterval: null,
            inputRefs: []
        };
    },
    mounted() {
        this.$emit('startTimer');
    },
    methods: {
        handleInput(e, index) {
            if (!/^\d$/.test(e.data) && e.inputType.indexOf('delete') === -1) {
                return this.code[index] = "";
            }

            if (e.inputType.indexOf('delete') !== -1) {
                this.inputRefs[index - 1]?.focus();
            } else {
                this.inputRefs[index + 1]?.focus();
            }
        },
        handlePaste(event) {
            event.preventDefault();
            const pastedData = event.clipboardData.getData("text").replace(/\D/g, ""),
                  activeIndex = this.inputRefs.findIndex(input => input === document.activeElement),
                  lastIndex = Math.min(activeIndex + pastedData.length, this.codeLength - 1);

            if (activeIndex === -1) return;

            for (let i = 0; i < pastedData.length; i++) {
                const currentIndex = activeIndex + i;
                if (currentIndex < this.codeLength) {
                    this.code[currentIndex] = pastedData[i];
                }
            }

            this.inputRefs[lastIndex]?.focus();
        },
        keyDownHandler(e, index) {
            if (e.key === "ArrowLeft" && index > 0) {
                this.inputRefs[index - 1]?.focus();
            }
            if (e.key === "ArrowRight" && index < this.codeLength - 1) {
                this.inputRefs[index + 1]?.focus();
            }
        },
        clearCode() {
            this.code = Array(this.codeLength).fill("");
            this.inputRefs[0]?.focus();
        },
        sendCode() {
            //TODO сделать логику для авторизации
        }
    },
    beforeUnmount() {
        if (this.timerInterval) clearInterval(this.timerInterval);
    }
};
</script>
