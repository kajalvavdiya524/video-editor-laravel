export default {
  importFile(data) {
    return axios.post('/video/import', data)
  },

  importTemplate(id) {
    return axios.get(`/video/templates/${id}`)
  },

  exportTemplate(data) {
    return axios({
        url: '/video/export-template',
        method: 'POST',
        data: { data },
        responseType: 'blob', // important
    })
  },

  saveTemplate(rows, filename, themeID, newFontColor, newStrokeColor) {
    return axios({
        url: '/video/export-template-to-server',
        method: 'POST',
        data: { rows, filename, themeID, newFontColor, newStrokeColor}
    })
  },

  createVideo(output, rows, creationOption,dimensions, thumbnail) {
    return axios({
        url: '/video/create-video',
        method: 'POST',
        data: { output, rows, creationOption,dimensions, thumbnail }
    })
  },

  exportAssets(data) {
    return axios({
        url: '/video/export-assets',
        method: 'POST',
        data: { data },
        // responseType: 'blob', // important
    })
  },

  uploadFile(data) {
    return axios.post('/video/upload_file', data)
  },

  uploadThumb(data) {
    return axios.post('/video/upload_thumb', data)
  },

  trimVideo(data) {
    return axios.post('/video/media/file/trim-video', data, { timeout: 60000 })
  },

  getThemes() {
    return axios.get('/video/api/themes')
  },

  saveCroppedImage(data) {
    return axios.post('/video/crop-image', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}