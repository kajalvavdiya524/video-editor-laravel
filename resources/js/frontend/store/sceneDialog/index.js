
const module = {
  namespaced: true,
  state: {
    sceneEditore: {},
  },
  getters: {
    getscene:(state) =>{
      console.log('state',state);
      return state.sceneEditore
    }
  },
  actions: {
    async InsertScene({ commit },payload) {
        console.log('commit', payload);
      commit("SET_SCENE", payload);
    },
   },

  mutations: {
    SET_SCENE(state, sceneEditore) {
      state.sceneEditore = sceneEditore;
    },
  },


};

export default module;
