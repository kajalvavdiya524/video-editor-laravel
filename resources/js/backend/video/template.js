window.ENTITY_URL = '/video/add-or-remove-company'
require('../../bootstrap');
require('./common')

import Sortable from 'sortablejs';

$(document).ready(() => {
  // init sortable
  const el = document.getElementById('sortable-list')
  const sortable = Sortable.create(el, {
    handle: '#sortable-list > tr > td:first-child',
    animation: 150,
    onEnd: (evt) => {
      axios.post('/video/templates/update-order', {
        orders: sortable.toArray()
      })
      .then((response) => {
      })
      .catch(function (error) {
         console.log(error)
      })
    }
  })

  const saveBtn = document.getElementById('update-btn')
  const deleteBtn = document.getElementById('delete-btn')
  const templateNameInput = document.getElementById('template-name')
  const templateFileNameInput = document.getElementById('template-file-name')
  const templateReadonlyCheck = document.getElementById('template-readonly')

  let templateId

  const parseError = (e) => {
    let errors = Object.values(e)
    errors = errors.flat()
    
    return errors
  }

  const showEditModal = (e) => {
    document.getElementById('errors').innerHTML = ''
    
    templateId = e.currentTarget.getAttribute('attr')

    axios.get(`/video/templates/${templateId}`)
    .then(function(response) {
      templateNameInput.value = response.data.template.name
      templateFileNameInput.value = response.data.template.file_name
      templateReadonlyCheck.checked = response.data.template.readonly

      $('#edit-modal').modal('show')
    }).catch(function(err) {
      console.log(err)
    })
  }

  const updateTemplate = () => {
    const name = templateNameInput.value
    const file_name = templateFileNameInput.value
    const readonly = templateReadonlyCheck.checked

    saveBtn.setAttribute('disabled', true)

    axios.put(`/video/templates/${templateId}`, {
      name,
      file_name,
      readonly
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

  const updateReadonly = (e) => {
    templateId = e.currentTarget.getAttribute('attr')

    axios.put(`/video/templates/${templateId}`, {
      readonly: e.currentTarget.checked
    })
    .then(function(response) {
      // location.reload();
    }).catch(function(err) {
      console.log(err)
    })
  }

  const showDeleteConfirmModal = (e) => {
    templateId = e.currentTarget.getAttribute('attr')
    $('#delete-confirm-modal').modal('show')

  }

  const deleteTemplate = (e) => {
    axios.delete(`/video/templates/${templateId}`)
    .then((response) => {
      location.reload()
    })
    .catch(function (error) {
       console.log(error)
    })
  }

  const toggleVisibility = (e) => {
    const target = e.currentTarget
    const visibility = target.getAttribute('visibility') == 'visible'

    templateId = target.getAttribute('attr')
    target.setAttribute('disabled', true)

    axios.put(`/video/templates/${templateId}`, {
      visibility: !visibility
    })
    .then((response) => {
      if (response.data.data.template.visibility) {
        target.setAttribute('visibility', 'visible')
        target.parentElement.parentElement.children[3].innerHTML = '<span class="badge badge-pill badge-success">Visible</span>'
        target.textContent = 'Hide'
      } else {
        target.setAttribute('visibility', 'invisible')
        target.parentElement.parentElement.children[3].innerHTML = '<span class="badge badge-pill badge-secondary">Invisible</span>'
        target.textContent = 'Show'
      }
    })
    .catch(function (error) {
       console.log(error)
    })
    .finally(() => {
      target.removeAttribute('disabled')
    })
  }

  const edits = document.getElementsByClassName("btn-edit")
  const deletes = document.getElementsByClassName("btn-delete")
  const readonlys = document.getElementsByClassName("btn-readonly")
  const visibilities = document.getElementsByClassName("btn-visibility")

  for(let i = 0; i < edits.length; i++) {
    edits[i].addEventListener('click', showEditModal )
  }

  for(let i = 0; i < deletes.length; i++) {
    deletes[i].addEventListener('click', showDeleteConfirmModal )
  }

  for(let i = 0; i < readonlys.length; i++) {
    readonlys[i].addEventListener('change', updateReadonly )
  }

  for(let i = 0; i < visibilities.length; i++) {
    visibilities[i].addEventListener('click', toggleVisibility )
  }

  deleteBtn.addEventListener('click', deleteTemplate );
  saveBtn.addEventListener('click', updateTemplate );
});