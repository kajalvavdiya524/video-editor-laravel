<template>
  <div class="fade-in">
    <div class="row">
      <div class="col">
        <div class="d-flex flex-column align-items-center">
          <div>
            <div id="test-text"></div>
            <img id="test-img" src="" />
            <preview-screen
              :loading="loadingAssets"
              :size="prevSizeIdx"
              :sizes="previewSizes"
            />
            <div ref="slideContainer" class="slide-container">
              <input
                v-model="previewTime"
                type="range"
                min="1"
                :max="preview && preview.vCtx ? preview.vCtx.duration : 10"
                step="0.02"
                class="slider"
                @mousedown="onTimelineDown"
                @mouseup="onTimelineUp"
              />
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <div class="d-flex align-items-center">
                <select
                  class="form-control"
                  v-model="prevSizeIdx"
                  style="width: 160px"
                  @change="onCanvasSizeChange"
                >
                  <option
                    v-for="(size, i) in previewSizes"
                    :value="size.value"
                    :key="i"
                  >
                    {{ size.width }} x {{ size.height }}
                  </option>
                </select>
                <button
                  type="button"
                  class="btn btn-primary btn-media ml-1"
                  :disabled="modelRows.length < 1 || loadingAssets"
                  @click="playBtnClicked"
                >
                  <i
                    :class="
                      playBtnLabel == 'Pause'
                        ? 'cil-media-pause'
                        : 'cil-media-play'
                    "
                  ></i>
                </button>
                <button
                  type="button"
                  class="btn btn-stop btn-primary btn-media ml-1"
                  :disabled="!playBtnLabel"
                  @click="stop"
                >
                  <i class="cil-media-stop"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-stop btn-primary btn-media ml-1"
                  @click="openEditSceneDialog"
                  :disabled="!playBtnLabel"
                >
                  <i class="cil-control"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-stop btn-primary btn-media ml-1 d-flex"
                  :class="caption ? 'captionActive':'' "
                  @click="captionstatus"
                  :disabled="!playBtnLabel"
                  title="Show/hide captions"
                >
                  <i class="cil-closed-captioning justify-content-center d-flex align-items-center" style="font-size:19px;" ></i>
                </button>
                <button
                  v-if="playBtnLabel == 'Resume' && preview.currentTime > 0"
                  type="button"
                  class="btn btn-primary btn-media ml-1"
                  @click="scrollCurrentRow"
                  title="Jump Video"
                >
                  <img :src="sceneJumpImage" style="width: 20px">
                </button>
                <div class="ml-5" >
                <i class="fas fa-volume-up" :disabled="!playBtnLabel" v-if="volumnMute" @click="updateicon('up')" style="color: black;cursor: pointer;"></i>
                 <i class="fas fa-volume-mute" v-else @click="updateicon('mute')" style="color: black;cursor: pointer;"></i>
                <input type="range" :disabled="!playBtnLabel" id="CanvasVolumn" min="0" max="100" step="1" v-model="CanvasVolumn" @input="updateSlider" :style="{backgroundSize: backgroundSize}">
                </div>
              </div>
              <span style="width: 61px;">{{ previewTimeLabel }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-3 row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <input
                class="form-control"
                id="output"
                type="text"
                v-model="outputFile"
                style="width: 320px"
              />
              <div  class="ml-2 my-tooltip" title="Thumbnail…" style="white-space: nowrap;font-size: 20px;cursor: pointer; " :disabled="!playBtnLabel" @click="modalThambile" >
                <img v-if="thumbnailPreview" :src="thumbnailPreview" class="mx-1" width="22" height="22" />
                  <i v-else class="fas fa-image fa-xl"></i>
                  <div class="tooltiptext" v-if="thumbnailPreview" >
                    <div class="tooltip-upload" >

                        <a :href="thumbnailPreview" title="Download" download=""  class="ml-2 download-filename"  style="cursor: pointer; margin-bottom: -4px;font-size: 16px; ">
                      <i class="cil-cloud-download font-weight-bold"></i>
                    </a>
                        </div>
                <img :src="thumbnailPreview" class="img-fluid"  style="max-width: 320px; max-height: 320px;background: #fff;" />
                </div>
              </div>
              <span class="ml-2" style="white-space: nowrap">{{
                overviewLabel
              }}</span>
            </div>
            <div class="card-header-actions d-flex">
              <btn-visibility
                class="mr-2"
                :visibilities="columnVisibilityTypes"
                :selected-column-visibility-name="selectedColumnVisibilityName"
                :custom-columns="locCustomColumns"
                @name-change="updateColumnVisibilityName"
                @show-modal="showCustomVisibilityModal"
              />
              <btn-template
                :loading="loadingTemplateBtn"
                :selected-template="selectedTemplate"
                :templates="templates"
                class="mr-2"
                @load="beforeLoad"
                @export="_exportTemplate"
                @before-save="beforeSavingTemplate"
              />
              <btn-theme
                :themes="themes"
                :selected-theme="selectedTheme"
                class="mr-2"
                @change="onThemeChanged"
              />
              <btn-project-drafts
                :projects="projectsDrafts"
                :selected-project="selectedProject"
                :loading="loadingProject"
                class="mr-2"
                @save="beforeSaveProject(true)"
                @change="beforeLoad"
                @new="beforeNewRow"
                @import="_importFile"
              />
              <btn-project
                :projects="projects"
                :selected-project="selectedProject"
                :loading="loadingProject"
                class="mr-2"
                @save="beforeSaveProject()"
                @change="beforeLoad"
                @new="beforeNewRow"
                @import="_importFile"
              />

              <btn-video-create
                :loading="loadingCreateBtn"
                :creation-data="locCreationData"
                :preview-sizes="previewSizes[this.prevSizeIdx]"
                :thumbnail-preview="thumbnailPreview"
                @submit="_createVideo"
                @export-assets="_exportAssets"
              />
            </div>
          </div>

          <div class="card-body" style="z-index:0">
            <my-vue-good-table
              :columns="tableColumns"
              :rows="groupedRows"
              :sort-options="{ enabled: false }"
              :line-numbers="true"
              :row-style-class="rowClassFn"
              :group-options="{
                enabled: true,
                collapsable: true,
              }"
              :styleClass="styleClass"
              :addSceneCallback="(scene) => addScene(scene)"
              :addSceneUsersCallback="(scene) => addUserScene(scene)"
            >
              <template slot="table-header-row" slot-scope="props">

                <template v-if="props.column.field == 'Actions'">
                  <img :src="deleteIcon" class="img-fluid delete-icon mr-2" @click="showParentRowDeleteConfirmation(props.row.children)">
                  <span class="fa-stack" @click="showSceneVideo(props.row.children)" style="cursor: pointer;">
                    <i class="fa fa-circle fa-stack-2x"></i>
                    <i class="fa fa-play fa-stack-1x fa-inverse"></i>
                </span>
                </template>

                <span v-if="props.column.field == 'Type'">
                  {{ props.row.isStar ? "Review" : props.row.type }}
                </span>
                <div
                  v-if="
                    props.column.field == 'Media' && props.row.type == 'Text'
                  "
                  class="top-text-area"
                >
                  <textarea
                    class="form-control form-control-sm"
                    style="overflow-x: auto; white-space: nowrap"
                    :rows="props.row.children.length"
                    v-model="props.row.multiText"
                    @input="
                      updateTextModelRows(
                        props.row.children,
                        props.row.multiText
                      )
                    "
                  />
                  <a
                    class="star"
                    data-toggle="tooltip"
                    data-placement="right"
                    title="Insert Star"
                    @click="
                      changeCell(
                        '★★★★★',
                        'Text',
                        props.row.children[props.row.children.length - 1]
                          .originalIndex
                      )
                    "
                  >
                    <i class="cil-star" />
                  </a>
                </div>
                <div
                  v-if="
                    props.column.field == 'Media' && props.row.type == 'Image'
                  "
                >
                  <div
                    v-for="(child, i) in props.row.children"
                    :key="i"
                    class="my-tooltip"
                  >
                  <div v-if="child.Type == 'Image'">
                    <img
                        :src="child.Filename"
                        class="mx-1 img-thumb"
                        width="20"
                        height="20"
                        @error="onImageNotFound"
                      />
                      <div class="tooltiptext">
                        <div v-if="isMedia('Image')" class="tooltip-upload">
                          <input
                            type="file"
                            :id="`upload-btn-${child.originalIndex}`"
                            hidden
                            @change="fileInputChanged($event, child)"
                          />
                          <label
                            :for="`upload-btn-${child.originalIndex}`"
                            data-toggle="tooltip"
                            title="Upload"
                            style="
                              cursor: pointer;
                              margin-bottom: -4px;
                              font-size: 16px;
                            "
                          >
                            <i class="cil-cloud-upload font-weight-bold"></i>
                          </label>
                          <library-img
                            type="Image"
                            @click.native="showLibraryModal(child)"
                          />
                        </div>
                        <img
                          :src="child.Filename"
                          style="max-width: 320px; max-height: 320px"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div
                  v-if="
                    props.column.field == 'Media' && props.row.type == 'Video'
                  "
                >
                  <div
                    v-for="(child, i) in props.row.children"
                    :key="i"
                    class="my-tooltip"
                  >
                    <div v-if="child.Type == 'Video'">
                      <video
                        v-for="(child, i) in props.row.children"
                        :key="i"
                        width="60"
                        height="60"
                        muted
                        :src="child.Filename"
                      >
                      </video>

                      <div class="tooltiptext">
                        <div v-if="isMedia('Video')" class="tooltip-upload">
                          <input
                            type="file"
                            :id="`upload-btn-${props.row.originalIndex}`"
                            hidden
                            @change="fileInputChanged($event, props.row)"
                          />

                          <label
                            :for="`upload-btn-${props.row.originalIndex}`"
                            data-toggle="tooltip"
                            title="Upload"
                            style="
                              cursor: pointer;
                              margin-bottom: -4px;
                              font-size: 16px;
                            "
                          >
                            <i class="cil-cloud-upload font-weight-bold"></i>
                          </label>
                          <library-img
                            type="Video"
                            @click.native="showLibraryModal(child)"
                          />
                        </div>
                        <video
                          :key="child.Filename"
                          max-width="320"
                          max-height="320"
                          autoplay="true"
                          muted
                          loop
                        >
                          <source :src="child.Filename" type="video/mp4" />
                        </video>
                      </div>
                    </div>
                  </div>
                </div>
                <template
                  v-if="
                    props.column.group_editable &&
                    visibleFor(props.column, props.row.type)
                  "
                >
                  <select
                    v-if="props.column.input_type == 'select'"
                    v-model="props.row.editableRow[props.column.field]"
                    class="form-control form-control-sm"
                    @change="
                      updateChildrenModelRows(
                        props.row.editableRow[props.column.field],
                        props.column.field,
                        props.row.children
                      )
                    "
                  >
                    <option
                      v-for="(option, i) in selectionOptions(props)"
                      :key="i"
                      :value="option.value == undefined ? option : option.value"
                      :style="
                        props.column.field == 'Font_Name'
                          ? `font-family: ${option}`
                          : ''
                      "
                    >
                      {{ option.text || option }}
                    </option>
                  </select>
                  <input
                    v-else-if="props.column.input_type == 'formatted-time'"
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    type="text"
                    pattern="[0-5][0-9]:[0-5][0-9]|[0-5][0-9]:[0-5][0-9].[0-5][0-9]$"
                    required
                    :readonly="isReadonly(props.column.field)"
                    :value="
                      timeFormattedStringWithDecimal(
                        props.row.editableRow[props.column.field]
                      )
                    "
                    :disabled="
                      !hasSameValues(props.row.children, props.column.field)
                    "
                    class="form-control form-control-sm"
                    @input="
                      updateTimeChildrenModelRows(
                        $event,
                        props.column.field,
                        props.row.children
                      )
                    "
                  />
                  <input
                    v-else-if="props.column.input_type == 'number'"
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    v-model="props.row.editableRow[props.column.field]"
                    :type="props.column.input_type"
                    class="form-control form-control-sm"
                    step="any"
                    @change="updateChildrenModelRows(
                      props.row.editableRow[props.column.field],
                      props.column.field,
                      props.row.children
                    )"
                  />
                  <div
                    v-else-if="props.column.input_type == 'percent'"
                    class="input-group input-group-sm
                    "
                  >
                    <input
                      :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                      type="number"
                      :value="getPercentInputValue(props.row.editableRow[props.column.field], props.row)"
                      class="form-control"
                      min="0"
                      :max="props.row.Type === 'Video' ? '1000' : '100'"
                      step=".01"
                      @change="updatePercentChildrenModelRows($event, props.column.field, props.row.children)"
                    />
                    <div class="input-group-append">
                      <span
                        class="btn input-group-text"
                      >
                        %
                      </span>
                    </div>
                  </div>
                  <input
                    v-else
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    v-model="props.row.editableRow[props.column.field]"
                    :type="props.column.input_type"
                    class="form-control form-control-sm"
                    @change="
                      updateChildrenModelRows(
                        props.row.editableRow[props.column.field],
                        props.column.field,
                        props.row.children
                      )
                    "
                  />
                </template>
              </template>
              <template slot="table-column" slot-scope="props">
                <dropdown-select
                  v-if="props.column.field == 'Type'"
                  :items="columnTypes"
                  v-model="sceneType"
                  item-value="value"
                  item-text="text"
                />
                <span v-else>
                  {{ props.column.label }}
                </span>
              </template>
              <template slot="table-row" slot-scope="props">
                <template v-if="props.column.field == 'Actions'">
                  <b-dropdown text="Actions" variant="primary" size="sm">
                    <b-dropdown-item
                      @click.prevent="
                        insertScene(props.row.originalIndex, 'below')
                      "
                      >Add Scene Below</b-dropdown-item
                    >
                    <b-dropdown-item
                      @click.prevent="onCopySubscene(props.row.originalIndex)"
                      >Copy Subscene</b-dropdown-item
                    >
                    <b-dropdown-item
                      @click.prevent="
                        addSubscene(props.row.originalIndex, props.row)
                      "
                      >Add Subscene</b-dropdown-item
                    >
                    <btn-action-save-scene :model-rows="modelRows" :scene-index="props.row.Scene" />
                    <b-dropdown-item
                      @click.prevent="
                        onDeleteRowClicked(props.row.originalIndex)
                      "
                      >Delete</b-dropdown-item
                    >
                  </b-dropdown>
                </template>

                <template v-else-if="props.column.field == 'Media'">
                  <div v-if="isMedia(props.row.Type)">
                    <template v-if="isMedia(props.row.Type)">
                      <input
                        type="file"
                        :id="`upload-btn-${props.row.originalIndex}`"
                        hidden
                        @change="fileInputChanged($event, props.row)"
                      />
                      <label
                        :for="`upload-btn-${props.row.originalIndex}`"
                        data-toggle="tooltip"
                        title="Upload"
                        style="
                          cursor: pointer;
                          margin-bottom: -4px;
                          font-size: 16px;
                        "
                      >
                        <i class="cil-cloud-upload font-weight-bold"></i>
                      </label>
                    </template>
                    <library-img
                      v-if="isMedia(props.row.Type)"
                      :type="props.row.Type"
                      @click.native="showLibraryModal(props.row)"
                    />
                    <i
                      v-if="props.row.Type == 'Image'"
                      class="cil-crop-rotate font-weight-bold ml-2 cursor-pointer"
                      @click="showCropModal(props.row)"
                    />
                    <div class="my-tooltip">
                      <i :ref="`showTrimButton-${props.row.originalIndex}`"
                         v-if="props.row.Type === 'Video' && props.row.Filename"
                         class="cil-crop-rotate font-weight-bold ml-2 cursor-pointer"
                         @click="showTrimVideoModal(props.row)"
                      />
                      <a
                        v-if="props.row.Filename"
                        :href="props.row.Filename"
                        download
                        class="ml-2 download-filename"
                      >
                        {{ fileNameText(props.row) }}
                      </a>
                      <img
                        v-if="props.row.Type == 'Image'"
                        :src="props.row.Filename"
                        class="tooltiptext"
                        width="20"
                        height="20"
                        @error="onImageNotFound"
                      />
                      <video
                        v-if="props.row.Type == 'Video'"
                        :key="props.row.Filename"
                        max-width="320"
                        max-height="320"
                        class="tooltiptext"
                        autoplay="true"
                        muted
                        loop
                      >
                        <source :src="props.row.Filename" type="video/mp4" />
                      </video>
                    </div>
                  </div>
                  <span v-else-if="isEffectType(props.row.Type)"></span>
                  <input
                    v-else
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    type="text"
                    v-model="props.row.Text"
                    class="form-control form-control-sm"
                    @change="
                      changeCell(
                        props.row.Text,
                        'Text',
                        props.row.originalIndex
                      )
                    "
                  />
                </template>
                <span v-else-if="visibleFor(props.column, props.row.Type)">
                  <select
                    v-if="props.column.input_type == 'select'"
                    v-model="props.row[props.column.field]"
                    class="form-control form-control-sm"
                    @change="
                      changeCell(
                        props.row[props.column.field],
                        props.column.field,
                        props.row.originalIndex
                      )
                    "
                  >
                    <option
                      v-for="(option, i) in selectionOptions(props)"
                      :key="i"
                      :value="option.value == undefined ? option : option.value"
                      :style="
                        props.column.field == 'Font_Name'
                          ? `font-family: ${option}`
                          : ''
                      "
                    >
                      {{ option.text || option }}
                    </option>
                  </select>

                  <div
                    v-else-if="props.column.input_type == 'percent'"
                    class="input-group input-group-sm"
                  >
                    <input
                      :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                      type="number"
                      :value="getPercentInputValue(props.row[props.column.field], props.row)"
                      class="form-control"
                      :min="['Image'].includes(props.row.Type ) && ['Top', 'Left'].includes(props.column.field) ? '-1000' : '0'"
                      :max="['Video', 'Image'].includes(props.row.Type) ? '1000' : '100'"
                      step=".01"
                      @change="updatePercentInputValue($event, props.row.originalIndex, props.column.field)"
                    />
                    <div class="input-group-append">
                      <span
                        class="btn input-group-text"
                      >
                        %
                      </span>
                    </div>
                  </div>
                  <input
                    v-else-if="props.column.input_type == 'formatted-time'"
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    type="text"
                    pattern="[0-5][0-9]:[0-5][0-9]|[0-5][0-9]:[0-5][0-9].[0-5][0-9]$"
                    required
                    :value="timeFormattedStringWithDecimal(props.row[props.column.field])"
                    class="form-control form-control-sm"
                    :readonly="isReadonly(props.column.field)"
                    @input="
                      changeTimeCell(
                        $event,
                        props.column.field,
                        props.row.originalIndex
                      )
                    "
                  />
                  <input
                    v-else-if="props.column.input_type == 'line_spacing_type'"
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    type="text"
                    pattern="^[0-9]*[.]?[0-9]{1,5}(px|pt|%)?$"
                    required
                    :value="props.row[props.column.field]"
                    class="form-control form-control-sm"
                    @change="
                      changeLineSpacingCell(
                        $event,
                        props.column.field,
                        props.row.originalIndex
                      )
                    "
                  />
                  <input
                    v-else-if="props.column.input_type == 'number'"
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    :type="props.column.input_type"
                    v-model="props.row[props.column.field]"
                    class="form-control form-control-sm"
                    step="any"
                    @change ="changeCell(
                      props.row[props.column.field],
                      props.column.field,
                      props.row.originalIndex
                    )"
                  />
                  <input
                    v-else
                    :id="`${props.row.Type}-${props.column.field}-${props.row.originalIndex}`"
                    :type="props.column.input_type"
                    v-model="props.row[props.column.field]"
                    class="form-control form-control-sm"
                    @change="
                      changeCell(
                        props.row[props.column.field],
                        props.column.field,
                        props.row.originalIndex
                      )
                    "
                  />
                </span>
                <span v-else></span>
              </template>
              <div slot="emptystate">
                Please choose a template or add scenes.
              </div>
            </my-vue-good-table>

            <edit-scene-dialog
              id="edit-scene-dialog"
              :size="previewSizes[prevSizeIdx]"
              :sizes="previewSizes"
              :rows="editSceneModel"
              :total-scene="editSceneModel.length"
              @update-row="updateModelRow"
              :modalstatus="isModalVisibale"
              @clicked="onClickChild"
            >
            </edit-scene-dialog>

            <b-modal
              size="lg"
              id="music-modal"
              :dialog-class="['audio-library-modal']"
              role="dialog"
              title="Audio Library"
              hide-footer
            >
              <div class="modal-body">
                <b-form-input
                  type="text"
                  v-model="searchText"
                  :settings="{
                    width: '100%',
                  }"
                  class="form-control mb-2"
                  @keyup="handleSearch"
                />

                <ul class="list-group">
                  <li
                    v-for="(music, i) in selectedMusics"
                    :key="i"
                    class="d-flex justify-content-between align-items-center mb-1"
                  >
                    <div class="content">
                      {{ music.name }}
                    </div>
                    <div class="content">
                      {{ music.tags }}
                    </div>
                    <div class="action">
                      <span class="font-weight-bold mr-2">
                        {{ music.duration }}s
                      </span>
                      <button
                        class="btn btn-primary"
                        @click="playAudio(music, i)"
                        v-if="!music.playing"
                      >
                        <i class="cil-media-play" />
                      </button>
                      <button
                        v-if="music.playing"
                        class="btn btn-secondary"
                        @click="stopAudio1(music, i)"
                      >
                        <i class="cil-media-pause" />
                      </button>
                      <button
                        class="btn btn-success"
                        @click="selectAudio(music)"
                      >
                        <i class="cil-check" />
                      </button>
                    </div>
                  </li>
                </ul>
              </div>
              <media-library-modal-footer
                @click="$bvModal.hide('music-modal')"
              />
            </b-modal>
            <b-modal
              id="upload-thambile"
              role="dialog"
              title="Upload New Thumbnail"
              hide-footer
            >
            <div class="modal-body">
              <input type="file" id="uploadthamb" placeholder="upload Thambnail" @change="uploadthumbile($event)" />
              <button class="btn btn-success mt-2" @click="GenerateThumbnail">Generate Thumbnail</button>
              <template v-if="thumbnailPreview">
                <img :src="thumbnailPreview"  class="img-fluid" />
                <br>
              <a :href="thumbnailPreview" title="Download" download=""  class="btn btn-success ml-2 download-filename"  style="cursor: pointer; margin-bottom: -4px;font-size: 14px; ">
                      <i class="cil-cloud-download font-weight-bold" style="size"></i>
                    </a>
                    <a class="btn btn-danger ml-2 download-filename" title="remove" @click="showThumbnailImage" style="cursor: pointer; margin-bottom: -4px;font-size: 14px; ">
                    <i class="cil-trash font-weight-bold" style="color:white"></i></a>
              </template>
              </div>
            </b-modal>

            <b-modal
              id="image-modal"
              role="dialog"
              title="Image Library"
              hide-footer
            >
              <div class="modal-body">
                <select2
                  v-model="selectedTags"
                  :options="tags"
                  :settings="{
                    multiple: true,
                    width: '100%',
                  }"
                  class="mb-2"
                />

                <nav>
                  <div class="nav nav-tabs" id="nav-tab" role="tablist">
                      <a class="nav-link active" id="nav-video-images-tab" data-toggle="tab" href="#nav-video-images" role="tab" aria-controls="nav-video-images" aria-selected="true">Video Images</a>
                      <a class="nav-link" id="nav-product-images-tab" data-toggle="tab" href="#nav-product-images" role="tab" aria-controls="nav-product-images" aria-selected="false">Product Images</a>
                      <a class="nav-link" id="nav-background-images-tab" data-toggle="tab" href="#nav-background-images" role="tab" aria-controls="nav-background-images" aria-selected="false">Stock Images</a>
                  </div>
                </nav>

                <div class="tab-content" id="nav-tabContent">
                  <div class="tab-pane fade show active" id="nav-video-images" role="tabpanel" aria-labelledby="nav-video-images-tab">
                    <table class="table image-library-table">
                      <tbody>
                        <tr v-for="(image, i) in selectedImages" :key="i">
                          <td><div class="table-image-box"><img v-lazy="image.thumb" :alt="image.file_name" class="table-media-boxed-img" /></div></td>
                          <td class="image-library-link" @click.prevent="downloadProductLibraryImage(image.url, image.name)" >
                            <div style="height:65px" class="align-middle d-table-cell">{{ image.name }}</div>
                          </td>
                          <td style="width:15%">
                            <button class="btn btn-success" @click="selectImage(image)">
                            <i class="cil-check" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <vue-pagination :pagination="image_library" @paginate="getImageLibrary()" :offset="2"></vue-pagination>
                  </div>
                  <div class="tab-pane fade" id="nav-product-images" role="tabpanel" aria-labelledby="nav-product-images-tab">
                    <table class="table image-library-table">
                      <tbody>
                        <tr v-for="(image, i) in selectedProductImages" :key="i">
                          <td><div class="table-image-box"><img v-lazy="image.url" :alt="image.name" class="table-media-boxed-img" /></div></td>
                          <td class="image-library-link" @click.prevent="downloadProductLibraryImage(image.full_url, image.name)" >
                            <div style="height:65px" class="align-middle d-table-cell">{{ image.name }}</div>
                          </td>
                          <td style="width:15%">
                            <button class="btn btn-success" @click="selectImage(image)">
                            <i class="cil-check" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <vue-pagination :pagination="product_image_library" @paginate="getProductImageLibrary()" :offset="2"></vue-pagination>
                  </div>
                  <div class="tab-pane fade" id="nav-background-images" role="tabpanel" aria-labelledby="nav-background-images-tab">
                    <table class="table image-library-table">
                      <tbody>
                        <tr v-for="(image, i) in selectedBackgroundImages" :key="i">
                          <td><div class="table-image-box"><img v-lazy="image.url" :alt="image.name" class="table-media-boxed-img" /></div></td>
                          <td class="image-library-link" @click.prevent="downloadProductLibraryImage(image.url, image.name)" >
                            <div style="height:65px" class="align-middle d-table-cell">{{ image.name }}</div>
                          </td>
                          <td style="width:15%">
                            <button class="btn btn-success" @click="selectImage(image)">
                            <i class="cil-check" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <vue-pagination :pagination="background_image_library" @paginate="getBackgroundImageLibrary()" :offset="2"></vue-pagination>
                  </div>
                </div>

              </div>

              <media-library-modal-footer
                @click="$bvModal.hide('image-modal')"
              />
            </b-modal>

            <b-modal
              id="video-modal"
              role="dialog"
              title="Video Library"
              hide-footer
            >
              <div class="modal-body">
                <select2
                  v-model="selectedTags"
                  :options="tags"
                  :settings="{
                    multiple: true,
                    width: '100%',
                  }"
                  class="mb-2"
                />

                <ul class="list-group">
                  <li
                    v-for="(video, i) in filteredVideos"
                    :key="`video-modal-${i}`"
                    class="d-flex justify-content-between align-items-center mb-1"
                  >
                    <img
                      src="/img/icons/video-folder.svg"
                      :alt="video.file_name"
                      class="media-boxed-img"
                    />
                    <div class="flex-grow-1 px-2" style="max-width: 360px">
                      <div>{{ video.name }}</div>
                      <div class="font-weight-bold">{{ video.duration }}s</div>
                    </div>
                    <button class="btn btn-success" @click="selectVideo(video)">
                      <i class="cil-check" />
                    </button>
                  </li>
                </ul>
              </div>
              <media-library-modal-footer
                @click="$bvModal.hide('video-modal')"
              />
            </b-modal>

            <div class="modal fade" id="new-confirm-modal" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p>This will discard existing rows. Are you sure?</p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary mr-2"
                        data-dismiss="modal"
                        @click="createNewRowConfirmed"
                      >
                        Yes
                      </button>
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="new-warning-modal" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p>You have unsaved changes, would you like to save?</p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary mr-2"
                        data-dismiss="modal"
                        @click="Savechanges"
                      >
                        Yes
                      </button>
                      <button
                        type="button"
                        class="btn btn-secondary mr-2"
                        data-dismiss="modal"
                      >
                        Cancel
                      </button>
                       <button
                        type="button"
                        class="btn btn-danger"
                        data-dismiss="modal"
                        @click="Discardchanges"
                      >
                        Discard
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="thumbnail-warning-modal" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p>Are you sure you want delete Thumbnail Image?</p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary mr-2"
                        data-dismiss="modal"
                        @click="removethumbnail"
                      >
                        Yes
                      </button>
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="modal fade" id="duplicate-project-modal" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p>
                      Overwrite
                      <strong
                        ><i>{{ projectName }}</i></strong
                      >?
                    </p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary mr-2"
                        data-dismiss="modal"
                        @click="_saveProject()"
                      >
                        Yes
                      </button>
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="template-rename" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p
                      v-html="
                        `Overwrite <i><strong>${outputFile}</strong></i>?`
                      "
                    ></p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-primary mr-2"
                        data-dismiss="modal"
                        @click="
                          _saveTemplate(sameTemplate && sameTemplate.readonly)
                        "
                      >
                        Yes
                      </button>
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                      >
                        No
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal fade" id="readonly-template" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Heads up!" />
                  <div class="modal-body">
                    <p>This template is readonly.</p>
                    <p>Try saving with a different name.</p>
                    <div class="d-flex justify-content-end">
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                      >
                        Ok
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <b-modal
              id="trim-video-modal"
              role="dialog"
              title="Trim video"
              hide-footer
            >
              <div class="modal-body text-center">
                <div class="left-half-white" v-show="halfChecked" />
                <div class="right-half-white" v-show="halfChecked" />
                <video
                  v-if="selectedVideo"
                  :key="selectedVideo.url_shorten"
                  controls
                  muted
                  loop
                  ref="trimVideo"
                  style="width: 100%"
                  @loadedmetadata="saveHeightWidth"
                >
                  <source :src="selectedVideo.url_shorten" />
                </video>
                <div class="row">
                  <div class="col-3 pr-1">
                    <div class="input-group d-flex flex-column align-items-center">
                      <div class="d-inline-flex align-items-center">
                        <input
                          v-model="videoTrimStart"
                          type="number"
                          class="form-control"
                          placeholder="Start"
                          step="0.001"
                        />
                        <span>&nbsp;s</span>
                      </div>
                      <span class="font-sm">Start</span>
                    </div>
                  </div>
                  <div class="col-3 px-1">
                    <div class="input-group d-flex flex-column align-items-center">

                      <div class="d-inline-flex align-items-center">
                        <input
                          v-model="videoTrimEnd"
                          type="number"
                          class="form-control"
                          placeholder="End"
                          step="0.001"
                        />
                        <span>&nbsp;s</span>
                      </div>
                      <span class="font-sm">End</span>
                    </div>
                  </div>
                  <div class="col-6">
                    <div
                      class="form-control form-check d-flex justify-content-around"
                    >
                      <div>
                        <input
                          type="checkbox"
                          class="form-check-input"
                          id="trim-check"
                          v-model="videoTrimChecked"
                        />
                        <label class="form-check-label" for="trim-check"
                          >Trim</label
                        >
                      </div>
                      <div>
                        <input
                          type="checkbox"
                          class="form-check-input"
                          id="half-check"
                          v-model="halfChecked"
                        />
                        <label class="form-check-label" for="half-check"
                          >Half</label
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Cancel</button>
                <template v-if="!(videoTrimChecked || halfChecked)">
                  <button
                    class="btn btn-primary"
                    :disabled="loadingUpload"
                    @click="selectVideoWithoutTrim"
                  >
                    <b-spinner
                      v-if="loadingUpload"
                      label="uploading..."
                      small
                    ></b-spinner>
                    Select
                  </button>
                </template>
                <template v-else>
                  <button
                    class="btn btn-secondary"
                    :disabled="isRestored"
                    @click="restoreOriginalFile"
                  >

                    Restore Original
                  </button>
                  <button
                    class="btn btn-secondary"
                    @click="previewTrimmedVideo"
                  >
                    Preview
                  </button>
                  <button
                    class="btn btn-primary"
                    @click="_trimVideo"
                    :disabled="trimmingVideo"
                  >
                    Trim
                  </button>
                </template>
              </div>
            </b-modal>

            <b-modal
              id="image-crop-modal"
              title="Image Crop & Scale"
              :dialog-class="['image-crop-modal']"
              role="dialog"
            >
              <div class="d-flex flex-wrap justify-content-around">
                <div class="cropper-section mb-3 position-relative">
                  <div class="image-loading-spinner" v-if="loadingImage">
                    <b-spinner variant="primary" label="Spinning"></b-spinner>
                  </div>
                  <vue-cropper
                    v-show="cropperIsHalf && cropperViewMode === 2"
                    ref="halfCropper"
                    :src="cropperFilepath"
                    :guides="true"
                    :view-mode="0"
                    :background="true"
                    :rotatable="true"
                    alt="Source Image"
                    drag-mode="crop"
                    preview="#crop-preview"
                    :imgStyle="cropperHalfImageStyle"
                    :style="cropperHalfImageStyle"
                    :min-container-width="240"
                    :min-container-height="270"
                    class="cropper-wrapper cropper-wrapper-half"
                    :class="cropperWrapperClass"
                    :zoom="onCropZoom"
                    :cropend="cropEnd"
                  />
                  <vue-cropper
                    v-show="!cropperIsHalf && cropperViewMode === 2"
                    ref="fullCropper"
                    :src="cropperFilepath"
                    :guides="true"
                    :view-mode="0"
                    :background="true"
                    :rotatable="true"
                    alt="Source Image"
                    drag-mode="crop"
                    preview="#crop-preview"
                    :imgStyle="cropperFullImageStyle"
                    :style="cropperFullImageStyle"
                    :min-container-width="480"
                    :min-container-height="270"
                    class="cropper-wrapper cropper-wrapper-full"
                    :class="cropperWrapperClass"
                    :zoom="onCropZoom"
                    :cropend="cropEnd"
                  />
                </div>
                <div
                  class="crop-preview-container"
                  :class="
                    cropperIsHalf
                      ? 'crop-preview-container-half'
                      : 'crop-preview-container-full'
                  "
                >
                <div id="crop-preview" class="mb-1"></div>
                </div>
              </div>
              <div :class="'tools text-center mt-1'">
                <button
                  v-if="!cropperResizing"
                  class="btn btn-outline-primary btn-sm"
                  :disabled="cropperResizing"
                  @click="cropperRotate(-10)"
                >
                  Rotate Left
                </button>
                <button
                  v-if="!cropperResizing"
                  class="btn btn-outline-primary btn-sm"
                  :disabled="cropperResizing"
                  @click="cropperRotate(10)"
                >
                  Rotate right
                </button>

                <br>
                <label class="my-0 mr-1 ml-6" for="resizeTop">Top:</label>
                <input
                  id="resizeTop"
                  name="top"
                  :disabled="!isTopNeedSave"
                  type="number"
                  min="1"
                  max="100"
                  step="0.001"
                  :value="getPercentInputValue(cropPosition.top)"
                  @blur="updateCropAreaPosition"
                >
                <label class="my-0 mr-1 ml-6" for="resizeLeft">Left:</label>
                <input
                  id="resizeLeft"
                  name="left"
                  :disabled="!isLeftNeedSave"
                  type="number"
                  min="1"
                  max="100"
                  step="0.001"
                  :value="getPercentInputValue(cropPosition.left)"
                  @change="updateCropAreaPosition"
                >
                <label class="my-0 mr-1 ml-6" for="resizeWidth">Width:</label>
                <input
                  id="resizeWidth"
                  name="width"
                  type="number"
                  min="1"
                  max="100"
                  step="0.001"
                  :value="getPercentInputValue(cropPosition.width)"
                  @change="updateCropAreaPosition"
                >
                <label class="my-0 mr-1 ml-6" for="resizeHeight">Height:</label>
                <input
                  id="resizeHeight"
                  type="number"
                  name="height"
                  min="1"
                  max="100"
                  step="0.001"
                  :value="getPercentInputValue(cropPosition.height)"
                  @change="updateCropAreaPosition"
                >
              </div>
              <template #modal-footer="{ hide }">
                <button
                  class="btn btn-secondary"
                  :disabled="isRestored"
                  @click="restoreOriginalImage"
                >

                  Restore Original
                </button>
                <button
                  class="btn btn-primary"
                  :disabled="cropping"
                  @click="_saveCroppedImage()"
                >
                  <b-spinner
                    v-if="cropping"
                    variant="light"
                    small
                    label="Spinning"
                    class="mr-2"
                  ></b-spinner>
                  <b-icon
                    v-else
                    icon="cloud-upload"
                    font-scale="1"
                    aria-hidden="true"
                    class="mr-2"
                  ></b-icon>

                  Save
                </button>
                <button class="btn btn-secondary" @click="(hide(), cropperResizing = false)">Close</button>
              </template>
            </b-modal>


          <div class="modal fade" id="delete-parent-segment" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <modal-header title="Heads up!" />
                <div class="modal-body">
                  <p>Are you sure you want delete this segment?</p>
                  <div class="d-flex justify-content-end">
                    <button
                      type="button"
                      class="btn btn-primary mr-2"
                      data-dismiss="modal"
                      @click="onDeleteParentRowClicked(deleteParentRow)"
                    >
                      Yes
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-dismiss="modal"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>
            </div>

            <div class="modal fade" id="not-exist-file-assets" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                <modal-header title="Heads up! - Missing Files" />
                <div class="modal-body">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Scene</th>
                        <th>Sub Scene</th>
                        <th>What is Missing?</th>
                      </tr>
                    </thead>
                    <tbody v-for="(value, i) in notExistFileAssets" :key="i">
                      <tr>
                        <td>{{value.scene}}</td>
                        <td>{{value.subscene}}</td>
                        <td>{{value.filename}}</td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="d-flex justify-content-end">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-dismiss="modal"
                    >
                      Ok
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>


            <div class="modal fade" id="custom-visibility-modal" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <modal-header title="Custom column visibility" />
                  <div class="modal-body">
                    <div
                      v-for="(column, i) in allColumns"
                      :key="i"
                      class="form-check"
                    >
                      <input
                        :id="`column-${i}`"
                        class="form-check-input"
                        type="checkbox"
                        :value="column.value"
                        v-model="tempCustomColumns"
                        :disabled="column.optional == false"
                      />
                      <label class="form-check-label" :for="`column-${i}`">
                        {{ column.text }}
                      </label>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <div class="d-flex mr-auto align-self-center" >
                      <label class="form-check-label align-self-center">
                          Timeframe:
                      </label>
                       <select class="form-control ml-2"  name="timeframe" style="width: 160px" @change="onTimeframeChange($event)">
                        <option value="1" :selected="isTimeframecolumn == '1'">Right</option>
                        <option value="2" :selected="isTimeframecolumn == '2'">Left</option>
                      </select>
                    </div>
                    <button
                      type="button"
                      class="btn btn-primary"
                      @click="updateCustomColumns"
                      :disabled="loadingCustomColumns"
                    >
                      Save
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


            <div class="modal fade" id="color-picker" role="dialog">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <modal-header title="Custom Color" />
                <div class="modal-body">
                  <chrome-picker v-model="colorPickerDefaultColor" :value="colorPickerDefaultColor" class="ml-auto mr-auto"/>
                  <div class="d-flex justify-content-end mt-4">
                    <button
                      type="button"
                      class="btn btn-primary mr-2"
                      data-dismiss="modal"
                      @click="addColor()"
                    >
                      Save
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary"
                      data-dismiss="modal"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from "lodash";
import {mapState, mapActions, createNamespacedHelpers} from "vuex";
import Select2 from "v-select2-component";
import {Sortable, MultiDrag} from "sortablejs";
import VueCropper from "vue-cropperjs";
import { Chrome } from 'vue-color'

import Vue from 'vue'
import VueLazyload from 'vue-lazyload';
Vue.use(VueLazyload);

import Preview from "../video_preview";
import {
  removeExtension,
  timeFormattedString,
  timeFromFormattedString,
  timeFormattedStringWithDecimal,
  getFileNameByPath
} from "../services/helpers";
import EditSceneDialog from "./common/EditSceneDialog.vue";
import UploadThumbnail from "./common/UploadThumbnail.vue";
import { deepCopy, delay } from "../utils.js";
import VuePagination from './common/Pagination.vue';

import "cropperjs/dist/cropper.css";
import html2canvas from 'html2canvas';
import BtnActionSaveScene from "./vue-good-table/components/BtnActionSaveScene";

const promiseRetry = require("promise-retry");
const {mapGetters: mapGettersProjects} = createNamespacedHelpers('project');

export default {
  components: {
    BtnActionSaveScene,
    Select2,
    VueCropper,
    EditSceneDialog,
    UploadThumbnail,
    VuePagination,
    'chrome-picker': Chrome
  },
  props: [
    "userId",
    "videoAnimations",
    "textAnimations",
    "columnVisibilityTypes",
    "defaultColumnVisibilityName",
    "allColumns",
    "colors",
    "font_names",
    "audioColumns",
    "customColumns",
    "creationData",
    "columnTypes",
    "previewSizes",
    "themes",
    "tags",
    "musics",
    "images",
    "videos",
    "deleteIcon",
    "timeframeColumn",
  ],
  data() {
    return {
      sceneJumpImage : "/img/icons/video_jump_icon.png",
      timeFormattedString: timeFormattedString,
      timeFormattedStringWithDecimal: timeFormattedStringWithDecimal,
      isModalVisibale:false,
      templaterowchange:false,
      CanvasVolumn:100,
      canvasiconvolume:100,
      backgroundSize: '100% 100%',
      volumnMute:true,
      caption: false,
      thumbimage: null,
      thumbnailPreview:null,
      loadId: null,
      loadType: "",
      loadingTemplateBtn: false,
      trimmingVideo: false,
      loadingProject: false,
      loadingCreateBtn: false,
      loadingUpload: false,
      loadingImage: false,

      cropping: false,
      loadingAssets: false,
      loadingCustomColumns: false,

      selectableFontColors: this.colors,
      selectableStrokeColors: this.colors,
      selectableFontNames: this.font_names,

      preview: null,
      previewTime: 0,
      previewTimeLabel: "00:00.0",

      prevSizeIdx: 1,

      modelRows: [],
      outputFile: "",
      playingState: "NO",

      selectedTheme: null,
      selectedTemplate: null,
      selectedProject: null,
      selectedVideo: null,
      selectedRow: null,

      selectedColumnVisibilityName: this.defaultColumnVisibilityName,
      locCustomColumns: this.customColumns,
      tempCustomColumns: this.customColumns,

      locCreationData: this.creationData,
      sceneType: "All",
      projectName: "",

      fileToUpload: null,

      videoTrimStart: 0,
      videoTrimEnd: 0,
      videoTrimChecked: false,
      halfChecked: false,
      useFilenameForPath: false,

      searchText: "",
      selectedMusics: [],
      selectedTags: [],
      innerMusics: [],
      cropperSelected: null,
      cropperViewMode: 2,
      cropperResizing: false,
      cropperOptions: [
        {
          value: null,
          text: "Select an option",
          disabled: true,
        },
        {
          value: {
            size: "full",
            position: null,
          },
          text: "Full Size Frame",
        },
        {
          label: "Half Size Frame",
          options: [
            {
              value: { size: "half", position: "left" },
              text: "Left",
            },
            {
              value: { size: "half", position: "right" },
              text: "Right",
            },
          ],
        },
      ],
      isPreviewRowsChanged: false,
      oldPreviewRows: null,
      isSaveAsDraft: false,
      deleteParentRow: null,
      isTimeframecolumn: this.timeframeColumn,
      savedCurrentCropperParams: null,
      resizingHeight: 0,
      resizingWidth: 0,
      resizingTop: 0,
      resizingLeft: 0,
      image_library: {
        total: 0,
        per_page: 5,
        from: 1,
        to: 0,
        current_page: 1
      },
      product_image_library: {
        total: 0,
        per_page: 5,
        from: 1,
        to: 0,
        current_page: 1
      },
      background_image_library: {
        total: 0,
        per_page: 5,
        from: 1,
        to: 0,
        current_page: 1
      },
      notExistFileAssets: [],
      colorPickerDefaultColor: '#000000',
      isColorPickerSelector: [],
      newFontColor: [],
      newStrokeColor: [],
      countNewAddedRow: 0,
      initialCropAutoCropEvent: false,
      isUserChangeSomeOnCrop: false,
      cropPosition: {
        top: 0,
        left: 0,
        width: 0,
        height: 0
      },
      changedByCropUi: false,
      isCropInit: false
  }
  },
  computed: {
    ...mapState("template", ["templates"]),
    ...mapState("project", ["projects"]),
    ...mapGettersProjects(["projectsDrafts"]),
      isLeftNeedSave() {
        return (this.selectedRow && this.selectedRow.Left && (
          this.selectedRow.AlignH === 'Left'
          || this.selectedRow.AlignH === 'Center'
          || this.selectedRow.AlignH === 'Right'
          || this.selectedRow.AlignH === 'FF'
          || this.selectedRow.AlignH === 'LH'
          || this.selectedRow.AlignH === 'RH'))
      },
      isTopNeedSave() {
        return (this.selectedRow && this.selectedRow.Top)
      },
      isRestored() {
        return this.selectedRow.Filename === this.selectedRow.Original_File_Url;
      },
      tableColumns() {
      if (this.selectedColumnVisibilityName == "all") {
        this.allColumns = this.allColumns.map((col) => {
          return {
            ...col,
            thClass:(col.field == "Actions") ? "actionHeader" : "",
            tdClass:(col.field == "Actions") ? "actionHeader" : "",
          }
        });
        return this.allColumns;
      } else if (this.selectedColumnVisibilityName == "media") {
        return this.allColumns.filter((c) => c.for.includes("media"));
      } else if (this.selectedColumnVisibilityName == "text") {
        return this.allColumns.filter((c) => c.for.includes("text"));
      } else if (this.selectedColumnVisibilityName == "custom") {
        return this.allColumns.filter((c) =>
          this.locCustomColumns.includes(c.value)
        );
      }
    },
    cropperPreviewClass() {
      return this.cropperSelected?.position
        ? `cropper-wrapper-${this.cropperSelected.position}`
        : "cropper-wrapper-full";
    },
    playBtnLabel() {
      if (this.playingState == "PLAYING") return "Pause";
      if (this.playingState == "PAUSED") return "Resume";
      else return null;
    },

    previewDuration() {
      let duration = 0;
      let prevScene = null;
      let end = 0;
      this.modelRows.forEach((row) => {
        if (row.Type != "Music" && row.Type != "LH" && row.Type != "RH" && row.Type != "FF") {
          const scene = +row.Scene;

          if (scene != prevScene) {
            prevScene = scene;
            duration += end;
            end = row.End ? +row.End : 0;
          } else {
            end = Math.max(+row.End, end);
          }
          if(!this.selectedTheme && !row.isCustom) {
            row.Size=40;
            row.Color='black';
            row.Font_Name='Helvetica-Bold';
          } else {
            delete row.isCustom
          }
        }
      });

      duration += end;

      return duration;
    },

    overviewLabel() {
      const selectedThemeName = this.selectedTheme
        ? ` | ${this.selectedTheme.name}`
        : "";
      const previewDuration =
        this.previewDuration == 0 ? "" : ` | ${Math.round(this.previewDuration * 10) / 10}s`;

      return `${selectedThemeName}${previewDuration}`;
    },

    editSceneModel() {
      const allowTypes = ["Background", "Image", "Text", "Caption"];
      return this.modelRows
        .filter(
          ({ Type, Text }) =>
            allowTypes.includes(Type) && (Type != "Text" || Text)
        )
        .map((r) => ({ ...r, sceneId: `${r.Type}-${r.originalIndex}` }));
    },

    cropperFilepath() {
      return this.selectedRow?.Filename ?? "";
    },

    groupers() {
      return this.modelRows
        .filter((r) => {
          return this.sceneType=="All"||this.sceneType==r.Type;
        })
        .reduce(function(pre, curr) {
            const found=pre.find(
              function(r) {
                if(r.Subscene!="") {
                  return r.Scene==curr.Scene&&r.Subscene==curr.Subscene;
                } else {
                  return false;
                }
              }
            );

            if (!found)
              return [
                ...pre,
                {
                  originalIndex: curr.originalIndex,
                  Scene: curr.Scene,
                  Subscene: curr.Subscene,
                  Type: curr.Type,
                },
              ];
            else
              return pre;
          }, []);
    },

    groupedRows() {
      const rows = this.groupers.map((g, i) => {

        const children = this.modelRows.filter(
          (r, j) => {
              if(r.Subscene == "" && g.Subscene == ""){
                return r.Scene==g.Scene&&r.originalIndex==g.originalIndex;
              } else {
                return r.Scene==g.Scene&&r.Subscene==g.Subscene;
              }
          }
        );
          const isStar = children
              .map((child) => child.Text)
              .some((t) => {
                  if (typeof t === 'string') {
                      return t.includes("★")
                  }
                  return false;
              });

        const multiText = children
          .filter(({ Type }) => Type != "VTT")
          .map((child) => child.Text)
          .join("\n");

        let editableRow = this.defaultRow(g);

        // set value if all children have the same value for each column
        this.allColumns.forEach((column) => {
          if (column.group_editable) {
            let isChildrenSame = false;
            if(column.field == 'Font_Name'){
              var FontColumns = children.filter(obj => {
                return obj.Type == 'Text' || obj.Type == 'VTT';
              });
              isChildrenSame = FontColumns.every(
                (child, ind, arr) => child[column.field] == arr[0][column.field]
              );
            } else{
              isChildrenSame = children.every(
                (child, ind, arr) => child[column.field] == arr[0][column.field]
              );
            }

            if (column.field == "Start") {
              // Start value is the min value of all children no matter the children have the same value or not
              editableRow["Start"] = Math.min.apply(
                Math,
                children.map((child) => +child.Start)
              );
            } else if (column.field == "Duration" || column.field == "End" || column.field == "Timeline_End") {
              editableRow[column.field] = Math.max.apply(
                Math,
                children.map((child) => +child[column.field])
              );
            } else {
              if (isChildrenSame) {
                // If all children have the same value for this field, the group row has to be the first of children
                editableRow[column.field] = children[0][column.field];
              } else {
                // Else, the group row should be ''
                editableRow[column.field] = "";
              }
            }
          }
        });

        return {
          groupper: true,
          type: g.Type,
          isStar,
          multiText,
          editableRow,
          children,
        };
      });

      console.log("[groupped Rows]", rows);
      return rows;
    },

    cropperIsHalf() {
      return this.cropperSelected?.size == "half";
    },
    cropperPosition() {
      if (this.cropperIsHalf) {
        return this.cropperSelected?.position;
      }
      return "";
    },
    cropperWrapperClass() {
      if (this.cropperPosition) {
        return `cropper-wrapper-${this.cropperPosition}`;
      }
      return ``;
    },

    cropperHalfImageStyle() {
      return {
        width: "240px",
        height: "270px",
      };
    },

    cropperFullImageStyle() {
      return {
        width: "480px",
        height: "270px",
      };
    },

    filteredVideos() {
      if (this.selectedTags.length == 0) return this.videos;
      else
        return this.videos.filter((v) => {
          return this.selectedTags.some((t) => v.tag_ids.includes(+t));
        });
    },
    selectedImages() {
      if (this.selectedTags.length == 0) return this.image_library.data;
      else
        return this.image_library.data.filter((i) => {
          return this.selectedTags.some((t) => i.tag_ids.includes(+t));
        });
    },
    selectedProductImages() {
      if (this.selectedTags.length == 0) return this.product_image_library.items;
      else
        return this.product_image_library.items.filter((i) => {
          return this.selectedTags.some((t) => i.tag_ids.includes(+t));
        });
    },
    selectedBackgroundImages() {
      if (this.selectedTags.length == 0) return this.background_image_library.stock;
      else
        return this.background_image_library.stock.filter((i) => {
          return this.selectedTags.some((t) => i.tag_ids.includes(+t));
        });
    },
    styleClass() {
      const className = ["vgt-table"];
      if (this.selectedColumnVisibilityName == "custom") {
        
        if (this.tableColumns.some((col) => col.field == "Actions"))
          className.push("sticky-column-1");
      }

      return className.join(" ");
    },

  },
  watch: {
    cropPosition: {
      handler(newVal) {
        const cropper = this.cropperIsHalf
          ? this.$refs.halfCropper.cropper
          : this.$refs.fullCropper.cropper;

        const {top, left, width, height} = newVal;

        if(!this.changedByCropUi) {
          cropper.setCropBoxData({
            top: cropper.getContainerData().height * top,
            left: cropper.getContainerData().width * left,
            width: cropper.getContainerData().width * width,
            height: cropper.getContainerData().height * height
          });
        }

        if (this.isCropInit) {
          cropper.setCanvasData(cropper.getCropBoxData());
          this.isCropInit = false;
        }

      },
      deep: true
    },
    modelRows: function (newVal) {
      this.$nextTick(() => {

        const children = document.querySelectorAll("#vgt-table tbody");
        const {updateGrouperRowOrder} = this;
        for (let i = 0; i < children.length; i++) {

          const sortable = Sortable.create(children[i], {
            group: `group-${i}`,
            animation: 150,
            filter: ".vgt-group-row",
            handle: ".line-numbers",
            selectedClass: "sortable-selected",
            preventOnFilter: false,

            onMove: (evt) => {
                return evt.related.className.indexOf("vgt-group-row") === -1;
            },
            onEnd: (/**Event*/ evt) => {
              const groupIndex = parseInt(
                  sortable.options.group.name.substring(6)
              );
            },
          });
        }

        Sortable.create(document.getElementById("vgt-table"), {
          animation: 150,
          handle: ".line-number",
          filter: "thead",
          // multiDrag: true, // Enable the plugin
          // selectedClass: "sortable-selected", // Class name for selected item
          // avoidImplicitDeselect: false, // true - if you don't want to deselect items on outside click
          onMove: (evt) => {
            const {dragged, related} = evt;
            if (related.tagName.indexOf("thead") !== -1) return false;

            const prevId = dragged.getAttribute("id");
            const nextId = related.getAttribute("id");

            if (prevId && nextId) {
              const prevIdx = prevId.split("--")[1];
              const nextIdx = nextId.split("--")[1];
              return Math.abs(+prevIdx - nextIdx) === 1;
            }
            return false;
          },
          onUpdate: (evt) => {
            updateGrouperRowOrder(evt);
          },
        });
      });

      if (this.oldPreviewRows !== null) return;
      // for json parse for reset observer
      this.oldPreviewRows = newVal ? JSON.parse(JSON.stringify(newVal)) : null;
    },
    async cropperResizing(newVal) {
        if (newVal) {
            this.cropperViewMode = 0;
            const cropper = this.cropperIsHalf
                ? this.$refs.resizeHalfCropper.cropper
                : this.$refs.resizeFullCropper.cropper;

          if ( this.isUserChangeSomeOnCrop ) {
            const cropperPrev = this.cropperIsHalf
              ? this.$refs.halfCropper.cropper
              : this.$refs.fullCropper.cropper;

            cropperPrev.getCroppedCanvas().toBlob(async (blob) => {
              this.$bvModal.hide("image-crop-modal");
              try {
                const formData = new FormData();
                formData.append("file", blob);
                const {data} = await this.saveCroppedImage(formData);
                this.selectedRow.Filename = data.data.path;

                const Size = !this.cropperIsHalf ? "full" : `half-${this.cropperPosition}`;
                this.selectedRow.Size = Size;
                const {originalIndex, Size: imageSize} = this.selectedRow;
                this.calculateHeightBasedOnWidth(originalIndex, this.selectedRow.Width, this.selectedRow.Size).then(
                  calcHeight => this.changeChildren(calcHeight, "Height", originalIndex, imageSize)
                );

                if (originalIndex) {
                  this.changeChildren(Size, "Size", originalIndex);
                }

                this.cropperSelected = null;

              } catch (error) {
                console.log(error);
              } finally {
                this.cropping = false;
                this.$bvModal.show("image-crop-modal");
              }
            })
          }

            let calcWidth = cropper.getContainerData().width * this.selectedRow.Width;
            let calcHeight = cropper.getContainerData().height * this.selectedRow.Height;
            let position = {
                top: cropper.getContainerData().height * this.selectedRow.Top,
                left: cropper.getContainerData().width * this.selectedRow.Left,
                width: calcWidth,
                height: calcHeight
            };
            if (calcWidth >= cropper.getImageData().naturalWidth) {
                position.width = cropper.getImageData().naturalWidth,
                position.height = cropper.getImageData().naturalHeight
                position.top = 0,
                position.left = 0
            }

            cropper.setCropBoxData(position);
            cropper.setCanvasData(cropper.getCropBoxData());
            cropper.getCanvasData();
            this.resizingWidth = this.selectedRow.Width;

            if (this.cropperIsHalf) {
                this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex, this.selectedRow.Width).then(
                    h => {
                        this.resizingHeight = h
                    }
                )
            } else {
                this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex, this.selectedRow.Width).then(
                    h => {
                        this.resizingHeight = h
                    }
                )
            }
            cropper.setAspectRatio((cropper.getImageData().width) / (cropper.getImageData().height))
        } else {
            this.cropperViewMode = 2;
        }
    },
    async resizingWidth(value) {
        const cropper = this.cropperIsHalf
            ? this.$refs.resizeHalfCropper.cropper
            : this.$refs.resizeFullCropper.cropper;

        const {width: containerWidth, height: containerHeight } = cropper.getContainerData();
        let preparedContainerWidth = this.cropperIsHalf ? containerWidth * 2 : containerWidth;
        let widthPX = preparedContainerWidth * value;
        this.resizingHeight = await this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex, value);
        cropper.setCropBoxData({width: widthPX, height: containerHeight * this.resizingHeight });
        cropper.setCanvasData(cropper.getCropBoxData());
    }
  },
  mounted() {
    this.subscribeChannel();
    this.initPreview();
    this.updateState();
    this.getTemplates();
    this.getProjects();
    this.getProjectsDrafts();
    this.updateInnerMusics(this.musics);
    this.selectedMusicsLists();
    this.getImageLibrary();
    this.getProductImageLibrary();
    this.getBackgroundImageLibrary();
    // Sortable.mount(new MultiDrag());
    this.preview.setRS(this.getScaleFactor(640, 360));

    this.$root.$on("bv::dropdown::show", (bvEvent) => {
      const { componentId } = bvEvent;
      const curEle = document.getElementById(componentId);
      if (curEle) {
        curEle.parentElement.classList.add("menu-opened");
      }
    });
    this.$root.$on("bv::dropdown::hide", (bvEvent) => {
      const { componentId } = bvEvent;
      const curEle = document.getElementById(componentId);
      if (curEle) {
        curEle.parentElement.classList.remove("menu-opened");
      }
    });
      this.$root.$on('bv::modal::hide', (bvEvent, modalId) => {
          if (modalId === 'trim-video-modal') {
            this.useFilenameForPath = false;
          }
          if (modalId === 'image-crop-modal') {
              this.changedByCropUi = false;
              this.isUserChangeSomeOnCrop = false;
          }
      });
    this.loadingUpload = false;
  },

  methods: {
    ...mapActions("media", [
      "uploadFile",
      "importTemplate",
      "exportTemplate",
      "exportAssets",
      "importFile",
      "saveTemplate",
      "trimVideo",
      "saveCroppedImage",
      "createVideo",
      "uploadThumb",
    ]),
    ...mapActions("template", ["getTemplates"]),
    ...mapActions("project", ["getProjects", "saveProject", "loadProject", 'getProjectsDrafts']),

    async calculateHeightBasedOnWidth(row, imageWidthPercent = 0.2, cropPositionInFrame = 'full', reverse = false) {
      const imgResolutions = await this.preview.getImageRect(this.modelRows[row]);
      const currentFrame = this.previewSizes[this.prevSizeIdx];
      const currentFrameWidth = currentFrame.width;
      let coefficient = (imgResolutions.height / imgResolutions.width) * (currentFrameWidth / currentFrame.height);

      if (reverse) {
        coefficient = (imgResolutions.width / imgResolutions.height) * (currentFrame.height / currentFrame.width);
      }

      return Math.min((coefficient.toFixed(4) * parseFloat(imageWidthPercent)).toFixed(4), 1.0);
    },
    calculateHeightBasedOnWidthForVideo(videoWidthInPercent) {
      const currentFrame = this.previewSizes[this.prevSizeIdx];
      const currentFrameWidth = currentFrame.width;
      const coefficient = (this.selectedVideo.height / this.selectedVideo.width) * (currentFrameWidth / currentFrame.height);
      return Math.min((coefficient.toFixed(4) * parseFloat(videoWidthInPercent)).toFixed(4), 1.0);
    },

    getImageLibrary() {
      axios.get(`/video/image-library?page=${this.image_library.current_page}`)
      .then((response) => {
          this.image_library = response.data;
      })
      .catch(() => {
          console.log('handle server error from here');
      });
    },
     updateSlider(e){
      let clickedElement = e.target,
                min = clickedElement.min,
                max = clickedElement.max,
                val = clickedElement.value;

            this.backgroundSize = (val - min) * 100 / (max - min) + '% 100%';
            this.preview.vCtx.volume = this.CanvasVolumn/100;
            this.canvasiconvolume=this.CanvasVolumn/100;
              if(this.CanvasVolumn!=0){
              this.volumnMute=true;
            }
    },
    updateicon(e){
      let that=this;
      if(that.playBtnLabel){
      if(e=='up'){
      that.volumnMute=false;
      that.CanvasVolumn=0;
      that.preview.vCtx.volume = 0/100;
       $("#CanvasVolumn").css("background-size","0% 100%");
      }
      else{
       $("#CanvasVolumn").css("background-size",that.backgroundSize);
       that.preview.vCtx.volume = that.canvasiconvolume;
       that.CanvasVolumn=that.CanvasVolumn;
       that.volumnMute=true;
       setTimeout(function(){
        that.CanvasVolumn=that.canvasiconvolume*100;
        document.getElementById("CanvasVolumn").value = that.canvasiconvolume*100;
        }, 100);

      }
    }
    },
    GenerateThumbnail(){
      // new
      let that=this;
        html2canvas(document.getElementById("preview")).then(function(canvas)
        {
          let dataURL = canvas.toDataURL();

            var canvas = document.createElement("canvas");
            var ctx = canvas.getContext("2d");

            canvas.width = 1920; // target width
            canvas.height = 1080; // target height

            var image = new Image();

            image.onload = function(e) {
                ctx.drawImage(image,
                    0, 0, image.width, image.height,
                    0, 0, canvas.width, canvas.height
                );
                // create a new base64 encoding
                var resampledImage = new Image();
                resampledImage = canvas.toDataURL();

                 that.thumbnailPreview = resampledImage;
                 that.thumbimage=resampledImage;
            };

            image.src = dataURL;


        });
    },
    getProductImageLibrary() {
      axios.get(`/file/file_list?pageSize=5&pageNumber=${this.product_image_library.current_page}`)
      .then((response) => {
          this.product_image_library = response.data;
      })
      .catch(() => {
          console.log('handle server error from here');
      });
    },

    getBackgroundImageLibrary() {
      axios.get(`/banner/background-stock?pageSize=5&stock_page=${this.background_image_library.current_page}`)
      .then((response) => {
          this.background_image_library = response.data;
      })
      .catch(() => {
          console.log('handle server error from here');
      });
    },

     downloadProductLibraryImage (responseUrl, file_name) {
      axios.get(responseUrl, { responseType: 'blob' })
        .then(response => {
          const blob = new Blob([response.data])
          const link = document.createElement('a')
          link.href = URL.createObjectURL(blob)
          link.download = file_name
          link.click()
          URL.revokeObjectURL(link.href)
        }).catch(console.error)
    },

    checkSubsceneOrdering() {
      let invalidRows = [];

      this.modelRows.forEach((row,index,rows) => {
          if (!!rows[index + 1] &&
              row.Scene === rows[index + 1].Scene
              && parseInt(rows[index + 1].Subscene) < parseInt(row.Subscene)) {
              invalidRows.push(row.Scene);
          }
      });
      if (invalidRows.length > 0) {
          let message = `Subscenes are out of order.<br>
          Scenes: ${ invalidRows.length === 1 ? invalidRows[0]+ '.' : invalidRows.join(', ') + '.'}`;
          toastr.warning(message, 'Warning');
      }
    },
    checkIsPreviewRowsChange() {
      if (this.oldPreviewRows !== null) {
        this.oldPreviewRows.forEach( (row, index) => {
          Object.keys(row).forEach(( key) => {
              if (row[key] !== this.modelRows[index][key]) {
                this.isPreviewRowsChanged = true;
              }
            }
          )
        });
      }
    },
    onClickChild(){
        this.isModalVisibale =!this.isModalVisibale;
    },
    async changeCell(changedData, column, row) {

        if((column == 'Color' || column == "Stroke_Color") && changedData == "Custom..."){

          (column == "Color")
            ? this.isColorPickerSelector['color_type'] = "Color"
            : this.isColorPickerSelector['color_type'] = "Stroke_Color";

          this.isColorPickerSelector['row'] = row;
          this.isColorPickerSelector['edit_row'] = 'child';
          $("#color-picker").appendTo("body").modal("show");

          this.modelRows = JSON.parse(JSON.stringify(this.modelRows));
        } else {

          let currentTime = this.preview.currentTime;

          if( column === "Type" && changedData === "Text") {
              this.modelRows[row]['Height'] = 1.0;
          }

          if( column === "Type" && changedData === "Image" && this.modelRows[row]['Width'] === 1) {
              this.modelRows[row]['Width'] = 0.2;
          }

          if (column === "Width" && this.modelRows[row]['Type'] === 'Image') {
              this.modelRows[row]['Height'] = await this.calculateHeightBasedOnWidth(row, changedData , this.modelRows[row]['Size'])
          }
          this.modelRows[row][column] = changedData;
          if (this.modelRows[row]['Type'] === 'VTT') {
              this.modelRows[row]['Size']="12";
          }

          this.countNewAddedRow = 0;
          this.checkCharacterCount(changedData, column, row);
          this.changeTimeCellAccordingly(column, row);
          await this.loadFirstScene();
          await this.startPlayFrom(currentTime);
      }
    },

     checkCharacterCount(changedData, column, row){

        (changedData == undefined) ? changedData = '': changedData = changedData;
        var rowNum = row + this.countNewAddedRow;
        var characterCount = (column == 'Character_Count') ? changedData : this.modelRows[rowNum]['Character_Count'];
        var chnagedTextCount = (column == 'Character_Count') ? this.modelRows[rowNum]['Text'].length : changedData.length;

        if((column == 'Text' && this.modelRows[rowNum]['Character_Count'] != '') || (column == 'Character_Count' && changedData != '') && this.modelRows[rowNum]['Type'] == 'Text'){
            if(chnagedTextCount > characterCount){
              var oldText = this.modelRows[rowNum]['Text'].substr(0, Number(this.modelRows[rowNum]['Character_Count']) + 1);
              var currentText = oldText.substr(0, Math.min(oldText.length, oldText.lastIndexOf(" ")));
              var newLineText = this.modelRows[rowNum]['Text'].substr(Math.min(oldText.length, (oldText.lastIndexOf(" ") == '-1') ? 0 : oldText.lastIndexOf(" ")  ));

              this.modelRows[rowNum]['Text'] = currentText;
              this.onCopySubscene(this.modelRows[rowNum]['originalIndex'], true, newLineText.trim());
              this.countNewAddedRow++;
          }
        }
    },

    startPlayFrom(currentTime) {
      if (currentTime > 0) {

        setTimeout(async () => {
          try{

            await this.preview.play(currentTime);
            await this.preview.pause();
          } catch (e) {
            console.log(e)
          }
        }, 1000)
      }
    },
    captionstatus() {
      if(this.playingState != "PLAYING")
      this.caption = !this.caption;
    },
    showThumbnailImage(){
        this.$bvModal.hide("upload-thambile");
        $("#thumbnail-warning-modal").appendTo("body").modal("show");
        $(".modal-backdrop").css('display','none');
    },
    removethumbnail(){
      this.thumbnailPreview=null;
    },
    changeTimeCellAccordingly(column, row) {
      if (column == "Start" || column == "Duration")
        this.modelRows[row]["End"] =
          +this.modelRows[row]["Start"] + +this.modelRows[row]["Duration"];
      else if (column == "End")
        this.modelRows[row]["Duration"] =
          +this.modelRows[row]["End"] - +this.modelRows[row]["Start"];

      this.updateTimeline();
    },
    modalThambile(){
      this.$bvModal.show("upload-thambile");

    },
    updateInnerMusics(musics) {
      this.innerMusics = musics.map((m) => ({ ...m, playing: false }));
    },
    selectedMusicsLists() {
      this.selectedMusics = this.innerMusics.filter((m) => {
        if (this.selectedTags.length == 0) return m;
        return this.selectedTags.some((t) => m.tag_ids.includes(+t));
      });
    },
    changeTimeCell(e, column, row) {
      if (/^[0-5][0-9]\:[0-5][0-9]\.[0-9]$/.test(e.target.value)) {
        this.modelRows[row][column] = timeFromFormattedString(e.target.value);
        this.changeTimeCellAccordingly(column, row);
      } else if (/^[0-5][0-9]\:[0-5][0-9]$/.test(e.target.value)) {
        this.modelRows[row][column] = timeFromFormattedString(e.target.value);
        this.changeTimeCellAccordingly(column, row);
      }
    },
    async changeLineSpacingCell(e, column, row) {
        if (/^[0-9]*[.]?[0-9]{1,5}(px|pt|%)?$/.test(e.target.value)) {
            this.modelRows[row][column] = e.target.value;
            await this.changeCell(e.target.value, column, row)
        }
    },
    async updateChildrenModelRows(changedData, column, children) {
      console.log("[updateChildrenModelRows]", changedData, column, children);

      if((column == 'Color' || column == "Stroke_Color") && changedData == "Custom..."){

        (column == "Color")
          ? this.isColorPickerSelector['color_type'] = "Color"
          : this.isColorPickerSelector['color_type'] = "Stroke_Color";

        this.isColorPickerSelector['row'] = children;
        this.isColorPickerSelector['edit_row'] = 'parent';
        $("#color-picker").appendTo("body").modal("show");

        this.modelRows = JSON.parse(JSON.stringify(this.modelRows));
      } else {
        children.forEach((child, i) => {
          const originalIndex = child.originalIndex ?? child.id;
          this.modelRows[originalIndex][column] = changedData;
          this.changeTimeCellAccordingly(column, originalIndex);
        });
      }


      this.countNewAddedRow = 0;
      if(column == "Character_Count"){
        this.modelRows.forEach((row, i) => {
          if(row.Character_Count != ""){
            this.checkCharacterCount(row.Character_Count, 'Character_Count', row.originalIndex);
          }
        });
      }

      let currentTime = this.preview.currentTime;
      this.loadFirstScene();
      await this.startPlayFrom(currentTime);
    },
    updateTimeChildrenModelRows(e, column, children) {
      if (/^[0-5][0-9]\:[0-5][0-9]\.[0-9]$/.test(e.target.value)) {
        children.forEach((child) => {
          this.modelRows[child.originalIndex][column] = timeFromFormattedString(
            e.target.value
          );
          this.changeTimeCellAccordingly(column, child.originalIndex);
        });
      } else if (/^[0-5][0-9]\:[0-5][0-9]$/.test(e.target.value)) {
        children.forEach((child) => {
          this.modelRows[child.originalIndex][column] = timeFromFormattedString(
            e.target.value
          );
          this.changeTimeCellAccordingly(column, child.originalIndex);
        });
      }
    },

    updatePercentChildrenModelRows(e,column, children){
        children.forEach((child) => {
          const {value} = e.target;

          if (value === '' || value === 0) {
              this.changeCell('', column, child.originalIndex);
          }

          if ((this.modelRows[child.originalIndex].Type === 'Video' || this.modelRows[child.originalIndex].Type === 'Image') && (column === 'Height' || column === 'Width')) {
              this.changeCell(+value / 100, column, child.originalIndex)
          } else if (this.modelRows[child.originalIndex].Type === 'Image' && column === 'Top' || column === 'Left') {
              this.changeCell(+value / 100, column, child.originalIndex)
          } else {
              this.changeCell(Math.min(+value / 100, 1.0), column, child.originalIndex)
          }
        });
        this.loadFirstScene();
    },

    updateModelRow(row) {
      try {
        if (row) {
          const { originalIndex, Left, Top, Width, Height } = row;
          this.changeChildren(Left, "Left", originalIndex);
          this.changeChildren(Top, "Top", originalIndex);
          this.changeChildren(Width, "Width", originalIndex);
          this.changeChildren(Height, "Height", originalIndex);
        }
        return true;
      } catch (error) {
        console.log("[error]", error);
      }
      return false;
    },
    changeChildren(changedData, column, row) {
      this.modelRows[row][column] = changedData;
    },
    updateTextModelRows: _.debounce(async function (children, multiText) {
      try {
        const splittedTexts = multiText.split("\n");

        var TextColumns = children.filter(obj => {
          return obj.Type == 'Text';
        });

        var totalTextRow = 0;
        TextColumns.forEach((child, i) => {
            totalTextRow++;
        });


        if(splittedTexts.length > totalTextRow){

          splittedTexts.splice(0, totalTextRow);
          var textIndex = 0;
          splittedTexts.forEach((child, i) => {
            const lastRow = TextColumns[TextColumns.length - 1];
            if(i == 0){
              var textLastRow  = (lastRow.Type != "VTT") ? TextColumns[TextColumns.length - 1] : TextColumns[TextColumns.length - 2];
              textIndex = textLastRow.originalIndex;
            } else {
              textIndex = textIndex;
            }

            const oldModels = deepCopy(this.modelRows);
            const _row = deepCopy(this.modelRows[textIndex]);
            _row['Text'] = child;
            _row['Type'] = "Text";
            _row['Scene'] = lastRow.Scene;
            _row['Subscene'] = lastRow.Subscene;

            oldModels.splice(textIndex + 1, 0, _row);

            this.modelRows = oldModels.map((r, id) => ({
              ...r,
              id,
              originalIndex: id,
            }));
            textIndex = textIndex + 1;
          });
        } else if(splittedTexts.length < totalTextRow){

          var oldTextCount = TextColumns.length;
          TextColumns = TextColumns.filter(obj => {
            return !splittedTexts.includes(obj.Text);
          });

          TextColumns.forEach((child, i) => {
            const oldModels = deepCopy(this.modelRows);

            (i == TextColumns.length - 1 && oldTextCount == TextColumns.length) 
            ? oldModels[child.originalIndex - i].Text = ""
            : oldModels.splice(child.originalIndex - i, 1);
            
            this.modelRows = oldModels.map((r, id) => ({
              ...r,
              id,
              originalIndex: id,
            }));
          });
        } else{
          TextColumns.forEach((child, i) => {
            this.modelRows[child.originalIndex].Text = splittedTexts[i] || "";
          });
        }

        this.countNewAddedRow = 0;
        TextColumns.forEach((child, i) => {
            if(this.modelRows[child.originalIndex].Character_Count != ''){
              this.checkCharacterCount(splittedTexts[i], 'Text', child.originalIndex);
            }
        });

        let currentTime = this.preview.currentTime;
        await this.loadFirstScene();
        await this.startPlayFrom(currentTime);
        this.updateTimeline();
      } catch (error) {
        console.log("[error]", error);
      }
    }, 500),

    selectionOptions(props) {
      if (props.column.field == "Color")
        return this.selectableFontColors;
      else if (props.column.field == "Stroke_Color")
        return this.selectableStrokeColors;
      else if (props.column.field == "Font_Name")
        return this.selectableFontNames;
      else if (props.column.field == "Animation") {
          if(props.column.options[props.row.Type]){
            return props.column.options[props.row.Type];
          } else if (props.column.options[props.row.type]){
            return props.column.options[props.row.type];
          }
      } else {
        return props.column.options;
      }
    },
    rowClassFn(row) {
      return row.groupper ? "vgt-group-row" : "vgt-child-row";
    },

    hasSameValues(children, field) {
      if(field == 'Font_Name'){
        var FontColumns = children.filter(obj => {
          return obj.Type == 'Text' || obj.Type == 'VTT';
        });
        return FontColumns.every((child, ind, arr) => child[field] == arr[0][field]);
      } else{
        return children.every((child, ind, arr) => child[field] == arr[0][field]);
      }
    },

    visibleFor(column, type) {
      return column.visible_for.includes(type);
    },

    isMedia(type) {
      return ["Video", "Music", "Image"].includes(type);
    },

    isEffectType(type) {
      return ["LH", "RH", "FF"].includes(type);
    },

    isReadonly(type){
      return ['Timeline_Start', 'Timeline_End'].includes(type);
    },

    renderTime() {
      this.previewTime = this.preview.currentTime;
      this.previewTimeLabel = timeFormattedStringWithDecimal(this.previewTime);
      this.updateState();

      this.animationRequestId = requestAnimationFrame(this.renderTime);
    },

    initPreview() {
      this.preview = new Preview();
      this.animationRequestId = requestAnimationFrame(this.renderTime);
    },

    getNotExistFileAssets() {
      if(this.playingState == "ENDED" || this.playingState == "NO" || (this.playingState == "PAUSED" && this.previewTime == 0)){
        var num = 0;
        this.modelRows.forEach((r, i) => {
          if(r.Type != 'Text' && r.Type != 'VTT' && r.Type != 'LH' && r.Type != 'RH' && r.Type != 'FF'){

            var file_name = true;
            if(r.Filename != ""){
              file_name = this.doesFileExist(r.Filename);
            }else{
              file_name = false;
            }

            if(file_name == false) {
              this.notExistFileAssets[num] = {};
              var Filename = r.Filename;
              Filename = Filename.replace('./samples/','');
              if(Filename == ''){
                Filename = r.Type;
              }
              this.notExistFileAssets[num]["filename"] = Filename;
              this.notExistFileAssets[num]['scene'] = r.Scene;
              this.notExistFileAssets[num]['subscene'] = r.Subscene;
              num++;
            }
          }
        });
      }
    },

    doesFileExist(urlToFile) {
        var xhr = new XMLHttpRequest();
        xhr.open('HEAD', urlToFile, false);
        xhr.send();

        if (xhr.status == "404") {
            return false;
        } else {
            return true;
        }
    },

    playBtnClicked() {
      this.notExistFileAssets = [];
      this.getNotExistFileAssets();
      if (
        this.playingState == "ENDED" ||
        this.playingState == "NO" ||
        (this.playingState == "PAUSED" && this.previewTime == 0)
      ){

        if(this.notExistFileAssets.length > 0) {
          $("#not-exist-file-assets").appendTo("body").modal("show");
        }else{
          this.start();
        }
      }
      else if (this.playingState == "PLAYING") this.pause();
      else if (this.playingState == "PAUSED") this.resume();

      this.updateState();
    },
    async loadAssets() {
      this.parseRows(this.modelRows);
      const { width: canvasWidth, height: canvasHeight } =
        this.previewSizes[this.prevSizeIdx];

      const cloneRows = this.modelRows.map(
        ({ Width, Height, Left, Top, ...r }) => {
          Left = Left > 1.0 ? +Left / canvasWidth : Left;
          Top = Top > 1.0 ? +Top / canvasHeight : Top;
          Width = Width > 1.0 ? +Width / canvasWidth : Width;
          Height = Height > 1.0 ? +Height / canvasHeight : Height;

          return {
            ...r,
            Left,
            Top,
            Width,
            Height,
          };
        }
      );

      // Simple example
      // await promiseRetry({ retries: 3, randomize: true }, (retry) => {
      //   return this.preview.loadAssets().catch(retry);
      // });

      await this.preview.init(deepCopy(cloneRows));
    },

    async start(allScene = true, switchPreloader = true) {
      try {
        this.loadingAssets = switchPreloader;
        await this.loadAssets();
        this.preview.parseScene(this.previewDuration, this.caption,this.CanvasVolumn);
        await delay(50);
        if (allScene) {
          this.$nextTick(() => {
            setTimeout( () => {
               this.preview.vCtx.volume = this.CanvasVolumn/100;
               this.preview.play();
              this.updateState();
            }, 500);
          });
        }
      } catch (error) {
        console.log("[start]", error);
      } finally {
        this.loadingAssets = false;
      }
    },
    async swapTwoRow(updateRow, newRow, flag = false) {
      const { editableRow } = newRow;
      const Scene = editableRow.Scene;
      const Start = editableRow.Start;

      updateRow.editableRow["Scene"] = Scene;
      this.updateChildrenModelRows(Scene, "Scene", updateRow.children);

      if (flag) {
        const duration = +updateRow.editableRow["Duration"];
        updateRow.editableRow["Start"] = +Start;
        updateRow.editableRow["End"] = +Start + duration;
        this.updateChildrenModelRows(Start, "Start", updateRow.children);
      }
    },
    async updateGrouperRowOrder(evt) {
      try {
        let { oldIndex, newIndex } = evt;
        if (Math.abs(oldIndex - newIndex) == 1) {
          const { swapTwoRow } = this;

          oldIndex = oldIndex - 2;
          newIndex = newIndex - 2;
          const oldGroupRow = deepCopy(this.groupedRows[oldIndex]);
          const newGroupRow = deepCopy(this.groupedRows[newIndex]);

          if (
            this.groupedRows[oldIndex].type == this.groupedRows[newIndex].type
          ) {
            swapTwoRow(this.groupedRows[oldIndex], newGroupRow, true);
            swapTwoRow(this.groupedRows[newIndex], oldGroupRow, true);
          } else {
            swapTwoRow(this.groupedRows[oldIndex], newGroupRow, false);
            swapTwoRow(this.groupedRows[newIndex], oldGroupRow, false);
          }
        }
        this.stop();
        this.modelRows = JSON.parse(JSON.stringify(this.modelRows));
        await this.loadAssets();
      } catch (error) {
        console.log("[error]", error);
      }
    },
    pause() {
      if (this.preview) {
        this.preview.pause();
      }
    },
    resume() {
      if (this.preview) {
        this.preview.resume();
      }
    },
    stop() {
      this.loadingAssets = false;

      if (this.preview) {
        this.preview.stop();
        if(this.playBtnLabel){
        this.preview.vCtx.volume = this.CanvasVolumn/100;
        }
      }

      this.updateState();
    },
    updateState() {
      this.playingState = this.preview.state;
    },
    onTimelineDown(evt) {
      this.pause();
      cancelAnimationFrame(this.animationRequestId);
    },
    onTimelineUp(evt) {
      const rect = evt.target.getBoundingClientRect();
      const secondsPerPixel =
        this.preview.vCtx.duration / (rect.right - rect.left);

      this.preview.currentTime = secondsPerPixel * (evt.x - rect.left) + 0.6;
      this.animationRequestId = requestAnimationFrame(this.renderTime);
      this.resume();
    },

    beforeLoad(id, type) {
      this.checkIsPreviewRowsChange();
      this.loadId = id;
      this.loadType = type;

      if (this.modelRows.length > 0 && this.isPreviewRowsChanged) {
        $("#new-warning-modal").appendTo("body").modal("show");
      }
      else this._loadChanges();
    },

    _loadChanges() {
      this.thumbnailPreview = null;
      this.newFontColor = [];
      this.newStrokeColor = [];
      if (this.loadType === "template") this._loadTemplate();
      else this._loadProject();
    },

    Savechanges(){
         if (this.loadType === "template") this._saveTemplate();
      else this._saveProject();
    },
    Discardchanges(){
      this.thumbnailPreview = null;
      if (this.loadType === "template") this._loadTemplate();
      else this._loadProject();
    },
    async _loadTemplate() {
      try {
        this.loadingTemplateBtn = true;
        this.oldPreviewRows = null;
        this.isPreviewRowsChanged = false;
        const response = await this.importTemplate(this.loadId);

        this.selectedTemplate = response.data.template;
        this.selectedProject = null;

        this.onImported(response.data.rows, response.data.template.file_name);
        this.loadFirstScene();
      } catch (err) {
        console.log(err);
        this.loadingTemplateBtn = false;
        toastr.error("Loading template failed");
      } finally {
        this.loadingTemplateBtn = false;
        this.loadId = null;
        this.loadType = null;
      }
    },
    openEditSceneDialog() {
      this.$bvModal.show('edit-scene-dialog')
      this.isModalVisibale=true;
    },
    onImported(rows, filename) {
      this.stop();
      this.preview.resetCtx();

      const lastRow = rows[rows.length - 1];
      const ThemeRow = (lastRow?.Type == "Frame") ? rows[rows.length - 2] : rows[rows.length - 1];

      if(lastRow?.Type == "Frame"){
        const th = this.previewSizes.find((frame) => frame.width == lastRow.Width && frame.height == lastRow.Height );
        if (th){
          this.prevSizeIdx = th.value;
        }else{
          this.prevSizeIdx = 1;
        }
        this.onCanvasSizeChange();
        rows.splice(-1, 1);
      }else{
        this.prevSizeIdx = 1;
        this.onCanvasSizeChange();
      }

      if (ThemeRow?.Type == "Theme") {
        const th = this.themes.find((theme) => theme.id == ThemeRow.Scene);
        if (th) this.onThemeChanged(th);
        rows.splice(-1, 1);
      } else{
        this.onThemeChanged({'name':'Custom'});
      }

      this.modelRows = rows.map((r, i) => ({ originalIndex: i, Timeline_Start:0, Timeline_End:0, ...r }));
      this.countNewAddedRow = 0;
      this.modelRows.forEach((row, i) => {
        if(row.Character_Count != ""){
          this.checkCharacterCount(row.Text, 'Text', row.originalIndex);
        }
      });

      this.updateTimeline();
      this.outputFile = removeExtension(filename);
    },

    updateTimeline(){
      let sceneInd = -1;
      let prevDuration = 0;
      let maxDurationForScene = 0;
      var rows = this.modelRows;
       for (let i = 0; i < rows.length; i++) {
          const row = rows[i];
          if (sceneInd != row.Scene && row.Type != 'Music') {
            sceneInd = row.Scene;
            prevDuration += maxDurationForScene;
            maxDurationForScene = 0;
          }

          if(row.Type != 'Music'){
            maxDurationForScene = Math.max(maxDurationForScene, +row.End);
          }

          this.modelRows[i]['Timeline_Start'] = +row.Start + prevDuration;
          this.modelRows[i]['Timeline_End'] = +row.End + prevDuration;
      }
    },

    beforeNewRow() {
      if (this.modelRows.length > 0) $("#new-confirm-modal").appendTo("body").modal("show");
      else this.createNewRowConfirmed();
    },

    createNewRowConfirmed() {
      const row = this.defaultRow({});
      row.originalIndex = 0;

      this.modelRows = [row];
      this.outputFile = "";
      this.selectedTheme = null;
      this.selectedProject = null;
      this.selectedTemplate = null;
      this.thumbnailPreview = null;

      if (this.preview) {
        this.preview.stop();
        this.loadFirstScene();
      }
    },

    onDeleteRowClicked(i) {
      this.modelRows.splice(i, 1);
      this.updateTimeline();
      this.sceneIdSequence();
    },

    onDeleteParentRowClicked(i) {
      var index = i[0].originalIndex;
      i.forEach((val) => {
          this.modelRows.splice(index, 1);
      });
      this.deleteParentRow = null;
      this.updateTimeline();
      this.sceneIdSequence();
    },

    showParentRowDeleteConfirmation(i) {
      this.deleteParentRow = i;
      $("#delete-parent-segment").appendTo("body").modal("show");
    },

    showSceneVideo(scene){
        if(scene){
        this.preview.currentTime=scene[0].Timeline_Start;
        }
    },

    onCopySubscene(i, removeCharacterCountText = false, Text = null) {
      const row = deepCopy(this.modelRows[i]);
      const oldModels = deepCopy(this.modelRows);
      oldModels.splice(i, 0, row);

      if(removeCharacterCountText){
        oldModels[i+1]['Character_Count'] = '';
      }
      if(Text){
        oldModels[i+1]['Text'] = Text;
      }

      this.modelRows = oldModels.map((r, ii) => ({
        ...r,
        id: ii,
        originalIndex: ii,
      }));
    },

    insertScene(i, position) {
      if (position == "above") {
        const row = this.defaultRow({
          Scene: +this.modelRows[i]["Scene"],
        });

        this.modelRows.splice(i, 0, row);
        this.modelRows.forEach((row, k) => {
          if (k > i) row.Scene = +row.Scene + 1;
        });
      } else {
        const row = this.defaultRow({
          Scene: +this.modelRows[i]["Scene"] + 1,
        });

        this.modelRows.splice(i + 1, 0, row);
        this.modelRows[i + 1]['originalIndex'] = i + 1;
        this.modelRows.forEach((row, k) => {
          if (k > i + 1) {
              row.Scene = +row.Scene + 1
          };
        });
      }
    },

    defaultRow(customColumnsObject, isTextRow = false) {
      const columnObj = {};
      for (const val of this.allColumns) {
        columnObj[val.field] = val.default;
      }

      if(isTextRow){
        return Object.assign(columnObj, customColumnsObject, {
          Filename: "",
        });
      } else {
        return Object.assign(columnObj, customColumnsObject, {
          Text: "",
          Filename: "",
        });
      }
    },

    onThemeChanged(theme) {
      if (theme.name != "Custom") {
        // this.SET_FONT_NAMES(theme.font_names.split(','))
        this.selectableFontColors = theme.font_colors.split(",");
        this.selectableStrokeColors = theme.stroke_colors.split(",");

        (theme.is_font_color_selector == '1')
          ? this.selectableFontColors.splice(this.selectableFontColors.length, 0, "Custom...")
          : this.selectableFontColors;

        (theme.is_stroke_color_selector == '1')
          ? this.selectableStrokeColors.splice(this.selectableStrokeColors.length, 0, "Custom...")
          : this.selectableStrokeColors;

        this.selectableFontNames = theme.font_names.split(",");

        const strokeColors = theme.stroke_colors.split(",");
        const defaultStrokeColor = strokeColors.includes(
          theme.default_font_color
        )
          ? theme.default_font_color
          : strokeColors[0];

        this.modelRows.forEach((row) => {
          if (!this.isMedia(row)) {
            row["Font_Name"] = row.Font_Name || theme.default_font_name;
            row["Size"] = row.Size || theme.font_size;
            row["Stroke_Color"] = row.Stroke_Color || defaultStrokeColor;
            row["Stroke_Width"] = row.Stroke_Width || theme.stroke_width;
            row["Color"] = row.Color || theme.default_font_color;
          }
        });
      }
      else{
        this.selectableFontNames = this.font_names;
        this.selectableFontColors = this.colors;
        this.selectableStrokeColors = this.colors;
      }

      this.selectedTheme = theme;
    },
      onCanvasSizeChange() {
      if (this.prevSizeIdx == 0) {
          this.resize(426, 240);
      } else if (this.prevSizeIdx == 1) {
          this.resize(640, 360);
      } else if (this.prevSizeIdx == 2) {
          this.resize(854, 480);
      } else if (this.prevSizeIdx == 3) {
          this.resize(1920, 1080);
      } else if (this.prevSizeIdx == 4) {
          this.resize(500, 500);
      } else if (this.prevSizeIdx == 5) {
          this.resize(1500, 1500);
      } else if (this.prevSizeIdx == 6) {
          this.resize(540, 960);
      } else if (this.prevSizeIdx == 7) {
          this.resize(1080, 1920);
      }

      if (this.playingState == "PLAYING" || this.playingState == "PAUSED") {
        this.stop();
        // this.start()
      }
    },

    resize(w, h) {
      this.loadFirstScene();
      $("#preview").attr("width", w);
      $("#preview").attr("height", h);
      $("#preview").width(w);
      $("#preview").height(h);
      this.$refs.slideContainer.style.width = `${w}px`;
      this.preview.setRS(this.getScaleFactor(w, h));
    },

    getScaleFactor(w, h) {
      return 1.0 / Math.max(1920 / w, 1080 / h);
    },

    addSubscene(i, row) {
      const _row = this.defaultRow({
        Scene: row["Scene"],
        Subscene: row["Subscene"],
      });
      const oldModels = deepCopy(this.modelRows);
      oldModels.splice(i + 1, 0, _row);
      this.modelRows = oldModels.map((r, id) => ({
        ...r,
        id,
        originalIndex: id,
      }));
    },

    subscribeChannel() {
      const pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
      });

      const channel = pusher.subscribe(`video-creation.${this.userId}`);

      channel.bind("video-creation-started", (data) => {
        console.log("video-creation-started", data);
        toastr.success("Video creation started.");

        this.locCreationData = {
          status: "working",
          percent: "0%",
        };
      });

      channel.bind("video-creation-completed", (data) => {
        console.log("video-creation-completed", data);

        toastr.success("Video creation has been completed.");

        this.locCreationData = {
          status: "OK",
          percent: "100%",
        };

        const outputFileName =
          this.outputFile == "" ? "Output.zip" : `${this.outputFile}.zip`;

        const link = document.createElement("a");
        link.setAttribute("download", outputFileName);
        link.href = `video_creation/zips/${data.task_id}.zip`;
        document.body.appendChild(link);
        link.click();
        link.remove();
      });

      channel.bind("video-creation-failed", (data) => {
        console.log("video-creation-failed", data);
        this.locCreationData = {
          status: "",
          percent: "",
        };

        toastr.error(data.error, "Video creation failed.");
      });

      channel.bind("video-creation-working", (data) => {
        console.log("video-creation-working", data);
        this.locCreationData = data;
      });
    },
    fileNameText(row) {
      if (this.isMedia(row.Type)) {
        if (!row.Filename) return "Drop file here...";

        const arr = row.Filename.split("/");
        const filename = row.name || arr[arr.length - 1];

        return filename.length > 22
          ? filename.substring(0, 22) + "..."
          : filename;
      }

      return "";
    },
    showLibraryModal(row) {
      this.fileToUpload = null;
      this.selectedRow = this.modelRows[row.originalIndex];
      this.selectedTags = [];

      this.$bvModal.show(`${row.Type.toLowerCase()}-modal`);
    },
      resizing(val) {

          // const cropper = this.cropperIsHalf
          //     ? this.$refs.resizingFullCropper.cropper
          //     : this.$refs.resizingFullCropper.cropper;
          // return

      },
    cropperRotate(degree) {
      const cropper = this.cropperIsHalf
        ? this.$refs.halfCropper
        : this.$refs.fullCropper;
      cropper.rotate(degree);
    },
    async showTrimVideoModal(row) {
        this.videoTrimChecked = true;
        this.selectedRow = this.modelRows[row.originalIndex];
        const {Filename:filename} = row;
        this.useFilenameForPath = true;
        const lastIndex = filename.lastIndexOf('/') + 1;
        const videoFile =  await fetch(filename);
        const publicPath = "/var/www/public/";
        const blob =  await videoFile.blob();
        const blobUrl =  await URL.createObjectURL(blob);

        this.selectedVideo = {
            url_shorten: blobUrl,
            path: publicPath + filename.slice(filename.lastIndexOf('samples/')),
            filename: filename.slice(lastIndex)
        };
        this.$bvModal.show("trim-video-modal");
    },
    showCropModal(row) {
      this.isCropInit = true;
      this.selectedRow = this.modelRows[row.originalIndex];
      const Size = "" + this.selectedRow.Size;
      const [size, position] = Size ? Size.split("-") : [];

      if (size == "full" || size == "half") {
        this.cropperSelected = {
          size,
          position,
        };
      }
      this.$bvModal.show("image-crop-modal");
      this.initCrop();
    },
    playAudio(music, idx) {
      this.stopAudio();

      this.audio = new Audio(music.url);
      this.audio.play();
      this.selectedMusics[idx].playing = true;
      this.innerMusics = this.innerMusics.map((m, i) =>
        idx == i ? { ...m, playing: true } : m
      );
    },
    stopAudio1(music, idx) {
      if (this.audio) {
        this.audio.pause();
      }
      this.selectedMusics[idx].playing = false;
    },
    stopAudio() {
      if (this.audio) {
        this.audio.pause();
      }

      this.selectedMusics = this.selectedMusics.map((m) => ({
        ...m,
        playing: false,
      }));

      this.innerMusics = this.innerMusics.map((m) => ({
        ...m,
        playing: false,
      }));
    },
    selectAudio(music) {
      this.stopAudio();
      this.selectedRow.Filename = music.url;
      this.selectedRow.name = music.name;

      this.$bvModal.hide("music-modal");
    },
    async selectImage(library) {
      try {
        let currentTime = this.preview.currentTime;
        if(library.full_url) {
          this.selectedRow.Filename = library.full_url;
        } else{
          this.selectedRow.Filename = library.url;
        }
        this.modelRows[this.selectedRow.originalIndex].Original_File_Url = library.url;
        const defaultWidthForCalcHeight = 0.4;
        this.selectedRow.Width = defaultWidthForCalcHeight;
        await this.fixImageSize();

        this.$bvModal.hide("image-modal");
        await this.loadFirstScene();
        await this.startPlayFrom(currentTime);
      } catch (error) {
        console.log(`[selectImage]`, error);
      }
    },
    selectVideo(video) {
      this.selectedVideo = video;
      this.videoTrimStart = 0.0;
      this.videoTrimEnd = video.duration;

      this.$bvModal.hide("video-modal");
      this.$bvModal.show("trim-video-modal");

      this.halfChecked = this.videoTrimChecked = false;
    },
    restoreOriginalFile() {
        this.$bvModal.hide("trim-video-modal");
        this.modelRows[this.selectedRow.originalIndex].Filename = this.selectedRow.Original_File_Url;
        this.$nextTick(() => {
            this.$refs[`showTrimButton-${this.selectedRow.originalIndex}`].click();
        });
    },
    restoreOriginalImage() {
      this.$bvModal.hide("image-crop-modal");
      this.modelRows[this.selectedRow.originalIndex].Filename = this.selectedRow.Original_File_Url;
      this.$nextTick(() => {
        this.showCropModal(this.selectedRow)
      });
    },
    async selectVideoWithoutTrim() {
      try {
        let currentTime = this.preview.currentTime;
        this.loadingUpload = true;
        if (this.fileToUpload) {
          const { data } = await this.upload(this.fileToUpload);
          this.selectedRow.Filename = data.url;
          this.selectedRow.Duration = data.duration;
          this.modelRows[this.selectedRow.originalIndex].Original_File_Url = data.url;
        } else {
          this.selectedRow.Filename = this.selectedVideo.url_shorten;
          this.selectedRow.Duration = this.selectedVideo.duration;
        }
        this.fixVideoSize();
        if (this.selectedRow) {
          const { originalIndex } = this.selectedRow;
          this.changeChildren("Video", "Type", originalIndex);
        }
        await this.loadFirstScene();
        await this.startPlayFrom(currentTime);
      } catch (error) {
        console.log("[selectVideoWithoutTrim]", error);
      } finally {
        this.loadingUpload = false;
        this.$bvModal.hide("trim-video-modal");
      }
    },

    async uploadthumbile(ev){
      var input = event.target;
      let that=this;
      if (input.files) {
        that.thumbimage=input.files[0];
        const formData = new FormData();
        formData.append("file", input.files[0]);

        try {
          const { data } = await that.uploadThumb(formData);

            var canvas = document.createElement("canvas");
            var ctx = canvas.getContext("2d");

            canvas.width = 1920; // target width
            canvas.height = 1080; // target height

            var image = new Image();

            image.onload = function(e) {
                ctx.drawImage(image,
                    0, 0, image.width, image.height,
                    0, 0, canvas.width, canvas.height
                );
                // create a new base64 encoding
                var resampledImage = new Image();
                resampledImage = canvas.toDataURL();

                 that.thumbnailPreview = resampledImage;
                 that.thumbimage=resampledImage;
            };
            image.src = data.url;
        } catch (err) {
          console.log(err);
          toastr.error("Upload failed");
        }
      }
    },

    fileInputChanged(ev, row) {
      this.beforeUpload(ev.target.files[0], this.modelRows[row.originalIndex]);
    },
    dropFile(ev, row) {
      const files = ev.dataTransfer.files;

      this.beforeUpload(files[0], row);
    },
    async beforeUpload(file, row) {
      try {
        let currentTime = this.preview.currentTime;

        if (file) {
          if (file.type.includes("video")) {
            // to be used in upload function
            this.fileToUpload = file;
            this.selectedRow = row;
            const blobUrl = URL.createObjectURL(file);
            const video = document.createElement("video");
            video.src = blobUrl;
            video.muted = true;
            let _this = this;
            video.addEventListener("loadedmetadata", function () {
              _this.selectedVideo.width = this.videoWidth;
              _this.selectedVideo.height = this.videoHeight;
            });
            this.selectedVideo = {
              url_shorten: blobUrl,
            };

            video.load();
            video.onloadeddata = () => {
              this.videoTrimEnd = video.duration;
              this.halfChecked = this.videoTrimChecked = false;
              this.$bvModal.show("trim-video-modal");
            };

            console.log("[beforeUpload]", row);
          } else if( file.type.includes("image")) {
              const { data } = await this.upload(file);
              this.selectedRow = row;
              row.Filename = data.url;
              await this.fixImageSize();

              if (data.type) {
                  row.Type = data.type;
              }
              row.Start = row.Start || 0;
              row.Duration = row.Duration || data.duration;
              this.modelRows[this.selectedRow.originalIndex].Original_File_Url = data.url;
              await this.stop();
              await this.loadFirstScene();

              this.startPlayFrom(currentTime);
              toastr.success("File upload has been finished.");
          } else {
              const { data } = await this.upload(file);
              row.Filename = data.url;

              if (data.type) {
                  row.Type = data.type;
              }
              row.Start = row.Start || 0;
              row.Duration = row.Duration || data.duration;
              this.loadFirstScene();
              toastr.success("File upload has been finished.");
          }
        }
      } catch (error) {
        toastr.error(`File upload has been failed. ${error.toString()}`);
      }
    },
    upload(file) {
      const formData = new FormData();
      formData.append("file", file);
      return this.uploadFile(formData);
    },

    // video trimming
      previewTrimmedVideo() {
      const self = this;
      const video = this.$refs.trimVideo;

      function checkTime() {
        if (video.currentTime >= self.videoTrimEnd) {
          video.pause();
        } else {
          setTimeout(checkTime, 100);
        }
      }

      video.pause();
      video.currentTime = this.videoTrimStart;
      video.play();
      checkTime();
    },
    async _trimVideo() {
      this.trimmingVideo = true;
      let path = null;

      if (this.fileToUpload) {
        try {
          const { data } = await this.upload(this.fileToUpload);
          this.modelRows[this.selectedRow.originalIndex].Original_File_Url = data.url;
          path = data.path;
        } catch (err) {
          toastr.error("File upload failed.");
          this.$bvModal.hide("trim-video-modal");
          this.trimmingVideo = false;
          return;
        }
      } else {
        path = this.selectedVideo.path;
      }
      let filename = '';

      if (this.useFilenameForPath) {
          filename = getFileNameByPath(this.selectedRow.Filename);
      }
      try {
        const { data } = await this.trimVideo({
          path,
          start: this.videoTrimStart,
          end: this.videoTrimEnd,
          trim: this.videoTrimChecked,
          halved: this.halfChecked,
          filename
        });
        this.selectedRow.Filename = data.path;

        if (this.videoTrimChecked) {
          this.selectedRow.Start = 0;
          this.selectedRow.Duration = data.duration;
          this.selectedRow.End = data.duration;
          this.videoTrimStart = 0;
          this.videoTrimEnd = data.duration;
        }
        this.fixVideoSize();
        toastr.success(data.message);
        this.useFilenameForPath = false;
        if (this.selectedRow) {
          const { originalIndex } = this.selectedRow;
          this.changeChildren("Video", "Type", originalIndex);
        }
        this.$bvModal.hide("trim-video-modal");
      } catch (err) {
        console.log(err);
        toastr.error(err.toString());
      } finally {
        this.trimmingVideo = false;
      }
    },

    // template functions
    async _exportTemplate() {
      this.loadingTemplateBtn = true;

      this.parseRows(this.modelRows);

      const currentFrame = this.previewSizes[this.prevSizeIdx];
      const frameRow = {
        Scene: this.prevSizeIdx,
        Type: "Frame",
        Width: currentFrame.width,
        Height: currentFrame.height,
      };

      if (this.selectedTheme) {
        const themeRow = {
          Scene: this.selectedTheme.id,
          Type: "Theme",
          Name: this.selectedTheme.name,
        };

        await this.exportTemplate({
          outputFile: this.outputFile,
          rows: [...this.modelRows, themeRow, frameRow],
        });
      } else {
        await this.exportTemplate({
          rows: [...this.modelRows, frameRow],
          outputFile: this.outputFile,
        });
      }

      this.loadingTemplateBtn = false;
    },

    async _exportAssets() {
      this.loadingCreateBtn = true;

      await this.exportAssets({
        rows: this.modelRows,
        outputFile: this.outputFile,
      });

      this.loadingCreateBtn = false;
    },

    async _importFile(file) {
      this.loadingProject = true;

      const formData = new FormData();

      formData.append("template", file);

      try {
        const { data } = await this.importFile(formData);

        this.selectedTemplate = null;
        console.log("[_importFile]", data);

        this.onImported(data.rows, file.name);
      } catch (err) {
        console.log(err);
        toastr.error("Importing failed");
      } finally {
        this.loadingProject = false;
      }
    },

    beforeSavingTemplate() {
      this.checkSubsceneOrdering();
      this.sameTemplate = this.templates.find((t) => t.name == this.outputFile);

      if (this.sameTemplate) {
        $("#template-rename").appendTo("body").modal("show");
      } else {
        this._saveTemplate();
      }
    },

    async _saveTemplate(isReadonly = false) {
      this.loadingTemplateBtn = true;

      this.oldPreviewRows = null;
      this.oldPreviewRows = this.modelRows;
      this.isPreviewRowsChanged = false;
      if (isReadonly) {
        $("#readonly-template").appendTo("body").modal("show");
      } else {
        this.parseRows(this.modelRows);

        const currentFrame = this.previewSizes[this.prevSizeIdx];
        const frameRow = {
          Scene: this.prevSizeIdx,
          Type: "Frame",
          Width: currentFrame.width,
          Height: currentFrame.height,
        };

        if (this.selectedTheme) {
          const themeRow = {
            Scene: this.selectedTheme.id,
            Type: "Theme",
            Name: this.selectedTheme.name,
          };

          await this.saveTemplate({
            outputFile: this.outputFile,
            rows: [...this.modelRows, themeRow, frameRow],
            themeID: (this.selectedTheme) ? this.selectedTheme.id : '',
            newFontColor: this.newFontColor,
            newStrokeColor: this.newStrokeColor
          });
        } else {
          await this.saveTemplate({
            rows:  [...this.modelRows, frameRow],
            outputFile: this.outputFile,
            themeID: (this.selectedTheme) ? this.selectedTheme.id : '',
            newFontColor: this.newFontColor,
            newStrokeColor: this.newStrokeColor
          });
        }

        await this.getTemplates();

        this.newFontColor = [];
        this.newStrokeColor = [];
      }

      this.loadingTemplateBtn = false;
    },

    // projects functions
    beforeSaveProject(isDraft = false) {
      this.checkSubsceneOrdering();
      this.isSaveAsDraft = isDraft;
      this.projectName = this.outputFile ? this.outputFile : "New Project";

      if (this.projects.find((p) => p.name == this.projectName)) {
        $("#duplicate-project-modal").appendTo("body").modal("show");
      } else {
        this._saveProject();
      }
    },

    async _saveProject() {
      let rows;
      this.oldPreviewRows = null;
      this.oldPreviewRows = this.modelRows;
      this.isPreviewRowsChanged = false;
      const currentFrame = this.previewSizes[this.prevSizeIdx];
      const frameRow = {
        Scene: this.prevSizeIdx,
        Type: "Frame",
        Width: currentFrame.width,
        Height: currentFrame.height,
      };

      if (this.selectedTheme) {
        const themeRow = {
          Scene: this.selectedTheme.id,
          Type: "Theme",
          Name: this.selectedTheme.name,
        };

        rows = [...this.modelRows, themeRow, frameRow];
      } else {
        rows = [...this.modelRows, frameRow];
      }

      try {
        this.loadingProject = true;
        this.parseRows(rows);

        await this.saveProject({
          rows,
          filename: this.projectName,
          Thumbnail: this.thumbnailPreview,
          isDraft: this.isSaveAsDraft,
          themeID: (this.selectedTheme) ? this.selectedTheme.id : '',
          newFontColor: this.newFontColor,
          newStrokeColor: this.newStrokeColor
        });
      } catch (err) {
        console.log("[err]", err);
        // toastr.error("Saving project failed");
      } finally {
        this.loadingProject = false;
        this.newFontColor = [];
        this.newStrokeColor = [];
      }

      await this.getProjects();
      await this.getProjectsDrafts();
      this.isSaveAsDraft = false;
    },

    async _loadProject() {
      this.loadingProject = true;

      try {
        this.oldPreviewRows = null;
        this.isPreviewRowsChanged = false;
        const { data } = await this.loadProject(this.loadId);

        this.selectedTemplate = null;
        this.selectedProject = data.project;
        this.thumbnailPreview = data.project.thumbnail_image;

        this.onImported(data.rows, data.project.file_name);
        this.loadFirstScene();
      } catch (err) {
        console.log(err);
        toastr.error("Project loading failed");
      } finally {
        this.loadingProject = false;
        this.loadId = null;
        this.loadType = null;
      }
    },

    async loadFirstScene() {
      this.notExistFileAssets = [];
      await this.stop();
      this.start(false, false);
    },
    async saveResizedImage() {
      await this.changeCell(this.resizingWidth, 'Width', this.selectedRow.originalIndex);
      await this.changeCell(this.resizingHeight, 'Height', this.selectedRow.originalIndex);
      await this.changeCell(Math.min(+this.resizingTop / 100, 1.0), 'Top', this.selectedRow.originalIndex);
      await this.changeCell(Math.min(+this.resizingLeft / 100, 1), 'Left', this.selectedRow.originalIndex);
      this.cropperResizing = false;
      this.$bvModal.hide("image-crop-modal");
      this.cropperSelected = null;
      this.loadFirstScene();

    },

    async _saveCroppedImage(hiddenSave = false) {
      if (this.cropperResizing) {
        await this.saveResizedImage();
        return;
      }

      this.cropping = true;

      const cropper = this.cropperIsHalf
        ? this.$refs.halfCropper.cropper
        : this.$refs.fullCropper.cropper;

      cropper.getCroppedCanvas().toBlob(async (blob) => {
        try {
          const formData = new FormData();
          formData.append("file", blob);
          const {data} = await this.saveCroppedImage(formData);
          this.selectedRow.Filename = data.data.path;

          const Size = !this.cropperIsHalf ? "full" : `half-${this.cropperPosition}`;
          this.selectedRow.Size = Size;
          const {originalIndex} = this.selectedRow;

          if (!hiddenSave) this.$bvModal.hide("image-crop-modal");

          if (originalIndex) {
            const {left, top, height, width} = this.cropPosition;
            this.changeChildren(Size, "Size", originalIndex);


            if (this.isLeftNeedSave) {
              this.changeChildren(left, 'Left', originalIndex);
            }

            if  (this.selectedRow.Top) {
              this.changeChildren(top, 'Top', originalIndex);
            }

            this.changeChildren(height, 'Height', originalIndex);
            this.changeChildren(width, 'Width', originalIndex);
          }
          await this.fixImageSize();
          this.cropperSelected = null;

          await this.loadFirstScene();
          if (!hiddenSave) toastr.success(data.message);
        } catch (error) {
          toastr.error("Cropping failed");
          console.log(error);
        } finally {
          this.cropping = false;
        }
      });
    },

    async _createVideo(option,vextension,voutput) {
      try {
        this.loadingCreateBtn = true;
        this.parseRows(this.modelRows);

        await this.createVideo({
          rows: this.modelRows,
          output: this.outputFile+'.'+vextension || "Output."+vextension,
          creationOption: option,
          dimensions:voutput,
          thumbnail: this.thumbnailPreview,
        });
      } catch (err) {
        console.log("[err]", err);
      } finally {
        this.loadingCreateBtn = false;
      }
    },

    parseRows(rows) {
      rows.forEach((r) => {
        r.End = +r.Start + +r.Duration;
      });
    },

    showCustomVisibilityModal() {
      this.tempCustomColumns = this.locCustomColumns;
      $("#custom-visibility-modal").appendTo("body").modal("show");
    },

    updateColumnVisibilityName(name) {
      this.selectedColumnVisibilityName = name;
    },

    async updateCustomColumns() {
      try {
        this.loadingCustomColumns = true;
        const response = await axios.post("/video/custom-visible-columns", {
          columns: this.tempCustomColumns,
          timeframe: this.isTimeframecolumn,
        });

        this.locCustomColumns = response.data.data.columns;
        this.timeframe = response.data.data.timeframe;
        this.allColumns = response.data.data.all_columns;
      } catch (err) {
        toastr.error("Updating custom columns failed");
      } finally {
        this.loadingCustomColumns = false;
        this.selectedColumnVisibilityName = "custom";
        $("#custom-visibility-modal").modal("hide");
      }
    },

    onTimeframeChange(event){
      this.isTimeframecolumn = event.target.value;
    },
    onImageNotFound(e) {
      if (e?.target) {
        console.log("[onImageNotFound]", e);
        e.target.src = "/img/icons/image-not-found.svg";
      }
    },
    getPercentInputValue(value) {
      if (value === 0 || value === '') {
          return '';
      }
      return (+value * 100).toFixed(2);
    },
    updatePercentInputValue: _.debounce(async function (e, originalIndex, field) {
      const {value} = e.target;

      if (value === '' || value === 0) {
          await this.changeCell('', field, originalIndex, field, originalIndex);
      }

      if ((this.modelRows[originalIndex].Type === 'Video' || this.modelRows[originalIndex].Type === 'Image') && (field === 'Height' || field === 'Width')) {
          await this.changeCell(+value / 100, field, originalIndex)
      } else if (this.modelRows[originalIndex].Type === 'Image' && field === 'Top' || field === 'Left') {
          await this.changeCell(+value / 100, field, originalIndex)
      } else {
          await this.changeCell(Math.min(+value / 100, 1.0), field, originalIndex)
      }
      this.loadFirstScene();
    }, 500),
    updateAbsoluteInputValue: _.debounce(async function (e, originalIndex, field) {
      const { value } = e.target;

      if (this.modelRows[originalIndex].Type === "Text" && field === 'Top' && value == 0
          || this.modelRows[originalIndex].Type === "Text" && field === 'Left' && value == 0) {
          await this.changeCell('', field, originalIndex)
      } else {
          await this.changeCell(+value, field, originalIndex);
      }
      this.loadFirstScene();
    }, 500),
      isPercentValue(value, row, field) {
          if (row &&  row.Type === 'Video' && field && (field === 'Width' || field === 'Height')  ) {
              return value != "" && 0.0001 <= value && value <= 10.0;
          }
          return value != "" && 0.0001 <= value && value <= 1.0;
      },
    async updateInputUnit(props) {
      try {
        const { column, row } = props;
        const { field } = column;
        const { width: canvasWidth, height: canvasHeight } =
          this.previewSizes[this.prevSizeIdx];
        let fieldValue = row[field];
        const { originalIndex } = row;

        if (fieldValue == "" || fieldValue <= 0.0001) {
          if (fieldValue == "" || fieldValue < 0.0001) {
            fieldValue = 0.0001;
          } else {
            fieldValue = 0;
          }
        } else {
          switch (field) {
            case "Width":
            case "Left": {
              if (fieldValue > 1.0) fieldValue = fieldValue / canvasWidth;
              else fieldValue = fieldValue * canvasWidth;
              break;
            }
            case "Height":
            case "Top": {
              if (fieldValue > 1.0) fieldValue = fieldValue / canvasHeight;
              else fieldValue = fieldValue * canvasHeight;
              break;
            }
          }
        }

        this.changeCell(fieldValue, field, originalIndex);

        await this.loadFirstScene();
      } catch (error) {
        console.log("[error]", error);
      }
    },
    handleSearch(e) {
      this.searchText = e.target.value;
      if (!e.target.value) {
        this.selectedMusicsLists();
      } else {
        this.selectedMusics = this.selectedMusics.filter(
          (item) =>
            String(item.name)
              .toLowerCase()
              .includes(e.target.value.toLowerCase()) ||
            String(item.tags)
              .toLowerCase()
              .includes(e.target.value.toLowerCase())
        );
      }
    },
    async cropEnd(event) {
      const cropper = this.cropperIsHalf
        ? this.$refs.halfCropper.cropper
        : this.$refs.fullCropper.cropper;
      if (event.detail.action === 'move'
        // || event.detail.action === 'crop'
      ) return;
      this.changedByCropUi = true;
      const {width: cropBoxWidth, height: cropBoxHeight, left, top} = event.target.cropper.cropBoxData;
      const {width: containerWidth, height: containerHeight} = cropper.getContainerData();
      let preparedContainerWidth = this.cropperIsHalf ? containerWidth * 2 : containerWidth;
      this.cropPosition.top = top / containerHeight;
      this.cropPosition.left = left / containerWidth;
      this.cropPosition.width = cropBoxWidth / preparedContainerWidth;
      this.cropPosition.height = await this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex, this.cropPosition.width);
    },
    addScene(newScene) {
      newScene = deepCopy(newScene);

      if (this.modelRows.length > 0 && Array.isArray(newScene)) {
          let lastSceneNumber = +this.modelRows[this.modelRows.length - 1].Scene + 1;

          newScene.forEach( (scene, index) => {
              scene.Scene = lastSceneNumber;
              scene.Subscene =  index + 1;
              scene.originalIndex = +this.modelRows[this.modelRows.length - 1].originalIndex + 1;
              this.modelRows.push(scene)
          });
          return;

      }
      newScene.Scene = 1;
      newScene.Subscene = 1;
      newScene.originalIndex = 0;

      if (this.modelRows.length > 0 && !Array.isArray(newScene)) {
          let lastSceneNumber = +this.modelRows[this.modelRows.length - 1].Scene;
          newScene.Scene = lastSceneNumber + 1;
          newScene.Subscene = 1;
          newScene.originalIndex = +this.modelRows[this.modelRows.length - 1].originalIndex + 1;
      }

      if (Array.isArray(newScene)) {
          newScene.forEach( (scene, index) => {
              scene.Scene = 1;
              scene.Subscene = index + 1;
              scene.originalIndex = index;
              this.modelRows.push(scene)
          });
      } else {
          this.modelRows.push(newScene);
      }
      this.updateTimeline()
    },

    addUserScene(newScene) {
      newScene = deepCopy(newScene);
      let lastSceneNumber = 1
      let originalIndex = 0
      if (this.modelRows.length > 0) {
        lastSceneNumber = +this.modelRows[this.modelRows.length - 1].Scene + 1
        originalIndex = +this.modelRows[this.modelRows.length - 1].originalIndex + 1
      }
      newScene.forEach( (scene, index) => {
        scene.isCustom = true
        scene.Scene = lastSceneNumber
        scene.Left = scene.Left === null ? '' : scene.Left
        scene.Top = scene.Top === null ? '' : scene.Top
        scene.originalIndex = originalIndex
        this.modelRows.push(scene)
      });
      this.updateTimeline()
    },

    addColor(){
      const { hex } = this.colorPickerDefaultColor;
      if(this.isColorPickerSelector.color_type == 'Color'){
        this.selectableFontColors.splice(-1, 0, hex)
        this.newFontColor.push(hex);
      }else{
        this.selectableStrokeColors.splice(-1, 0, hex);
        this.newStrokeColor.push(hex);
      }

      (this.isColorPickerSelector.edit_row == 'parent')
        ? this.updateChildrenModelRows(hex, this.isColorPickerSelector.color_type, this.isColorPickerSelector.row)
        : this.changeCell(hex, this.isColorPickerSelector.color_type, this.isColorPickerSelector.row);
    },

    sceneIdSequence(){

      var SceneId = (this.groupedRows[0].editableRow.Scene == 0 || this.groupedRows[0].editableRow.Scene == '') ? '' : 0;
      var lastScene = -1;
      this.groupedRows.forEach((rows,index) => {
          const { editableRow } = rows;
          const { children } = rows;

          if(editableRow.Scene != SceneId && editableRow.Scene != lastScene){
            SceneId++;
            lastScene = editableRow.Scene;
          }

          editableRow.Scene = SceneId;
          editableRow.originalIndex = index;

          children.forEach((child,i) => {
            child.Scene = SceneId
          });
      });

      const oldModels = deepCopy(this.modelRows);
      this.modelRows = oldModels.map((r, ii) => ({
        ...r,
        id: ii,
        originalIndex: ii,
      }));
    },
    async fixImageSize() {
      let expectedHeight = 1;
      let rowTop = this.selectedRow.Top;
      let width = 0;
      let height = 0;

      if (rowTop) {
        expectedHeight = +(1 - rowTop).toFixed(4);
      }
      do {

        if ( (expectedHeight - height) >= 0.07) {
          width = +(width + 0.01).toFixed(2);
        } else {
          width = +(width + 0.00001).toFixed(5);
        }
        height = await this.calculateHeightBasedOnWidth(
          this.selectedRow.originalIndex, width
        );

        if (
          width >= 1
          || width >= 0.5 && (this.selectedRow.AlignH === 'Center-LH' || this.selectedRow.AlignH === 'Center-RH')
        ) {
          break;
        }
      } while (height < expectedHeight);

      if (width >= 1) {
        this.selectedRow.Height = await this.calculateHeightBasedOnWidth(
          this.selectedRow.originalIndex, this.selectedRow.Width
        );
      } else {
        this.selectedRow.Height = height;
        this.selectedRow.Width = width;
      }
    },
    fixVideoSize() {
      let expectedHeight = 1;
      let rowTop = this.selectedRow.Top;
      let width = 0;
      let height = 0;

      if (rowTop) {
        expectedHeight = 1 - rowTop;
      }

      while (height < expectedHeight) {
        if ( (expectedHeight - height) >= 0.07) {
          width = +(width + 0.01).toFixed(2);
        } else {
          width = +(width + 0.00001).toFixed(5);
        }
        height = this.calculateHeightBasedOnWidthForVideo(width);
        if (width === height) {
          width = 1;
          height = 1;
          if (this.selectedVideo.Left) {
            width = 1 - this.selectedRow.Left;
            height = width;
            break;
          }
        }

        if (width >= 1) {
          break;
        }
      }

      if (width >= 1) {
        this.selectedRow.Height = this.calculateHeightBasedOnWidthForVideo(this.selectedRow.Width);
      } else {
        this.selectedRow.Height = height;
        this.selectedRow.Width = width;
      }

    },
    saveHeightWidth(evt){
      let {videoHeight, videoWidth} = evt.target;
      this.selectedVideo.height = videoHeight;
      this.selectedVideo.width = videoWidth;
    },
    scrollCurrentRow(){
      var currentTime = this.preview.currentTime;
      var copyGrouper = deepCopy(this.groupedRows);

      copyGrouper.forEach((headerRow, i) => {
        headerRow.vgt_header_id = i;
      });

      const childrenGrouper = copyGrouper.filter((r) => {
        return r.editableRow.Timeline_Start <= currentTime && r.editableRow.Timeline_End > currentTime && r.editableRow.Type != "Music";
      });

      var ids = 'vgt-body--' + childrenGrouper[0].vgt_header_id;
      var divid = document.getElementById(ids);
      divid.scrollIntoView(true);
      document.getElementsByClassName('vgt-responsive')[0].scrollTop -= 45;

      childrenGrouper.forEach(obj => {
        var vgt_id = 'vgt-body--' + obj.vgt_header_id;
        const curEle = document.getElementById(vgt_id);

        var elem = curEle.children[0].children[1];
        if(!elem.children[0].classList.contains('expand')){
          elem.click();
        } else {
          curEle.classList.add('highlight');
        }
        setInterval(function(){curEle.classList.remove('highlight')},2000);
      });
    },
    onCrop(evt) {
      this.isUserChangeSomeOnCrop = true;
    },
    onCropZoom(evt) {
      this.isUserChangeSomeOnCrop = true;
    },
    onChangeCropperResizing(val) {
      if(this.isUserChangeSomeOnCrop) {
        this._saveCroppedImage(true).then(
          resolve => {
            this.cropperResizing = !this.cropperResizing;
          }
        );
      }
      this.$nextTick(() => {

      })

    },
    updateCropAreaPosition(evt) {
      const {name, value} = evt.target;
      this.changedByCropUi = false;
      const cropper = this.cropperIsHalf
        ? this.$refs.halfCropper.cropper
        : this.$refs.fullCropper.cropper;
      switch (name) {
        case 'width':
          this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex, parseFloat(value) / 100).then(
            height => {
              this.cropPosition['height'] = height;
            }
          );
          break;
        case 'height':
          let width = this.calculateHeightBasedOnWidth(this.selectedRow.originalIndex,parseFloat(value) / 100, 'full', true  ).then(
            width => {
              this.cropPosition['width'] = width;
            }
          );

      }
      this.cropPosition[name] = parseFloat(value) / 100;
    },
    initCrop() {
      setTimeout(() => {
        const cropper = this.cropperIsHalf
          ? this.$refs.halfCropper.cropper
          : this.$refs.fullCropper.cropper;

        this.cropPosition = {
          top: this.preview.calculateVerticalPositionOfImage(this.selectedRow),
          left: this.preview.calculateHorizontalPositionOfImage(this.selectedRow),
          width: this.selectedRow.Width,
          height: this.selectedRow.Height
        };
        setTimeout(() => {
          cropper.setCanvasData(cropper.getCropBoxData());
        }, 500)
      }, 500);
    },
  },
};
</script>
<style lang="scss">
.btn-media {
  font-size: 13px;
}

.resize-container{
  margin-left: 114px;
}
#resize-image-input:hover{
  cursor: pointer;
}
.resize-image-label {
  cursor: pointer;
}
#resize-image-input-off {
  cursor: pointer;
}
.resize-input-container{
  margin-top:7px;
  input:hover {
    cursor: pointer;
  }
}
.slider {
  -webkit-appearance: none;
  width: 100%;
  height: 10px;
  border-radius: 5px;
  background: #d3d3d3;
  outline: none;
  opacity: 0.7;
  -webkit-transition: 0.2s;
  transition: opacity 0.2s;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2f2585;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #2819ae;
  cursor: pointer;
}
.my-tooltip {
  position: relative;
  display: inline-block;
  overflow-wrap: anywhere;
}

.my-tooltip {
  .tooltiptext {
    position: absolute;
    top: 0;
    left: 100%;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s;
    max-width: 320px;
    max-height: 320px;
    padding: 1px;
    background: transparent;
    position: absolute;
    top: 0;
    left: 100%;
    z-index: 3;
  }
}
.my-tooltip .tooltiptext .tooltip-upload {
  position: absolute;
  background: white;
  border: solid 1px;
  left: -8px;
  width: 52px;
  text-align: center;
  border-right: solid 1px lightgrey;
  z-index: 2;
}
.my-tooltip .tooltiptext > img,
.my-tooltip .tooltiptext > video {
  position: absolute;
  border: solid 1px;
  left: 43px;
  max-width: 320px;
  max-height: 320px;
}
.my-tooltip:hover .tooltiptext {
  visibility: visible !important;
  opacity: 1;
}

#test-text,
#test-img {
  position: absolute;
  visibility: hidden;
  height: auto;
  width: auto;
  white-space: nowrap;
}
#loading-ui {
  width: 426px;
  height: 240px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.media-boxed-img {
  max-width: 70px;
  max-height: 70px;
  border: solid 1px grey;
}
.crop-preview-container {
  border: solid 1px grey;
  &-full {
    width: 192px !important;
    height: 108px !important;
  }
  &-half {
    width: 96px !important;
    height: 108px !important;
  }
}

#crop-preview {
  height: 100%;
  width: 100%;
  margin: auto;
  overflow: hidden;
}
.left-half-white,
.right-half-white {
  background: white;
  position: absolute;
  top: 0;
  width: 25%;
  height: 100%;
}
.left-half-white {
  left: 0;
}
.right-half-white {
  left: 75%;
}
.download-filename {
  white-space: nowrap;
}
.img-thumb {
  max-width: 60px;
  max-height: 60px;
  border: solid 1px grey;
  object-fit: contain;
}
.top-text-area .star {
  position: absolute;
  top: 5px;
  right: 8px;
  cursor: pointer;
}
input:invalid {
  border-color: #e55353;
}
input.form-control:invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
.vgt-child-row {
  position: relative;
}
.menu-opened {
  z-index: 10 !important;
}
.audio-library-modal {
  .list-group {
    max-height: 500px;
    overflow: auto;

    .content {
      line-break: anywhere;
    }
    .action {
      flex-shrink: 0;
    }
  }

  @media (min-width: 768px) {
    max-width: 700px;
  }
}
.image-crop-modal {
  @media (min-width: 768px) {
    max-width: 750px;
    width: 750px;
  }
}
.cropper-section {
  width: 480px;
  background: #a0a0a0;
}
.cropper-wrapper {
  height: 270px;

  &-half {
    width: 240px;
  }
  &-left {
    margin-right: auto;
  }
  &-right {
    margin-left: auto;
  }
  &-full {
    width: 480px;
  }
}
.loading-success {
  opacity: 0;
}
.captionActive{
  background: #678dfd !important;
}

.image-loading-spinner {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  display: none;
}

.delete-icon
{
  max-width: 23px;
  max-height: 23px;
  margin-left: 9px;
  margin-top: -4px;
  cursor: pointer;
}

.nav-tabs .nav-link{
  padding: 10px;
}

.image-library-table tr td{
  border: none !important;
  height: 90px;
}

.image-library-table tr:last-child td{
  height: auto !important;
}

.image-library-table tr td:first-child{
  width: 24%;
}

.image-library-table tr{
  line-height: 63px;
}

.image-library-table {
  height: 450px;
}

.table-media-boxed-img {
  display: block;
  width: 100%;
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
  height: 65px;
}

.table-image-box{
  width: 65px;
  height: 65px;
  border: solid 1px grey;
}

.image-library-link{
  word-break: break-all;
  cursor: pointer;
  color: #007bff;
  line-height: normal;
}

input[id=CanvasVolumn] {
   -webkit-appearance: none;
    /* display: block; */
    /* width: 100%; */
    /* margin: 16px 0; */
    background: #3e3e3f00;
    background: #d3d3d3;
    background-repeat: no-repeat;
    height: 9px;
    border-radius: 5px;
    background-image: -webkit-gradient(linear, 20% 0%, 20% 100%, color-stop(0%, #2a1ab9), color-stop(100%, #2a1ab9))
}
input[id=CanvasVolumn]::-webkit-slider-thumb {
  -webkit-appearance: none;
  border: 1px solid #000000;
  height: 13px;
  width: 13px;
  border-radius: 3px;
  background: #ffffff;
  cursor: pointer;
   border-radius: 5px;
  //margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
  box-shadow: 1px 1px 1px #000000, 0px 0px 1px #0d0d0d; /* Add cool effects to your sliders! */
}
input[id=CanvasVolumn] {
   -webkit-appearance: none;
    /* display: block; */
    /* width: 100%; */
    /* margin: 16px 0; */
    background: #3e3e3f00;
    background: #d3d3d3;
    background-repeat: no-repeat;
    height: 9px;
    border-radius: 5px;
    background-image: -webkit-gradient(linear, 20% 0%, 20% 100%, color-stop(0%, #2a1ab9), color-stop(100%, #2a1ab9))
}
input[id=CanvasVolumn]::-webkit-slider-thumb {
  -webkit-appearance: none;
  border: 1px solid #000000;
  height: 13px;
  width: 13px;
  border-radius: 3px;
  background: #ffffff;
  cursor: pointer;
   border-radius: 5px;
  //margin-top: -14px; /* You need to specify a margin in Chrome, but in Firefox and IE it is automatic */
  box-shadow: 1px 1px 1px #000000, 0px 0px 1px #0d0d0d; /* Add cool effects to your sliders! */
}
.vc-chrome-fields .vc-input__input{
    font-size: 17px !important;
    height: 26px !important;
}
.vc-chrome-fields .vc-input__label{
  font-size: 13px !important;
}
.tools {
  input, label {
    margin-top: 5px;
  }
}
.highlight .vgt-row-header {
  background-color: #22a79085 !important;
}
table.vgt-table th.actionHeader, table.vgt-table td.actionHeader {
    position: -webkit-sticky;
    position: sticky;
    z-index: 5;
    left: 60px;
    border-left: solid 1px #dcdfe6;
}
</style>
