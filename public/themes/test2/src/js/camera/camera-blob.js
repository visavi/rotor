class CameraBlob {
  constructor(dataURL) {
    this._dataURL = dataURL
  }

  toBlob() {
    let byteString

    if (this._dataURL.split(',')[0].indexOf('base64') >= 0) {
      byteString = atob(this._dataURL.split(',')[1])
    } else {
      byteString = unescape(this._dataURL.split(',')[1])
    }

    const mimeType = this._dataURL.split(',')[0].split(':')[1].split('')[0]
    const uintArray = new Uint8Array(byteString.length)

    for (let i = 0; i < byteString.length; i++) {
      uintArray[i] = byteString.charCodeAt(i)
    }

    return new Blob([uintArray], { type: mimeType })
  }
}

export default CameraBlob
