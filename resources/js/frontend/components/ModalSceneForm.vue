<template>
  <b-modal
    ref="modal"
    title="Save Scene"
    @show="resetModal"
    @ok="handleOk"
  >
    <form ref="form" @submit.stop.prevent="saveScene">
      <b-form-group
        label="Title"
        label-for="name-input"
        invalid-feedback="Name is required"
        :state="titleState"
      >
        <b-form-input
          id="name-input"
          v-model="title"
          :state="titleState"
          required
          autofocus
        ></b-form-input>
      </b-form-group>
    </form>
  </b-modal>
</template>

<script>
import { createNamespacedHelpers } from 'vuex'

const { mapActions: mapActionsScene } = createNamespacedHelpers('scenes')

export default {
  props: {
    scenes: {
      type: Array,
      default: () => [],
    },
    editSceneData: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      title: '',
      titleState: null
    }
  },
  watch: {
    editSceneData(newValue) {
      if (newValue) {
        this.title = newValue.title
      }
    }
  },
  methods: {
    ...mapActionsScene(['addScene', 'editScene']),
    show() {
      this.$refs.modal.show()
    },
    checkFormValidity() {
      const valid = this.$refs.form.checkValidity()
      this.titleState = valid
      return valid
    },
    resetModal() {
      this.title = ''
      this.titleState = null
    },
    handleOk(bvModalEvent) {
      bvModalEvent.preventDefault()
      this.saveScene()
    },
    saveScene() {
      if (!this.checkFormValidity()) {
        return
      }
      if (this.editSceneData) {
        this.editScene({
          id: this.editSceneData.id,
          title: this.title,
        })
      } else {
        this.addScene({
          title: this.title,
          scene_data: this.scenes
        })
      }

      this.$nextTick(() => {
        this.$refs.modal.hide()
      })
    }
  }
}
</script>