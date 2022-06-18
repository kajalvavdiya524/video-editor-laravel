<template>
  <button class="btn" :disabled="loading">
    <template v-if="loading">
      <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
      {{ loadingText }}
    </template>
    <template v-else>
      {{ text }}
    </template>
  </button>
</template>
<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false,
      required: true
    },
    loadingText: {
      type: String,
      default: 'Loading...',
      required: false
    },
    text: {
      type: String,
      required: true
    }
  }
}
</script>