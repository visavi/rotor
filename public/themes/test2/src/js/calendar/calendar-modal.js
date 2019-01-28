import $ from 'jquery'
import keyboard from '../util/keyboard'
import { ClassName, DataAttribute, Event, Prop, Selector } from './calendar-constants'

const yearRegex = /^\d{4}$/

class CalendarModal {
  constructor(element) {
    this._element = element
  }

  isInitialized() {
    return $(this._element).prop(Prop.INITIALIZED)
  }

  render() {
    const $modalCalendar = $(this._element)
    const $modalHeader = $modalCalendar.find(Selector.HEADER)
    const $modalBody = $modalCalendar.find(Selector.BODY)
    const $monthDropdown = $modalHeader.find(Selector.MONTH_DROPDOWN)
    const $monthDropdownOptions = $modalHeader.find(Selector.MONTH_DROPDOWN_OPTIONS)
    const $inputYear = $modalHeader.find(Selector.INPUT_YEAR)
    const $linkPreviousMonth = $modalHeader.find(Selector.LINK_PREVIOUS_MONTH)
    const $linkNextMonth = $modalHeader.find(Selector.LINK_NEXT_MONTH)
    const $linkToday = $modalBody.find(Selector.LINK_TODAY)
    const $linkSelectedDate = $modalBody.find(Selector.LINK_SELECTED_DATE)

    $modalCalendar.prop(Prop.INITIALIZED, true)

    $modalCalendar.on(Event.TRIGGER_EACH_DATE, (e, date) => {
      const selectedDate = $modalCalendar.calendar(Event.DATE)
      const today = new Date()

      today.setHours(0, 0, 0, 0)

      $(e.relatedTarget)
        .toggleClass(ClassName.ACTIVE, !!selectedDate && date.getTime() === selectedDate.getTime())
        .toggleClass(ClassName.TODAY, date.getTime() === today.getTime())
        .toggleClass(ClassName.SUNDAY, date.getDay() === 0)
    })

    $modalCalendar.on(Event.TRIGGER_QUERY, (e, date) => {
      const month = date.getMonth()
      const $month = $($monthDropdownOptions.find(Selector.MONTH_DROPDOWN_OPTION).get(month))

      $monthDropdown.prop(Prop.MONTH, date.getMonth()).text($month.text())
      $inputYear.val(date.getFullYear())
    })

    $monthDropdownOptions.find(Selector.MONTH_DROPDOWN_OPTION).on(Event.ON_CLICK, (e) => {
      const year = Number($inputYear.val())
      const month = Number($(e.target).attr(DataAttribute.DATA_MONTH))

      $modalCalendar.calendar(Event.QUERY_DATE, new Date(year, month, 1))

      e.preventDefault()
    })

    $linkNextMonth.on(Event.ON_CLICK, () => {
      $modalCalendar.calendar(Event.QUERY_NEXT_MONTH)
    })

    $linkPreviousMonth.on(Event.ON_CLICK, () => {
      $modalCalendar.calendar(Event.QUERY_PREVIOUS_MONTH)
    })

    $linkToday.on(Event.ON_CLICK, () => {
      $modalCalendar.calendar(Event.QUERY_DATE, new Date())
    })

    $linkToday.on(Event.ON_CLICK, () => {
      $modalCalendar.calendar(Event.QUERY_DATE, new Date())
    })

    $linkSelectedDate.on(Event.ON_CLICK, () => {
      const date = $modalCalendar.calendar(Event.DATE)
      $modalCalendar.calendar(Event.QUERY_DATE, date)
    })

    $inputYear
      .on(Event.ON_KEYDOWN, (e) => {
        const key = keyboard(e)

        if (key.isNavigation || key.isCommand || key.isSpecial || key.isFunction) {
          return
        }

        if (!key.isNumber && !key.isCtrlHeld) {
          e.preventDefault()
        }

        // maxlength not working for input[type=number] in Chrome or Firefox
        if (key.isNumber && $inputYear.val().length >= 4) {
          e.preventDefault()
        }
      })
      .on(Event.ON_INPUT, () => {
        const val = $inputYear.val()

        if (yearRegex.test(val)) {
          const year = Number(val)
          const month = Number($monthDropdown.prop(Prop.MONTH))

          $modalCalendar.calendar(Event.QUERY_DATE, new Date(year, month, 1))
        }
      })
  }
}

export default CalendarModal
