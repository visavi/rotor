import $ from 'jquery'
import { NAME, Event, Prop, Selector } from './input-suggestion-constants'
import InputSuggestionMode from './input-suggestion-mode'
import InputSuggestionList from './input-suggestion-list'

/*
 * Class Definition
 */

class InputSuggestion {
  constructor(element) {
    this._element = element
    this._inputSuggestionList = new InputSuggestionList(element)
  }

  isAsync() {
    return $(this._element).is(Selector.DATA_ASYNC)
  }

  isInitialized() {
    return $(this._element).prop(Prop.INITIALIZED)
  }

  initialize() {
    if (this.isInitialized()) return

    this._inputSuggestionList.bindEvents()

    if (this.isAsync()) {
      new InputSuggestionMode(this._element).configureAsyncMode()
    } else {
      new InputSuggestionMode(this._element).configureStaticMode()
    }

    $(this._element).prop(Prop.INITIALIZED, true)
  }

  refresh() {
    if (!this.isAsync()) return
    this._inputSuggestionList.refresh()
  }

  static jQueryPlugin(event) {
    return this.each(function () {
      var inputSuggestion = new InputSuggestion(this)

      inputSuggestion.initialize()

      if (event === Event.REFRESH) {
        inputSuggestion.refresh()
      }
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = InputSuggestion.jQueryPlugin
$.fn[NAME].Constructor = InputSuggestion
$.fn[NAME].noConflict = () => $.fn[NAME] = InputSuggestion.jQueryPlugin

/*
 * Auto Initialize
 */

$(Selector.DATA_TOGGLE).suggestion()

export default InputSuggestion
