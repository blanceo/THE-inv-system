<?php

// ========================================
// FILE 3: calendar.php (Main Calendar Page)
// ========================================
require_once 'check_session.php';

$isAdmin = isAdmin();
$userName = $_SESSION['full_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reservation Calendar - LabTrack</title>
  <link rel="stylesheet" href="form.css">
  <style>

    /* Active navigation button */
.active-nav {
  background: linear-gradient(to right, #ff7c1e, #ffb15a) !important;
  color: #fff !important;
  cursor: default !important;
  transform: none !important;
}

.active-nav:hover {
  background: linear-gradient(to right, #ff7c1e, #ffb15a) !important;
  color: #fff !important;
  transform: none !important;
}

/* Match the nav styles from form.css */
nav { 
  background: #c9c9c7;
  padding: 12px; 
  display: flex; 
  justify-content: center; 
  gap: 30px;
  margin-bottom: 20px;
}

nav button { 
  padding: 20px 25px; 
  border: none; 
  border-radius: 20px; 
  background: linear-gradient(to right, #000000, #424242);
  color: white; 
  cursor: pointer; 
  font-size: 1rem;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

nav button:hover:not(.active-nav) { 
  background: linear-gradient(to right, #ff7c1e, #ffb15a);
  color: #fff; 
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.25);
}

nav button:active {
  transform: translateY(1px);
}

    .calendar-container {
      max-width: 1200px;
      margin: 30px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #FFD600;
    }

    .profname {
        padding: -20px ;
    }

    .calendar-header h2 {
      font-size: 28px;
      color: #1e1e1e;
    }

    .month-navigation {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .month-navigation button {
      background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
      color: #FFD600;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .month-navigation button:hover {
      background: linear-gradient(135deg, #FFD600 0%, #f0c000 100%);
      color: #1e1e1e;
      transform: translateY(-2px);
    }

    .month-navigation span {
      font-size: 20px;
      font-weight: bold;
      min-width: 200px;
      text-align: center;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 10px;
    }

    .calendar-day-header {
      text-align: center;
      font-weight: bold;
      padding: 15px;
      background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
      color: #FFD600;
      border-radius: 8px;
    }

    .calendar-day {
      min-height: 45px;
      padding: 10px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: white;
      position: relative;
    }

    .calendar-day:hover {
      border-color: #FFD600;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
    }

    .calendar-day.other-month {
      background: #f5f5f5;
      color: #999;
    }

    .calendar-day.today {
      border-color: #4a9eff;
      background: #e3f2fd;
    }

    .calendar-day.due-today {
  border-color: #28a745;
  border-width: 3px;
  background: linear-gradient(135deg, #d4edda 0%, #e8f5e9 100%);
  box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
}

.calendar-day.due-today .reservation-count {
  background: #28a745;
  color: white;
  animation: urgentPulse 1.5s ease infinite;
}

@keyframes urgentPulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
  }
  50% {
    transform: scale(1.1);
    box-shadow: 0 0 0 5px rgba(40, 167, 69, 0);
  }
}

    .legend-due-today {
    background: linear-gradient(135deg, #d4edda 0%, #e8f5e9 100%);
    border-color: #28a745;
}

    .calendar-day.has-reservations {
      background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
      border-color: #FFD600;
      border-width: 3px;
    }

    .day-number {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .reservation-count {
      position: absolute;
      top: 5px;
      right: 5px;
      background: #FFD600;
      color: #1e1e1e;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
    }

    .reservation-preview {
      font-size: 11px;
      color: #666;
      margin-top: 5px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 1;
    }

    .modal-content {
      background: white;
      border-radius: 16px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6), 0 0 0 3px #FFD600;
      transform: scale(0.9);
      transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .modal.show .modal-content {
      transform: scale(1);
    }

    .modal-header {
      background: linear-gradient(135deg, #FFD600 0%, #f0c000 100%);
      color: #1e1e1e;
      padding: 20px 25px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      font-size: 22px;
      margin: 0;
    }

    .close-btn {
      background: #1e1e1e;
      color: #FFD600;
      border: none;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .close-btn:hover {
      background: #FFD600;
      color: #1e1e1e;
      transform: rotate(90deg);
    }

    .modal-body {
      padding: 25px;
    }

    .reservation-item {
      background: #f8f9fa;
      border-left: 4px solid #FFD600;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 8px;
      transition: all 0.2s ease;
    }

    .reservation-item:hover {
      background: #fff9e6;
      transform: translateX(5px);
    }

    .reservation-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .item-name {
      font-size: 18px;
      font-weight: bold;
      color: #1e1e1e;
    }

    .teacher-name {
      color: #666;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .item-purpose {
      color: #888;
      font-size: 13px;
      font-style: italic;
    }

    .delete-reservation-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: bold;
      transition: all 0.2s ease;
    }

    .delete-reservation-btn:hover {
      background: #c82333;
      transform: scale(1.05);
    }

    .no-reservations {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 16px;
    }

    .legend {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }

    .legend-box {
      width: 20px;
      height: 20px;
      border-radius: 4px;
      border: 2px solid;
    }

    .legend-today {
      background: #e3f2fd;
      border-color: #4a9eff;
    }

    .legend-reserved {
      background: #fff9e6;
      border-color: #FFD600;
    }

    .back-btn {
      background: #6c757d;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .back-btn:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

<header>
  <h1>LabTrack - Reservation Calendar</h1>
  <div class="profname" style="float: center;">
    Welcome, <?php echo $userName; ?>
  </div>
</header>

<div id="app">
  <?php if ($isAdmin): ?>
  <!-- Admin Navigation -->
  <nav>
    <button onclick="window.location.href='form.php'">Inventory</button>
    <button onclick="window.location.href='form.php#reservationRequests'">Reservation Requests</button>
    <button class="active-nav">📅 Calendar</button>
    <button onclick="logout()">Logout</button>
  </nav>
<?php else: ?>
  <!-- Teacher Navigation -->
  <nav>
    <button class="active-nav">📅 Calendar</button>
    <button onclick="window.location.href='teacher_dashboard.php#reservations'">Send Requests</button>
    <button onclick="window.location.href='teacher_dashboard.php#myReservations'">My Reservations</button>
    <button onclick="window.location.href='teacher_dashboard.php#inventory'">View Inventory</button>
    <button onclick="logout()">Logout</button>
  </nav>
<?php endif; ?>

  <div class="calendar-container">
    <div class="calendar-header">
      <h2>📅 Calendar - Reservation Schedule</h2>
      <div class="month-navigation">
        <button id="prevMonth">← Previous</button>
        <span id="currentMonth">Navigate The Months</span>
        <button id="nextMonth">Next →</button>
      </div>
    </div>

    <div class="calendar-grid" id="calendarGrid">
      <!-- Calendar will be generated here -->
    </div>

    <div class="legend">
  <div class="legend-item">
    <div class="legend-box legend-today"></div>
    <span>Today</span>
  </div>
  <div class="legend-item">
    <div class="legend-box legend-reserved"></div>
    <span>Has Reservations</span>
  </div>
  <div class="legend-item">
    <div class="legend-box legend-due-today"></div>
    <span>Due Today</span>
  </div>
</div>

  <!-- Modal for viewing reservations -->
  <div id="reservationModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalDate">Reservations for [Date]</h3>
        <button class="close-btn" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Reservation details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<script>
let currentDate = new Date();
let reservations = [];
const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;


function loadReservations() {
  fetch('get_approved_reservations.php')
    .then(res => res.json())
    .then(data => {
      console.log('Reservations loaded:', data); // Debug line
      if (data.error) {
        console.error('Error from server:', data.error);
        reservations = [];
      } else {
        reservations = data;
      }
      // Make sure we're showing current month
      currentDate = new Date();
      renderCalendar();
    })
    .catch(error => {
      console.error('Error loading reservations:', error);
      currentDate = new Date();
      renderCalendar();
    });
}

function renderCalendar() {
  const year = currentDate.getFullYear();
  const month = currentDate.getMonth();
  
  const monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];
  document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const prevMonthDays = new Date(year, month, 0).getDate();

  const grid = document.getElementById('calendarGrid');
  grid.innerHTML = '';

  const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  dayHeaders.forEach(day => {
    const header = document.createElement('div');
    header.className = 'calendar-day-header';
    header.textContent = day;
    grid.appendChild(header);
  });

  for (let i = firstDay - 1; i >= 0; i--) {
    const day = prevMonthDays - i;
    const dayEl = createDayElement(day, true, month - 1, year);
    grid.appendChild(dayEl);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dayEl = createDayElement(day, false, month, year);
    grid.appendChild(dayEl);
  }

  const remainingDays = 42 - (firstDay + daysInMonth);
  for (let day = 1; day <= remainingDays; day++) {
    const dayEl = createDayElement(day, true, month + 1, year);
    grid.appendChild(dayEl);
  }
}

function createDayElement(day, isOtherMonth, month, year) {
  const dayEl = document.createElement('div');
  dayEl.className = 'calendar-day';
  
  if (isOtherMonth) {
    dayEl.classList.add('other-month');
  }

  const today = new Date();
  const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  
  // Check if this date is today
  const isToday = !isOtherMonth && 
      day === today.getDate() && 
      month === today.getMonth() && 
      year === today.getFullYear();
  
  // Check for reservations on this date
  const dayReservations = reservations.filter(r => r.date_needed === dateStr && r.status === 'approved');
  
  // Determine styling based on conditions
  if (dayReservations.length > 0 && isToday) {
    // Has reservations AND is today = Due Today (GREEN)
    dayEl.classList.add('due-today');
  } else if (isToday) {
    // Just today, no reservations (BLUE)
    dayEl.classList.add('today');
  } else if (dayReservations.length > 0) {
    // Has reservations but not today (YELLOW)
    dayEl.classList.add('has-reservations');
  }
  
  if (dayReservations.length > 0) {
    const countBadge = document.createElement('div');
    countBadge.className = 'reservation-count';
    countBadge.textContent = dayReservations.length;
    dayEl.appendChild(countBadge);

    const preview = document.createElement('div');
    preview.className = 'reservation-preview';
    preview.textContent = dayReservations[0].item_name;
    if (dayReservations.length > 1) {
      preview.textContent += ` +${dayReservations.length - 1} more`;
    }
    dayEl.appendChild(preview);
  }

  const dayNumber = document.createElement('div');
  dayNumber.className = 'day-number';
  dayNumber.textContent = day;
  dayEl.insertBefore(dayNumber, dayEl.firstChild);

  dayEl.onclick = () => showReservations(dateStr, dayReservations);

  return dayEl;
}

function showReservations(date, dayReservations) {
  if (dayReservations.length === 0) {
    return;
  }

  const modal = document.getElementById('reservationModal');
  const modalBody = document.getElementById('modalBody');
  const modalDate = document.getElementById('modalDate');

  const dateObj = new Date(date + 'T00:00:00');
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  modalDate.textContent = `Reservations for ${dateObj.toLocaleDateString('en-US', options)}`;

  let html = '';
  dayReservations.forEach(reservation => {
    html += `
      <div class="reservation-item">
        <div class="reservation-item-header">
          <div>
            <div class="item-name">${reservation.item_name}</div>
            <div class="teacher-name">👤 ${reservation.teacher_name}</div>
            ${reservation.purpose ? `<div class="item-purpose">📝 ${reservation.purpose}</div>` : ''}
          </div>
          ${isAdmin ? `
            <button class="delete-reservation-btn" onclick="deleteReservation(${reservation.id})">
              Delete
            </button>
          ` : ''}
        </div>
      </div>
    `;
  });

  modalBody.innerHTML = html;
  modal.classList.add('show');
}

function logout() {
  showConfirmation(
    'Are you sure you want to logout?',
    () => {
      window.location.href = 'logout.php';
    }
  );
}

function closeModal() {
  document.getElementById('reservationModal').classList.remove('show');
}

function deleteReservation(reservationId) {
  showConfirmation(
    'Are you sure you want to remove this reservation from the calendar? It will be marked as rejected.',
    () => {
      fetch('delete_calendar_reservation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: reservationId })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          loadReservations();
          closeModal();
        } else {
          showNotification(data.message || 'Failed to delete reservation', 'error');
        }
      })
      .catch(error => {
        showNotification('Error: ' + error.message, 'error');
      });
    }
  );
}

document.getElementById('prevMonth').addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() - 1);
  renderCalendar();
});

document.getElementById('nextMonth').addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() + 1);
  renderCalendar();
});

document.getElementById('reservationModal').addEventListener('click', (e) => {
  if (e.target.id === 'reservationModal') {
    closeModal();
  }
});

// Add notification functions (same as before)
function showNotification(message, type = 'info', duration = 3000) {
  const existingNotification = document.querySelector('.modern-notification');
  if (existingNotification) existingNotification.remove();

  const notification = document.createElement('div');
  notification.className = `modern-notification ${type}`;
  
  let icon = '';
  switch(type) {
    case 'success': icon = '✓'; break;
    case 'error': icon = '✕'; break;
    case 'warning': icon = '⚠'; break;
    case 'info': icon = 'ℹ'; break;
  }
  
  notification.innerHTML = `
    <div class="notification-icon">${icon}</div>
    <div class="notification-message">${message}</div>
  `;
  
  document.body.appendChild(notification);
  setTimeout(() => notification.classList.add('show'), 10);
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 400);
  }, duration);
}

function showConfirmation(message, onConfirm, onCancel = null) {
  const existingConfirm = document.querySelector('.modern-confirmation');
  if (existingConfirm) existingConfirm.remove();

  const confirmation = document.createElement('div');
  confirmation.className = 'modern-confirmation';
  confirmation.innerHTML = `
    <div class="modern-confirmation-backdrop"></div>
    <div class="modern-confirmation-content">
      <div class="confirmation-header">
        <span class="confirmation-icon">⚠</span>
        <h3>Confirm Action</h3>
      </div>
      <p class="confirmation-message">${message}</p>
      <div class="confirmation-buttons">
        <button class="confirm-cancel-btn">Cancel</button>
        <button class="confirm-yes-btn">Confirm</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(confirmation);
  setTimeout(() => confirmation.classList.add('show'), 10);
  
  const yesBtn = confirmation.querySelector('.confirm-yes-btn');
  const cancelBtn = confirmation.querySelector('.confirm-cancel-btn');
  const backdrop = confirmation.querySelector('.modern-confirmation-backdrop');
  
  const closeConfirmation = () => {
    confirmation.classList.remove('show');
    setTimeout(() => confirmation.remove(), 300);
  };
  
  yesBtn.addEventListener('click', () => {
    closeConfirmation();
    onConfirm();
  });
  
  cancelBtn.addEventListener('click', () => {
    closeConfirmation();
    if (onCancel) onCancel();
  });
  
  backdrop.addEventListener('click', () => {
    closeConfirmation();
    if (onCancel) onCancel();
  });
}

currentDate = new Date();
loadReservations();
</script>

</body>
</html>