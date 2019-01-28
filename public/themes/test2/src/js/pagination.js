import $ from 'jquery'

/*
 * Constants
 */

const NAME = 'pagination'
const NAMESPACE = `admin4b.${NAME}`

const Default = {
  CURRENT_PAGE: 1,
  PAGE_LENGTH: 10,
  COUNT: 1
}

const ELLIPSIS = '...'

const Event = {
  ON_CLICK: `click.${NAMESPACE}`
}

/*
 * Class Definition
 */

class Pagination {
  constructor(element) {
    this._element = element
  }

  update(options) {
    this._options = this._options || {}
    this._options.count = Number(options && options.count) || this._options.count || Default.COUNT
    this._options.current = Number(options && options.current) || this._options.current || Default.CURRENT_PAGE
    this._options.length = Number(options && options.length) || this._options.length || Default.PAGE_LENGTH
    this._options.pages = Math.ceil(Math.max(this._options.count, 1) / Math.max(this._options.length, 1))

    const pages = this._pages()
    this._render(pages)
  }

  _pages() {
    const interval = 1
    const left = this._options.current - interval
    const right = this._options.current + interval + 1
    const pages = []

    for (let page = 1, previous = null; page <= this._options.pages; page++) {
      if (page === 1 || page === this._options.pages || (page >= left && page < right)) {
        if (previous) {
          if (page - previous === 2) {
            pages.push(previous + 1)
          } else if (page - previous !== 1) {
            pages.push(ELLIPSIS)
          }
        }
        pages.push(page)
        previous = page
      }
    }

    return pages
  }

  _goToPage(page) {
    if (page !== this._options.current) {
      this.update({ current: page })
      $(this._element).trigger('page:change', this._options)
    }
  }

  _render(pages) {
    const $pages = []

    for (let page of pages) {
      const $a = $(`<a href="#" class="page-link">${page}</a>`)
        .on(Event.ON_CLICK, () => this._goToPage(page))

      const $li = $('<li class="page-item">')
        .toggleClass('active', page === this._options.current)
        .toggleClass('disabled', page === ELLIPSIS)
        .append($a)

      $pages.push($li)
    }

    $(this._element).html($pages)
  }

  static jQueryPlugin(options) {
    return this.each(function () {
      const pagination = new Pagination(this)
      pagination.update(options)
    })
  }
}

/*
 * jQuery Plugin
 */

$.fn[NAME] = Pagination.jQueryPlugin
$.fn[NAME].Constructor = Pagination
$.fn[NAME].noConflict = () => $.fn[NAME] = Pagination.jQueryPlugin

export default Pagination
