import $ from 'jquery'
import { ClassName, Event, Prop, Selector } from './input-suggestion-constants'
import InputSuggestionItem from './input-suggestion-item'

/*
 * Class Definition
 */

class InputSuggestionMode {
  constructor(element) {
    this.$input = $(element)
    this.$suggestion = this.$input.closest(Selector.INPUT_SUGGESTION)
    this.$suggestionList = this.$suggestion.find(Selector.INPUT_SUGGESTION_LIST)
    this.$loadingList = this.$suggestionList.find(Selector.LIST_GROUP_LOADING)
    this.$emptyList = this.$suggestionList.find(Selector.LIST_GROUP_EMPTY)
    this.$itemList = this.$suggestionList.find(Selector.LIST_GROUP_ITEMS)
  }

  configureAsyncMode() {
    this.$loadingList.hide()
    this.$emptyList.hide()
    this.$itemList.hide()

    this.$input
      .on(Event.ON_INPUT, () => {
        this.$loadingList.show()
        this.$emptyList.hide()
        this.$itemList.hide()
      })
      .on(`${Event.ON_INPUT_DELAY} ${Event.TRIGGER_SHOW}`, () => {
        this.$input.trigger(Event.TRIGGER_SEARCH)
      })
  }

  configureStaticMode() {
    this.$emptyList.hide()

    const $items = this.$itemList.children()
    let detachedItems = []

    this.$input.on(`${Event.ON_INPUT} ${Event.TRIGGER_SHOW}`, () => {
      $items.removeClass(ClassName.ACTIVE)

      detachedItems.forEach(($item) => {
        var $prev = $item.prop(Prop.PREVIOUS_ITEM)

        if ($prev.length) {
          $item.insertAfter($prev)
        } else {
          this.$itemList.prepend($item)
        }
      })

      detachedItems = []

      $items.each((i, item) => {
        const $item = $(item)
        const itemText = new InputSuggestionItem(item).text()

        if (!itemText.contains(this.$input.val())) {
          detachedItems.push($item)
          $item.prop(Prop.PREVIOUS_ITEM, $item.prev())
        }
      })

      detachedItems.forEach(($item) => $item.detach())

      if (this.$itemList.children().length) {
        this.$emptyList.hide()
        this.$itemList.show()
      } else {
        this.$emptyList.show()
        this.$itemList.hide()
      }
    })
  }
}

export default InputSuggestionMode
