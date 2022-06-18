const removeExtension = (filename) => {
  return filename.split('.').slice(0, -1).join('.')
}

const copyToClipboard = (text) => {
	if (window.clipboardData && window.clipboardData.setData) {
    // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
    return window.clipboardData.setData("Text", text);
  } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
    var textarea = document.createElement("textarea");
    textarea.textContent = text;
    textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in Microsoft Edge.
    document.body.appendChild(textarea);
    textarea.select();
    try {
      return document.execCommand("copy");  // Security exception may be thrown by some browsers.
    }
    catch (ex) {
      console.warn("Copy to clipboard failed.", ex);
      return false;
    }
    finally {
      document.body.removeChild(textarea);
    }
  }
}

const timeFormattedString = (time) => {
  const min = (Math.floor(time / 60)).toString().padStart(2, '0');
  const sec = (Math.floor(time) % 60).toString().padStart(2, '0');

  return `${min}:${sec}`
}

const timeFormattedStringWithDecimal = (time) => {
  const min = (Math.floor(time / 60)).toString().padStart(2, '0');
  const sec = ((time) % 60).toFixed(2).toString().padStart(5, '0');

  return `${min}:${sec}`
}

const timeFromFormattedString = (str) => {
  const min = str.substring(0, 2);
  const sec = str.substring(3, 5);
  const dec = str.substring(6, 7);

  if(dec){
    return +min * 60 + +sec+'.'+dec
  } else{
    return +min * 60 + +sec
  }
}

const getValueMeasurementLineSpacing = (str) => {
  if (typeof str === 'string' && str.includes('%')) {
    return '%';
  }
  if (typeof str === 'string' && str.includes('pt')) {
    return 'pt';
  }
  if (typeof str === 'string' && str.includes('px')) {
    return 'px'
  }
  return '';
};

const getFileNameByPath = (path) => {
  return path.slice(path.lastIndexOf('/') + 1)
};

export {
  removeExtension,
  copyToClipboard,
  timeFormattedString,
  timeFromFormattedString,
  timeFormattedStringWithDecimal,
  getValueMeasurementLineSpacing,
  getFileNameByPath
}