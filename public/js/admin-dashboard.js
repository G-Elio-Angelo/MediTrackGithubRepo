document.addEventListener("DOMContentLoaded", function() {
  const userNames = window.dashboardData.userNames;
  const medicineNames = window.dashboardData.medicineNames;
  const medicineStocks = window.dashboardData.medicineStocks;
  const lowStockNames = window.dashboardData.lowStockNames;
  const lowStockValues = window.dashboardData.lowStockValues;

  // USERS CHART
  const userCtx = document.getElementById('userChart');
  new Chart(userCtx, {
    type: 'bar',
    data: {
      labels: userNames,
      datasets: [{
        label: 'Registered Users',
        data: Array(userNames.length).fill(1),
        backgroundColor: '#007bff',
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        title: { display: true, text: 'Total Registered Users', font: { size: 16 } }
      },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
  });

  // MEDICINE CHART
  const medCtx = document.getElementById('medicineChart');
  const medicineColors = medicineStocks.map(value => {
    if (value < 10) return '#dc3545';
    if (value < 30) return '#ffc107';
    return '#28a745';
  });

  new Chart(medCtx, {
    type: 'bar',
    data: {
      labels: medicineNames,
      datasets: [{
        label: 'Stock Count',
        data: medicineStocks,
        backgroundColor: medicineColors,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Medicine Stock Levels', font: { size: 16 } },
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              const total = medicineStocks.reduce((a, b) => a + b, 0);
              const percent = ((ctx.raw / total) * 100).toFixed(1);
              return `${ctx.label}: ${ctx.raw} pcs (${percent}%)`;
            }
          }
        }
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Stock Quantity' } },
        x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 } }
      }
    }
  });

  // LOW STOCK CHART
  const lowCtx = document.getElementById('lowStockChart');
  new Chart(lowCtx, {
    type: 'bar',
    data: {
      labels: lowStockNames,
      datasets: [{
        label: 'Low Stock',
        data: lowStockValues,
        backgroundColor: '#dc3545',
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: { display: true, text: 'Low Stock Alert', font: { size: 16 } },
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              return `${ctx.label}: ${ctx.raw} pcs left`;
            }
          }
        }
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Quantity Left' } }
      }
    }
  });
});
