import $ from 'jquery'
import { Event, Prop } from './camera-constants'

class StopCommand {
  constructor(element) {
    this._element = element
  }

  execute() {
    const $video = $(this._element)

    if (!$video.prop(Prop.PLAYING)) {
      return
    }

    const stream = $video.prop(Prop.STREAM)

    if (stream.getVideoTracks && typeof stream.getVideoTracks === 'function') {
      const tracks = stream.getVideoTracks()

      if (tracks && tracks[0] && tracks[0].stop) {
        tracks[0].stop()
      }
    } else if (stream.stop) {
      stream.stop() // Deprecated, may be removed in the near future
    }

    $video.prop(Prop.PLAYING, false)
    $video.prop(Prop.STREAM, null)

    $video.trigger(Event.TRIGGER_STOP)
  }
}

export default StopCommand
