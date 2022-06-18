require('../../bootstrap');

const coreui = require('@coreui/coreui');

$(document).ready(() => {
  const toastElList = [].slice.call(document.querySelectorAll('.toast'))
  const toastList = toastElList.map((toastEl) => {
    return new coreui.Toast(toastEl, {
      autohide: false
    })
  })

  toastList.forEach((t) => {
    t.show()
  })

  const deleteRequest = (e) => {
    const id = e.currentTarget.getAttribute('data-id')

    axios.delete(`admin/auth/video/comments/${id}`)
    .then((response) => {
      location.reload()
    })
    .catch(function (error) {
      console.log(error)
    })
  }

  const deletes = document.getElementsByClassName("btn-delete")

  for(let i = 0; i < deletes.length; i++) {
    deletes[i].addEventListener('click', deleteRequest )
  }
})