<template>
  <div>
    <input
      type="file"
      id="import-btn"
      hidden
      @change="$emit('import', $refs.importBtn.files[0])"
      ref="importBtn"
    />
    <div class="dropdown mr-2">
      <loadable-button
        text="Drafts"
        :loading="loading"
        class="btn-secondary dropdown-toggle"
        data-toggle="dropdown"
        data-flip="false"
      />
      <div class="dropdown-menu">
        <a href="#" class="dropdown-item" @click.prevent="$emit('new')">New</a>
        <a href="#" class="dropdown-item" @click.prevent="$emit('save')"
          >Save</a
        >
        <label
          for="import-btn"
          class="dropdown-item"
          style="cursor: pointer"
          @click="$refs.importBtn.value = null"
          >Import</label
        >
        <div class="dropdown-divider"></div>
        <a
          v-for="project in projects"
          :key="project.id"
          href="#"
          class="dropdown-item"
          :class="{
            active: selectedProject && selectedProject.id == project.id,
          }"
          @click.prevent="$emit('change', project.id, 'project')"
          >{{ project.name }}</a
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["loading", "selectedProject", "projects"],
};
</script>
