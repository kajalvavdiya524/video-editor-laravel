import sceneApi from '../../services/api/scenes'

const module = {
    namespaced: true,
    state: {
        scenesData: {},
        loadingData: {
          getScene: false,
          addScene: false,
          deleteScene: false
        },
    },
    getters: {
        getScenesData(state) {
            return state.scenesData
        },
        getLoadingData(state) {
            return state.loadingData
        }
    },
    mutations: {
        SET_SCENES(state, scenesData) { state.scenesData = scenesData },
        SET_LOADING_DATA(state, payload) {
            state.loadingData = {
                ...state.loadingData,
                ...payload
            }
        }
    },
    actions: {
        async getScenes({ commit }, page = 1) {
            commit('SET_LOADING_DATA', { getScene: true })
            const { data } = await sceneApi.getScenes(page)
            commit('SET_SCENES', data)
            commit('SET_LOADING_DATA', { getScene: false })
        },
        async addScene({ dispatch, commit }, payload) {
            commit('SET_LOADING_DATA', { getScene: true, addScene: true })
            await sceneApi.addScene(payload)
            await dispatch('getScenes');
            commit('SET_LOADING_DATA', { getScene: false, addScene: false })
        },
        async editScene({ dispatch, commit }, payload) {
            commit('SET_LOADING_DATA', { getScene: true })
            await sceneApi.editScene(payload)
            await dispatch('getScenes');
            commit('SET_LOADING_DATA', { getScene: false })
        },
        async deleteScene({ dispatch, commit  }, id) {
            commit('SET_LOADING_DATA', { getScene: true, deleteScene: true })
            await sceneApi.deleteScene(id)
            await dispatch('getScenes');
            commit('SET_LOADING_DATA', { getScene: false, deleteScene: false })
        }
    },
}

export default module
