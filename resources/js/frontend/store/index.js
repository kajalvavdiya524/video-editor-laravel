import Vue from 'vue'
import Vuex from 'vuex'

// Global vuex
import media from './media'
import template from './template'
import project from './project'
import sceneDialog from './sceneDialog'
import scenes from './scenes'

Vue.use(Vuex)

/**
 * Main Vuex Store
 */
const store = new Vuex.Store({
  modules: {
    project,
    template,
    media,
    sceneDialog,
    scenes,
  }
})

export default store
