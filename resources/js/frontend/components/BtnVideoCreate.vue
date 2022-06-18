<template>
  <div>
    <div
      v-if="
        creationData &&
        (creationData.status == 'working' || creationData.status == 'started')
      "
      style="margin-top: -4px"
    >
      <div class="text-center">{{ creationData.percent }}</div>
      <div class="progress" style="width: 100px">
        <div
          class="progress-bar progress-bar-striped"
          role="progressbar"
          :style="progressStyle"
          aria-valuenow="10"
          aria-valuemin="0"
          aria-valuemax="100"
        ></div>
      </div>
    </div>
    <div v-else class="dropdown mr-2">
      <loadable-button
        text="Create"
        :loading="loading"
        class="btn-primary dropdown-toggle"
        data-toggle="dropdown"
      />
      <div class="dropdown-menu" aria-labelledby="export-menu-button">
        <a href="#" class="dropdown-item" @click.prevent="showModal"
          >Download</a
        >
        <a
          href="#"
          class="dropdown-item"
          @click.prevent="$emit('export-assets')"
          >Download Assets</a
        >
        <a href="/admin/auth/video/shares" class="dropdown-item">Sharing...</a>
      </div>
    </div>

    <div
      class="modal fade"
      id="createModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document" style="width: 46%;">
        <div class="modal-content">
          <modal-header title="Generation Options" />
          <div class="modal-body">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="createOption"
                id="flexRadioDefault1"
                value="0"
                v-model="creationOption"
              />
              <label class="form-check-label" for="flexRadioDefault1">
                Video
              </label>
              <span class="mx-4"></span>
              <input
                class="form-check-input"
                type="checkbox"
                name="createOption"
                id="flexRadioDefault2"
                value="1"
                v-model="creationOption"
              />
              <label class="form-check-label" for="flexRadioDefault2">
                VTT
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                name="createOption"
                id="flexRadioDefault3"
                value="2"
                v-model="creationOption"
                :disabled="!creationOption.includes('0')"
              />
              <label class="form-check-label" for="flexRadioDefault3">
                Burn in captions
              </label>
              <span class="mx-4"></span>
              <input
                class="form-check-input"
                type="checkbox"
                name="createOption"
                id="flexRadioDefault4"
                value="3"
                v-model="creationOption"
                :disabled="!thumbnailPreview"
              />
              <label class="form-check-label" for="flexRadioDefault4">
                Thumbnail
              </label>
            </div>
          </div>
          <div class="modal-footer spacebtwn">
                <select 
                name="videooption"
                v-model="videooption"
                class="form-control rounded "
                style="width: 244px;"
                >
                 <option style="width:100px"> Select </option>
                  <option  
                    v-for="(size, i) in columnTypes"
                    :value="size.value"
                    :key="i"
                    :class="{ 'selected': videooption == size.value}"
                    style="width:100px"
                  >
                    {{ size.text }}
                  </option>
                </select>
                <div class="d-flex">
                    <select  class="form-control" name="Createvideo" v-model="Createvideo" style="width: 144px;">
                      <option  
                    v-for="(size, i) in videoTypes"
                    :value="size.value"
                    :key="i"
                    :class="{ 'selected': Createvideo == size.value}"
                    style="width:100px"
                  >
                    {{ size.text }}
                  </option>
                    </select>
                    <button type="button" class="btn btn-primary mx-1" @click="submit">
                      Create
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-dismiss="modal"
                    >
                      Close
                    </button>
                </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    loading: {
      type: Boolean,
      default: false,
    },
    creationData: {
      type: Object,
      default: () => null,
    },
    previewSizes: {
      type: Object,
      default: () => null,
    },
    thumbnailPreview:{
      type: String,
      default: null,
    }
  },
  data() {
    return {
      creationOption: ["0"],
      videooption:1,
      Createvideo:"MP4",
      columnTypes: [
          { value: 1, text: 'Video 1920x1080',dimensions: '1920x1080'},
          { value: 2, text: 'Instagram 1500x1500',dimensions:'1500x1500' },
          { value: 3, text: 'Instagram Story 1080x1920',dimensions:'1080x1920' },
        ],
      videoTypes: [
          { value: "MP4", text: 'MP4' },
          { value: "GIF", text: 'GIF' },
        ],

    };
  },
  computed: {
    progressStyle() {
      return {
        width: this.creationData.percent,
      };
    },
  },
  methods: {
    showModal() {
      let ps = this.previewSizes;
      var ratio = ps.width / ps.height;
      if(ratio > 1){
        this.videooption = 1; 
      } else if(ratio < 1) {
        this.videooption = 3; 
      } else if(ratio == 1){
        this.videooption = 2; 
      }

      this.creationOption= ["0"];
      $("#flexRadioDefault4:checkbox").prop("checked", false);
   
      $("#createModal").modal("show");
    },
    submit() {
      $("#createModal").modal("hide");
         let vt=this.videooption; 
           const selectdata = this.columnTypes.filter(function(obj) {
              return obj.value == vt;
              });
      this.$emit("submit", this.creationOption,this.Createvideo,selectdata);
    },
    shareModal() {
      console.log("[shareModal]");
    },
  },
};
</script>
<style scoped>
    select option{
    background-color: #ced2d8 !important;
    color: black;
    }
    .spacebtwn{
      justify-content: space-between !important;
    }
</style>
