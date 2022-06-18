<template>
  <div class="dropdown">
    <loadable-button text="Templates" :loading="loading" class="btn-secondary dropdown-toggle" data-toggle="dropdown" />
    <div class="dropdown-menu">
      <a href="#" class="dropdown-item" @click.prevent="$emit('export')">Download Template</a>
      <a href="#" class="dropdown-item" @click.prevent="$emit('before-save')">Save Template</a>
      <div class="dropdown-divider"></div>
      <a
        v-for="(template, i) in templates"
        :key="template.id"
        href="#"
        class="dropdown-item"
        @click.prevent="$emit('load', template.id, 'template')"
        :class="{active: selectedTemplate && selectedTemplate.id == template.id }"
      >{{ template.name }}</a>
    </div>
  </div>
</template>

<script>
  import { mapState, mapActions } from 'vuex'
  export default {
    props: ['loading', 'selectedTemplate', 'templates']
  }
</script>
