const userAgent = window.navigator.userAgent

export default {
  isIE10: userAgent.indexOf("MSIE ") > 0,
  isIE11: userAgent.indexOf('Trident/') > 0
}
