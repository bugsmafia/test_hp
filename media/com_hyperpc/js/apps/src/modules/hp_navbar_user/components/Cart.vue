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
            <li class="uk-nav-header">{{ text.cart}}</li>
            <li v-if="cartIsEmpty"><span class="uk-text-bold">{{ text.cartIsEmpty }}</span></li>
        </ul>

        <a v-if="!cartIsEmpty" :href="cartUrl" class="uk-button uk-button-primary uk-button-small uk-position-top-right uk-margin-top">
            {{ text.goToCart }}
        </a>

        <ul class="uk-list" :class="$style.itemsList">
            <li v-for="item in cartItemsValue.slice(0, maxItemsCount)" class="uk-flex" :class="$style.item">
                <div class="uk-margin-right" :class="$style.itemImageWrapper">
                    <img :src="item.image" :alt="item.name" class="uk-position-center" />
                </div>
                <div>
                    <div class="uk-text-small tm-color-gray-100">{{ item.category }}</div>
                    <div class="uk-text-bold tm-margin-4-top">{{ item.name }}</div>
                    <div v-if="item.specification" class="uk-text-small tm-color-gray-100 tm-margin-12-top">{{ text.specification + item.specification }}</div>
                </div>
            </li>
        </ul>

        <div v-if="hiddenItems" class="tm-text-size-14 tm-color-gray-300 tm-margin-32-top">
            {{ hiddenItemsText }}
        </div>
    </div>
</template>

<script>
import {registerDocumentEvent, registerLocalStorageEvent} from "../../../utilities/helpers";

export default {
    props: {
        cartItems: {
          type: Array,
          default: []
        },
        cartUrl: {
            type: String,
            required: true
        },
        maxItemsCount: {
            type: Number,
            default: 3
        },
    },
    data() {
        return {
            cartItemsValue: this.cartItems,
            text: {
                cart: Joomla.Text._('MOD_HP_NAVBAR_USER_CART', 'Cart'),
                goToCart: Joomla.Text._('MOD_HP_NAVBAR_USER_CART_GO_TO_CART', 'Go to cart'),
                cartIsEmpty: Joomla.Text._('MOD_HP_NAVBAR_USER_CART_IS_EMPTY', 'There is nothing in the cart yet'),
                specification: Joomla.Text._('MOD_HP_NAVBAR_USER_CART_SPECIFICATION_NUMBER', 'Specification #'),
                moreItems: Joomla.Text._('MOD_HP_NAVBAR_USER_CART_MORE_ITEMS', 'And %d more in your cart')
            }
        }
    },
    computed: {
        cartIsEmpty() {
            return this.cartItemsValue.length === 0;
        },
        hiddenItems() {
            return Math.max(0, this.cartItemsValue.length - this.maxItemsCount);
        },
        hiddenItemsText() {
            return this.text.moreItems.replace(/%d/, this.hiddenItems);
        }
    },
    mounted() {
        registerLocalStorageEvent('hp_cart_items', (e) => {
            this.cartItemsValue = JSON.parse(e.newValue);
        });

        registerDocumentEvent('hpcartupdated', (e, data) => {
            this.cartItemsValue = data.items;
        });
    }
};
</script>

<style module>
    .item:not(:first-child) {
        margin-top: 32px;
    }

    .itemImageWrapper {
        background: #111;
        border-radius: 8px;
        width: 72px;
        height: 72px;
        position: relative;
        overflow: hidden;
    }
</style>
