<template>
  <div class="dropdown mr-2">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle font-weight-bold" style="color: #3c4b64;" data-toggle="dropdown">
      Type
    </button>
    <div class="dropdown-menu">
      <a
        v-for="(item, i) in items"
        :key="i"
        href="#"
        class="dropdown-item"
        :class="{active: sel == item.value }"
        style="padding: 0.25rem 0.5rem; font-size: 0.765625rem;"
        @click.prevent="handleInput(itemValue ? item[itemValue] : item)"
      >
        {{ itemText ? item[itemText] : item }}
      </a>
    </div>
  </div>
</template>

<script>
  export default {
    props: ['value', 'items', 'itemValue', 'itemText'],
    data() {
      return {
        sel: this.value
      }
    },
    methods: {
      handleInput(sel) {
        this.sel = sel
        this.$emit('input', sel)
      }
    }
  }
</script>