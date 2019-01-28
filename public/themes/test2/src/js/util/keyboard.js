const letterRegex = /[a-zA-Z]/

const keyboard = function (e) {
  const code = e.which || e.keyCode
  const char = String.fromCharCode(code)

  const key = {
    isAlt: code === 18,
    isAltHeld: e.altKey,
    isArrowDown: code === 40,
    isArrowLeft: code === 37,
    isArrowRight: code === 39,
    isArrowUp: code === 38,
    isBackspace: code === 8,
    isCapsLock: code === 20,
    isCtrl: code === 17,
    isCtrlHeld: e.ctrlKey,
    isDelete: code === 46 || code === 63272, /* Safari */
    isEnd: code === 35,
    isEnter: code === 13,
    isEscape: code === 27,
    isF1: code === 112,
    isF2: code === 113,
    isF3: code === 114,
    isF4: code === 115,
    isF5: code === 116,
    isF6: code === 117,
    isF7: code === 118,
    isF8: code === 119,
    isF9: code === 120,
    isF10: code === 121,
    isF11: code === 122,
    isF12: code === 123,
    isHome: code === 36,
    isInsert: code === 45,
    isLetter: letterRegex.test(char),
    isNumber: code >= 48 && code <= 57 || code >= 96 && code <= 105,
    isPageDown: code === 34,
    isPageUp: code === 33,
    isPauseBreak: code === 19,
    isShift: code === 16,
    isShiftHeld: e.shiftKey,
    isSpace: code === 32,
    isTab: code === 9
  }

  key.isAlphanumeric = key.isLetter || key.isNumber
  key.isArrow = key.isArrowDown || key.isArrowLeft || key.isArrowRight || key.isArrowUp
  key.isNavigation = key.isArrow || key.isPageUp || key.isPageDown || key.isHome || key.isEnd
  key.isCommand = key.isBackspace || key.isDelete || key.isTab || key.isEscape || key.isEnter || key.isSpace
  key.isSpecial = key.isCapsLock || key.isCtrl || key.isAlt || key.isShift
  key.isFunction = key.isF1 || key.isF2 || key.isF3 || key.isF4 || key.isF5 || key.isF6 || key.isF7 ||
    key.isF8 || key.isF9 || key.isF10 || key.isF11 || key.isF12

  return key
}

export default keyboard
