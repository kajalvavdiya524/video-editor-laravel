<template>
  <li>
    <b-dropdown-item @click="$refs.modal.show()">Save Scene</b-dropdown-item>
    <modal-scene-form ref="modal" :scenes="scenes"></modal-scene-form>
  </li>
</template>

<script>
import ModalSceneForm from "../../ModalSceneForm";

export default {
  name: 'BtnActionSaveScene',
  props: {
    modelRows: {
      type: Array,
      required: true,
    },
    sceneIndex: {
      type: Number,
      required: true
    },
  },
  components: {
    ModalSceneForm
  },
  computed: {
    scenes() {
      return this.modelRows.filter((row) => row.Scene === this.sceneIndex)
    }
  },
}
</script>