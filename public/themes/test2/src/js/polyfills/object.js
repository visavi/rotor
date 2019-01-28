import browser from '../util/browser'

if (browser.isIE10 || browser.isIE11) {
  Object.keys = function (obj) {
    const keys = []

    if (!obj) {
      return keys
    }

    for (const p in obj) {
      if (obj.hasOwnProperty(p)) {
        keys.push(p)
      }
    }

    return keys
  }
}
