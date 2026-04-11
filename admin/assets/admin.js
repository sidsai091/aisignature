/* =============================================
   ADMIN DASHBOARD — JAVASCRIPT
   ============================================= */

/* ---- Sidebar toggle (mobile) ---- */
(function () {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', function () {
    sidebar.classList.toggle('open');
  });

  // Close when clicking outside on mobile
  document.addEventListener('click', function (e) {
    if (sidebar.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        e.target !== toggle) {
      sidebar.classList.remove('open');
    }
  });
})();

/* ---- Revenue chart initialiser ---- */
function initRevenueChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  const gold    = '#c9a84c';
  const goldBg  = 'rgba(201,168,76,0.08)';
  const muted   = '#7b85a3';
  const border  = 'rgba(201,168,76,0.15)';

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue (USD)',
        data: values,
        backgroundColor: goldBg,
        borderColor: gold,
        borderWidth: 1.5,
        borderRadius: 5,
        borderSkipped: false,
        hoverBackgroundColor: 'rgba(201,168,76,0.18)',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f1629',
          borderColor: border,
          borderWidth: 1,
          titleColor: gold,
          bodyColor: muted,
          padding: 10,
          callbacks: {
            label: function(ctx) {
              return ' $' + ctx.parsed.y.toLocaleString();
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255,255,255,0.03)' },
          ticks: { color: muted, font: { size: 11 } }
        },
        y: {
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: {
            color: muted,
            font: { size: 11 },
            callback: function(v) { return '$' + v.toLocaleString(); }
          },
          beginAtZero: true
        }
      }
    }
  });
}
