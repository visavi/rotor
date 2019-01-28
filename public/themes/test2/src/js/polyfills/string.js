if (!String.prototype.startsWith) {
  String.prototype.startsWith = function (searchString, position) {
    position = position || 0
    return this.indexOf(searchString, position) === position
  }
}

String.prototype.contains = function (str) {
  return new RegExp(str, "i").test(this)
}

String.prototype.replaceAll = function (search, replacement) {
  return this.replace(new RegExp(search, 'g'), replacement)
}
