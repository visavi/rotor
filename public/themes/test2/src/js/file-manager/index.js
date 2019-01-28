import $ from 'jquery'
import TypeValidation from './file-manager-type'
import MaxsizeValidation from './file-manager-maxsize'

/*
 * Constants
 */

const NAME = 'fileManager'
const NAMESPACE = `admin4b.${NAME}`

const DataAttribute = {
  DATA_MAXSIZE: 'data-maxsize',
  DATA_TYPE: 'data-type'
}

const Event = {
  ON_CLICK: `click.${NAMESPACE}`,
  ON_CHANGE: `change.${NAMESPACE}`,
  TRIGGER_CLICK: 'click',
  TRIGGER_CHANGE: 'file:change'
}

const Error = {
  MAXSIZE: 'maxsize',
  TYPE: 'type'
}

const Prop = {
  INITIALIZED: `${NAMESPACE}:initialized`,
}

const Selector = {
  DATA_TOGGLE: '[data-toggle="file-manager"]'
}

/*
 * Class Definition
 */

class FileManager {
  constructor(element) {
    this._element = element
  }

  initialize() {
    const $element = $(this._element)

    if ($element.prop(Prop.INITIALIZED)) return

    const $input = $('<input/>').attr('type', 'file')

    $input.on(Event.ON_CHANGE, () => {
      const file = $input.get(0).files[0]

      if (file) {
        this._validate(file)
        $element.trigger(Event.TRIGGER_CHANGE, file)
      }
    })

    $element.on(Event.ON_CLICK, () => $input.trigger(Event.TRIGGER_CLICK))
    $element.prop(Prop.INITIALIZED, true)
  }

  _validate(file) {
    const maxsize = $(this._element).attr(DataAttribute.DATA_MAXSIZE)
    const type = $(this._element).attr(DataAttribute.DATA_TYPE)

    file.errors = []

    if (!new MaxsizeValidation(file).isValid(maxsize)) {
      file.errors.push(Error.MAXSIZE)
    }

    if (!new TypeValidation(file).isValid(type)) {
      file.errors.push(Error.TYPE)
    }
  }

  static jQueryPlugin() {
    return this.each(function () {
      const fileManager = new FileManager(this)
      fileManager.initialize()
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = FileManager.jQueryPlugin
$.fn[NAME].Constructor = FileManager
$.fn[NAME].noConflict = () => $.fn[NAME] = FileManager.jQueryPlugin

/*
 * Auto Initialize
 */

$(Selector.DATA_TOGGLE).fileManager()

export default FileManager
