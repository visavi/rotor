$modalCalendar.on('calendar:change', function (e, date) {
  console.log('Selected date: ' + date);
});

$modalCalendar.on('calendar:each', function (e, date) {
  console.log('Anchor tag: ' + e.relatedTarget); // Only for dates in the queried month and year
  console.log('Current date: ' + date);
});

$modalCalendar.on('calendar:query', function (e, date) {
  console.log('Queried date' + date);
});
