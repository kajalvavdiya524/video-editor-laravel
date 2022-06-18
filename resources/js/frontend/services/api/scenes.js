export default {
    getScenes(page) {
        return axios.get(`/video/scene/get?page=${page}`)
    },
    addScene(data) {
        return axios.post('/video/scene/save', data)
    },
    editScene(data) {
        return axios.post('/video/scene/edit', data)
    },
    deleteScene(id) {
        return axios.delete(`/video/scene/delete/${id}`)
    }
}