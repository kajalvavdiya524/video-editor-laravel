export default {
  getTemplates() {
    return axios.get('/video/templates/all')
  }
}