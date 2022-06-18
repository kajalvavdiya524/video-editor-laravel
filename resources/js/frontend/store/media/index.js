import mediaAPI from "../../services/api/media";

const module = {
  namespaced: true,
  state: {
    themes: [],
  },

  actions: {
    async importFile({ commit }, formData) {
      try {
        const response = await mediaAPI.importFile(formData);

        return response;
      } catch (error) {
        console.log(error);
      }
    },

    async importTemplate({ commit }, id) {
      try {
        const response = await mediaAPI.importTemplate(id);

        return response;
      } catch (error) {
        throw error;
      }
    },

    async createVideo({ commit }, { output, rows, creationOption,dimensions, thumbnail }) {
      try {
        await mediaAPI.createVideo(output, rows, creationOption,dimensions, thumbnail);
      } catch (error) {
        throw error;
      }
    },

    async exportTemplate({ commit }, { rows, outputFile }) {
      const filename = outputFile ? outputFile : "New Template";

      try {
        const response = await mediaAPI.exportTemplate(rows);

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `${filename}.xlsx`);
        document.body.appendChild(link);
        link.click();
      } catch (err) {
        throw err;
      }
    },

    async exportAssets({ commit }, { rows, outputFile }) {
      const filename = outputFile ? outputFile : "New Assets";

      try {
        const response = await mediaAPI.exportAssets(rows);

        const link = document.createElement("a");
        link.setAttribute("download", `${filename}.zip`);
        link.href = response.data.asset_path;
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (err) {
        console.log(err);
        throw err;
      }
    },

    async saveTemplate({ commit }, { rows, outputFile, themeID, newFontColor, newStrokeColor }) {
      const filename = outputFile ? outputFile : "Output";

      try {
        const { data } = await mediaAPI.saveTemplate(rows, filename, themeID, newFontColor, newStrokeColor);

        toastr.success(data.message);
      } catch (error) {
        toastr.error(error.response.data.message);
        throw error;
      }
    },

    async uploadFile({ commit }, payload) {
      try {
        const response = await mediaAPI.uploadFile(payload);

        return response;
      } catch (err) {
        throw err;
      }
    },

    async uploadThumb({ commit }, payload) {
      try {
        const response = await mediaAPI.uploadThumb(payload);

        return response;
      } catch (err) {
        throw err;
      }
    },

    async trimVideo({ commit }, payload) {
      try {
        const response = await mediaAPI.trimVideo(payload);

        return response;
      } catch (err) {
        console.log(err);
      }
    },

    async getThemes({ commit }) {
      try {
        const response = await mediaAPI.getThemes();

        commit("SET_THEMES", response.data.themes);
      } catch (err) {
        throw err;
      }
    },

    async saveCroppedImage({ commit }, payload) {
      try {
        const response = await mediaAPI.saveCroppedImage(payload);

        return response;
      } catch (err) {
        throw err;
      }
    },
  },

  mutations: {
    SET_THEMES(state, themes) {
      state.themes = themes;
    },
  },
};

export default module;
