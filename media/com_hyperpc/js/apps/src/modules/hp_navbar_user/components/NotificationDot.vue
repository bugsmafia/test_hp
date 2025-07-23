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
    <span v-if="showNotification" :class="$style.dot"></span>
</template>

<script>
export default {
    data() {
        return {
            showNotification: false,
            NOTIFICATION_EXPIRY_DAYS: 30
        };
    },
    methods: {
        checkNotificationStatus() {
            const lastClick = localStorage.getItem('hp_tabbar_chat_last_click');
            if (!lastClick) {
                this.showNotification = true;
            } else {
                const lastClickDate = new Date(parseInt(lastClick, 10)),
                      now = new Date(),
                      timeDiff = now - lastClickDate,
                      daysDiff = timeDiff / (1000 * 60 * 60 * 24);

                this.showNotification = daysDiff > this.NOTIFICATION_EXPIRY_DAYS;
            }

            if (!this.showNotification) {
                this.$nextTick(() => this.unmount());
            }
        },
        handleClick() {
            this.showNotification = false;
            localStorage.setItem('hp_tabbar_chat_last_click', Date.now());
            this.unmount();
        },
        unmount() {
            this.$.appContext.app.unmount();
        }
    },
    mounted() {
        this.checkNotificationStatus();
        const parentElement = this.$el.parentElement.closest('a');
        if (parentElement) {
            parentElement.addEventListener('click', this.handleClick);
        }
    },
    beforeUnmount() {
        const parentElement = this.$el.parentElement;
        if (parentElement) {
            parentElement.removeEventListener('click', this.handleClick);
        }
    }
}
</script>

<style module>
    .dot {
        position: absolute;
        top: -1px;
        right: -3px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--tm-color-lime);
    }
</style>
