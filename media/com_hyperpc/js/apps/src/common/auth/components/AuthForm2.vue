<!--
HYPERPC - The shop of powerful computers.

This file is part of the HYPERPC package.
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@package    HYPERPC
@license    Proprietary
@copyright  Proprietary https://hyperpc.ru/license
@link       https://github.com/HYPER-PC/HYPERPC".

@author     Artem Vyshnevskiy
-->

<template>
    <div class="uk-card uk-card-default uk-card-body uk-flex uk-flex-column" :class="$style.card">
        <div class="tm-margin-8-bottom tm-text-size-24 tm-color-white tm-font-semi-bold">
            {{ cardHeading }}
        </div>

        <div v-if="currentStep === 1">
            <div v-if="!currentMethod.registrationAllowed" class="tm-text-size-14 uk-margin-bottom tm-color-silver">
                {{ text.onlyForRegistred }}
            </div>
            <div v-if="stepOneDescription.length" class="tm-text-size-14 tm-color-silver">
                {{ stepOneDescription }}
            </div>

            <form class="tm-margin-32-top" @submit.prevent="sendCode">
                <input type="hidden" name="jform[type]" :value="currentMethod.type">

                <template v-for="(method) in methods">
                    <LabelInfield
                        class="tm-margin-32-bottom"
                        v-show="method.type === currentMethod.type"
                        :disabled="method.type !== currentMethod.type"
                        :input-type="getInputType(method.type)"
                        :label="getInputLabel(method.type)"
                    />
                </template>

                <button class="uk-button uk-button-primary uk-button-large uk-width-1-1 tm-margin-16-bottom" type="submit">
                    {{ text.sendCode }}
                </button>
            </form>

            <div v-if="currentMethod.alternative" class="uk-text-center">
                <a href="#" @click.prevent="changeMethod">
                    {{ alternativeMethodText }}
                </a>
            </div>
        </div>

        <div v-else-if="currentStep === 2">
            <div v-if="stepTwoDescription.length" class="tm-text-size-14 tm-margin-32-bottom tm-color-silver">
                {{ stepTwoDescription }}
                <span class="uk-text-nowrap">{{ currentValue }}</span>
                &thinsp;
                <a href="#" @click.prevent="goToStepOne">{{ text.change }}</a>
            </div>

            <form class="tm-margin-32-top" @submit.prevent="verifyCode">
                <div class="tm-margin-32-bottom uk-grid uk-grid-small uk-flex-center">
                    <div v-for="i in codeLength">
                        <input
                            class="uk-input uk-form-width-xsmall uk-form-large uk-text-center uk-display-inline-block"
                            required="required"
                            :data-code="i - 1"
                            :name="`pwd[${i - 1}]`"
                            inputmode="numeric"
                            maxlength="1"
                            type="text"
                            autocomplete="off"
                            pattern="\d*">
                    </div>
                </div>

                <button class="uk-button uk-button-primary uk-button-large uk-width-1-1 tm-margin-16-bottom" type="submit">
                    {{ text.verifyCode }}
                </button>
            </form>
        </div>

        <div class="uk-text-small tm-color-gray-300 uk-margin-auto-top" v-html="privacyText"></div>
    </div>
</template>

<script>
import LabelInfield from "../../components/LabelInfield.vue";

export default {
    components: {
        LabelInfield,
    },
    props: {},
    data() {
        return {
            currentStep: 1,
            methods: Joomla.getOptions('authProps', {}).methods || [],
            codeLength: Joomla.getOptions('authProps', {}).codeLength || 4,
            currentMethod: {},
            currentValue: '',
            text: {
                onlyForRegistred: 'Only for registered users',
                signInAndRegister: 'Sign in and register',
                mobile: {
                    stepOne: {
                        heading: 'Sign in by phone number',
                        description: 'We\'ll send you an SMS code',
                        changeTo: 'Sign in by phone number',
                        inputLabel: 'Phone number'
                    },
                    stepTwo: {
                        heading: 'Enter code from sms',
                        description: 'We have sent a verification code to',
                    }
                },
                email: {
                    stepOne: {
                        heading: 'Sign in by Email',
                        changeTo: 'Sign in by Email',
                        inputLabel: 'my@email.com'
                    },
                    stepTwo: {
                        heading: 'Enter code from Email',
                        description: 'We have sent a verification code to',
                    }
                },
                sendCode: 'Send Code',
                verifyCode: 'Verify Code',
                privacyText: 'By clicking the "%s" button, you agree to the terms of the <a href="/legal-info/privacy-policy" target="_blank">privacy policy</a>',
                change: 'Change'
            }
        }
    },
    mounted() {
        if (this.methods.length > 0) {
            this.currentMethod = this.methods[0];
        } else {
            this.$nextTick(() => this.$.appContext.app.unmount());
        }
    },
    computed: {
        stepOneHeading() {
            return this.currentMethod.registrationAllowed ?
                this.text.signInAndRegister :
                this.text[this.currentMethod.type]?.stepOne.heading;
        },
        stepOneDescription() {
            return this.text[this.currentMethod.type]?.stepOne.description || '';
        },
        stepOneInputLabel() {
            return this.text[this.currentMethod.type]?.stepOne.inputLabel || '';
        },
        stepTwoHeading() {
            return this.text[this.currentMethod.type]?.stepTwo.heading || '';
        },
        stepTwoDescription() {
            return this.text[this.currentMethod.type]?.stepTwo.description || '';
        },
        cardHeading() {
            return this.currentStep === 1 ?
                this.stepOneHeading :
                this.stepTwoHeading;
        },
        alternativeMethodText() {
            return this.text[this.currentMethod.alternative]?.stepOne.changeTo || '';
        },
        submitButtonText() {
            return this.currentStep === 1 ? this.text.sendCode : this.text.verifyCode;
        },
        privacyText() {
            return this.text.privacyText.replace('%s', this.submitButtonText);
        }
    },
    methods: {
        changeMethod() {
            const alternative = this.currentMethod.alternative;

            this.currentMethod = this.methods.find((method) => {
                return method.type === alternative;
            });
        },
        sendCode() {
            // TODO send code logic
            this.currentValue = '+7 (123) 000-00-00';
            this.currentStep = 2;
        },
        goToStepOne() {
            this.currentStep = 1
        },
        getInputType(methodType) {
            switch (methodType) {
                case 'email':
                    return 'email';
                case 'mobile':
                    return 'tel';
                default:
                    return 'text';
            }
        },
        getInputLabel(methodType) {
            return this.text[methodType]?.stepOne.inputLabel || '';
        },
        verifyCode() {
            // TODO verify code logic
            console.log('verify request');
        }
    }
}
</script>

<style module>
    .card {
        height: 480px;
    }
</style>