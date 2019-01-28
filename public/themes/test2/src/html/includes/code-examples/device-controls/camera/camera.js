$('#modal-camera')
  .on('shown.bs.modal', function () {
    $('#camera').camera('play');
  })
  .on('hidden.bs.modal', function () {
    $('#camera').camera('stop');
  });

$('#button-snapshot').on('click', function () {
  $('#camera').camera('snapshot');
});

$('#camera').on('camera:snapshot', function (e, blob) {
  $('#snapshot-preview').attr('src', blob.dataURL);
  $('#modal-camera').modal('hide');
});
