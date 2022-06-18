<template>
  <img
    :src="item.src"
    class="ml-2"
    style="width: 16px; height: 22px; cursor: pointer;"
    data-toggle="tooltip"
    :title="item.title"
  >
</template>
<script>
export default {
  props: {
    type: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      items: [
        {
          type: 'Image',
          src: 'img/icons/img-folder.svg',
          title: 'Image Library'
        }, {
          type: 'Video',
          src: 'img/icons/video-folder.svg',
          title: 'Video Library'
        }, {
          type: 'Music',
          src: 'img/icons/music-folder.svg',
          title: 'Audio Library'
        }
      ]
    }
  },
  computed: {
    item() {
      return this.items.find((item) => item.type == this.type)
    }
  }
}
</script>