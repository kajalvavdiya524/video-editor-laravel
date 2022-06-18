import templateAPI from '../../services/api/template'

const module = {
  namespaced: true,
  state: {
    templates: []
  },

  actions: {
    async getTemplates({ commit }) {
      const response = await templateAPI.getTemplates()
      commit('SET_TEMPLATES', response.data.templates)
    }
  },

  mutations: {
    SET_TEMPLATES(state, templates) { state.templates = templates }
  }
}

export default module