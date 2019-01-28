import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'spinner'

const ClassName = {
  SHOW: 'show'
}

const Event = {
  HIDE: 'hide',
  SHOW: 'show'
}

/*
 * Class Definition
 */

class Spinner {
  constructor(element) {
    this._element = element
  }

  show() {
    $(this._element).addClass(ClassName.SHOW)
  }

  hide() {
    $(this._element).removeClass(ClassName.SHOW)
  }

  static jQueryPlugin(event) {
    return this.each(function () {
      const spinner = new Spinner(this)

      switch (event) {
        case Event.SHOW:
          spinner.show()
          break
        case Event.HIDE:
          spinner.hide()
          break
      }
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = Spinner.jQueryPlugin
$.fn[NAME].Constructor = Spinner
$.fn[NAME].noConflict = () => $.fn[NAME] = Spinner.jQueryPlugin

export default Spinner
