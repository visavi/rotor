var $calendar = $('#calendar');
var $btnApply = $('#btnApply')

$calendar.on('show.bs.modal', function (e) {
  var $formControl = $(e.relatedTarget)
    .closest('.form-group')
    .find('.form-control');

  $btnApply.prop('target', $formControl);
  $calendar.calendar('date', $formControl.prop('date') || new Date());
});

$btnApply.on('click', function () {
  var $target = $btnApply.prop('target');
  var date = $calendar.calendar('date');
  var formattedDate = moment(date).format('dddd, MMMM D, YYYY');

  $target.prop('date', date).text(formattedDate);
});
