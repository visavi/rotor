$('.pagination').on('page:change', function(e, pagination) {
  console.log(pagination); // { count: 200, current: 5, length: 10, pages: 20 }
});
