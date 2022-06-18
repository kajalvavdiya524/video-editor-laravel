<template>
  <b-modal :id="id" title="Scene Editor" hide-footer no-close-on-backdrop @hide="hidemodal">
    <div class="px-3">
      <div class="drawing-pane">
        <canvas
          id="scene-editor"
          :width="screenWidth"
          :height="screenHeight"
          class="drawing-pane__canvas border border-secondary d-block m-auto"
          style="display: none !important;"
        />
         <canvas
          id="scene-editor-New"
          :width="screenWidth"
          :height="screenHeight"
          class="drawing-pane__canvas border border-secondary d-block m-auto"
        />
        <span
          ref="border"
          class="drawing-pane__border"
          :style="borderStyle"
          @mousemove="moveRect"
          @mousedown="beginMoveRect"
          @mouseup="stopMoveRect"
          @mouseleave="stopMoveRect"
        >
        </span>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <b-button-toolbar
          key-nav
          aria-label="Toolbar with button groups"
          class="my-2"
        >
          <b-button-group class="mx-1">
            <b-button
              size="sm"
              @click="gotoFirst"
              :disabled="!previousSceneEnabled"
              >&laquo;</b-button
            >
            <b-button
              size="sm"
              @click="gotoPrev"
              :disabled="!previousSceneEnabled"
              >&lsaquo;</b-button
            >
          </b-button-group>
          <div class="d-flex align-items-center mx-2">
            <span>{{ scene + 1 }} / {{ totalScene }} </span>
          </div>
          <b-button-group class="mx-1">
            <b-button size="sm" @click="gotoNext" :disabled="!nextSceneEnabled"
              >&rsaquo;</b-button
            >
            <b-button size="sm" @click="gotoLast" :disabled="!nextSceneEnabled"
              >&raquo;</b-button
            >
          </b-button-group>
        </b-button-toolbar>
        <b-button
          size="sm"
          @click="saveSelectedRow"
          :disabled="!isModified"
          variant="primary"
        >
          <i class="cil-save"></i> Save
        </b-button>
      </div>
      <div class="global-property">
        <h3 class="global-property__title">Global Properties</h3>
        <div class="row">
          <div class="col-5">Screen Size</div>
          <div class="col-7">
            <b-form-select
              v-model="screenSelectSize"
              :options="screenSizes"
              size="sm"
              class="w-100"
            ></b-form-select>
          </div>
        </div>
      </div>
      <div class="block-property" v-if="selectedRow">
        <h3 class="block-property__title">Properties</h3>

        <div class="row mb-1">
          <div class="col-5">Type</div>
          <div class="col-7">
            {{ selectedRow.Type }}
          </div>
        </div>

        <div class="row mb-1">
          <div class="col-5">Left</div>

          <b-input-group class="col-7" size="sm">
            <b-form-input
              v-if="isLeftAbsolute"
              v-model="leftAbsolute"
              class="text-right"
            ></b-form-input>
            <b-form-input
              v-else
              class="text-right"
              type="number"
              min="0"
              max="100.00"
              step="any"
              v-model="leftPercent"
            ></b-form-input>
            <b-input-group-append>
              <b-input-group-text
                v-if="isLeftAbsolute"
                @click="handleChangeLeftUnit"
              >
                px
              </b-input-group-text>
              <b-input-group-text v-else @click="handleChangeLeftUnit">
                %
              </b-input-group-text>
            </b-input-group-append>
          </b-input-group>
        </div>
        <div class="row mb-1">
          <div class="col-5">Top</div>
          <b-input-group class="col-7" size="sm">
            <b-form-input
              v-if="isTopAbsolute"
              v-model="topAbsolute"
              class="text-right"
            ></b-form-input>
            <b-form-input
              v-else
              class="text-right"
              type="number"
              min="0"
              max="100.00"
              step="any"
              v-model="topPercent"
            ></b-form-input>
            <b-input-group-append>
              <b-input-group-text
                v-if="isTopAbsolute"
                @click="handleChangeTopUnit"
              >
                px
              </b-input-group-text>
              <b-input-group-text v-else @click="handleChangeTopUnit">
                %
              </b-input-group-text>
            </b-input-group-append>
          </b-input-group>
        </div>
        <div class="row mb-1">
          <div class="col-5">Width</div>
          <b-input-group class="col-7" size="sm">
            <b-form-input
              v-if="isWidthAbsolute"
              class="text-right"
              v-model="widthAbsolute"
            ></b-form-input>
            <b-form-input
              v-else
              class="text-right"
              type="number"
              min="0"
              max="100.00"
              step="any"
              v-model="widthPercent"
            ></b-form-input>
            <b-input-group-append>
              <b-input-group-text
                v-if="isWidthAbsolute"
                @click="handleChangeWidthUnit"
              >
                px
              </b-input-group-text>
              <b-input-group-text v-else @click="handleChangeWidthUnit">
                %
              </b-input-group-text>
            </b-input-group-append>
          </b-input-group>
        </div>

        <div class="row mb-1">
          <div class="col-5">Height</div>
          <b-input-group class="col-7" size="sm">
            <b-form-input
              v-if="isHeightAbsolute"
              v-model="heightAbsolute"
              class="text-right"
            ></b-form-input>
            <b-form-input
              v-else
              class="text-right"
              type="number"
              min="0"
              max="100.00"
              step="any"
              v-model="heightPercent"
            ></b-form-input>
            <b-input-group-append>
              <b-input-group-text
                v-if="isHeightAbsolute"
                @click="handleChangeHeightUnit"
              >
                px
              </b-input-group-text>
              <b-input-group-text v-else @click="handleChangeHeightUnit">
                %
              </b-input-group-text>
            </b-input-group-append>
          </b-input-group>
        </div>
      </div>
    </div>{{canvasCheck}}
  </b-modal>
</template>
<script>
import ScenePreview from "../../ScenePreview";
import { delay } from "../../utils";
const promiseRetry = require("promise-retry");
import { mapState, mapActions, mapGetters } from "vuex";

 
export default {
  props: {
    modalstatus:{
      type: Boolean,
      default:false,
    },
    size: {
      type: Object,
      default: () => ({ width: 0, height: 0 }),
    },
    sizes: {
      type: Array,
      default: () => [{ width: 426, height: 240 }],
    },
    id: {
      type: String,
      default: "edit-scene-dialog",
    },
    rows: {
      type: Array,
      default: () => [],
    },
    totalScene: {
      type: Number,
      default: 0,
    },
  }, 
  data() {
    return {
      selectTextLeft:0,
      selectTextTop:0,
      NewCanvas:null,
      modalcanvascall:0,
      showselectedRow:0,
      loadingAssets: false,
      isModalstatus:false,
      scene: 0,
      preview: null,
      previewTime: 0,
      previewDuration: 0,
      curRows: [],
      border: null,
      borderSize: {
        width: 0,
        height: 0,
        left: 0,
        top: 0,
      },
      moveActive: false,
      prevPoint: null,
      isModified: false,
      screenSelectSize: "",
      selectedRow: null,
      rowObject: {},
    };
  },
  mounted() {
    this.initPreview();
    this.getscene();
    this.preview.setRS(this.getScaleFactor(426));
    this.gotoFirst();
    this.screenSelectSize = `${this.size.width}-${this.size.height}`;
    this.removelocalStorage();
  },
  methods: {
        ...mapGetters('sceneDialog',["getscene"]),
        ...mapActions("sceneDialog", ["InsertScene"]),

    removelocalStorage(){
      localStorage.removeItem("left");
    },
    hidemodal(){
      this.modalcanvascall=0;
      this.showselectedRow=0;
      this.$emit('clicked', false);
    },
    checktextmoment(){
       this.selectTextLeft=0; 
        this.selectTextTop=0;
    },
    initPreview() {
      this.preview = new ScenePreview();
      this.animationRequestId = requestAnimationFrame(this.renderTime);
    },
    getScaleFactor(w) {
      return (1.0 * w) / window.screen.width;
    },
    updateState() {
      this.playingState = this.preview.state;
    },
    renderTime() {
      this.previewTime = this.preview.currentTime;
      this.updateState();

      this.animationRequestId = requestAnimationFrame(this.renderTime);
    },
    parseRows(rows) {
      rows.forEach((r) => {
        r.End = +r.Start + +r.Duration;
      });
    },

    async loadAssets() {
      try {
        if (this.selectedRow) {
          const cloneRow = JSON.parse(JSON.stringify(this.selectedRow));

          // Simple example
          await promiseRetry({ retries: 3, randomize: true }, (retry) => {
            return this.preview.loadAssets().catch(retry);
          });
          if(this.modalcanvascall===0){
            this.modalcanvascall=1;
          }else{
            this.modalcanvascall=2;
          }
  
          await this.preview.init(cloneRow, "scene-editor",this.modalcanvascall);          
        }
      } catch (error) {
        console.log("[loadAssets]", error);
      }
    },

    async start(allScene = true) {
      try {
        this.loadingAssets = true;
        await this.loadAssets();
          this.preview.parseScene(
          this.previewDuration,
          this.borderedWidth,
          this.borderedHeight,
          this.borderSize.left,
          this.borderSize.top,
          this.selectTextLeft,
          this.selectTextTop);
        if (allScene) {
          this.$nextTick(async () => {
            await delay(50);
            this.preview.play();
            this.updateState();
          });
        }
      } catch (error) {
        console.log("[start]", error);
      } finally {
        this.loadingAssets = false;
      }
    },

    saveSelectedRow() {
      if (this.selectedRow) {
        if (this.$emit("update-row", this.$store.state.sceneDialog.sceneEditore)) {
          this.isModified = false;
        }
      }
    },

    async gotoFirst() {
      let that=this;
      that.scene = 0;

      setTimeout(function(){
       that.gotoborderStyle('gofirst');
        that.drawSelectedRow();
                }, 100);
    },

    async gotoPrev() {
      let that=this;
      if (that.previousSceneEnabled) {
        that.scene--;
         setTimeout(function(){
          that.gotoborderStyle('prev');
          that.drawSelectedRow();
           }, 100);
      }
    },
    async gotoLast() {
      let that=this;
      that.scene = that.totalScene - 1;
              setTimeout(function(){
              that.gotoborderStyle('golast');
              that.drawSelectedRow();
              }, 100);

    },
    async gotoNext() {
     let that=this;
      if (that.nextSceneEnabled) {
        that.scene++;
        setTimeout(function(){
         that.gotoborderStyle('next');
         that.drawSelectedRow();
        }, 100);

      }
    },

    pause() {
      if (this.preview) {
        this.preview.pause();
      }
    },
    async drawSelectedRow() {
      if (this.selectedRow) {
        this.isModified = true;
        const { Top, Left, Width, Height } = this.selectedRow;
        this.borderSize = {
          left: Left > 1.0 ? Left : Left * this.screenWidth,
          top: Top * 100,
          width: Width > 1.0 ? Width : Width * this.screenWidth,
          height: Height > 1.0 ? Height : Height * this.screenHeight,
        };
        await this.start(false);
        this.pause();
      }
    },
    async updateRowPosition(dx, dy) {
      try {
        if (this.selectedRow) {
          const { Left, Top, Width, Height, ...rest } = this.selectedRow;
          const screenWidth = this.screenWidth;
          const screenHeight = this.screenHeight;
          const deltaX = Left > 1.0 ? +dx : dx / screenWidth;
          const deltaY = Top > 1.0 ? +dy : dy / screenHeight;
          
          const Topcount=Top + deltaY;
          const LeftCount=Left + deltaX;

          if(LeftCount > 1 || Topcount > 1 ){

                if(LeftCount >1){
                    let leftcnt=1.0;
                }else{
                    let leftcnt=Left + deltaX;
                }

                if(Topcount > 1){
                    let topcnt=1.0;
                }else{
                    let topcnt=Top + deltaY;
                }

              this.selectedRow = {
                ...rest,
                Left: leftcnt,
                Top: topcnt,
                Width: +Width,
                Height: +Height,
              };

           }else{
            this.selectedRow = {
                ...rest,
                Left: +Left + deltaX,
                Top: +Top + deltaY,
                Width: +Width,
                Height: +Height,
              };
           }  

          this.InsertScene(this.selectedRow);
          this.drawSelectedRow();
        }
      } catch (error) {}
    },
    moveRect(e) {
      if (this.moveActive) {
        const { clientX: prevClientX, clientY: prevClientY } = this.prevPoint;
        this.selectTextLeft=0; 
        this.selectTextTop=0;

        this.updateRowPosition(
          +e.clientX - prevClientX,
          +e.clientY - prevClientY
        );

        this.prevPoint = { clientX: e.clientX, clientY: e.clientY };
      }
    },
    beginMoveRect(e) {
      this.moveActive = true;
      this.prevPoint = { clientX: e.clientX, clientY: e.clientY };
    },
    stopMoveRect(e) {
      this.moveActive = false;
      this.prevPoint = null;
    },

    handleChangeWidthUnit() {
      const { width: screenWidth } = this.screenSize;
      const Width = +this.selectedRow.Width;

      if (this.isWidthAbsolute) {
        this.selectedRow.Width = Math.min(Width / screenWidth, 1.0);
      } else {
        this.selectedRow.Width = Width * screenWidth;
      }
    },
    handleChangeHeightUnit() {
      const { height: screenHeight } = this.screenSize;
      const Height = +this.selectedRow.Height;

      if (this.isHeightAbsolute) {
        this.selectedRow.Height = Math.min(Height / screenHeight, 1.0);
      } else {
        this.selectedRow.Height = Height * screenHeight;
      }
    },
    handleChangeLeftUnit() {
      const { width: screenWidth } = this.screenSize;
      const Left = +this.selectedRow.Left;
      if (this.isLeftAbsolute) {
        this.selectedRow.Left = Math.min(Left / screenWidth, 1.0);
      } else {
        this.selectedRow.Left = Left * screenWidth;
      }
    },
    handleChangeTopUnit() {
      const { height: screenHeight } = this.screenSize;
      const Top = +this.selectedRow.Top;

      if (this.isTopAbsolute) {
        this.selectedRow.Top = Math.min(Top / screenHeight, 1.0);
      } else {
        this.selectedRow.Top = Top * screenHeight;
      }
    },

   async gotoborderStyle(check){

      if (localStorage.getItem("left") != null) {
      let selectindex=this.selectedRow.originalIndex ;
      let showalldata=JSON.parse(localStorage.getItem('left'));
          const selectdata = showalldata.filter(function(obj) {
              return obj.originalIndex == selectindex; 
              });
              if(selectdata[0]['Text']){
                this.borderSize.top=selectdata[0]['Top'];
                this.borderSize.left=selectdata[0]['Left'];   

                this.selectTextLeft=selectdata[0]['Left']; 
                this.selectTextTop=selectdata[0]['Top'];
              }else{
                this.selectTextLeft=0; 
                this.selectTextTop=0;   
              }
        }
    },
  },
  computed: {
    canvasCheck(){
      let that=this;
        if(that.modalstatus){
          that.isModalstatus=true;
          if(that.showselectedRow===0){
              that.showselectedRow=1;
              that.gotoNext();
              setTimeout(function(){
              that.gotoPrev();
                  }, 100);
            }
        }
    },
    screenSize() {
      const [width, height] = this.screenSelectSize.split("-");
      return {
        width: +width,
        height: +height,
      };
    },
    isWidthAbsolute() {
      return Math.abs(this.selectedRow.Width) > 1.0;
    },
    widthPercent: {
      get: function () {
        return (this.selectedRow.Width * 100).toFixed(0);
      },
      set: function (m) {
        this.selectedRow.Width = +m / 100;
        this.checktextmoment();
        this.drawSelectedRow();
      },
    },
    widthAbsolute: {
      get: function () {
        return (+this.selectedRow.Width).toFixed(0);
      },
      set: function (m) {
        if (Math.abs(m) < 1.0) m = 1.0;
        else if (m == 0) m = 0;
        this.selectedRow.Width = m;
        this.drawSelectedRow();
      },
    },
    isHeightAbsolute() {
      return Math.abs(this.selectedRow.Height) > 1.0;
    },
    heightPercent: {
      get: function () {
        return (this.selectedRow.Height * 100).toFixed(0);
      },
      set: function (m) {
        this.selectedRow.Height = +m / 100;
        this.checktextmoment();
        this.drawSelectedRow();
      },
    },
    heightAbsolute: {
      get: function () {
        return (+this.selectedRow.Height).toFixed(0);
      },
      set: function (m) {
        if (Math.abs(m) < 1.0) m = 1.0;
        else if (m == 0) m = 0;
        this.selectedRow.Height = m;
        this.drawSelectedRow();
      },
    },
    isLeftAbsolute() {
      return Math.abs(this.selectedRow.Left) > 1.0;
    },
    leftPercent: {
      get: function () {
        return (this.selectedRow.Left * 100).toFixed(0);
      },
      set: function (m) {
        this.selectedRow.Left = +m / 100;
        this.checktextmoment();
        this.drawSelectedRow();
      },
    },
    leftAbsolute: {
      get: function () {
        return (+this.selectedRow.Left).toFixed(0);
      },
      set: function (m) {
        if (Math.abs(m) < 1.0) m = 1.0;
        else if (m == 0) m = 0;
        this.selectedRow.Left = m;
        this.checktextmoment();
        this.drawSelectedRow();
      },
    },

    isTopAbsolute() {
      return Math.abs(this.selectedRow.Top) > 1.0;
    },
    topPercent: {
      get: function () {
        return (this.selectedRow.Top * 100).toFixed(0);
      },
      set: function (m) {
        this.selectedRow.Top = +m / 100;
        this.checktextmoment();
        this.drawSelectedRow();
      },
    },
    topAbsolute: {
      get: function () {
        return (+this.selectedRow.Top).toFixed(0);
      },
      set: function (m) {
        if (Math.abs(m) < 1.0) m = 1.0;
        else if (m == 0) m = 0;
        this.selectedRow.Top = m;
        this.drawSelectedRow();
      },
    },

    screenWidth() {
      return this.screenSize.width;
    },
    screenHeight() {
      return this.screenSize.height;
    },

    borderStyle() {
      return `
        width: ${this.borderedWidth}px;
        height: ${this.borderedHeight}px;
        left: ${this.borderedLeft}px;
        top: ${this.borderedTop}px;
        visibility: ${
          this.borderedWidth > 0 && this.borderedHeight > 0
            ? "visible"
            : "hidden"
        };
      `;
    },
    borderedWidth() {
      const calcWidth = +this.borderSize.width;
      return calcWidth > 0 ? calcWidth + 4 : 0;
    },
    borderedHeight() {
      const calcHeight = +this.borderSize.height;
      return calcHeight > 0 ? calcHeight + 2 : 0;
    },
    borderedLeft() {
      return +this.borderSize.left + 2;
    },
    borderedTop() {
          if(this.selectTextLeft==0){
            if(this.selectedRow !=null){
                if(this.selectedRow.Type==="Image"){
                    let imgtp=this.selectedRow.Top * this.screenHeight;
                    console.log('imgtp',imgtp);
                    return this.borderSize.top=imgtp;
                }else{
                    return +this.borderSize.top;
                }
            }
              return +this.borderSize.top;
          }else{
            if(this.selectedRow.Type==="Image"){
                let imgtp=this.selectedRow.Top * this.screenHeight;
                return this.borderSize.top=imgtp;
            }else{
               if(this.selectTextTop % 1 !=0){
                let sizetop= this.selectTextTop;
                return this.borderSize.top=sizetop;
              }else{
                return this.borderSize.top=this.selectTextTop;
              }
            }
          }
    },
    previousSceneEnabled() {
      return this.scene > 0;
    },
    nextSceneEnabled() {
      return this.scene < this.totalScene - 1;
    },
    screenSizes() {
      return this.sizes.map((v) => ({
        value: `${v.width}-${v.height}`,
        obj: v,
        text: `${v.width} x ${v.height}`,
      }));
    },
  },
  watch: {
    scene: async function (newScene) {
      try {
        this.selectedRow = { ...this.rows[newScene], Start: 0, End: 1 };
        this.drawSelectedRow();
        this.isModified = false;
      } catch (e) {
        console.log("[catch]", e);
      }
    },    
  },
};
</script>
<style lang="scss" scoped>
.drawing-pane {
  position: relative;
  &__border {
    border: 1px solid red;
    background: transparent;
    position: absolute;
    z-index: 1;
    cursor: pointer;
    visibility: hidden;
  }
}
.block-property {
  margin-top: 10px;

  &__title {
    font-size: 1.25rem;
  }
  .row {
    height: 30px;
    margin: 5px auto;
  }
  .col {
    padding-left: 0;
  }
}

.global-property {
  margin-top: 10px;
  margin-bottom: 10px;
  &__title {
    font-size: 1.25rem;
  }
}
.drawing-pane {
  overflow: hidden;
}
.scene-editor-New{
  overflow:hidden;
}

</style>
