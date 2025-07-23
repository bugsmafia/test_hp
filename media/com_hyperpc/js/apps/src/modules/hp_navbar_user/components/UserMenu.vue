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
    <div class="uk-container">
        <ul class="uk-nav uk-nav-primary">
            <li class="uk-nav-header uk-flex uk-flex-between">{{ text.profile }}</li>
            <li v-if="!isAuthorizedValue">
                <span class="uk-text-bold">
                    <a href="#login-form-modal" data-uk-toggle>{{ text.signIn }}</a>&nbsp;{{ text.or }}&nbsp;<a href="#login-form-modal" data-uk-toggle>{{ text.register }}</a>
                </span>
            </li>
            <li v-else class="uk-flex uk-flex-middle">
                <span class="uk-icon tm-margin-16-right" :class="$style.avatar">
                    <img class="uk-border-circle" :src="avatar" alt="Avatar">
                </span>
                <span class="uk-h4 uk-margin-remove">{{ nameValue }}</span>
            </li>
            <a v-if="isAuthorizedValue" :href="profileLink" class="uk-button uk-button-primary uk-button-small uk-position-top-right uk-margin-top">{{ text.goToProfile }}</a>
        </ul>
        <ul class="uk-nav uk-navbar-dropdown-nav uk-nav-medium">
            <li>
                <a href="#load-configuration-modal" data-uk-toggle>
                    <span :class="$style.listIcon" class="uk-icon uk-preserve tm-margin-8-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" viewBox="0 0 13 16" fill="none">
                            <path d="M2.46682 16H10.5332C12.1601 16 13 15.1657 13 13.5708V6.86664C13 5.83295 12.8638 5.36041 12.2055 4.70328L8.24796 0.782649C7.60477 0.14767 7.07509 0 6.12165 0H2.46682C0.847497 0 0 0.834333 0 2.43655V13.5708C0 15.1657 0.847497 16 2.46682 16ZM2.59546 14.5233C1.86903 14.5233 1.50582 14.1541 1.50582 13.4749V2.52515C1.50582 1.85325 1.86903 1.4767 2.60303 1.4767H5.81141V5.49331C5.81141 6.56391 6.34866 7.08076 7.4383 7.08076H11.4942V13.4749C11.4942 14.1541 11.131 14.5233 10.397 14.5233H2.59546ZM7.58207 5.78865C7.26426 5.78865 7.12806 5.65575 7.12806 5.35302V1.66867L11.2899 5.78865H7.58207ZM7.16589 8.52054C7.16589 8.15874 6.86321 7.89294 6.5 7.89294C6.13679 7.89294 5.83411 8.15874 5.83411 8.52054V10.7503L5.89464 11.8874L5.31199 11.2967L4.74447 10.7282C4.63097 10.6101 4.45693 10.5288 4.29802 10.5288C3.94994 10.5288 3.69267 10.7725 3.69267 11.0974C3.69267 11.282 3.76834 11.4222 3.91211 11.5478L5.99301 13.4084C6.16705 13.5635 6.31083 13.6373 6.5 13.6373C6.68917 13.6373 6.83294 13.5635 7.00698 13.4084L9.08789 11.5478C9.23166 11.4222 9.30733 11.282 9.30733 11.0974C9.30733 10.7725 9.05006 10.5288 8.70198 10.5288C8.54307 10.5288 8.36903 10.6101 8.25553 10.7282L7.68801 11.2967L7.10535 11.8874L7.16589 10.7503V8.52054Z" fill="#9C9C9C"/>
                        </svg>
                    </span>
                    {{ text.loadConfiguration }}
                </a>
            </li>
            <li>
                <a :href="configsLink">
                    <span :class="$style.listIcon" class="uk-icon uk-preserve tm-margin-8-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="18" viewBox="0 0 15 18" fill="none">
                            <path d="M3.00279 9.02159C3.23324 8.98561 3.4567 8.96401 3.69413 8.96401C3.92458 8.96401 4.15503 8.98561 4.39246 9.02879V2.96147C4.39246 2.30652 4.72765 1.93946 5.40503 1.93946H8.36592V5.85478C8.36592 6.89839 8.86173 7.4022 9.86732 7.4022H13.6103V13.6351C13.6103 14.2972 13.2751 14.6571 12.5978 14.6571H8.16341C8.07961 15.1753 7.89106 15.6647 7.63268 16.0965H12.7235C14.2249 16.0965 15 15.2832 15 13.7286V7.19348C15 6.18586 14.8743 5.72523 14.2668 5.08467L10.6145 1.26291C10.0209 0.643946 9.53212 0.5 8.65223 0.5H5.27933C3.78492 0.5 3.00279 1.31329 3.00279 2.87511V9.02159ZM9.58101 5.71804V2.12659L13.4218 6.14268H10C9.7067 6.14268 9.58101 6.01312 9.58101 5.71804ZM3.38687 17.5H3.99441C4.23184 17.5 4.42039 17.3489 4.46927 17.1041L4.63687 16.406C4.72067 16.3844 4.81844 16.3412 4.90223 16.2981L5.49581 16.6651C5.69134 16.7947 5.92179 16.7875 6.10335 16.6075L6.51536 16.1685C6.68296 15.9958 6.69693 15.7583 6.56425 15.5351L6.21508 14.9306C6.27095 14.837 6.2919 14.765 6.32682 14.6715L6.99721 14.5059C7.23464 14.4483 7.38827 14.2612 7.38827 14.0093V13.3975C7.38827 13.16 7.22765 12.9729 6.99721 12.9153L6.3338 12.7498C6.29888 12.6418 6.25698 12.5627 6.22207 12.4907L6.59218 11.8645C6.71788 11.6558 6.69693 11.3967 6.52933 11.2384L6.09637 10.8065C5.92877 10.641 5.69832 10.5978 5.50279 10.7202L4.90223 11.1088C4.80447 11.0584 4.72067 11.0296 4.63687 11.0008L4.46927 10.2955C4.41341 10.058 4.23184 9.89966 3.99441 9.89966H3.38687C3.14944 9.89966 2.96089 10.0652 2.91201 10.2955L2.75838 10.9936C2.64665 11.0296 2.56285 11.0584 2.47207 11.1088L1.87849 10.7202C1.68994 10.605 1.45251 10.6338 1.28492 10.8065L0.865922 11.2384C0.698324 11.4039 0.656425 11.6558 0.782123 11.8645L1.15922 12.4907C1.1243 12.5627 1.08939 12.6562 1.04749 12.7498L0.384078 12.9153C0.153631 12.9729 0 13.1672 0 13.3975V14.0093C0 14.2612 0.153631 14.4483 0.384078 14.5059L1.06145 14.6715C1.09637 14.765 1.13128 14.837 1.1662 14.9306L0.817039 15.5423C0.691341 15.7583 0.705307 16.003 0.872905 16.1685L1.28492 16.6075C1.45251 16.7875 1.68994 16.7947 1.88547 16.6651L2.48603 16.2981C2.5838 16.3484 2.6676 16.3844 2.75838 16.406L2.91201 17.1041C2.96089 17.3417 3.14944 17.5 3.38687 17.5ZM3.69413 14.8946C3.05168 14.8946 2.5419 14.362 2.5419 13.6998C2.5419 13.0521 3.05168 12.5123 3.69413 12.5051C4.32263 12.4979 4.85335 13.0521 4.85335 13.6998C4.85335 14.362 4.32263 14.8946 3.69413 14.8946Z" fill="#9C9C9C"/>
                        </svg>
                    </span>
                    {{ text.configurations }}
                </a>
            </li>
            <li>
                <a :href="ordersLink">
                    <span :class="$style.listIcon" class="uk-icon uk-preserve tm-margin-8-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none">
                            <path d="M1.05934 12.6117L6.88573 15.8307C7.29034 16.0564 7.70231 16.0564 8.10692 15.8307L13.9333 12.6117C14.6469 12.2185 15 11.8107 15 10.7838V4.87756C15 4.1056 14.7057 3.61766 14.0436 3.24624L8.872 0.376878C7.96714 -0.125626 7.03286 -0.125626 6.12065 0.376878L0.948995 3.24624C0.294262 3.61766 0 4.1056 0 4.87756V10.7838C0 11.8107 0.353114 12.2185 1.05934 12.6117ZM10.9465 5.29995L5.51005 2.29222L6.71653 1.62221C7.2462 1.32362 7.74644 1.31634 8.28347 1.62221L12.9181 4.20756L10.9465 5.29995ZM7.49632 7.20801L2.0819 4.20756L4.11231 3.06418L9.54144 6.07192L7.49632 7.20801ZM1.86856 11.4829C1.46395 11.2572 1.32418 11.0314 1.32418 10.6454V5.35822L6.80481 8.41693V14.2431L1.86856 11.4829ZM13.1314 11.4829L8.18784 14.2431V8.41693L13.6685 5.35822V10.6454C13.6685 11.0314 13.5287 11.2572 13.1314 11.4829Z" fill="#9C9C9C"/>
                        </svg>
                    </span>
                    {{ text.orders }}
                </a>
            </li>
            <li>
                <a :href="reviewsLink">
                    <span :class="$style.listIcon" class="uk-icon uk-preserve tm-margin-8-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                            <path d="M4.27314 15C4.65237 15 4.93679 14.8076 5.39729 14.38L7.72009 12.2138H11.8172C13.8555 12.2138 15 10.9881 15 8.87173V3.34204C15 1.22565 13.8555 0 11.8172 0H3.18284C1.14447 0 0 1.22565 0 3.34204V8.87173C0 10.9881 1.17156 12.2138 3.13544 12.2138H3.41309V13.9739C3.41309 14.6081 3.73138 15 4.27314 15ZM4.60497 13.4323V11.4014C4.60497 10.981 4.43567 10.8171 4.04966 10.8171H3.2167C1.93679 10.8171 1.32731 10.1401 1.32731 8.82898V3.3848C1.32731 2.07363 1.93679 1.39667 3.2167 1.39667H11.7765C13.0564 1.39667 13.6727 2.07363 13.6727 3.3848V8.82898C13.6727 10.1401 13.0564 10.8171 11.7765 10.8171H7.6456C7.23928 10.8171 7.03612 10.8884 6.75169 11.2019L4.60497 13.4323ZM6.08804 8.80048L7.51693 7.69596L8.94582 8.80048C9.23702 9.0285 9.58239 8.82185 9.44695 8.40855L8.89165 6.64846L10.3476 5.54394C10.6456 5.31591 10.5305 4.92399 10.1648 4.92399H8.36343L7.82844 3.17102C7.71332 2.8076 7.31377 2.8076 7.20542 3.17102L6.66366 4.92399H4.85553C4.49661 4.92399 4.37472 5.31591 4.67946 5.54394L6.14221 6.64846L5.58014 8.40855C5.44469 8.81473 5.79007 9.0285 6.08804 8.80048Z" fill="#9C9C9C"/>
                        </svg>
                    </span>
                    {{ text.reviews }}
                </a>
            </li>
            <li v-if="isAuthorizedValue">
                <a :href="logoutLink">
                    <span :class="$style.listIcon" class="uk-icon tm-color-gray-100 tm-margin-8-right" data-uk-icon="sign-out"></span>
                    {{ text.logout }}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import { registerDocumentEvent } from '../../../utilities/helpers';

export default {
    props: {
        isAuthorized: Boolean,
        avatar: {
            type: String,
            default: '/media/hyperpc/img/user/placeholder.png'
        },
        profileLink: String,
        name: String,
        configsLink: String,
        ordersLink: String,
        reviewsLink: String,
        logoutLink: String
    },
    data() {
        return {
            isAuthorizedValue: this.isAuthorized,
            nameValue: this.name,
            text: {
                profile: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_PROFILE', 'Profile'),
                goToProfile: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_GO_TO_PROFILE', 'Go to profile'),
                signIn: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_SIGN_IN', 'Sign in'),
                or: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_OR', 'or'),
                register: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_REGISTER', 'register'),
                loadConfiguration: Joomla.Text._('MOD_HP_NAVBAR_USER_LOAD_CONFIGURATION', 'Load configuration'),
                configurations: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_CONFIGURATIONS', 'Configurations'),
                orders: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_ORDERS', 'Orders'),
                reviews: Joomla.Text._('MOD_HP_NAVBAR_USER_MENU_REVIEWS', 'Reviews'),
                logout: Joomla.Text._('JLOGOUT', 'Sign out')
            }
        }
    },
    mounted() {
        registerDocumentEvent('hpuserloggedin', (e, data) => {
            this.isAuthorizedValue = true;
            this.nameValue = data.name;
        });
    }
};
</script>

<style module>
    .avatar {
        width: 42px;
        height: 42px;
    }

    .listIcon {
        width: 20px;
    }
</style>
