const NAME = 'suggestion'
const NAMESPACE = `admin4b.${NAME}`

const ClassName = {
  ACTIVE: 'active',
  INPUT_SUGGESTION: 'input-suggestion',
  INPUT_SUGGESTION_LIST: 'input-suggestion-list',
  LIST_GROUP: 'list-group',
  LIST_GROUP_ITEMS: 'list-group.items',
  LIST_GROUP_EMPTY: 'list-group.empty',
  LIST_GROUP_LOADING: 'list-group.loading',
  OPEN: 'open'
}

const DataAttribute = {
  DATA_TEXT: 'data-text'
}

const Event = {
  ON_CLICK: `click.${NAMESPACE}`,
  ON_INPUT: `input.${NAMESPACE}`,
  ON_INPUT_DELAY: 'input:delay',
  ON_KEYDOWN: `keydown.${NAMESPACE}`,
  ON_KEYPRESS: `keypress.${NAMESPACE}`,
  ON_KEYUP: `keyup.${NAMESPACE}`,
  REFRESH: 'refresh',
  TRIGGER_CHANGE: `${NAME}:change`,
  TRIGGER_SHOW: `${NAME}:show`,
  TRIGGER_HIDE: `${NAME}:hide`,
  TRIGGER_SEARCH: `${NAME}:search`
}

const Prop = {
  INITIALIZED: `${NAMESPACE}.initialized`,
  PREVIOUS_ITEM: `${NAMESPACE}.previousItem`,
}

const Selector = {
  ACTIVE: `.${ClassName.ACTIVE}`,
  INPUT: 'input',
  DATA_TOGGLE: `[data-toggle="${NAME}"]`,
  DATA_ASYNC: '[data-async]',
  DATA_TEXT: `[${DataAttribute.DATA_TEXT}]`,
  FIRST_CHILD: ':first-child',
  INPUT_SUGGESTION: `.${ClassName.INPUT_SUGGESTION}`,
  INPUT_SUGGESTION_LIST: `.${ClassName.INPUT_SUGGESTION_LIST}`,
  LIST_GROUP_LOADING: `.${ClassName.LIST_GROUP_LOADING}`,
  LIST_GROUP_EMPTY: `.${ClassName.LIST_GROUP_EMPTY}`,
  LIST_GROUP_ITEMS: `.${ClassName.LIST_GROUP_ITEMS}`,
  LAST_CHILD: ':last-child',
  OPEN: `.${ClassName.OPEN}`
}

export {
  NAME,
  ClassName,
  DataAttribute,
  Event,
  Prop,
  Selector
}
