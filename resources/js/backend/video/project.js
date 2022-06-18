require('../../bootstrap');
import Sortable from 'sortablejs';

$(document).ready(() => {
  // const baseUrl = document.head.querySelector('meta[name="api-base-url"]').content
  const baseUrl = ''

  // init sortable
  const el = document.getElementById('sortable-list')
  const sortable = Sortable.create(el, {
    handle: '#sortable-list > tr > td:first-child',
    animation: 150,
    onEnd: (evt) => {
      axios.post(`${baseUrl}/projects/update-order`, {
        orders: sortable.toArray()
      })
      .then((response) => {
      })
      .catch(function (error) {
        toastr.error('Something went wrong.')
      })
    }
  })

  const saveBtn = document.getElementById('update-btn')
  const deleteBtn = document.getElementById('delete-btn')

  const projectNameInput = document.getElementById('project-name')
  const projectFileNameInput = document.getElementById('project-file-name')
  const projectVisibilityCheck = document.getElementById('project-visibility')

  let projectId

  const parseError = (e) => {
    let errors = Object.values(e)
    errors = errors.flat()
    
    return errors
  }

  const showEditModal = (e) => {
    document.getElementById('errors').innerHTML = ''
    
    projectId = e.currentTarget.getAttribute('attr')

    axios.get(`${baseUrl}/video/projects/${projectId}`)
    .then(function(response) {
      projectNameInput.value = response.data.project.name
      projectFileNameInput.value = response.data.project.file_name
      projectVisibilityCheck.checked = response.data.project.visibility

      $('#edit-modal').modal('show')
    }).catch(function(err) {
      toastr.error('Something went wrong.')
    })
  }

  const updateProject = () => {
    const name = projectNameInput.value
    const file_name = projectFileNameInput.value
    const visibility = projectVisibilityCheck.checked

    saveBtn.setAttribute('disabled', true)

    axios.put(`${baseUrl}/video/projects/${projectId}`, {
      name,
      file_name,
      visibility
    })
    .then(function(response) {
      location.reload();
    }).catch(function(err) {
      const errors = parseError(err.response.data.errors)
      const errorMessage = errors.map((err) => {
        return `<li>
                  ${err}
                </li>`
      })

      document.getElementById('errors').innerHTML = `<div class="alert alert-danger" role="alert"><ul>${errorMessage}</ul></div>`
    }).finally(function() {
      saveBtn.removeAttribute('disabled')
    })
  }

  const showDeleteConfirmModal = (e) => {
    projectId = e.currentTarget.getAttribute('attr')
    $('#delete-confirm-modal').modal('show')
  }

  const deleteProject = (e) => {
    axios.delete(`${baseUrl}/video/projects/${projectId}`)
    .then((response) => {
      location.reload()
    })
    .catch(function (error) {
      toastr.error('Something went wrong.')
    })
  }

  const toggleVisibility = (e) => {
    const target = e.currentTarget
    const visibility = target.getAttribute('visibility') == 'visible'

    projectId = target.getAttribute('attr')
    target.setAttribute('disabled', true)

    axios.put(`${baseUrl}/video/projects/${projectId}`, {
      visibility: !visibility
    })
    .then((response) => {
      if (response.data.data.project.visibility) {
        target.setAttribute('visibility', 'visible')
        target.parentElement.parentElement.children[4].innerHTML = '<span class="badge badge-pill badge-success">Visible</span>'
        target.textContent = 'Hide'
      } else {
        target.setAttribute('visibility', 'invisible')
        target.parentElement.parentElement.children[4].innerHTML = '<span class="badge badge-pill badge-secondary">Invisible</span>'
        target.textContent = 'Show'
      }
    })
    .catch(function (error) {
      toastr.error('Something went wrong.')
    })
    .finally(() => {
      target.removeAttribute('disabled')
    })
  }

  const edits = document.getElementsByClassName("btn-edit")
  const deletes = document.getElementsByClassName("btn-delete")
  const shows = document.getElementsByClassName("btn-show")

  for(let i = 0; i < shows.length; i++) {
    shows[i].addEventListener('click', toggleVisibility )
  }

  for(let i = 0; i < deletes.length; i++) {
    deletes[i].addEventListener('click', showDeleteConfirmModal )
  }

  for(let i = 0; i < edits.length; i++) {
    edits[i].addEventListener('click', showEditModal )
  }

  deleteBtn.addEventListener('click', deleteProject );
  saveBtn.addEventListener('click', updateProject );
});