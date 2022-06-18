window.ENTITY_URL = '/admin/auth/video/themes/add-or-remove-company'
require('../../bootstrap');
require('./common')

$(document).ready(() => {
  const deleteBtn = document.getElementById('delete-btn')

  let themeId

  const showDeleteConfirmModal = (e) => {
    themeId = e.currentTarget.getAttribute('attr')
    $('#delete-confirm-modal').modal('show')
  }

  const deleteProject = (e) => {
    axios.delete(`/admin/auth/video/themes/${themeId}`)
    .then((response) => {
      location.reload()
    })
    .catch(function (error) {
       console.log(error)
    })
  }

  const deletes = document.getElementsByClassName("btn-delete")

  for(let i = 0; i < deletes.length; i++) {
    deletes[i].addEventListener('click', showDeleteConfirmModal )
  }

  deleteBtn.addEventListener('click', deleteProject );
});