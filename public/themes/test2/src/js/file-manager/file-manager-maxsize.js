import io from 'inoutjs'

const sizeRegex = /^(\d*\.?\d+)(B|KB|MB|GB)?$/gi

class MaxsizeValidation {
  constructor(file) {
    this._file = file
  }

  isValid(maxsize) {
    if (!maxsize) return true
    if (!maxsize.match(sizeRegex)) return false

    const matches = sizeRegex.exec(maxsize)
    const size = matches[1]
    const unit = matches[2]

    return io(this._file).lowerOrEqual(size, unit)
  }
}

export default MaxsizeValidation
