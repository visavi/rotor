import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'typing'
const NAMESPACE = `admin4b.${NAME}`
const DELAY = 700

const Event = {
  ON_INPUT: `input.${NAMESPACE}`,
  TRIGGER_INPUT_DELAY: 'input:delay'
}

const Prop = {
  INITIALIZED: `${NAMESPACE}:initialized`,
}

const Selector = {
  INPUT: 'input'
}

/*
 * Class Definition
 */

class TypingEvent {
  constructor(element) {
    this._element = element
    this._timeout = null
  }

  initialize() {
    $(this._element).on(Event.ON_INPUT, () => {
      this._watchAndTrigger()
    })
  }

  _watchAndTrigger() {
    clearTimeout(this._timeout)

    this._timeout = setTimeout(() => {
      $(this._element).trigger(Event.TRIGGER_INPUT_DELAY)
    }, DELAY)
  }

  static jQueryPlugin() {
    return this.each(function () {
      const typingEvent = new TypingEvent(this)
      typingEvent.initialize()
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = TypingEvent.jQueryPlugin
$.fn[NAME].Constructor = TypingEvent
$.fn[NAME].noConflict = () => $.fn[NAME] = TypingEvent.jQueryPlugin

/*
 * Auto Initialize
 */

$(Selector.INPUT).typing()

export default TypingEvent
