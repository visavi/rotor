import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'observe'

const Event = {
  PROPERTY_CHANGE: 'DOMAttrModified onpropertychange'
}

/*
 * Class Definition
 */

class ObserveEvent {
  constructor(element) {
    this._element = element
  }

  onChange(callback) {
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (typeof callback === 'function') {
          callback.apply(mutation.target)
        }
      })
    })

    $(this._element).on(Event.PROPERTY_CHANGE, () => {
      if (typeof callback === 'function') {
        callback.apply(this)
      }
    })

    observer.observe(this._element, { attributes: true })
  }

  static jQueryPlugin(callback) {
    return this.each(function () {
      new ObserveEvent(this).onChange(callback)
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = ObserveEvent.jQueryPlugin
$.fn[NAME].Constructor = ObserveEvent
$.fn[NAME].noConflict = () => $.fn[NAME] = ObserveEvent.jQueryPlugin

export default ObserveEvent
