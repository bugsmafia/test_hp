<template>
    <Badge :count="countValue" />
</template>

<script>
import Badge from "../../../common/components/Badge.vue";
import { registerDocumentEvent, registerLocalStorageEvent } from "../../../utilities/helpers";

export default {
    name: "CompareBadge",
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
        registerLocalStorageEvent('hp_compared_items_count', (e) => {
            this.countValue = e.newValue
        })

        registerDocumentEvent('hpcompareupdated', (e, data) => {
            this.countValue = data.totalCount
        })
    },
}
</script>
