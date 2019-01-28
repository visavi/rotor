$('#myCamera').on('camera:play', function (e) {
  console.log('Camera is playing.');
});

$('#myCamera').on('camera:error', function (e, error) {
  console.log('Could not start the camera: ' + error); // Original error from browser
});

$('#myCamera').on('camera:notSupported', function (e, error) {
  console.log('Camera not supported.');
});

$('#myCamera').on('camera:stop', function (e) {
  console.log('Camera is stopped.');
});

$('#myCamera').on('camera:snapshot', function (e, blob) {
  console.log('Base64 picture URL: ' + blob.dataURL);
});
