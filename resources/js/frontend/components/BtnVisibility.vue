<template>
  <div class="dropdown">
    <button
      class="btn btn-info dropdown-toggle"
      data-toggle="dropdown"
      aria-haspopup="true"
      aria-expanded="false"
    >
      Columns
    </button>
    <div class="dropdown-menu">
      <a
        v-for="(visibility, i) in visibilities"
        :key="i"
        href="#"
        class="dropdown-item"
        @click.prevent="$emit('name-change', visibility.value)"
        :class="{active: selectedColumnVisibilityName == visibility.value }"
      >
        {{ visibility.text }}
      </a>
      <div class="dropdown-item cursor-pointer" :class="{active: selectedColumnVisibilityName == 'custom' }" @click="$emit('name-change', 'custom')">
        <span>Custom...</span>
        <button class="btn btn-sm ml-auto" :class="selectedColumnVisibilityName == 'custom' ? 'btn-secondary' : 'btn-primary'" @click.stop="$emit('show-modal')">
          <i class="cil-pencil"></i>
        </button>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    selectedColumnVisibilityName: {
      type: String,
      default: 'custom'
    },
    visibilities: {
      type: Array,
      required: true,
      default: () => []
    }
  }
}
</script>