import api from "../../services/api/project";

const module = {
  namespaced: true,
  state: {
    projects: [],
    projectsDrafts: [],
  },

  actions: {
    async getProjects({ commit }) {
      const response = await api.getProjects();
      commit("SET_PROJECTS", response.data.projects);
    },
    async getProjectsDrafts({commit}) {
      const response = await api.getProjects({drafts:true});
      commit("SET_PROJECTS_DRAFTS", response.data.projectsDrafts);
    },
    async saveProject({ commit }, payload) {
      try {
        const { data } = await api.saveProject(payload);
        toastr.success(data.message);
      } catch (err) {
        throw err;
      }
    },
    async loadProject({ commit }, id) {
      try {
        const response = await api.loadProject(id);

        return response;
      } catch (err) {
        throw err;
      }
    },
  },

  mutations: {
    SET_PROJECTS(state, projects) {
      state.projects = projects;
    },
    SET_PROJECTS_DRAFTS(state, projects) {
      state.projectsDrafts = projects;
    },
  },

  getters: {
    projectsDrafts(state) {
      return state.projectsDrafts;
    }
  },
};

export default module;
