import $ from 'jquery'
import CameraBlob from './camera-blob'
import { DataAttribute, Event, Prop } from './camera-constants'

const sizeRegex = /^(\d+)x(\d+)$/gi

class SnapshotCommand {
  constructor(element) {
    this._element = element
  }

  execute() {
    const $video = $(this._element)

    if (!$video.prop(Prop.PLAYING)) {
      return
    }

    const video = $video.get(0)
    const size = this._getSize($video.attr(DataAttribute.DATA_SIZE))

    const canvas = document.createElement('canvas')
    canvas.width = size.width
    canvas.height = size.height

    const context = canvas.getContext('2d')
    context.translate(size.width, 0) // 'translate' and 'scale' to flip horizontally
    context.scale(-1, 1)
    context.drawImage(video, 0, 0, size.width, size.height)

    const dataURL = canvas.toDataURL()
    const blob = new CameraBlob(dataURL).toBlob()
    blob.dataURL = dataURL

    $video.trigger(Event.TRIGGER_SNAPSHOT, blob)
  }

  _getSize(size) {
    if (!size || !size.match(sizeRegex)) {
      return { width: 320, height: 240 }
    }

    const matches = sizeRegex.exec(size)

    return { width: matches[1], height: matches[2] }
  }
}

export default SnapshotCommand
