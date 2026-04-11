/* =============================================
   CUSTOM DATE & TIME PICKERS
   ============================================= */

(function () {
  'use strict';

  /* ---- Config ---- */
  const BOOKED_DATES_API = 'api/booked-dates.php';
  const MONTHS = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];

  /* ---- State ---- */
  let bookedDates   = [];       // ['YYYY-MM-DD', ...]
  let calYear       = 0;
  let calMonth      = 0;        // 0-indexed
  let selectedDate  = null;     // 'YYYY-MM-DD'
  let selectedHour  = null;     // 1-12
  let selectedMin   = null;     // 0 | 15 | 30 | 45
  let selectedAmPm  = 'AM';

  /* ---- DOM refs ---- */
  const dateWrap       = document.getElementById('datepickerWrap');
  const dateDisplay    = document.getElementById('dateDisplay');
  const dateDisplayTxt = document.getElementById('dateDisplayText');
  const calDropdown    = document.getElementById('calendarDropdown');
  const calPrev        = document.getElementById('calPrev');
  const calNext        = document.getElementById('calNext');
  const calMonthLabel  = document.getElementById('calMonthLabel');
  const calYearLabel   = document.getElementById('calYearLabel');
  const calDaysEl      = document.getElementById('calDays');
  const hiddenDate     = document.getElementById('eventDate');

  const timeWrap       = document.getElementById('timepickerWrap');
  const timeDisplay    = document.getElementById('timeDisplay');
  const timeDisplayTxt = document.getElementById('timeDisplayText');
  const timeDropdown   = document.getElementById('timeDropdown');
  const hourCol        = document.getElementById('hourCol');
  const minCol         = document.getElementById('minCol');
  const ampmCol        = document.getElementById('ampmCol');
  const timeConfirm    = document.getElementById('timeConfirm');
  const hiddenTime     = document.getElementById('eventTime');

  if (!dateWrap || !timeWrap) return;

  /* ============================================================
     FETCH BOOKED DATES
     ============================================================ */
  fetch(BOOKED_DATES_API)
    .then(function (r) { return r.json(); })
    .then(function (data) {
      bookedDates = data.booked || [];
      renderCalendar();
    })
    .catch(function () {
      // API unavailable — still show calendar without blocked dates
      renderCalendar();
    });

  /* ============================================================
     CALENDAR DATE PICKER
     ============================================================ */

  /* -- Init month to current -- */
  (function () {
    const now = new Date();
    calYear  = now.getFullYear();
    calMonth = now.getMonth();
  })();

  function renderCalendar() {
    calMonthLabel.textContent = MONTHS[calMonth];
    calYearLabel.textContent  = calYear;

    const today    = new Date();
    today.setHours(0,0,0,0);
    const firstDay = new Date(calYear, calMonth, 1).getDay(); // 0=Sun
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    calDaysEl.innerHTML = '';

    // Leading empty cells
    for (let i = 0; i < firstDay; i++) {
      const empty = document.createElement('div');
      empty.className = 'cal-day cal-day-empty';
      calDaysEl.appendChild(empty);
    }

    for (let d = 1; d <= daysInMonth; d++) {
      const cell    = document.createElement('div');
      const dateStr = pad(calYear) + '-' + pad(calMonth + 1) + '-' + pad(d);
      const cellDate = new Date(calYear, calMonth, d);
      cellDate.setHours(0,0,0,0);

      cell.className  = 'cal-day';
      cell.textContent = d;
      cell.dataset.date = dateStr;

      const isPast   = cellDate < today;
      const isBooked = bookedDates.indexOf(dateStr) !== -1;
      const isToday  = cellDate.getTime() === today.getTime();
      const isSel    = dateStr === selectedDate;

      if (isPast)   { cell.classList.add('cal-day-past');   cell.setAttribute('aria-disabled','true'); }
      if (isBooked) { cell.classList.add('cal-day-booked'); cell.setAttribute('title','Unavailable'); }
      if (isToday)  { cell.classList.add('cal-day-today'); }
      if (isSel)    { cell.classList.add('cal-day-selected'); }

      if (!isPast && !isBooked) {
        cell.setAttribute('tabindex','0');
        cell.setAttribute('role','button');
        cell.addEventListener('click', function () { selectDate(dateStr, d); });
        cell.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectDate(dateStr, d); }
        });
      }

      calDaysEl.appendChild(cell);
    }
  }

  function selectDate(dateStr, d) {
    selectedDate = dateStr;
    hiddenDate.value = dateStr;

    // Format display: "Wed, 18 Apr 2026"
    const dt = new Date(calYear, calMonth, d);
    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    dateDisplayTxt.textContent = days[dt.getDay()] + ', ' + d + ' ' + MONTHS[calMonth] + ' ' + calYear;
    dateDisplay.classList.add('has-value');

    // Clear error
    const err = document.getElementById('eventDateError');
    if (err) err.classList.remove('visible');
    hiddenDate.classList.remove('error');

    closeDatePicker();
    renderCalendar(); // refresh to show selection highlight
  }

  calPrev.addEventListener('click', function () {
    calMonth--;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    renderCalendar();
  });

  calNext.addEventListener('click', function () {
    calMonth++;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    renderCalendar();
  });

  function openDatePicker() {
    calDropdown.classList.add('open');
    dateDisplay.setAttribute('aria-expanded', 'true');
    dateDisplay.classList.add('active');
    // Close time picker if open
    timeDropdown.classList.remove('open');
    timeDisplay.setAttribute('aria-expanded','false');
    timeDisplay.classList.remove('active');
  }

  function closeDatePicker() {
    calDropdown.classList.remove('open');
    dateDisplay.setAttribute('aria-expanded', 'false');
    dateDisplay.classList.remove('active');
  }

  dateDisplay.addEventListener('click', function (e) {
    e.stopPropagation();
    if (calDropdown.classList.contains('open')) {
      closeDatePicker();
    } else {
      openDatePicker();
    }
  });

  dateDisplay.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      calDropdown.classList.contains('open') ? closeDatePicker() : openDatePicker();
    }
    if (e.key === 'Escape') closeDatePicker();
  });

  /* ============================================================
     TIME PICKER
     ============================================================ */

  function buildTimeColumns() {
    // Hours 1-12
    for (let h = 1; h <= 12; h++) {
      const item = document.createElement('div');
      item.className = 'time-item';
      item.textContent = pad(h);
      item.dataset.val = h;
      item.addEventListener('click', function () {
        hourCol.querySelectorAll('.time-item').forEach(function (i) { i.classList.remove('selected'); });
        item.classList.add('selected');
        selectedHour = h;
        scrollCenter(hourCol, item);
      });
      hourCol.appendChild(item);
    }

    // Minutes: 00, 15, 30, 45
    [0, 15, 30, 45].forEach(function (m) {
      const item = document.createElement('div');
      item.className = 'time-item';
      item.textContent = pad(m);
      item.dataset.val = m;
      item.addEventListener('click', function () {
        minCol.querySelectorAll('.time-item').forEach(function (i) { i.classList.remove('selected'); });
        item.classList.add('selected');
        selectedMin = m;
        scrollCenter(minCol, item);
      });
      minCol.appendChild(item);
    });

    // AM/PM
    ampmCol.querySelectorAll('.time-item').forEach(function (item) {
      item.addEventListener('click', function () {
        ampmCol.querySelectorAll('.time-item').forEach(function (i) { i.classList.remove('selected'); });
        item.classList.add('selected');
        selectedAmPm = item.dataset.val;
      });
    });

    // Default: select 10:00 AM
    selectTimeItem(hourCol, 10);
    selectTimeItem(minCol, 0);
    selectTimeItem(ampmCol, 'AM');
    selectedHour = 10;
    selectedMin  = 0;
    selectedAmPm = 'AM';
  }

  function selectTimeItem(col, val) {
    col.querySelectorAll('.time-item').forEach(function (item) {
      if (String(item.dataset.val) === String(val)) {
        item.classList.add('selected');
        scrollCenter(col, item);
      }
    });
  }

  function scrollCenter(col, item) {
    // Scroll so selected item is vertically centered
    const offset = item.offsetTop - (col.clientHeight / 2) + (item.clientHeight / 2);
    col.scrollTo({ top: offset, behavior: 'smooth' });
  }

  timeConfirm.addEventListener('click', function () {
    if (selectedHour === null || selectedMin === null) return;
    const displayStr = pad(selectedHour) + ':' + pad(selectedMin) + ' ' + selectedAmPm;
    // Convert to 24h for hidden input
    let h24 = selectedHour;
    if (selectedAmPm === 'AM' && selectedHour === 12) h24 = 0;
    if (selectedAmPm === 'PM' && selectedHour !== 12) h24 = selectedHour + 12;
    hiddenTime.value = pad(h24) + ':' + pad(selectedMin);

    timeDisplayTxt.textContent = displayStr;
    timeDisplay.classList.add('has-value');

    const err = document.getElementById('eventTimeError');
    if (err) err.classList.remove('visible');
    hiddenTime.classList.remove('error');

    closeTimePicker();
  });

  function openTimePicker() {
    timeDropdown.classList.add('open');
    timeDisplay.setAttribute('aria-expanded','true');
    timeDisplay.classList.add('active');
    // Close date picker if open
    calDropdown.classList.remove('open');
    dateDisplay.setAttribute('aria-expanded','false');
    dateDisplay.classList.remove('active');
    // Re-center selected items
    const selH = hourCol.querySelector('.time-item.selected');
    const selM = minCol.querySelector('.time-item.selected');
    if (selH) setTimeout(function () { scrollCenter(hourCol, selH); }, 50);
    if (selM) setTimeout(function () { scrollCenter(minCol, selM); }, 50);
  }

  function closeTimePicker() {
    timeDropdown.classList.remove('open');
    timeDisplay.setAttribute('aria-expanded','false');
    timeDisplay.classList.remove('active');
  }

  timeDisplay.addEventListener('click', function (e) {
    e.stopPropagation();
    timeDropdown.classList.contains('open') ? closeTimePicker() : openTimePicker();
  });

  timeDisplay.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      timeDropdown.classList.contains('open') ? closeTimePicker() : openTimePicker();
    }
    if (e.key === 'Escape') closeTimePicker();
  });

  buildTimeColumns();

  /* ============================================================
     CLOSE ON OUTSIDE CLICK
     ============================================================ */
  document.addEventListener('click', function (e) {
    if (dateWrap && !dateWrap.contains(e.target)) closeDatePicker();
    if (timeWrap && !timeWrap.contains(e.target)) closeTimePicker();
  });

  /* ---- Utility ---- */
  function pad(n) { return String(n).padStart(2, '0'); }

})();
