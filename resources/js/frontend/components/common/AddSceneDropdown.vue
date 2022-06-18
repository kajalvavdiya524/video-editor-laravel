<template>
  <b-dropdown
    size="sm"
    @click="show = !show"
    toggle-class="font-weight-bolder"
    menu-class="custom-dropdown pt-0"
    text="+"
    class=""
    variant="outline-secondary"
  >
    <b-tabs content-class="mt-3" fill style="width: 100%">
      <b-tab title="Default Scene" active>
        <div class="default-scene-items">
          <div
            class="dropdown-item"
            v-for="(option, i) in options"
            :key="i"
            @click="addSceneCallback(option.template)"
          >
            <b-card
              class="m-1  d-flex justify-content-center align-items-center scene-template"
              body-class="m-0 p-0 w-100  d-flex justify-content-center align-content-center align-items-center overflow-hidden"
            >
              <div class="row">
                <div
                  v-for="(type, index) in option.types"
                  :key="type"
                  :class="{
                    [`col-${12 / option.types.length}`]: true,
                    'border-right-custom': (index + 1) !== option.types.length
                  }"
                >{{type}}</div>
              </div>
            </b-card>
          </div>
        </div>
      </b-tab>
      <b-tab title="Custom Scenes">
        <div v-if="scenes.length">
          <div class="default-scene-items">
            <div
              class="dropdown-item"
              v-for="(scene, i) in scenes"
              :key="scene.id"
            >
              <div class="custom-card">
                <b-card
                  class="m-1  d-flex justify-content-center align-items-center scene-template"
                  no-body
                  @click="addSceneUsersCallback(scene.scene_data)"
                >
                  <div class="row">
                    <div class="col-12">
                      <span class="title-text">{{scene.title}}</span>
                    </div>
                  </div>
                </b-card>
                <b-button
                  type
                  v-if="scene.isDelete"
                  class="button-close button-edit"
                  size="sm"
                  @click="closeModalEdit(scene)"
                >
                  <b-icon icon="pencil" aria-hidden="true"></b-icon>
                </b-button>
                <b-button
                  type
                  v-if="scene.isDelete"
                  role="menuitemradio"
                  class="button-close"
                  size="sm"
                  @click="closeModalDelete(scene.id)"
                >
                  <span>×</span>
                </b-button>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-center">
            <b-pagination
                v-if="getPaginationData.total > getPaginationData.per_page"
                v-model="currentPage"
                :total-rows="getPaginationData.total"
                :per-page="getPaginationData.per_page"
                size="sm"
            ></b-pagination>
          </div>
        </div>
        <div v-else class="d-flex justify-content-center align-items-center" style="min-height: 215px">
          <h5>There are no user scene</h5>
        </div>
        <div v-else class="d-flex justify-content-center align-items-center" style="min-height: 215px">
          <h5>There are no user scene</h5>
        </div>
      </b-tab>
    </b-tabs>
    <b-modal v-model="deleteModal" title="Delete Scene" @ok="deleteScene(deleteSceneId)">Are you sure you want to delete this scene?</b-modal>
    <modal-scene-form ref="modal" :edit-scene-data.sync="editScene"></modal-scene-form>
  </b-dropdown>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
import ModalSceneFormal from '../ModalSceneForm'
import { deepCopy } from '../../utils.js'

const { mapGetters: mapGettersScene, mapActions: mapActionsScene } = createNamespacedHelpers('scenes')

export default {
  props: {
    addSceneCallback: {
      required: true,
      default: () => {}
    },
    addSceneUsersCallback: {
      required: true,
      default: () => {}
    }
  },
  components: {
    ModalSceneFormal
  },
  data() {
    return {
      show: false,
      options: [
          {
            types: ['Video'],
            template: {
              "Actions": "",
              "Scene": 1,
              "Subscene": 1,
              "Type": "Video",
              "Name": "",
              "Media": "",
              "Character_Count": "",
              "Left_direction": "",
              "Left": 0,
              "Top": 0,
              "Width": 1,
              "Height": 1,
              "AlignH": "Center",
              "AlignV": "Center",
              "Duration": 5.95,
              "Start": "0",
              "End": "",
              "Font_Name": "",
              "Line_Spacing": 1,
              "Size": 1,
              "Color": "",
              "Kerning": "",
              "Background_Color": "#FFFFFF",
              "Stroke_Width": "",
              "Stroke_Color": "",
              "Animation": "",
              "Animation_duration": "",
              "Props": "",
              "Text": "",
              "Filename": "./samples/add-scene-video.mp4",
              "originalIndex": 2,
              "Original_File_Url": "./samples/add-scene-video.mp4"
            },
          },
          {
            types: ['Image'],
            template: {
              "Actions": "",
              "Scene": 1,
              "Subscene": 1,
              "Type": "Image",
              "Name": "",
              "Media": "",
              "Character_Count": "",
              "Left_direction": "",
              "Left": '',
              "Top": '',
              "Width": 0.12,
              "Height": 0.21,
              "AlignH": "Center-FF",
              "AlignV": "Center",
              "Duration": 6,
              "Start": "0",
              "End": "",
              "Font_Name": "",
              "Line_Spacing": 1,
              "Size": 1,
              "Color": "",
              "Kerning": "",
              "Background_Color": "#FFFFFF",
              "Stroke_Width": "",
              "Stroke_Color": "",
              "Animation": "",
              "Animation_duration": "",
              "Props": "",
              "Text": "",
              "Filename": "./samples/add-scene-image.png",
              "originalIndex": 2
            },
          },
          {
            types: ['Text'],
            template: {
              "Actions": "",
              "Scene": 1,
              "Subscene": 1,
              "Type": "Text",
              "Name": "",
              "Media": "",
              "Character_Count": "",
              "Left_direction": "",
              "Left": '',
              "Top": '',
              "Width": 1,
              "Height": 1,
              "AlignH": "Center",
              "AlignV": "Center",
              "Duration": 6,
              "Start": "0",
              "End": "",
              "Font_Name": "NeueHaasDisplay-Black",
              "Line_Spacing": 1,
              "Size": 100,
              "Color": "black",
              "Kerning": "",
              "Background_Color": "#FFFFFF",
              "Stroke_Width": "",
              "Stroke_Color": "",
              "Animation": "",
              "Animation_duration": "",
              "Props": "",
              "Text": "Text",
              "Filename": "",
              "originalIndex": 2
            },
          },
          {
            types: ['Text', 'Video'],
            template: [
              {
                "Actions": "",
                "Scene": 1,
                "Subscene": 1,
                "Type": "Text",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": "",
                "Width": 0.5,
                "Height": 1,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "NeueHaasDisplay-Black",
                "Line_Spacing": 1,
                "Size": 100,
                "Color": "black",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "Text",
                "Filename": "",
                "originalIndex": 0
              }, {
                "Actions": "",
                "Scene": 1,
                "Subscene": 2,
                "Type": "Video",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": 0.5,
                "Top": 0.25,
                "Width": 0.5,
                "Height": 0.5,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 5.95,
                "Start": "0",
                "End": 8,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-video.mp4",
                "originalIndex": 1,
                "Original_File_Url": "./samples/add-scene-video.mp4"
              }
            ]
          },
          {
            types: ['Video', 'Text'],
            template: [
              {
                "Actions": "",
                "Scene": 1,
                "Subscene": 2,
                "Type": "Video",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": 0.25,
                "Width": 0.5,
                "Height": 0.5,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 5.95,
                "Start": "0",
                "End": 8,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-video.mp4",
                "originalIndex": 1,
                "Original_File_Url": "./samples/add-scene-video.mp4"
              }, {
                "Actions": "",
                "Scene": 1,
                "Subscene": 1,
                "Type": "Text",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": 0.7,
                "Top": "",
                "Width": 0.5,
                "Height": 1,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "NeueHaasDisplay-Black",
                "Line_Spacing": 1,
                "Size": 100,
                "Color": "black",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "Text",
                "Filename": "",
                "originalIndex": 0,
                "Original_File_Url": ""
              },
            ]
          },
          {
            types: ['Text', 'Image'],
            template: [
              {
                "Actions": "",
                "Scene": 1,
                "Subscene": 1,
                "Type": "Text",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": "",
                "Width": 0.5,
                "Height": 1,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "NeueHaasDisplay-Black",
                "Line_Spacing": 1,
                "Size": 100,
                "Color": "black",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "Text",
                "Filename": "",
                "originalIndex": 0
              },
              {
                "Actions": "",
                "Scene": "1",
                "Subscene": "2",
                "Type": "Image",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": "",
                "Width": 0.12,
                "Height": 0.21,
                "AlignH": "Center-RH",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-image.png",
                "originalIndex": 1
              }
            ]
          },
          {
            types: ['Image', 'Text'],
            template: [
              {
                "Actions": "",
                "Scene": "1",
                "Subscene": "2",
                "Type": "Image",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": 0,
                "Top": "",
                "Width": 0.12,
                "Height": 0.21,
                "AlignH": "Center-LH",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-image.png",
                "originalIndex": 1
              },
              {
                "Actions": "",
                "Scene": 1,
                "Subscene": 1,
                "Type": "Text",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": 0.7,
                "Top": "",
                "Width": 0.5,
                "Height": 1,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "NeueHaasDisplay-Black",
                "Line_Spacing": 1,
                "Size": 100,
                "Color": "black",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "Text",
                "Filename": "",
                "originalIndex": 0
              }
            ]
          },
          {
            types: ['Image', 'Video'],
            template: [
              {
                "Actions": "",
                "Scene": 1,
                "Subscene": 1,
                "Type": "Image",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": "",
                "Width": 0.12,
                "Height": 0.21,
                "AlignH": "Center-LH",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-image.png",
                "originalIndex": 0
              }, {
                "Actions": "",
                "Scene": "1",
                "Subscene": "2",
                "Type": "Video",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": 0.5,
                "Top": 0.25,
                "Width": 0.5,
                "Height": 0.5,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 5.95,
                "Start": "0",
                "End": 2,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-video.mp4",
                "originalIndex": 1,
                "Original_File_Url": "./samples/add-scene-video.mp4"
              }
            ]
          },
          {
            types: ['Video', 'Image'],
            template: [
              {
                "Actions": "",
                "Scene": "1",
                "Subscene": "2",
                "Type": "Video",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": 0.25,
                "Width": 0.5,
                "Height": 0.5,
                "AlignH": "Center",
                "AlignV": "Center",
                "Duration": 5.95,
                "Start": "0",
                "End": 2,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-video.mp4",
                "originalIndex": 1,
                "Original_File_Url": "./samples/add-scene-video.mp4"
              }, {
                "Actions": "",
                "Scene": "1",
                "Subscene": "2",
                "Type": "Image",
                "Name": "",
                "Media": "",
                "Character_Count": "",
                "Left_direction": "",
                "Left": "",
                "Top": "",
                "Width": 0.12,
                "Height": 0.21,
                "AlignH": "Center-RH",
                "AlignV": "Center",
                "Duration": 6,
                "Start": "0",
                "End": 5,
                "Font_Name": "",
                "Line_Spacing": 1,
                "Size": 1,
                "Color": "",
                "Kerning": "",
                "Background_Color": "#FFFFFF",
                "Stroke_Width": "",
                "Stroke_Color": "",
                "Animation": "",
                "Animation_duration": "",
                "Props": "",
                "Text": "",
                "Filename": "./samples/add-scene-image.png",
                "originalIndex": 1
              }
            ]
          }
        ],
      rows: 4,
      currentPage: 1,
      deleteModal: false,
      deleteSceneId: null,
      editScene: null
    }
  },
  watch: {
    currentPage(newPage) {
      this.getScenes(newPage)
    },
    getScenesData(data) {
      this.currentPage = data?.current_page || 1
    }
  },
  computed: {
    ...mapGettersScene(['getScenesData','getLoadingData']),
    scenes() {
      return this.getScenesData.data ? this.getScenesData.data : []
    },
    getPaginationData() {
      return {
        total: this.getScenesData.total,
        per_page: this.getScenesData.per_page
      }
    }
  },
  mounted() {
    this.getScenes()
  },
  methods: {
    ...mapActionsScene(['getScenes', 'deleteScene']),
    closeModalDelete(id) {
      this.deleteModal = true
      this.deleteSceneId = id
    },
    closeModalEdit(scene) {
      this.editScene = deepCopy(scene)
      this.$refs.modal.show()
    },
  }
}
</script>

<style lang="scss">
  .scene-template {
    font-size: 12px !important;
    min-width: 120px;
    min-height: 60px
  }

  .custom-dropdown.dropdown-menu {
    width: 434px;
  }

  .default-scene-items {
    display: flex !important;
    justify-content: center;
    flex-wrap: wrap;
  }

  .default-scene-items .dropdown-item {
    padding: 0.2rem 0.3rem;
    width: auto;
  }

  .dropdown-item.active,
  .dropdown-item:active {
    color: #4f5d73;
    text-decoration: none;
    background-color: #ebedef;
  }

  .border-right-custom  {
    position: relative;

    &:after {
      position: absolute;
      content: '';
      width: 1px;
      background-color: rgba(0, 0, 0, 0.125);
      top: -50px;
      border: 0;
      bottom: -50px;
      right: 0;

    }
  }
  .custom-card {
    position: relative;
  }
  .button-close {
      position: absolute;
      display: flex;
      justify-content: center;
      align-items: center;
      right: -10px;
      top: -10px;
      border-radius: 50%;
      padding: 0;
      width: 20px;
      height: 20px;
      line-height: 0;
      background: #ffffff;
      color: #798189;
      font-size: 12px;
  }
  .button-edit {
    right: 12px;
  }
  .button-edit svg {
    font-size: 100% !important;
  }
  .title-text {
      display: block;
      text-align: center;
      width: 100px;
      text-overflow: ellipsis;
      overflow: hidden;
  }
</style>
