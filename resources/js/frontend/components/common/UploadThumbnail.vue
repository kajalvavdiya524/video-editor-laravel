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
      
    }
  },
  computed: {
   
  }
}
</script>