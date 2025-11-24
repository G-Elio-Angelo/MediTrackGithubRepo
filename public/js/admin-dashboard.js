document.addEventListener("DOMContentLoaded", function() {
  const medicineNames = window.dashboardData.medicineNames || [];
  const medicineStocks = window.dashboardData.medicineStocks || [];
  const lowStockNames = window.dashboardData.lowStockNames || [];
  const lowStockValues = window.dashboardData.lowStockValues || [];
  const medicineExpiries = window.dashboardData.medicineExpiries || [];
  const nearExpiryNames = window.dashboardData.nearExpiryNames || [];
  const nearExpiryDates = window.dashboardData.nearExpiryDates || [];

  // MEDICINE CHART - Horizontal bar with percentage and clickable bars to reveal expiry
  const medCtx = document.getElementById('medicineChart');
  const medicineColors = medicineStocks.map(value => {
    if (value < 10) return '#dc3545';
    if (value < 30) return '#ffc107';
    return '#28a745';
  });

  const medChart = new Chart(medCtx, {
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
      indexAxis: 'x',
      responsive: true,
      plugins: {
        title: { display: true, text: 'Medicine Stock Levels', font: { size: 16 } },
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              const total = medicineStocks.reduce((a, b) => a + b, 0) || 1;
              const percent = ((ctx.raw / total) * 100).toFixed(1);
              const expiry = medicineExpiries[ctx.dataIndex] || 'N/A';
              return `${ctx.label}: ${ctx.raw} pcs (${percent}%) — Exp: ${expiry}`;
            }
          }
        }
      },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Stock Quantity' } },
        x: { title: { display: true, text: 'Medicine' } }
      },
      onClick: (evt, activeEls) => {
        const points = medChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, false);
        if (points.length) {
          const idx = points[0].index;
          const name = medicineNames[idx] || 'Medicine';
          const expiry = medicineExpiries[idx] || 'N/A';
          alert(`${name} — Nearest expiry: ${expiry}`);
        }
      }
    }
  });

  new DataTable('#LowMedicine', {
    searchable: true,
    fixedHeight: true,
    perPage: 5,
    perPageSelect: [5, 10, 15, 20],
  });

  new DataTable('#MedicineList', {
    searchable: true,
    fixedHeight: true,
    perPage: 10,
    perPageSelect: [10, 25, 50, 100],
  });
  new DataTable('#UserTable', {
    searchable: true,
    fixedHeight: true,
    perPage: 10,
    perPageSelect: [10, 25, 50, 100],
  });
  
  // LOW STOCK CHART - Animated bars with emphasis
  const lowCtx = document.getElementById('lowStockChart');
  new Chart(lowCtx, {
    type: 'bar',
    data: {
      labels: lowStockNames,
      datasets: [{
        label: 'Low Stock',
        data: lowStockValues,
        backgroundColor: lowStockValues.map(v => v < 5 ? '#b71c1c' : '#f44336'),
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      animation: { duration: 900, easing: 'easeOutQuart' },
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
        y: { beginAtZero: true, title: { display: true, text: 'Quantity' } }
      }
    }
  });

  // EXPIRY CHART - medicines expiring within the next 10 days
  const expiryCtx = document.getElementById('expiryChart');
  if (expiryCtx && nearExpiryNames.length) {
    new Chart(expiryCtx, {
      type: 'bar',
      data: {
        labels: nearExpiryNames,
        datasets: [{
          label: 'Days until expiry',
          data: nearExpiryDates.map(d => {
            const dt = new Date(d);
            const now = new Date();
            const diff = Math.ceil((dt - now) / (1000 * 60 * 60 * 24));
            return diff >= 0 ? diff : 0;
          }),
          backgroundColor: '#ff9800',
          borderRadius: 6
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
          title: { display: true, text: 'Medicines Near Expiry (days left)', font: { size: 16 } },
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                const d = nearExpiryDates[ctx.dataIndex] || 'N/A';
                return `${ctx.label}: ${ctx.raw} day(s) — Exp: ${d}`;
              }
            }
          }
        },
        scales: {
          x: { beginAtZero: true, title: { display: true, text: 'Days Remaining' } }
        }
      }
    });
  }
});
