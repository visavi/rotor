import $ from 'jquery'

const $viewport = $('.app .app-content .content-body')

export default function viewport(element) {
  const viewportHeight = $viewport.height()
  const viewportOffset = $viewport.offset()
  const viewportTop = $viewport.scrollTop()
  const viewportBottom = viewportTop + viewportHeight

  const $element = $(element)
  const elementOffset = $element.offset()

  if (!elementOffset) return

  const elementTop = (elementOffset.top - viewportOffset.top) + viewportTop
  const elementBottom = elementTop + $element.outerHeight()

  const isVisibleTop = elementTop > viewportTop
  const isVisibleBottom = elementBottom < viewportBottom

  if (!isVisibleTop) {
    $viewport.scrollTop(elementTop)
  }

  if (!isVisibleBottom) {
    $viewport.scrollTop(elementBottom - viewportHeight)
  }
}
