const NAME = 'calendar'
const NAMESPACE = `admin4b.${NAME}`

const ClassName = {
  ACTIVE: 'active',
  TODAY: 'today',
  SUNDAY: 'sunday'
}

const DataAttribute = {
  DATA_MONTH: 'data-calendar-month'
}

const Event = {
  DATE: 'date',
  QUERY_DATE: 'query:date',
  QUERY_NEXT_MONTH: 'query:nextMonth',
  QUERY_NEXT_YEAR: 'query:nextYear',
  QUERY_PREVIOUS_MONTH: 'query:previousMonth',
  QUERY_PREVIOUS_YEAR: 'query:previousYear',
  ON_CLICK: `click.${NAMESPACE}`,
  ON_KEYDOWN: `keydown.${NAMESPACE}`,
  ON_INPUT: `input.${NAMESPACE}`,
  TRIGGER_EACH_DATE: `${NAME}:each`,
  TRIGGER_CHANGE: `${NAME}:change`,
  TRIGGER_QUERY: `${NAME}:query`
}

const Prop = {
  INITIALIZED: `${NAMESPACE}.initialized`,
  DATE: `${NAMESPACE}.date`,
  MONTH: `${NAMESPACE}.month`,
  QUERY: `${NAMESPACE}.query`
}

const Selector = {
  CALENDAR: '.modal-calendar',
  HEADER: '.modal-header',
  BODY: '.modal-body',
  INPUT_YEAR: '.form-control',
  MONTH_DROPDOWN: '.dropdown .btn',
  MONTH_DROPDOWN_OPTIONS: '.dropdown-menu',
  MONTH_DROPDOWN_OPTION: 'a',
  LINK_PREVIOUS_MONTH: '[data-calendar-month=previous]',
  LINK_NEXT_MONTH: '[data-calendar-month=next]',
  LINK_TODAY: '[data-calendar-link=today]',
  LINK_SELECTED_DATE: '[data-calendar-link=selected]',
  TABLE: '.table-calendar'
}

export {
  NAME,
  ClassName,
  DataAttribute,
  Event,
  Prop,
  Selector
}
