import $ from 'jquery'
import keyboard from '../util/keyboard'
import viewport from '../util/viewport'
import { ClassName, Event, Selector } from './input-suggestion-constants'
import InputSuggestionItem from './input-suggestion-item'

/*
 * Class Definition
 */

class InputSuggestionList {
  constructor(element) {
    this.$input = $(element)
    this.$suggestion = this.$input.closest(Selector.INPUT_SUGGESTION)
    this.$suggestionList = this.$suggestion.find(Selector.INPUT_SUGGESTION_LIST)
    this.$loadingList = this.$suggestionList.find(Selector.LIST_GROUP_LOADING)
    this.$emptyList = this.$suggestionList.find(Selector.LIST_GROUP_EMPTY)
    this.$itemList = this.$suggestionList.find(Selector.LIST_GROUP_ITEMS)
  }

  bindEvents() {
    this._bindInputEvents()
    this._bindItemEvents()
  }

  refresh() {
    this._bindItemEvents()

    if (this.$itemList.children().length) {
      this.$itemList.show()
    } else {
      this.$emptyList.show()
    }

    this.$loadingList.hide()
  }

  _bindItemEvents() {
    const $items = this.$itemList.children()

    $items.on(Event.ON_CLICK, (e) => {
      this.$suggestion.removeClass(ClassName.OPEN)
      this._changeSelectedItem(e.currentTarget)
    })

    this.$input.on(Event.ON_KEYPRESS, (e) => {
      if (keyboard(e).isEnter) {
        const $ativeItem = $items.filter(Selector.ACTIVE)

        if ($ativeItem.length) {
          this._changeSelectedItem($ativeItem)
        }
      }
    })
  }

  _bindInputEvents() {
    this.$suggestion.onFocusOut(() => this._hide())

    this.$input
      .on(Event.ON_INPUT, () => this._show())
      .on(Event.ON_KEYUP, (e) => {
        if (keyboard(e).isEscape) {
          this._hide()
        }
      })
      .on(Event.ON_KEYDOWN, (e) => {
        const key = keyboard(e)

        if (key.isArrowUp || key.isArrowDown) {
          e.preventDefault()

          if (!this.$suggestion.is(Selector.OPEN)) {
            this._show()
            return
          }

          const $currentItem = this.$itemList.children(Selector.ACTIVE)
          let $activeItem = null

          if (key.isArrowDown) {
            if ($currentItem.is(Selector.LAST_CHILD)) {
              return
            }

            if ($currentItem.length) {
              $currentItem.removeClass(ClassName.ACTIVE)
              $activeItem = $currentItem.next().addClass(ClassName.ACTIVE)
            } else {
              $activeItem = this.$itemList.children().eq(0).addClass(ClassName.ACTIVE)
            }
          }

          if (key.isArrowUp) {
            if ($currentItem.is(Selector.FIRST_CHILD)) {
              viewport(this.$input)
              return
            }

            $currentItem.removeClass(ClassName.ACTIVE)
            $activeItem = $currentItem.prev().addClass(ClassName.ACTIVE)
          }

          viewport($activeItem)
        }
      })
      .on(Event.TRIGGER_CHANGE, () => this._hide())
  }

  _changeSelectedItem(selectedItem) {
    this.$input.val(new InputSuggestionItem(selectedItem).text())
    this.$input.trigger(Event.TRIGGER_CHANGE, selectedItem)
  }

  _hide() {
    if (!this.$suggestion.is(Selector.OPEN)) return

    this.$suggestion.removeClass(ClassName.OPEN)
    this.$input.trigger(Event.TRIGGER_HIDE)

    viewport(this.$input)
  }

  _show() {
    if (this.$suggestion.is(Selector.OPEN)) return

    this.$itemList.children().removeClass(ClassName.ACTIVE)
    this.$suggestion.addClass(ClassName.OPEN)
    this.$input.trigger(Event.TRIGGER_SHOW)
  }
}

export default InputSuggestionList
