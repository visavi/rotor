const calendarPeriod = (year, month) => {
  const firstDateInMonth = new Date(year, month, 1)
  const lastDateInMonth = new Date(year, month + 1, 0)

  const firstDate = new Date(firstDateInMonth)
  firstDate.setDate(firstDateInMonth.getDate() - firstDateInMonth.getDay())

  const lastDate = new Date(lastDateInMonth)
  lastDate.setDate(lastDateInMonth.getDate() + (6 - lastDateInMonth.getDay()))

  return {
    firstDateInMonth: firstDateInMonth,
    lastDateInMonth: lastDateInMonth,
    firstDate: firstDate,
    lastDate: lastDate
  }
}

export default calendarPeriod
