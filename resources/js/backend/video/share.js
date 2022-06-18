require('../../bootstrap');

window.toastr = require('toastr')
import { copyToClipboard } from '../../frontend/services/helpers'

$(document).ready(() => {
  $('#emails').select2({
    tags: true,
    width: '100%'
  })

  const shareNameInput = document.getElementById('share-name')
  const shareFileNameInput = document.getElementById('share-file-name')

  const saveBtn = document.getElementById('update-btn')
  const deleteBtn = document.getElementById('delete-btn')

  let shareId

  const parseError = (e) => {
    let errors = Object.values(e)
    errors = errors.flat()
    
    return errors
  }
  
  const showDeleteConfirmModal = (e) => {
    shareId = e.currentTarget.getAttribute('data-id')
    $('#delete-confirm-modal').modal('show')
  }

  const deleteShare = (e) => {
    axios.delete(`/admin/auth/video/shares/${shareId}`)
    .then((response) => {
      location.reload()
    })
    .catch(function (error) {
       console.log(error)
    })
  }

  const showEditModal = (e) => {
    document.getElementById('errors').innerHTML = ''
    
    shareId = e.currentTarget.getAttribute('data-id')

    axios.get(`/admin/auth/video/shares/${shareId}`)
    .then(function(response) {
      shareNameInput.value = response.data.share.name
      shareFileNameInput.value = response.data.share.file_name

      $('#edit-modal').modal('show')
    }).catch(function(err) {
      console.log(err)
    })
  }

  const updateShare = () => {
    const name = shareNameInput.value
    const file_name = shareFileNameInput.value

    saveBtn.setAttribute('disabled', true)

    axios.put(`/admin/auth/video/shares/${shareId}`, {
      name,
      file_name
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

  const shareVideo = (e) => {
    e.preventDefault()

    const t = e.currentTarget
    const uuid = t.getAttribute('data-uuid')
    const id = t.getAttribute('data-id')
    const tos = $('#emails').select2('data').map((to) => to.id)
    const subject = document.getElementById('subject').value
    let body = document.getElementById('message').value

    body = body.replace('<link>', `http://dev2.rapidads.io/admin/auth/video/video-review/${uuid}`)

    t.setAttribute('disabled', true)

    axios.post(`/admin/auth/video/shares/send`, {
      subject,
      body,
      tos,
      share_id: id
    })
    .then((response) => {
      toastr.success('Successfully sent')
    }).catch((err) => {
      toastr.error('Sharing failed')
    }).finally(() => {
      t.removeAttribute('disabled')
    })
  }

  const previewVideo = (e) => {
    e.preventDefault()

    const path = e.currentTarget.getAttribute('path-mp4')

    document.getElementById('source-mp4').setAttribute('src', path)
    document.getElementById('video-mp4').load()

    $('#preview-modal').modal('show')
  }

  const copyUrl = (e) => {
    const uuid = e.currentTarget.getAttribute('data-uuid')
    
    copyToClipboard(`http://dev2.rapidads.io/admin/auth/video/video-review/${uuid}`);
  }

  const deletes = document.getElementsByClassName("btn-delete")
  const edits = document.getElementsByClassName("btn-edit")
  const shares = document.getElementsByClassName("btn-share")
  const previews = document.getElementsByClassName("btn-preview")
  const copies = document.getElementsByClassName("btn-copy")

  for(let i = 0; i < edits.length; i++) {
    edits[i].addEventListener('click', showEditModal )
  }

  for(let i = 0; i < deletes.length; i++) {
    deletes[i].addEventListener('click', showDeleteConfirmModal )
  }

  for(let i = 0; i < shares.length; i++) {
    shares[i].addEventListener('click', shareVideo )
  }

  for(let i = 0; i < previews.length; i++) {
    previews[i].addEventListener('click', previewVideo )
  }

  for(let i = 0; i < copies.length; i++) {
    copies[i].addEventListener('click', copyUrl )
  }

  deleteBtn.addEventListener('click', deleteShare )
  saveBtn.addEventListener('click', updateShare )
});