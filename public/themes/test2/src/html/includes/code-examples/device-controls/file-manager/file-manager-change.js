$('[data-toggle="file-manager"]').on('file:change', function (e, file) {
  // file.errors is binded by the component after validation
  if (file.errors.length) {
    console.log('File errors: ', file.errors);
  }
});
