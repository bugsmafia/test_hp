<template>
  <span class="uk-badge" v-show="!isHidden" :class="classList">
    {{ computedCount }}
  </span>
</template>

<script>
export default {
  name: "Badge",
  props: {
    count: {
      type: Number,
      default: 0
    },
    isHiddenOnZero: {
      type: Boolean,
      default: true
    },
    classList: {
      type: String,
      default: ''
    },
    maxSymbols: {
      type: Number,
      default: 2,
    }
  },
  computed: {
    isHidden() {
      return this.isHiddenOnZero && this.count == 0
    },
    computedCount() {
      return this.count.toString().length > this.maxSymbols ? `${'9'.repeat(this.maxSymbols)}+` : this.count
    },
  },
}
</script>
