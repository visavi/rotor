import $ from 'jquery'
import { Event, Prop } from './camera-constants'

navigator.getSupportedUserMedia =
  navigator.getUserMedia ||
  navigator.webkitGetUserMedia ||
  navigator.mozGetUserMedia ||
  navigator.msGetUserMedia

class PlayCommand {
  constructor(element) {
    this._element = element
  }

  execute() {
    const $video = $(this._element)

    if ($video.prop(Prop.PLAYING)) {
      return
    }

    const mediaConstraint = { video: true, audio: false }

    if (navigator.mediaDevices) {
      navigator.mediaDevices.getUserMedia(mediaConstraint)
        .then((stream) => this._onSuccess(stream))
        .catch((error) => this._onFailure(error))
    } else if (navigator.getSupportedUserMedia) {
      navigator.getSupportedUserMedia(mediaConstraint,
        (stream) => this._onSuccess(stream),
        (error) => this._onFailure(error))
    } else {
      $video.trigger(Event.TRIGGER_NOT_SUPPORTED)
    }
  }

  _play(stream) {
    const video = $(this._element).get(0)

    if ('srcObject' in video) {
      video.srcObject = stream
    } else if (navigator.mozGetUserMedia) {
      video.mozSrcObject = stream
    } else {
      const windowURL = window.URL || window.webkitURL || window.mozURL || window.msURL
      video.src = windowURL.createObjectURL(stream)
    }

    video.onloadedmetadata = () => video.play()
  }

  _onSuccess(stream) {
    this._play(stream)

    $(this._element)
      .prop(Prop.STREAM, stream)
      .prop(Prop.PLAYING, true)
      .trigger(Event.TRIGGER_PLAY)
  }

  _onFailure(error) {
    $(this._element).trigger(Event.TRIGGER_ERROR, error)
  }
}

export default PlayCommand
