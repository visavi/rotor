$('[data-toggle="file-manager"]').on('file:change', function (e, file) {
  $('#file-name').text(file.name);
  $('#file-size').text((file.size / 1024).toFixed(2) + ' KB');
  $('#is-invalid').toggleClass('d-none', !file.errors.length);
  $('#is-valid').toggleClass('d-none', !!file.errors.length);

  if (file.type.startsWith('image')) {
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function() {
      var url = URL.createObjectURL(file);
      $('#file-preview').attr('src', url).removeClass('d-none');
    };
  } else {
    $('#file-preview').removeAttr('src').addClass('d-none');
  }

  $('#file-empty').addClass('d-none');
  $('#file-data').removeClass('d-none');
});
