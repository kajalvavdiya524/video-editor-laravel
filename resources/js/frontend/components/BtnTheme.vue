<template>
  <div class="dropdown">
    <button
      class="btn btn-secondary dropdown-toggle"
      type="button"
      data-toggle="dropdown"
    >
      Themes
    </button>
    <div class="dropdown-menu">
      <a
        v-for="(theme, i) in themes"
        :key="i"
        href="#"
        :value="theme.id"
        @click.prevent="themeChanged(theme)"
        class="dropdown-item"
        :class="{active: selectedTheme && selectedTheme.id == theme.id}"
      >{{ theme.name }}</a>
      <a
        href="#"
        class="dropdown-item"
        :value="customTheme.id"
        @click.prevent="themeChanged(customTheme)"
        :class="{active: selectedTheme && selectedTheme.name == 'Custom'}"
      >{{ customTheme.name }}</a>
    </div>
  </div>
</button>
</template>

<script>
  export default {
    props: {
      themes: {
        type: Array,
        default: () => []
      },
      selectedTheme: {
        type: Object,
        default: null
      }
    },
    data() {
      return {
        customTheme: {
          id: 0,
          name: 'Custom',
          font_name: '',
          font_size: 100,
          stroke_width: 1,
          stroke_color: '',
          color1: '',
          color2: ''
        }
      }
    },
    methods: {
      themeChanged(theme) {
        this.$emit('change', theme)
      }
    }
  }
</script>
