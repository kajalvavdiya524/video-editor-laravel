require('../../bootstrap');

$(document).ready(function () {
  let mediaId = 0

  $('#tags-select').select2({
    tags: true
  });
  $('#tags-select-search').select2();

  const saveBtn = document.getElementById('tag-save-btn')
  const btns = document.getElementsByClassName("tag-btn")

  const saveTags = (e) => {
    saveBtn.setAttribute('disabled', true)

    const allTags = $('#tags-select').find('option').get().map((t) => t.innerText)
    const selectedTags = $('#tags-select').find(':selected').get().map((t) => t.innerText)

    axios.put('/admin/auth/video/media/tags', {
      allTags,
      selectedTags,
      mediaId
    })
    .then((response) => {
      location.reload()
    }).catch((err) => {
      console.log(err)
    }).finally(() => {
      saveBtn.removeAttribute('disabled')
    })
  }

  const showTagModal = (e) => {
    mediaId = e.target.getAttribute('atr')
    axios.get('/admin/auth/video/media/tags?id=' + mediaId)
    .then((response) => {
      $('#tags-select').val(response.data.tags).trigger('change')
      $('#tag-modal').modal('show')
    })
    .catch(function (error) {
      console.log(error)
    })
  }

  for(let i = 0; i < btns.length; i++) {
    btns[i].addEventListener('click', showTagModal )
  }

  saveBtn.addEventListener('click', saveTags );
});