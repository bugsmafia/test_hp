<template>
    <Badge :count="countValue" />
</template>

<script>
import Badge from "../../../common/components/Badge.vue";
import { registerDocumentEvent, registerLocalStorageEvent } from "../../../utilities/helpers";

export default {
    name: "CartBadge",
    components: {
        Badge
    },
    props: {
        count: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            countValue: Number(this.count)
        }
    },
    mounted() {
        registerLocalStorageEvent('hp_cart_items_count', (e) => {
            this.countValue = e.newValue
        });

        registerDocumentEvent('hpcartupdated', (e, data) => {
            this.countValue = data.count
        });
    },
}
</script>
