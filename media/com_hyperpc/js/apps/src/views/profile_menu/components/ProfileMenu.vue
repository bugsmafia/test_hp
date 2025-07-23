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
    <div>
        <div class="uk-card uk-card-small uk-card-body uk-card-default">
            <div v-if="!isAuthorized" class="uk-text-center@s">
                <div class="uk-h4">{{ text.signInOrRegister }}</div>
                <button type="button" class="uk-button uk-button-primary uk-width-medium" data-uk-toggle="#login-form-modal">
                    {{ text.signIn }}
                </button>
            </div>
            <a v-else :href="routes.profile" class="uk-link-reset uk-flex uk-flex-middle">
                <span class="uk-flex uk-flex-middle uk-width-expand">
                    <span :class="$style.avatar" class="uk-flex-none tm-margin-16-right">
                        <img :src="avatarSrc" class="uk-border-circle" alt="Avatar">
                    </span>
                    <span class="tm-color-white-smoke">
                        <span class="tm-font-semi-bold tm-text-medium">{{ userName }}</span>
                        <span class="uk-text-small uk-display-block tm-margin-4-top">{{ userSub }}</span>
                    </span>
                </span>
                <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
            </a>
        </div>
        <div class="uk-card uk-card-small uk-card-body uk-card-default tm-margin-16-top">
            <ul class="uk-nav uk-nav-default uk-margin-remove">
                <li>
                    <a :href="routes.compare" class="uk-flex-between">
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.8 4C5.24183 4 5.6 4.35817 5.6 4.8V16C5.6 17.3255 6.67452 18.4 8 18.4H19.2C19.6418 18.4 20 18.7582 20 19.2C20 19.6418 19.6418 20 19.2 20H8C5.79086 20 4 18.2091 4 16V4.8C4 4.35817 4.35817 4 4.8 4Z" fill="currentColor"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.80781 11.1953C9.24964 11.1953 9.60781 11.5535 9.60781 11.9953L9.60781 15.1953C9.60781 15.6371 9.24964 15.9953 8.80781 15.9953C8.36598 15.9953 8.00781 15.6371 8.00781 15.1953L8.00781 11.9953C8.00781 11.5535 8.36598 11.1953 8.80781 11.1953Z" fill="currentColor"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8 5.58203C13.2418 5.58203 13.6 5.9402 13.6 6.38203L13.6 15.182C13.6 15.6239 13.2418 15.982 12.8 15.982C12.3582 15.982 12 15.6239 12 15.182L12 6.38203C12 5.9402 12.3582 5.58203 12.8 5.58203Z" fill="currentColor"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16.8078 8.00781C17.2496 8.00781 17.6078 8.36598 17.6078 8.80781L17.6078 15.2078C17.6078 15.6496 17.2496 16.0078 16.8078 16.0078C16.366 16.0078 16.0078 15.6496 16.0078 15.2078L16.0078 8.80781C16.0078 8.36598 16.366 8.00781 16.8078 8.00781Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="tm-margin-12-right">{{ text.compare }}</span>
                            <CompareBadge :count="compareCount" />
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li>
                    <a :href="routes.cart" class="uk-flex-between">
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.67943 15.9719H18.1958C18.58 15.9719 18.9316 15.675 18.9316 15.2497C18.9316 14.8325 18.58 14.5356 18.1958 14.5356H8.86747C8.47504 14.5356 8.22978 14.2708 8.17255 13.8616L8.04991 13.0271H18.2612C19.512 13.0271 20.1743 12.2808 20.3541 11.0612L20.9673 7.04112C20.9837 6.93681 21 6.8004 21 6.71214C21 6.23872 20.6648 5.91775 20.1007 5.91775H6.99527L6.87263 5.05115C6.76635 4.35306 6.48021 4 5.57272 4H2.7685C2.35972 4 2 4.35306 2 4.76229C2 5.17954 2.35972 5.5326 2.7685 5.5326H5.32745L6.59466 14.0221C6.77453 15.2337 7.42857 15.9719 8.67943 15.9719ZM19.2995 7.35406L18.7844 10.9168C18.719 11.326 18.4983 11.5827 18.0977 11.5827L7.83735 11.5908L7.20783 7.35406H19.2995ZM9.34983 20C10.1429 20 10.7806 19.3741 10.7806 18.5958C10.7806 17.8175 10.1429 17.1916 9.34983 17.1916C8.5568 17.1916 7.91911 17.8175 7.91911 18.5958C7.91911 19.3741 8.5568 20 9.34983 20ZM16.904 20C17.6971 20 18.3266 19.3741 18.3266 18.5958C18.3266 17.8175 17.6971 17.1916 16.904 17.1916C16.111 17.1916 15.4651 17.8175 15.4651 18.5958C15.4651 19.3741 16.111 20 16.904 20Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="tm-margin-12-right">{{ text.cart }}</span>
                            <CartBadge :count="cartCount" />
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li>
                    <a href="#load-configuration-modal" class="uk-flex-between" data-uk-toggle>
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.84633 21H17.1537C19.0309 21 20 20.0614 20 18.2672V10.725C20 9.56207 19.8428 9.03046 19.0832 8.29119L14.5169 3.88048C13.7747 3.16613 13.1636 3 12.0634 3H7.84633C5.97788 3 5 3.93862 5 5.74112V18.2672C5 20.0614 5.97788 21 7.84633 21ZM7.99476 19.3387C7.15658 19.3387 6.73749 18.9234 6.73749 18.1592V5.84079C6.73749 5.08491 7.15658 4.66128 8.00349 4.66128H11.7055V9.17997C11.7055 10.3844 12.3254 10.9659 13.5827 10.9659H18.2625V18.1592C18.2625 18.9234 17.8434 19.3387 16.9965 19.3387H7.99476ZM13.7485 9.51223C13.3818 9.51223 13.2247 9.36271 13.2247 9.02215V4.87725L18.0268 9.51223H13.7485ZM13.2683 12.5856C13.2683 12.1786 12.9191 11.8796 12.5 11.8796C12.0809 11.8796 11.7317 12.1786 11.7317 12.5856V15.0941L11.8015 16.3733L11.1292 15.7088L10.4744 15.0692C10.3434 14.9363 10.1426 14.8449 9.95926 14.8449C9.55763 14.8449 9.26077 15.1191 9.26077 15.4845C9.26077 15.6922 9.34808 15.85 9.51397 15.9912L11.915 18.0844C12.1158 18.2589 12.2817 18.3419 12.5 18.3419C12.7183 18.3419 12.8842 18.2589 13.085 18.0844L15.486 15.9912C15.6519 15.85 15.7392 15.6922 15.7392 15.4845C15.7392 15.1191 15.4424 14.8449 15.0407 14.8449C14.8574 14.8449 14.6566 14.9363 14.5256 15.0692L13.8708 15.7088L13.1985 16.3733L13.2683 15.0941V12.5856Z" fill="currentColor"/>
                                </svg>
                            </span>
                            {{ text.loadConfiguration }}
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li>
                    <a :href="routes.configurations" class="uk-flex-between">
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.40317 12.0229C6.66434 11.9848 6.9176 11.9619 7.18669 11.9619C7.44786 11.9619 7.70903 11.9848 7.97812 12.0305V5.60627C7.97812 4.91279 8.35801 4.52413 9.1257 4.52413H12.4814V8.66977C12.4814 9.77477 13.0433 10.3082 14.183 10.3082H18.425V16.9077C18.425 17.6088 18.0452 17.9898 17.2775 17.9898H12.2519C12.1569 18.5385 11.9432 19.0567 11.6504 19.514H17.4199C19.1215 19.514 20 18.6528 20 17.0068V10.0872C20 9.02032 19.8575 8.5326 19.169 7.85436L15.0298 3.80779C14.3571 3.15241 13.8031 3 12.8059 3H8.98324C7.28957 3 6.40317 3.86113 6.40317 5.51482V12.0229ZM13.8585 8.52498V4.72227L18.2114 8.9746H14.3333C14.0009 8.9746 13.8585 8.83743 13.8585 8.52498ZM6.83845 21H7.527C7.79609 21 8.00978 20.84 8.06518 20.5809L8.25512 19.8417C8.35009 19.8188 8.46089 19.7731 8.55587 19.7274L9.22858 20.116C9.45019 20.2532 9.71136 20.2456 9.91713 20.055L10.3841 19.5902C10.574 19.4073 10.5899 19.1558 10.4395 18.9196L10.0438 18.2794C10.1071 18.1804 10.1308 18.1041 10.1704 18.0051L10.9302 17.8298C11.1993 17.7688 11.3734 17.5707 11.3734 17.304V16.6562C11.3734 16.4047 11.1913 16.2066 10.9302 16.1456L10.1783 15.9704C10.1387 15.8561 10.0912 15.7722 10.0517 15.696L10.4711 15.033C10.6136 14.812 10.5899 14.5377 10.3999 14.37L9.90922 13.9128C9.71927 13.7375 9.4581 13.6918 9.2365 13.8213L8.55587 14.2329C8.44507 14.1795 8.35009 14.149 8.25512 14.1185L8.06518 13.3717C8.00186 13.1202 7.79609 12.9526 7.527 12.9526H6.83845C6.56937 12.9526 6.35568 13.1279 6.30028 13.3717L6.12616 14.1109C5.99953 14.149 5.90456 14.1795 5.80168 14.2329L5.12896 13.8213C4.91527 13.6994 4.64618 13.7299 4.45624 13.9128L3.98138 14.37C3.79143 14.5453 3.74395 14.812 3.88641 15.033L4.31378 15.696C4.27421 15.7722 4.23464 15.8713 4.18715 15.9704L3.43529 16.1456C3.17412 16.2066 3 16.4124 3 16.6562V17.304C3 17.5707 3.17412 17.7688 3.43529 17.8298L4.20298 18.0051C4.24255 18.1041 4.28212 18.1804 4.32169 18.2794L3.92598 18.9272C3.78352 19.1558 3.79935 19.4149 3.98929 19.5902L4.45624 20.055C4.64618 20.2456 4.91527 20.2532 5.13687 20.116L5.8175 19.7274C5.92831 19.7807 6.02328 19.8188 6.12616 19.8417L6.30028 20.5809C6.35568 20.8323 6.56937 21 6.83845 21ZM7.18669 18.2413C6.45857 18.2413 5.88082 17.6774 5.88082 16.9763C5.88082 16.2904 6.45857 15.7189 7.18669 15.7113C7.89898 15.7036 8.50047 16.2904 8.50047 16.9763C8.50047 17.6774 7.89898 18.2413 7.18669 18.2413Z" fill="currentColor"/>
                                </svg>
                            </span>
                            {{ text.configurations }}
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li>
                    <a :href="routes.orders" class="uk-flex-between">
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.27121 17.9764L11.2629 21.7989C11.7484 22.067 12.2428 22.067 12.7283 21.7989L19.72 17.9764C20.5763 17.5094 21 17.0251 21 15.8058V8.7921C21 7.8754 20.6469 7.29597 19.8524 6.85492L13.6464 3.44754C12.5606 2.85082 11.4394 2.85082 10.3448 3.44754L4.13879 6.85492C3.35311 7.29597 3 7.8754 3 8.7921V15.8058C3 17.0251 3.42374 17.5094 4.27121 17.9764ZM16.1359 9.2937L9.61206 5.72201L11.0598 4.92638C11.6954 4.5718 12.2957 4.56315 12.9402 4.92638L18.5017 7.99647L16.1359 9.2937ZM11.9956 11.5595L5.49828 7.99647L7.93477 6.63871L14.4497 10.2104L11.9956 11.5595ZM5.24228 16.636C4.75674 16.3679 4.58901 16.0998 4.58901 15.6414V9.36288L11.1658 12.9951V19.9136L5.24228 16.636ZM18.7577 16.636L12.8254 19.9136V12.9951L19.4022 9.36288V15.6414C19.4022 16.0998 19.2344 16.3679 18.7577 16.636Z" fill="currentColor"/>
                                </svg>
                            </span>
                            {{ text.orders }}
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li>
                    <a :href="routes.reviews" class="uk-flex-between">
                        <span class="uk-flex uk-flex-middle">
                            <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.12777 21C8.58284 21 8.92415 20.7819 9.47675 20.2974L12.2641 17.8423H17.1806C19.6266 17.8423 21 16.4532 21 14.0546V7.78765C21 5.38907 19.6266 4 17.1806 4H6.81941C4.37336 4 3 5.38907 3 7.78765V14.0546C3 16.4532 4.40587 17.8423 6.76253 17.8423H7.09571V19.8371C7.09571 20.5558 7.47765 21 8.12777 21ZM8.52596 19.2233V16.9216C8.52596 16.4451 8.3228 16.2594 7.85959 16.2594H6.86005C5.32415 16.2594 4.59278 15.4922 4.59278 14.0062V7.8361C4.59278 6.35012 5.32415 5.5829 6.86005 5.5829H17.1318C18.6677 5.5829 19.4072 6.35012 19.4072 7.8361V14.0062C19.4072 15.4922 18.6677 16.2594 17.1318 16.2594H12.1747C11.6871 16.2594 11.4433 16.3401 11.102 16.6955L8.52596 19.2233ZM10.3056 13.9739L12.0203 12.7221L13.735 13.9739C14.0844 14.2323 14.4989 13.9981 14.3363 13.5297L13.67 11.5349L15.4172 10.2831C15.7747 10.0247 15.6366 9.58052 15.1977 9.58052H13.0361L12.3941 7.59382C12.256 7.18195 11.7765 7.18195 11.6465 7.59382L10.9964 9.58052H8.82664C8.39594 9.58052 8.24966 10.0247 8.61535 10.2831L10.3707 11.5349L9.69616 13.5297C9.53363 13.99 9.94808 14.2323 10.3056 13.9739Z" fill="currentColor"/>
                                </svg>
                            </span>
                            {{ text.reviews }}
                        </span>
                        <span :class="$style.chevron" class="uk-icon tm-color-silver uk-flex-none" data-uk-icon="chevron-right"></span>
                    </a>
                </li>
                <li v-if="isAuthorized">
                    <a :href="routes.logout">
                        <span class="uk-icon tm-color-gray-100 tm-margin-12-right uk-flex-none">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g stroke="#000" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                    <path d="m15.75 8.75 3.5 3.25-3.5 3.25" />
                                    <path d="m19 12h-8.25" />
                                    <path d="m15.25 4.75h-8.5c-1.10457 0-2 .89543-2 2v10.5c0 1.1046.89543 2 2 2h8.5" />
                                </g>
                            </svg>
                        </span>
                        {{ text.logout }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import CompareBadge from '../../../modules/hp_navbar_user/components/CompareBadge.vue';
import CartBadge from '../../../modules/hp_navbar_user/components/CartBadge.vue';
import { registerDocumentEvent } from '../../../utilities/helpers';

export default {
    components: {
        CompareBadge,
        CartBadge
    },
    props: {
        user: Object,
        routes: Object,
        cartCount: Number,
        compareCount: Number
    },
    data() {
        return {
            isAuthorized: this.user.isAuthorized || false,
            userName: this.user.name || '',
            userPhone: this.user.phone || '',
            userEmail: this.user.email || '',
            userAvatar: this.user.avatar || '',
            text: {
                signIn: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_SIGN_IN', 'Sign In'),
                signInOrRegister: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_SIGN_IN_OR_REGISTER', 'Sign In or Register'),
                cart: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_CART', 'Cart'),
                compare: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_COMPARE', 'Compare'),
                configurations: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_CONFIGURATIONS', 'Configurations'),
                loadConfiguration: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_LOAD_CONFIGURATION', 'Load Configuration'),
                orders: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_ORDERS', 'Orders'),
                reviews: Joomla.Text._('COM_HYPERPC_PROFILE_MENU_REVIEWS', 'Reviews'),
                logout: Joomla.Text._('JLOGOUT', 'Sign out'),
            }
        }
    },
    mounted() {
        registerDocumentEvent('hpuserloggedin', (e, data) => {
            this.isAuthorized = true;
            this.userName = data.name;
            this.userEmail = data.email;
            /** @todo add phone */
        });
    },
    computed: {
        avatarSrc() {
            return this.userAvatar || '/media/hyperpc/img/user/placeholder.png';
        },
        userSub() {
            if (this.userPhone.match(/^\+[0-9\s\(\)-]{10,}$/)) {
                return this.userPhone;
            } else if (!this.userEmail.match(/^no-name-.+@gmail.com/)) {
                return this.userEmail;
            }

            return '';
        }
    }
};
</script>

<style module>
    .avatar {
        width: 40px;
        height: 40px;
    }

    .chevron > svg > polyline {
        stroke-width: 2.06;
    }
</style>
