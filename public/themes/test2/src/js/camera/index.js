import $ from 'jquery'
import PlayCommand from './camera-play'
import StopCommand from './camera-stop'
import SnapshotCommand from './camera-snapshot'
import { NAME, Event } from './camera-constants'

/*
 * Class Definition
 */

class Camera {
  constructor(element) {
    this._element = element
  }

  play() {
    new PlayCommand(this._element).execute()
  }

  stop() {
    new StopCommand(this._element).execute()
  }

  snapshot() {
    new SnapshotCommand(this._element).execute()
  }

  static jQueryPlugin(event) {
    return this.each(function () {
      const camera = new Camera(this)

      switch (event) {
        case Event.PLAY:
          camera.play()
          break
        case Event.STOP:
          camera.stop()
          break
        case Event.SNAPSHOT:
          camera.snapshot()
          break
      }
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = Camera.jQueryPlugin
$.fn[NAME].Constructor = Camera
$.fn[NAME].noConflict = () => $.fn[NAME] = Camera.jQueryPlugin

export default Camera
