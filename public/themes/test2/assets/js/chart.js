(function () {

  var ctx = document.getElementById('sales').getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        data: [120, 32, 54, 26, 31, 48, 53, 26, 51, 60, 82, 145],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
      },
      tooltips: {
        enabled: true,
        mode: 'single',
        callbacks: {
          label: function (tooltipItems, data) {
            var dataset = data.datasets[tooltipItems.datasetIndex]
            var value = dataset.data[tooltipItems.index]

            return value + ' sales';
          }
        }
      }
    }
  });

  var ctx = document.getElementById('pay-methods').getContext('2d');

  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Credit card', 'Debit card', 'Cash', 'Other'],
      datasets: [{
        data: [48, 27, 18, 7],
        backgroundColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(54, 162, 235, 1)'
        ]
      }]
    },
    options: {
      responsive: true,
      tooltips: {
        enabled: true,
        mode: 'single',
        callbacks: {
          label: function (tooltipItems, data) {
            var dataset = data.datasets[tooltipItems.datasetIndex]
            var value = dataset.data[tooltipItems.index]

            return value + '%';
          }
        }
      }
    }
  });

  var ctx = document.getElementById('cash-flow').getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Revenues',
        data: [1200.25, 320.87, 540.01, 260.15, 310.89, 480.20, 530.27, 260.43, 510.50, 600.23, 820.55, 1450.80],
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }, {
        label: 'Expenses',
        data: [980.25, 360.87, 330.01, 220.15, 330.89, 520.20, 460.27, 210.43, 390.50, 520.23, 820.55, 945.80],
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
      },
      tooltips: {
        enabled: true,
        mode: 'single',
        callbacks: {
          label: function (tooltipItems, data) {
            var dataset = data.datasets[tooltipItems.datasetIndex]
            var value = dataset.data[tooltipItems.index]

            return '$' + value;
          }
        }
      }
    }
  });

})();
