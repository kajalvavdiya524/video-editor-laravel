<template>
  <div :style="sizeStyle">
    <div v-show="loading" class="loading-container">
      <div class="d-flex align-items-center justify-content-center loading">
        <div class="spinner-border text-primary" role="status"></div>
      </div>
    </div>
    <canvas v-show="!loading" :id="id" width="640" height="360" />
  </div>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: "",
    },
    size: {
      type: Number,
      default: 0,
    },
    sizes: {
      type: Array,
      default: [{ width: 640, height: 360 }],
    },
    id: {
      type: String,
      default: "preview",
    },
  },
  computed: {
    sizeStyle() {
      const index = this.sizes.findIndex((s) => s.value == this.size);
      const validIndex = index ? index : 0;

      return {
        width: `${this.sizes[validIndex].width}px`,
        height: `${this.sizes[validIndex].height}px`,
      };
    },
  },
};
</script>
<style scoped>
.loading-container {
  height: 100%;
}
.loading {
  height: 100%;
}
canvas {
  width: 100%;
  height: 100%;
}
</style>
