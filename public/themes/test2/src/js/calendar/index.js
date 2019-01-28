import $ from 'jquery'
import CalendarTable from './calendar-table'
import CalendarModal from './calendar-modal'
import { NAME, Event, Selector } from './calendar-constants'

/*
 * Class Definition
 */

class Calendar {
  constructor(element) {
    this._table = new CalendarTable(element)
  }

  getQueryDate() {
    return this._table.getQueryDate()
  }

  setQueryDate(date) {
    this._table.setQueryDate(date)
  }

  getSelectedDate() {
    return this._table.getSelectedDate()
  }

  setSelectedDate(date) {
    this._table.setSelectedDate(date)
  }

  static jQueryPlugin(event, date) {
    const calendar = new Calendar(this)
    const calendarModal = new CalendarModal(this)

    if (!calendarModal.isInitialized()) {
      calendarModal.render()
    }

    if (event === Event.DATE && !date) {
      return calendar.getSelectedDate()
    }

    if (event === Event.QUERY_DATE && !date) {
      return calendar.getQueryDate()
    }

    return this.each(function () {
      date = date instanceof Date ? date : calendar.getQueryDate() || new Date()
      date.setHours(0, 0, 0, 0)

      switch (event) {
        case Event.DATE:
          calendar.setSelectedDate(date)
          break
        case Event.QUERY_DATE:
          calendar.setQueryDate(date)
          break
        case Event.QUERY_NEXT_MONTH:
          calendar.setQueryDate(date.addMonths(1))
          break
        case Event.QUERY_NEXT_YEAR:
          calendar.setQueryDate(date.addYears(1))
          break
        case Event.QUERY_PREVIOUS_MONTH:
          calendar.setQueryDate(date.addMonths(-1))
          break
        case Event.QUERY_PREVIOUS_YEAR:
          calendar.setQueryDate(date.addYears(-1))
          break
      }
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = Calendar.jQueryPlugin
$.fn[NAME].Constructor = Calendar
$.fn[NAME].noConflict = () => $.fn[NAME] = Calendar.jQueryPlugin

export default Calendar
