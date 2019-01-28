import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'scrollTo'

const Event = {
  TOP: 'top',
  BOTTOM: 'bottom'
}

/*
 * Class Definition
 */

class ScrollEvent {
  constructor(element) {
    this._element = element
  }

  toBottom() {
    const scrollHeight = $(this._element).prop('scrollHeight')
    $(this._element).scrollTop(scrollHeight)
  }

  toTop() {
    $(this._element).scrollTop(0)
  }

  static jQueryPlugin(event) {
    return this.each(function () {
      const scroll = new ScrollEvent(this)

      switch (event) {
        case Event.TOP:
          scroll.toTop()
          break
        case Event.BOTTOM:
          scroll.toBottom()
          break
      }
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = ScrollEvent.jQueryPlugin
$.fn[NAME].Constructor = ScrollEvent
$.fn[NAME].noConflict = () => $.fn[NAME] = ScrollEvent.jQueryPlugin

export default ScrollEvent
