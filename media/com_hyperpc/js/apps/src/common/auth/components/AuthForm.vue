<template>
    <div class="uk-form-width-small uk-flex uk-flex-column" style="height: 480px;">
        <div class="tm-margin-8-bottom tm-text-size-24 tm-color-white tm-font-semi-bold">
            {{ headingText }}
        </div>
        <div class="tm-text-size-14 tm-margin-32-bottom tm-color-silver">
            {{ headingDescriptionText }}
            <span v-if="step === 2">{{ login }}</span>
            <a v-if="step === 2" @click="step = 1">Изменить</a>
        </div>
        <form novalidate class="uk-flex uk-flex-column" @submit.prevent="submit">
            <TextInput
                v-show="loginType === 'email' && step === 1"
                class="tm-margin-32-bottom"
                :input-attributes="{ type: 'email' }"
                :label="'E-mail'"
                :message="v$.email.$errors.length ? v$.email.$errors[0].$message : ''"
                :is-dirty="v$.email.$dirty"
                :suggestions-type="'email'"
                @blur="v$.email.$validate()"
                @textInputChange="setFormValue($event, 'email')"
            />
            <TextInput
                v-show="loginType === 'phone' && step === 1"
                class="tm-margin-32-bottom"
                :input-attributes="phoneInputAttributes"
                :mask-class="'tm-mask-phone-new'"
                :mask-data="phoneMask"
                :label="'Номер телефона'"
                :message="v$.phone.$errors.length ? v$.phone.$errors[0].$message : ''"
                :is-dirty="v$.phone.$dirty"
                @blur="v$.phone.$validate()"
                @textInputChange="setFormValue($event, 'phone')"
            />

            <CodeInput v-if="step === 2"
                       ref="codeInput"
                       :timer="timer"
                       @startTimer="startTimer"
                       v-model="code"
            />

            <div v-if="step === 1" class="jsAuthCaptcha tm-margin-32-bottom">
                <div id="recaptcha" ref="recaptcha"></div>
            </div>

            <button class="uk-button uk-button-primary uk-button-large uk-text-normal tm-margin-16-bottom"
                    type="submit" :disabled="isSubmitting">
                {{ submitButtonText }}
            </button>
        </form>

        <a href="#" class="uk-text-center" v-if="!singleLoginType" @click.prevent="changeLoginType">
            {{ switcherButtonText }}
        </a>
        <div class="uk-margin-auto-top uk-text-small tm-color-gray-300">
            Нажимая кнопку "{{ submitButtonText }}", Вы соглашаетесь
            с условиями <a href="#">политики конфиденциальности</a>
        </div>
    </div>
</template>

<script>
import TextInput from "../../components/TextInput.vue";
import {required, email, helpers} from '@vuelidate/validators';
import {geoData, createCustomValidators} from "../../../utilities/custom-validators";
import {registerLocalStorageEvent, registerDocumentEvent} from "../../../utilities/helpers";
import CodeInput from "./CodeInput.vue";
import { useVuelidate } from "@vuelidate/core";

export default {
    name: "AuthForm",
    components: { TextInput, CodeInput },
    data() {
        return {
            v$: useVuelidate(),
            countryCode: localStorage.getItem('hp_geo_geoid').substring(0, 2) || window.Joomla.getOptions('defaultGeoLocation').geoId.substring(0, 2),
            phone: '',
            email: '',
            //TODO доделать прототип json, добавить переводы
            steps: [
                {
                    forms: {
                        phone: {
                            type: 'phone',
                            headingText: 'Войти или зарегистрироваться',
                            headingDescriptionText: 'Пришлем код для входа по SMS',
                            labelText: 'Номер телефона',
                            switcherButtonText: 'Войти по E-mail',
                        },
                        email: {
                            type: 'email',
                            headingText: 'Вход через E-mail',
                            headingDescriptionText: 'Только для зарегистрированных пользователей',
                            labelText: 'E-mail',
                            switcherButtonText: 'Войти по номеру телефона',
                        }
                    }
                },
                {
                    forms: {
                        phone: {
                            type: 'phone',
                            headingText: 'Введите код из SMS',
                            headingDescriptionText: 'Мы отправили код подтверждения на номер',
                            switcherButtonText: 'Войти по E-mail',
                        },
                        email: {
                            type: 'email',
                            headingText: 'Вход через E-mail',
                            switcherButtonText: 'Войти по номеру телефона',
                        },
                        headingDescriptionText: 'Мы отправили код подтверждения на адрес',
                    }
                }
            ],
            step: 1,
            login: "",
            code: "",
            loginType: "phone",
            timer: 0,
            errorMessage: "",
            recaptchaToken: "",
            isSubmitting: false
        };
    },
    validations() {
        const customValidators = createCustomValidators();
        return {
            //TODO Добавить переводы
            email: {
                required: helpers.withMessage('Это поле необходимо заполнить.', required),
                email: helpers.withMessage('Пожалуйста, введите корректный адрес электронной почты.', email),
            },
            phone: {
                [geoData[this.countryCode].ruleName]: customValidators[geoData[this.countryCode].ruleName],
            }
        }
    },
    computed: {
        currentStepIndex() {
            return this.step - 1;
        },
        headingText() {
            return this.steps[this.currentStepIndex].forms[this.loginType].headingText;
        },
        labelText() {
            return this.steps[this.currentStepIndex].forms[this.loginType].labelText;
        },
        headingDescriptionText() {
            return this.steps[this.currentStepIndex].forms[this.loginType].headingDescriptionText;
        },
        submitButtonText() {
            return this.step === 1 ? Joomla.Text._('COM_HYPERPC_AUTH_SIGN_IN', 'Submit') : Joomla.Text._('COM_HYPERPC_CONFIRM', 'Confirm');
        },
        switcherButtonText() {
            return this.steps[this.currentStepIndex].forms[this.loginType].switcherButtonText;
        },
        phoneInputAttributes() {
            return {
                inputmode: "numeric",
                required: 'required',
                type: 'tel',
            }
        },
        phoneMask() {
            return geoData[this.countryCode];
        }
    },
    mounted() {
        registerLocalStorageEvent('hp_geo_geoid', (e) => {
            this.countryCode = localStorage.getItem('hp_geo_geoid').substring(0, 2);
        })

        registerDocumentEvent('hplocationdefined', (e) => {
            this.countryCode = localStorage.getItem('hp_geo_geoid').substring(0, 2);
        })
        this.loadRecaptcha();
    },
    methods: {
        changeLoginType() {
            this.loginType === 'phone' ? this.loginType = 'email' : this.loginType = 'phone';
        },
        loadRecaptcha() {
            if (window.grecaptcha) {
                this.renderRecaptcha();
            } else {
                const script = document.createElement("script");
                script.src = "https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit";
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                window.onRecaptchaLoad = this.renderRecaptcha;
            }
        },
        renderRecaptcha() {
            if (this.$refs.recaptcha) {
                window.grecaptcha.render(this.$refs.recaptcha, {
                    sitekey: "your-site-key",
                    callback: (token) => { this.recaptchaToken = token; },
                    "expired-callback": () => { this.recaptchaToken = ""; }
                });
            }
        },
        async submit() {
            this.errorMessage = "";
            this.isSubmitting = true;
            this.login = this[this.loginType];

            if (this.step === 1) {
                try {
                    const response = await fetch("/api/auth/send-code", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ login: this.login, recaptcha: this.recaptchaToken })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.step = 2;
                        this.startTimer();
                    } else {
                        this.errorMessage = data.message;
                    }
                } catch (error) {
                    this.errorMessage = "Ошибка отправки кода.";
                }
            } else {
                try {
                    const response = await fetch("/api/auth/verify-code", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ login: this.login, code: this.code })
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.location.href = "/account";
                    } else {
                        this.errorMessage = "Неверный код.";
                    }
                } catch (error) {
                    this.errorMessage = "Ошибка проверки кода.";
                }
            }
            this.isSubmitting = false;
        },
        startTimer() {
            this.timer = 30;
            const interval = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                } else {
                    clearInterval(interval);
                }
            }, 1000);
        },
        setFormValue(e, formValue) {
            this[formValue] = e.value;
        },
        async resendCode() {
            this.errorMessage = "";
            try {
                const response = await fetch("/api/auth/send-code", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ login: this.login, recaptcha: this.recaptchaToken })
                });
                const data = await response.json();
                if (data.success) {
                    this.startTimer();
                } else {
                    this.errorMessage = data.message;
                }
            } catch (error) {
                this.errorMessage = "Ошибка повторной отправки кода.";
            }
        },
        phoneInputAttributes() {
            return {
                inputmode: "numeric",
                required: 'required',
                type: 'tel',
            };
        },
        phoneMask() {
            return geoData[this.countryCode];
        }
    }
};
</script>