Date.prototype.addDays = function (days) {
  const date = new Date(this.getTime())
  date.setDate(this.getDate() + days)

  return date
}

Date.prototype.addMonths = function (months) {
  const year = this.getFullYear()
  const month = this.getMonth() + months
  const date = new Date(year, month, 1)
  const lastDate = new Date(year, month + 1, 0)

  if (this.getDate() > lastDate.getDate()) {
    date.setDate(lastDate.getDate())
  } else {
    date.setDate(this.getDate())
  }

  return date
}

Date.prototype.addYears = function (years) {
  const date = new Date(this.getTime())
  date.setFullYear(this.getFullYear() + years)

  return date
}
