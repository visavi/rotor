import $ from 'jquery'
import calendarPeriod from './calendar-period'
import { Event, Prop, Selector } from './calendar-constants'

class CalendarTable {
  constructor(element) {
    this._element = element
  }

  getSelectedDate() {
    return $(this._element).prop(Prop.DATE)
  }

  setSelectedDate(date) {
    $(this._element)
      .prop(Prop.DATE, date)
      .trigger(Event.TRIGGER_CHANGE, date)

    this.setQueryDate(date)
  }

  getQueryDate() {
    return $(this._element).prop(Prop.QUERY)
  }

  setQueryDate(date) {
    this._render(date)

    $(this._element)
      .prop(Prop.QUERY, date)
      .trigger(Event.TRIGGER_QUERY, date)
  }

  _render(date) {
    const $modalCalendar = $(this._element)
    const $table = $modalCalendar.find(Selector.TABLE)
    const $tbody = $('<tbody>')
    const period = calendarPeriod(date.getFullYear(), date.getMonth())

    let currentDate = period.firstDate

    while (currentDate <= period.lastDate) {
      const $tr = $('<tr>')

      for (let weekDay = 0; weekDay < 7; weekDay++) {
        const $td = $('<td>')
        const calendarEach = $.Event(Event.TRIGGER_EACH_DATE)

        if (currentDate.getMonth() === period.firstDateInMonth.getMonth()) {
          const $a = $('<a>')
            .attr('href', '#')
            .prop(Prop.DATE, currentDate)
            .text(currentDate.getDate())
            .on(Event.ON_CLICK, (e) => {
              this.setSelectedDate($(e.target).prop(Prop.DATE))
              e.preventDefault()
            })

          calendarEach.relatedTarget = $a.get(0)

          $td.append($a)
        } else {
          $td.text(currentDate.getDate())
        }

        $tr.append($td)
        $modalCalendar.trigger(calendarEach, currentDate)

        currentDate = currentDate.addDays(1)
      }

      $tbody.append($tr)
    }

    $table.find('tbody').remove()
    $table.append($tbody)
  }
}

export default CalendarTable
