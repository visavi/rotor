class TypeValidation {
  constructor(file) {
    this._file = file
  }

  isValid(type) {
    if (!type) return true

    const pattern = '^' + type.replaceAll(',', '|') + '$'
    return new RegExp(pattern).test(this._file.type)
  }
}

export default TypeValidation
