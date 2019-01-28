import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'onFocusOut'
const NAMESPACE = `admin4b.${NAME}`

const Event = {
  ON_CLICK: `click.${NAMESPACE}`,
  ON_FOCUS: `focus.${NAMESPACE}`,
}

/*
 * Class Definition
 */

class FocusOutEvent {
  constructor(element) {
    this._element = element
  }

  trigger(event, callback) {
    if (!$(event.target).closest($(this._element)).length) {
      if (typeof callback === 'function') {
        callback.call(this._element)
      }
    }
  }

  static jQueryPlugin(callback) {
    return this.each(function () {
      const $document = $(document)
      const focusOutEvent = new FocusOutEvent(this)
      const focusOutFunction = (e) => focusOutEvent.trigger(e, callback)

      $document.on(Event.ON_CLICK, focusOutFunction)
      $document.find('input').on(Event.ON_FOCUS, focusOutFunction)
      $document.find('select').on(Event.ON_FOCUS, focusOutFunction)
      $document.find('textarea').on(Event.ON_FOCUS, focusOutFunction)
      $document.find('button').on(Event.ON_FOCUS, focusOutFunction)
      $document.find('a').on(Event.ON_FOCUS, focusOutFunction)
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = FocusOutEvent.jQueryPlugin
$.fn[NAME].Constructor = FocusOutEvent
$.fn[NAME].noConflict = () => $.fn[NAME] = FocusOutEvent.jQueryPlugin

export default FocusOutEvent
