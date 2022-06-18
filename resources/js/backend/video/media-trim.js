require('../../bootstrap');

var self = this

this.trimFileId = null;
this.trimUrl = null;

this.trimVideo = function() {
  const formData = new FormData();

  const start = document.getElementById('trim-start').value
  const end = document.getElementById('trim-end').value
  const trim = document.getElementById('trim-check').checked

  formData.append('thisFolder', document.getElementById('this-folder-id').value);
  formData.append('id', self.trimFileId );
  formData.append('start', start );
  formData.append('end', end );
  formData.append('trim', trim );

  const btn = document.getElementById('trim-video-save-button')
  
  btn.setAttribute('disabled', true)
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'

  axios.post('/admin/auth/video/media/file/trim', formData, { timeout: 60000 } ).then(function(response) {
    location.reload();
  }).catch(function(err) {
    console.log(err)
  }).finally(function() {
    btn.removeAttribute('disabled')
    btn.innerHTML = 'Save'
  })
}

this.showTrimVideoModal = function(e) {
  self.trimFileId = e.target.getAttribute('atr')
  axios.get('/admin/auth/video/media/video?id=' + self.trimFileId + '&folder=' + document.getElementById('this-folder-id').value )
  .then((response) => {
    self.trimUrl = response.data.media.url;
    const source = document.createElement('source');
    const video = document.getElementById('trim-video-video');

    source.setAttribute('src', self.trimUrl);
    document.getElementById('trim-start').setAttribute('value', 0);
    document.getElementById('trim-end').setAttribute('value', response.data.media.duration);

    video.innerHTML = '';
    video.appendChild(source);
    video.load();
    video.play();
  
    $('#trim-video-modal').modal('show')
  })
  .catch(function (error) {
      console.log(error)
  })
}

this.previewVideo = function(e) {
  const video = document.getElementById('trim-video-video');
  const start = document.getElementById('trim-start').value;
  const end = document.getElementById('trim-end').value;

  function checkTime() {
      if (video.currentTime >= end) {
          video.pause();
      } else {
          setTimeout(checkTime, 100);
      }
  }

  video.pause();
  video.currentTime = start;
  video.play();
  checkTime();
}

const files = document.getElementsByClassName("file-trim-file")
for(let i = 0; i < files.length; i++){
  files[i].addEventListener('click', this.showTrimVideoModal )
}

document.getElementById('trim-video-save-button').addEventListener('click', this.trimVideo );
document.getElementById('trim-video-preview-button').addEventListener('click', this.previewVideo );