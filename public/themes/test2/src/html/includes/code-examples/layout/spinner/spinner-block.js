$('#spinner-toggle').on('click', function () {
  $('#spinner').spinner('show');

  setTimeout(function () {
    $('#spinner').spinner('hide');
  }, 3000);
});
