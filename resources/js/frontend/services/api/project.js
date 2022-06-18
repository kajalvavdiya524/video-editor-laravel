export default {
  getProjects(params = {}) {
    return axios.get('/video/projects/all', {params})
  },
  saveProject(data) {
    return axios.post('/video/projects', data)
  },
  loadProject(id) {
    return axios.get(`/video/projects/${id}`)
  }
}