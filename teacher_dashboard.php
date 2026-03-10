<?php
require_once 'check_session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard - LabTrack</title>
  <link rel="stylesheet" href="form.css">

</head>
<body>

<header>
    <div style="display: flex; justify-content: space-between; align-items: center;">
    <!-- LEFT SIDE: logos + title -->
    <div style="display: flex; align-items: left; gap: 5px;">
      <img src="uploads/pcc.png" alt="PCC Logo" style="width: 52px; height: 52px; object-fit: contain;">
      <img src="uploads/pccSHS.png" alt="PCC SHS Logo" style="width: 52px; height: 52px; object-fit: contain;">
      <img src="uploads/stemlogo.png" alt="STEM Logo" style="width: 52px; height: 52px; object-fit: contain;">
    </div>
    <h1 style="font-size:30px;">LabTrack - Teacher's Dashboard</h1>
    <div style="display: flex; align-items: center; gap: 20px;">
  <div id="welcome" style="text-align: right; font-size: 16px; line-height: 1.3;">
    Welcome,<br><strong><?php echo $_SESSION['full_name']?></strong>
  </div>
  
  <!-- Profile Avatar -->
  <div class="profile-avatar-wrapper" onclick="document.getElementById('profileUpload').click()" title="Click to change profile picture">
    <img id="profileAvatar" src="" alt="Profile" class="profile-avatar-img">
    <div id="profileAvatarInitials" class="profile-avatar-initials">
      <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
    </div>
    <div class="profile-avatar-overlay">📷</div>
    <input type="file" id="profileUpload" accept="image/*" style="display:none;" onchange="handleProfileUpload(event)">
  </div>
</div>
    </div>
  </div>
</header>


<div id="app">
  <nav>
  <button onclick="window.location.href='calendar.php'" id="navCalendar">📅 Calendar</button>
  <button onclick="showSection('reservations')" id="navReservations">Send Requests</button>
  <button onclick="showSection('myReservations')" id="navMyReservations">My Reservations</button>
  <button onclick="showSection('inventory')" id="navInventory">View Inventory</button>
  <button onclick="logout()">Logout</button>
</nav>

  <section id="reservations" class="active">
  <div class="reservation-form">
    <h2>Reserve Equipment</h2>
    <form id="reservationForm">
      <div id="itemsContainer">
        <div class="item-input-group">
          <label><strong>Item Name:</strong></label>
          <div style="display: flex; gap: 10px; align-items: center;">
            <input 
              type="text" 
              class="item-name-input" 
              name="item_names[]" 
              placeholder="Enter equipment name" 
              required 
              style="flex: 1; padding: 8px;">
            <button type="button" class="remove-item-btn" onclick="removeItemInput(this)" style="display: none;">
              ✕
            </button>
          </div>
        </div>
      </div>
      
<button 
  type="button" 
  id="addItemBtn" 
  onclick="addItemInput(); 
  loadInventoryForAutocomplete();
  initializeAllAutocomplete();
  highlightMatch(text, query);
  updateRemoveButtons();">
  + Add Another Item
</button>

      <br>
      
      <label><strong>Date Needed:</strong></label>
      <input 
        type="date" 
        id="reserveDate" 
        name="date_needed" 
        required style="padding: 8px;">
      
      <br><br>

            <label><strong>Time Needed:</strong></label>
      <input 
        type="time" 
        id="reserveTime" 
        name="time_needed" 
        required style="padding: 8px;">
        
      <br><br>
      
      <label><strong>Purpose/Activity:</strong></label>
      <input 
        type="text" 
        id="reservePurpose" 
        name="purpose" 
        placeholder="e.g., Chemistry Lab, Biology Experiment" 
        style="width: 300px; padding: 8px;">
      
      <br><br>
      
      <button type="submit" id="reserveBtn">
        Submit Reservation Request
      </button>
    </form>
  </div>

  <div id="reserveMessage" style="margin-top: 15px;"></div>
  </div>

</section>

<!-- My Reservations Section -->
<section id="myReservations">
  <h2>My Reservation Requests</h2>
  
  <!-- Filter Buttons -->
  <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; align-items: center;">
    <button class="res-filter-btn active-filter" onclick="filterMyReservations('')">All</button>
    <button class="res-filter-btn" onclick="filterMyReservations('approved')">Approved</button>
    <button class="res-filter-btn" onclick="filterMyReservations('pending')">Pending</button>
    <button class="res-filter-btn" onclick="filterMyReservations('rejected')">Rejected</button>

    <button id="cancelReservationBtn" onclick="cancelSelectedReservation()" disabled>✕ Cancel Request</button>
    <button id="borrowReservationBtn" onclick="markAsBorrowed()" disabled>📤 Mark as Borrowed</button>
    <button id="returnReservationBtn" onclick="markAsReturned()" disabled>📥 Mark as Returned</button>
  </div>

  <div id="teacherReservationsList">
    <p>Loading your reservations...</p>
  </div>
  <div class="pagination-container" id="myReservationsPagination" style="display:none;">
    <div class="pagination-info" id="myReservationsPaginationInfo"></div>
    <div class="pagination-controls">
      <button id="myResFirstPage" class="pagination-btn">First</button>
      <button id="myResPrevPage" class="pagination-btn">Previous</button>
      <div id="myResPageNumbers" class="page-numbers"></div>
      <button id="myResNextPage" class="pagination-btn">Next</button>
      <button id="myResLastPage" class="pagination-btn">Last</button>
    </div>
    <div class="page-size-selector">
      <label>Items per page:
        <select id="myResPageSize">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="15">15</option>
          <option value="20">20</option>
        </select>
      </label>
    </div>
  </div>
</section>

  <!-- Inventory Section -->
  <section id="inventory">
    <h2>Available Inventory</h2>

    <div class="search-bar">
      <label>Room:
        <select id="roomFilter"><option value="">All</option></select>
      </label>
        <input type="text" id="searchInput" placeholder="Search equipment..." onkeyup="searchTable()">
    </div>

<button id="sortTable" onclick="sortTable()">Sort A–Z</button>

<button class="room-filter-btn active-room-filter" onclick="filterByRoom('')">All Rooms</button>
<button class="room-filter-btn" onclick="filterByRoom('Chemical Room')">Chemical Room</button>
<button class="room-filter-btn" onclick="filterByRoom('Laboratory 1')">Laboratory 1</button>
<button class="room-filter-btn" onclick="filterByRoom('Laboratory 2')">Laboratory 2</button>
<button class="room-filter-btn" onclick="filterByRoom('Storage Room')">Storage Room</button>

    <table id="inventoryTable">
      <thead>
        <tr>
          <th>Item</th>
          <th>Room</th>
          <th>Description</th>
          <th>Available Quantity</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody id="inventoryBody">
        <tr><td colspan="5">Loading inventory...</td></tr>
      </tbody>
    </table>

      <!-- Pagination Controls -->
  <div class="pagination-container">
    <div class="pagination-info" id="paginationInfo"></div>
    <div class="pagination-controls">
      <button id="firstPage" class="pagination-btn">First</button>
      <button id="prevPage" class="pagination-btn">Previous</button>
      <div id="pageNumbers" class="page-numbers"></div>
      <button id="nextPage" class="pagination-btn">Next</button>
      <button id="lastPage" class="pagination-btn">Last</button>
    </div>
    <div class="page-size-selector">
      <label>Items per page:
        <select id="pageSize">
          <option value="10" selected>10</option>
          <option value="15">15</option>
          <option value="20">20</option>
          <option value="25">25</option>
        </select>
      </label>
    </div>
  </div>

  </section>
</div>

<script>

  // Handle hash navigation from calendar
window.addEventListener('DOMContentLoaded', function() {
  const hash = window.location.hash.substring(1); // Remove the #
  if (hash) {
    showSection(hash);
  }
});

// ====== PROFILE AVATAR ======
(function initProfileAvatar() {
  // Key is per-user so different accounts don't share a picture
  const userId = '<?php echo $_SESSION['user_id']; ?>';
  const storageKey = 'profilePic_' + userId;

  const img = document.getElementById('profileAvatar');
  const initials = document.getElementById('profileAvatarInitials');
  const saved = localStorage.getItem(storageKey);

  if (saved) {
    img.src = saved;
    img.style.display = 'block';
    initials.style.display = 'none';
  }

  window.handleProfileUpload = function(event) {
    const file = event.target.files[0];
    if (!file) return;

    // 2MB limit
    if (file.size > 2 * 1024 * 1024) {
      alert('Image too large. Please choose an image under 2MB.');
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const dataUrl = e.target.result;
      localStorage.setItem(storageKey, dataUrl);
      img.src = dataUrl;
      img.style.display = 'block';
      initials.style.display = 'none';
    };
    reader.readAsDataURL(file);
  };
})();

// Right-click to remove profile picture
document.querySelector('.profile-avatar-wrapper').addEventListener('contextmenu', function(e) {
  e.preventDefault();
  
  const userId = '<?php echo $_SESSION['user_id']; ?>';
  const storageKey = 'profilePic_' + userId;
  
  if (!localStorage.getItem(storageKey)) return; // nothing to remove
  
  if (confirm('Remove your profile picture?')) {
    localStorage.removeItem(storageKey);
    const img = document.getElementById('profileAvatar');
    const initials = document.getElementById('profileAvatarInitials');
    img.src = '';
    img.style.display = 'none';
    initials.style.display = 'flex';
  }
});

function filterByRoom(room) {
  activeRoomFilter = room;
  currentPage = 1;

  // Sync the dropdown to match (or reset it)
  const dropdown = document.getElementById('roomFilter');
  if (dropdown) dropdown.value = room;

  document.querySelectorAll('.room-filter-btn').forEach(btn => btn.classList.remove('active-room-filter'));
  event.target.classList.add('active-room-filter');

  filterAndSortInventoryData();
  renderInventoryTable();
}

// ====== MODERN NOTIFICATION SYSTEM ======
function showNotification(message, type = 'info', duration = 3000) {
  const existingNotification = document.querySelector('.modern-notification');
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement('div');
  notification.className = `modern-notification ${type}`;
  
  let icon = '';
  switch(type) {
    case 'success':
      icon = '✓';
      break;
    case 'error':
      icon = '✕';
      break;
    case 'warning':
      icon = '⚠';
      break;
    case 'info':
      icon = 'ℹ';
      break;
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

// ====== MODERN CONFIRMATION DIALOG ======
function showConfirmation(message, onConfirm, onCancel = null) {
  const existingConfirm = document.querySelector('.modern-confirmation');
  if (existingConfirm) {
    existingConfirm.remove();
  }

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

// Navigation
function showSection(sectionId) {
  document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
  document.getElementById(sectionId).classList.add("active");
  updateActiveNav(sectionId);
  
  // Reset reservation selection when leaving My Reservations
  if (sectionId !== 'myReservations') {
    selectedReservation = null;
    selectedReservationStatus = null;
    updateActionButtons(null);
  }
  
  if (sectionId === 'myReservations') {
    loadMyReservations();
  } else if (sectionId === 'inventory') {
    loadInventory();
  }
}

function logout() {
  showConfirmation(
    'Are you sure you want to logout?',
    () => {
      window.location.href = 'logout.php';
    }
  );
}

// Reservation System - Updated to handle multiple items
document.getElementById('reservationForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const submitBtn = document.getElementById('reserveBtn');
  const itemInputs = document.querySelectorAll('.item-name-input');
  const dateNeeded = document.getElementById('reserveDate').value;
  const purpose = document.getElementById('reservePurpose').value;
  const timeNeeded = document.getElementById('reserveTime').value;

  
  // Collect all item names
  const itemNames = [];
  itemInputs.forEach(input => {
    if (input.value.trim()) {
      itemNames.push(input.value.trim());
    }
  });
  
  if (itemNames.length === 0) {
    showNotification('Please enter at least one item name', 'warning');
    return;
  }
  
  if (!dateNeeded) {
    showNotification('Please select a date', 'warning');
    return;
  }
  
  submitBtn.textContent = 'Submitting...';
  submitBtn.disabled = true;
  
  // Prepare data for multiple items
  const formData = new FormData();
  formData.append('item_names', JSON.stringify(itemNames));
  formData.append('date_needed', dateNeeded);
  formData.append('time_needed', timeNeeded);
  formData.append('purpose', purpose);
  
  fetch('submit_reservation.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      
      // Reset form
      this.reset();
      
      // Reset to single item input
      const itemsContainer = document.getElementById('itemsContainer');
      itemsContainer.innerHTML = `
        <div class="item-input-group">
          <label><strong>Item Name:</strong></label>
          <div style="display: flex; gap: 10px; align-items: center;">
            <input 
              type="text" 
              class="item-name-input" 
              name="item_names[]" 
              placeholder="Enter equipment name" 
              required 
              style="flex: 1; padding: 8px;">
            <button type="button" class="remove-item-btn" onclick="removeItemInput(this)" style="display: none;">
              ✕
            </button>
          </div>
        </div>
      `;
      itemInputCount = 1;
      
      loadMyReservations();
    } else {
      showNotification(data.message, 'error');
    }
    submitBtn.textContent = 'Submit Reservation Request';
    submitBtn.disabled = false;
  })
  .catch(error => {
    showNotification('Error submitting reservation', 'error');
    submitBtn.textContent = 'Submit Reservation Request';
    submitBtn.disabled = false;
  });
});

// Track selected reservation
let selectedReservation = null;
let selectedReservationStatus = null;

// My Reservations Pagination State
let allMyReservations = [];
let myResCurrentPage = 1;
let myResPageSize = 10;

function loadMyReservations() {
  // Reset selection when reloading
  selectedReservation = null;
  selectedReservationStatus = null;
  updateActionButtons(null);

  fetch('get_reservations.php')
    .then(response => response.json())
    .then(reservations => {
      allMyReservations = Array.isArray(reservations) ? reservations : [];
      myResCurrentPage = 1;
      renderMyReservationsTable();
      setupMyReservationsPagination();
    })
    .catch(error => {
      console.error('Error loading reservations:', error);
      document.getElementById('teacherReservationsList').innerHTML = '<p>Error loading reservations.</p>';
    });
}

function filterMyReservations(status) {
  myResActiveFilter = status;
  myResCurrentPage = 1;

  // Reset selection
  selectedReservation = null;
  selectedReservationStatus = null;
  updateActionButtons(null);

  // Update active button style
  document.querySelectorAll('.res-filter-btn').forEach(btn => {
    btn.classList.remove('active-filter');
  });
  event.target.classList.add('active-filter');

  renderMyReservationsTable();
}

function renderMyReservationsTable() {
  const container = document.getElementById('teacherReservationsList');
  const paginationContainer = document.getElementById('myReservationsPagination');

  // Apply filter
  const filtered = myResActiveFilter
    ? allMyReservations.filter(r => r.status === myResActiveFilter)
    : allMyReservations;

  if (!filtered || filtered.length === 0) {
    const label = myResActiveFilter || 'reservation';
    container.innerHTML = `<p>No ${myResActiveFilter ? myResActiveFilter + ' ' : ''}reservation requests found.</p>`;
    paginationContainer.style.display = 'none';
    return;
  }

  paginationContainer.style.display = 'flex';

  const totalPages = Math.ceil(filtered.length / myResPageSize);

  // Clamp current page
  if (myResCurrentPage > totalPages) myResCurrentPage = totalPages;

  const startIndex = (myResCurrentPage - 1) * myResPageSize;
  const endIndex = Math.min(startIndex + myResPageSize, filtered.length);
  const pageData = filtered.slice(startIndex, endIndex);

  let html = `
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background: #f8f9fa;">
          <th style="padding: 12px; border: 1px solid #ddd; width: 40px;">✎</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Item</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Date Needed</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Time Needed</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Purpose</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Status</th>
          <th style="padding: 12px; border: 1px solid #ddd;">Submitted</th>
        </tr>
      </thead>
      <tbody>
  `;

  pageData.forEach(reservation => {
    const statusClass = `reservation-status status-${reservation.status}`;
    const dateNeeded = new Date(reservation.date_needed).toLocaleDateString();
    const timeNeeded = reservation.time_needed || 'Not specified';
    const submitted = new Date(reservation.created_at).toLocaleDateString();
    const isEditable = ['pending', 'approved', 'borrowed'].includes(reservation.status);

    html += `
      <tr class="${isEditable ? 'reservation-row-editable' : 'reservation-row-readonly'}"
          data-reservation-id="${reservation.id}"
          data-reservation-status="${reservation.status}"
          onclick="${isEditable ? `selectReservation(${reservation.id}, '${reservation.status}', this)` : ''}">
        <td style="padding: 12px; border: 1px solid #ddd; text-align: center; font-size: 16px;">
          ${isEditable ? '✎' : ''}
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">${reservation.item_name}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${dateNeeded}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${timeNeeded}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${reservation.purpose || 'N/A'}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">
          <span class="${statusClass}">${reservation.status.toUpperCase()}</span>
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">${submitted}</td>
      </tr>
    `;
  });

  html += '</tbody></table>';
  container.innerHTML = html;

  updateMyReservationsPaginationInfo(filtered.length);
  updateMyReservationsPaginationButtons(totalPages);
}

function updateMyReservationsPaginationInfo(totalCount) {
  const info = document.getElementById('myReservationsPaginationInfo');
  const total = totalCount ?? allMyReservations.length;
  if (!total) { info.textContent = 'No records found'; return; }

  const totalPages = Math.ceil(total / myResPageSize);
  const start = (myResCurrentPage - 1) * myResPageSize + 1;
  const end = Math.min(myResCurrentPage * myResPageSize, total);
  info.textContent = `Showing ${start}–${end} of ${total} requests (Page ${myResCurrentPage} of ${totalPages})`;
}

function updateMyReservationsPaginationButtons(totalPages) {
  const pageNumbers = document.getElementById('myResPageNumbers');
  pageNumbers.innerHTML = '';

  let startPage = Math.max(1, myResCurrentPage - 2);
  let endPage = Math.min(totalPages, myResCurrentPage + 2);
  if (myResCurrentPage <= 3) endPage = Math.min(5, totalPages);
  if (myResCurrentPage >= totalPages - 2) startPage = Math.max(1, totalPages - 4);

  for (let i = startPage; i <= endPage; i++) {
    const btn = document.createElement('button');
    btn.className = `page-number ${i === myResCurrentPage ? 'active' : ''}`;
    btn.textContent = i;
    btn.onclick = () => goToMyReservationsPage(i);
    pageNumbers.appendChild(btn);
  }

  document.getElementById('myResFirstPage').disabled = myResCurrentPage === 1;
  document.getElementById('myResPrevPage').disabled = myResCurrentPage === 1;
  document.getElementById('myResNextPage').disabled = myResCurrentPage === totalPages;
  document.getElementById('myResLastPage').disabled = myResCurrentPage === totalPages;
}

function setupMyReservationsPagination() {
  document.getElementById('myResFirstPage').onclick = () => goToMyReservationsPage(1);
  document.getElementById('myResPrevPage').onclick = () => goToMyReservationsPage(myResCurrentPage - 1);
  document.getElementById('myResNextPage').onclick = () => goToMyReservationsPage(myResCurrentPage + 1);
  document.getElementById('myResLastPage').onclick = () => goToMyReservationsPage(
    Math.ceil(allMyReservations.length / myResPageSize)
  );

  document.getElementById('myResPageSize').onchange = (e) => {
    myResPageSize = parseInt(e.target.value);
    myResCurrentPage = 1;
    renderMyReservationsTable();
  };
}

function goToMyReservationsPage(page) {
  const totalPages = Math.ceil(allMyReservations.length / myResPageSize);
  if (page < 1 || page > totalPages) return;

  // Reset row selection when changing pages
  selectedReservation = null;
  selectedReservationStatus = null;
  updateActionButtons(null);

  myResCurrentPage = page;
  renderMyReservationsTable();
  document.getElementById('teacherReservationsList').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Select/deselect a reservation row
function selectReservation(reservationId, status, rowElement) {
  // Check if this row is already selected
  const isAlreadySelected = rowElement.classList.contains('selected-reservation');
  
  // Remove ALL previous selections first
  document.querySelectorAll('.reservation-row-editable').forEach(row => {
    row.classList.remove('selected-reservation');
  });
  
  // If clicking a different row (wasn't already selected), select it
  if (!isAlreadySelected) {
    selectedReservation = reservationId;
    selectedReservationStatus = status;
    rowElement.classList.add('selected-reservation');
    updateActionButtons(status);
  } else {
    // If clicking the same row, deselect it
    selectedReservation = null;
    selectedReservationStatus = null;
    updateActionButtons(null);
  }
}

// Update which action buttons are enabled
function updateActionButtons(status) {
  const cancelBtn = document.getElementById('cancelReservationBtn');
  const borrowBtn = document.getElementById('borrowReservationBtn');
  const returnBtn = document.getElementById('returnReservationBtn');
  
  // Disable all buttons first
  cancelBtn.disabled = true;
  borrowBtn.disabled = true;
  returnBtn.disabled = true;
  
  // Enable appropriate button
  if (status === 'pending') {
    cancelBtn.disabled = false;
  } else if (status === 'approved') {
    borrowBtn.disabled = false;
  } else if (status === 'borrowed') {
    returnBtn.disabled = false;
  }
}

// Cancel pending reservation
function cancelSelectedReservation() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  showConfirmation(
    'Are you sure you want to cancel this reservation request?',
    () => updateReservationStatus(selectedReservation, 'cancelled')
  );
}

// Mark as borrowed
function markAsBorrowed() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  showConfirmation(
    'Mark this item as borrowed?',
    () => updateReservationStatus(selectedReservation, 'borrowed')
  );
}

// Mark as returned
function markAsReturned() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  showConfirmation(
    'Mark this item as returned?',
    () => updateReservationStatus(selectedReservation, 'returned')
  );
}

// Send update to server
function updateReservationStatus(reservationId, newStatus) {
  const formData = new FormData();
  formData.append('reservation_id', reservationId);
  formData.append('status', newStatus);
  
  fetch('update_reservation_status.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      // Reset selection
      selectedReservation = null;
      selectedReservationStatus = null;
      loadMyReservations();
    } else {
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    showNotification('Error: ' + error.message, 'error');
  });
}

// Update which action buttons are enabled
function updateActionButtons(status) {
  const cancelBtn = document.getElementById('cancelReservationBtn');
  const borrowBtn = document.getElementById('borrowReservationBtn');
  const returnBtn = document.getElementById('returnReservationBtn');
  
  // Reset all buttons
  cancelBtn.disabled = true;
  borrowBtn.disabled = true;
  returnBtn.disabled = true;
  
  // Enable appropriate button based on status
  if (status === 'pending') {
    cancelBtn.disabled = false;
  } else if (status === 'approved') {
    borrowBtn.disabled = false;
  } else if (status === 'borrowed') {
    returnBtn.disabled = false;
  }
}

// Cancel pending reservation
function cancelSelectedReservation() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  
  showConfirmation(
    'Are you sure you want to cancel this reservation request?',
    () => {
      updateReservationStatus(selectedReservation, 'cancelled');
    }
  );
}

// Mark as borrowed
function markAsBorrowed() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  
  showConfirmation(
    'Mark this item as borrowed?',
    () => {
      updateReservationStatus(selectedReservation, 'borrowed');
    }
  );
}

// Mark as returned
function markAsReturned() {
  if (!selectedReservation) {
    showNotification('Please select a reservation first', 'warning');
    return;
  }
  
  showConfirmation(
    'Mark this item as returned?',
    () => {
      updateReservationStatus(selectedReservation, 'returned');
    }
  );
}

// Update reservation status
function updateReservationStatus(reservationId, newStatus) {
  const formData = new FormData();
  formData.append('reservation_id', reservationId);
  formData.append('status', newStatus);
  
  fetch('update_reservation_status.php', { 
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      selectedReservation = null;
      selectedReservationStatus = null;
      loadMyReservations(); // Reload the table
    } else {
      showNotification(data.message, 'error');
    }
  })
  .catch(error => {
    showNotification('Error updating reservation: ' + error.message, 'error');
  });
}

// Inventory System with Pagination
let allInventoryData = [];
let filteredInventoryData = [];
let currentPage = 1;
let pageSize = 10;
let currentSortAsc = true;
let myResActiveFilter = ''; // '' = All
let activeRoomFilter = ''; // '' = All Rooms

function loadInventory() {
  console.log('Loading inventory...');
  
  const tbody = document.getElementById('inventoryBody');
  tbody.innerHTML = '<tr><td colspan="5">Loading inventory...</td></tr>';
  
  fetch('api/get_inventory.php')
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      console.log('Inventory data received:', data);
      
      allInventoryData = Array.isArray(data) ? data : [];
      populateRoomFilter();
      filterAndSortInventoryData();
      renderInventoryTable();
      setupPagination();
    })
    .catch(error => {
      console.error('Error loading inventory:', error);
      document.getElementById('inventoryBody').innerHTML = 
        '<tr><td colspan="5">Error loading inventory: ' + error.message + '</td></tr>';
    });
}

function populateRoomFilter() {
  const roomFilter = document.getElementById('roomFilter');
  
  if (!allInventoryData || allInventoryData.length === 0) {
    roomFilter.innerHTML = '<option value="">All</option>';
    return;
  }
  
  // Get unique rooms from inventory using correct field names
  const rooms = [...new Set(allInventoryData.map(item => {
    const room = item.room || item.Room;
    return room && room.trim() !== '' ? room : null;
  }).filter(room => room))];
  
  // Clear existing options but keep "All"
  roomFilter.innerHTML = '<option value="">All</option>';
  
  // Add room options
  rooms.forEach(room => {
    const option = document.createElement('option');
    option.value = room;
    option.textContent = room;
    roomFilter.appendChild(option);
  });
}

function filterAndSortInventoryData() {
  const q = (document.getElementById('searchInput').value || '').toLowerCase().trim();

  filteredInventoryData = allInventoryData.filter(item => {
    const room = (item.room || item.Room || '').toString();

    // Room button filter takes priority; falls back to dropdown if no button active
    if (activeRoomFilter && room !== activeRoomFilter) return false;

    // Search match
    if (q) {
      const itemName = item.item || item.Item || '';
      const description = item.description || item.Description || '';
      const remarks = item.remarks || item.Remarks || '';
      const searchText = (itemName + ' ' + description + ' ' + remarks).toLowerCase();
      if (!searchText.includes(q)) return false;
    }

    return true;
  });

  filteredInventoryData.sort((a, b) => {
    const itemA = (a.item || a.Item || '').toString().toLowerCase();
    const itemB = (b.item || b.Item || '').toString().toLowerCase();
    return currentSortAsc ? itemA.localeCompare(itemB) : itemB.localeCompare(itemA);
  });

  currentPage = 1;
}

function sortTable() {
  currentSortAsc = !currentSortAsc;
  filterAndSortInventoryData();
  renderInventoryTable();
}

function renderInventoryTable() {
  const tbody = document.getElementById('inventoryBody');
  
  if (!filteredInventoryData || filteredInventoryData.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5">No inventory items found in database</td></tr>';
    updatePaginationInfo();
    return;
  }
  
  // Calculate pagination
  const totalPages = Math.ceil(filteredInventoryData.length / pageSize);
  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, filteredInventoryData.length);
  const pageData = filteredInventoryData.slice(startIndex, endIndex);

  let html = '';
  pageData.forEach(item => {
    // Use the correct field names from your database
    const itemName = item.item || item.Item || 'N/A';
    const room = item.room || item.Room || 'N/A';
    const description = item.description || item.Description || 'N/A';
    const quantity = item.beginning || item.Beginning || item.quantity || '0';
    const remarks = item.remarks || item.Remarks || 'N/A';
    
    const rowId = item.id || item.ID || item.item_id || '';
const imagePath = item.image_path || '';

html += `
  <tr data-id="${rowId}" data-image="${escapeHtml(imagePath)}">
    <td>
      <div class="item-cell-container">
        <span class="item-name-text">${escapeHtml(itemName)}</span>
        <img src="uploads/eye.png" class="eye-icon" 
             onclick="openImageModal('${rowId}', '${escapeHtml(itemName)}', '${escapeHtml(room)}', '${escapeHtml(imagePath)}', event)" 
             onmouseenter="showHoverPreview(this, '${escapeHtml(imagePath)}', '${escapeHtml(itemName)}', '${escapeHtml(room)}')" 
             onmouseleave="hideHoverPreview()" 
             title="View Image" alt="View Image">
      </div>
    </td>
    <td>${escapeHtml(room)}</td>
    <td>${escapeHtml(description)}</td>
    <td style="text-align: center;">${escapeHtml(quantity)}</td>
    <td>${escapeHtml(remarks)}</td>
  </tr>
`;
  });
  
  tbody.innerHTML = html;
  updatePaginationInfo();
  updatePaginationButtons(totalPages);
}

function updatePaginationInfo() {
  const paginationInfo = document.getElementById('paginationInfo');
  
  if (!filteredInventoryData.length) {
    paginationInfo.textContent = 'No records found';
    return;
  }

  const totalItems = filteredInventoryData.length;
  const totalPages = Math.ceil(totalItems / pageSize);
  const startIndex = (currentPage - 1) * pageSize + 1;
  const endIndex = Math.min(currentPage * pageSize, totalItems);

  paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${totalItems} items (Page ${currentPage} of ${totalPages})`;
}

function updatePaginationButtons(totalPages) {
  const pageNumbers = document.getElementById('pageNumbers');
  const firstPageBtn = document.getElementById('firstPage');
  const prevPageBtn = document.getElementById('prevPage');
  const nextPageBtn = document.getElementById('nextPage');
  const lastPageBtn = document.getElementById('lastPage');

  // Clear page numbers
  pageNumbers.innerHTML = '';

  // Calculate which page numbers to show
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, currentPage + 2);

  // Adjust if we're near the start or end
  if (currentPage <= 3) {
    endPage = Math.min(5, totalPages);
  }
  if (currentPage >= totalPages - 2) {
    startPage = Math.max(1, totalPages - 4);
  }

  // Add page number buttons
  for (let i = startPage; i <= endPage; i++) {
    const pageBtn = document.createElement('button');
    pageBtn.className = `page-number ${i === currentPage ? 'active' : ''}`;
    pageBtn.textContent = i;
    pageBtn.onclick = () => goToPage(i);
    pageNumbers.appendChild(pageBtn);
  }

  // Update button states
  firstPageBtn.disabled = currentPage === 1;
  prevPageBtn.disabled = currentPage === 1;
  nextPageBtn.disabled = currentPage === totalPages;
  lastPageBtn.disabled = currentPage === totalPages;
}

function setupPagination() {
  const firstPageBtn = document.getElementById('firstPage');
  const prevPageBtn = document.getElementById('prevPage');
  const nextPageBtn = document.getElementById('nextPage');
  const lastPageBtn = document.getElementById('lastPage');
  const pageSizeSelect = document.getElementById('pageSize');

  firstPageBtn.onclick = () => goToPage(1);
  prevPageBtn.onclick = () => goToPage(currentPage - 1);
  nextPageBtn.onclick = () => goToPage(currentPage + 1);
  lastPageBtn.onclick = () => goToPage(Math.ceil(filteredInventoryData.length / pageSize));
  
  pageSizeSelect.onchange = (e) => {
    pageSize = parseInt(e.target.value);
    currentPage = 1;
    filterAndSortInventoryData();
    renderInventoryTable();
  };
}

function goToPage(page) {
  const totalPages = Math.ceil(filteredInventoryData.length / pageSize);
  if (page < 1 || page > totalPages) return;
  
  currentPage = page;
  renderInventoryTable();
  
  // Scroll to top of table
  document.getElementById('inventoryBody').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function searchTable() {
  filterAndSortInventoryData();
  renderInventoryTable();
}

// Simple HTML escape function
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Event listeners for inventory
document.getElementById('roomFilter').addEventListener('change', () => {
  filterAndSortInventoryData();
  renderInventoryTable();
});

// Load reservations when page loads
document.addEventListener('DOMContentLoaded', function() {
  loadMyReservations();
  setupPagination();
});

// Highlight active navigation button
function updateActiveNav(sectionId) {
  // Remove active class from all nav buttons
  document.querySelectorAll('nav button').forEach(btn => {
    btn.classList.remove('active-nav');
  });
  
  // Add active class to current section
  if (sectionId === 'reservations') {
    document.getElementById('navReservations').classList.add('active-nav');
  } else if (sectionId === 'myReservations') {
    document.getElementById('navMyReservations').classList.add('active-nav');
  } else if (sectionId === 'inventory') {
    document.getElementById('navInventory').classList.add('active-nav');
  }
}

// Update the showSection function to highlight active nav
const originalShowSection = showSection;
function showSection(sectionId) {
  document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
  document.getElementById(sectionId).classList.add("active");
  updateActiveNav(sectionId);
  
  if (sectionId === 'myReservations') {
    loadMyReservations();
  } else if (sectionId === 'inventory') {
    loadInventory();
  }
}

// Set initial active state on page load (Calendar is default now)
document.addEventListener('DOMContentLoaded', function() {
  // Since calendar is the landing page, no button is active initially
  // But if they navigate to teacher_dashboard.php directly, highlight reservations
  const urlParams = new URLSearchParams(window.location.search);
  const section = urlParams.get('section') || window.location.hash.substring(1) || 'reservations';
  
  if (section) {
    showSection(section);
  }
  
  loadMyReservations();
  setupPagination();
});

// ====== HOVER PREVIEW SYSTEM ======
let hoverPreviewElement = null;
let hoverPreviewTimeout = null;

function showHoverPreview(eyeIcon, imagePath, itemName, room) {
  // Clear any existing timeout
  if (hoverPreviewTimeout) {
    clearTimeout(hoverPreviewTimeout);
  }
  
  // Remove existing preview
  hideHoverPreview();
  
  // Create preview element
  hoverPreviewElement = document.createElement('div');
  hoverPreviewElement.className = 'hover-preview';
  
  let imageHtml = '';
  if (imagePath) {
    imageHtml = `<img src="${imagePath}" class="hover-preview-image" alt="${itemName}">`;
  } else {
    imageHtml = `<div class="hover-preview-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
      <span style="font-size: 48px;">📷</span>
    </div>`;
  }
  
  hoverPreviewElement.innerHTML = `
    ${imageHtml}
    <div class="hover-preview-details">
      <div><span class="label">Item:</span> ${itemName}</div>
      <div><span class="label">Room:</span> ${room}</div>
    </div>
  `;
  
  // Add hover listeners to keep preview visible
  hoverPreviewElement.addEventListener('mouseenter', () => {
    if (hoverPreviewTimeout) {
      clearTimeout(hoverPreviewTimeout);
      hoverPreviewTimeout = null;
    }
  });
  
  hoverPreviewElement.addEventListener('mouseleave', () => {
    hideHoverPreview();
  });
  
  document.body.appendChild(hoverPreviewElement);
  
  // Position the preview
  const iconRect = eyeIcon.getBoundingClientRect();
  hoverPreviewElement.style.position = 'fixed';
  
  // Position above the eye icon with some spacing
  const previewTop = iconRect.top - hoverPreviewElement.offsetHeight - 10;
  
  // If there's not enough space above, position below
  if (previewTop < 10) {
    hoverPreviewElement.style.top = (iconRect.bottom + 10) + 'px';
  } else {
    hoverPreviewElement.style.top = previewTop + 'px';
  }
  
  // Center horizontally relative to the eye icon
  hoverPreviewElement.style.left = (iconRect.left - (hoverPreviewElement.offsetWidth / 2) + (iconRect.width / 2)) + 'px';
  
  // Show immediately
  setTimeout(() => {
    if (hoverPreviewElement) {
      hoverPreviewElement.classList.add('show');
    }
  }, 50);
}

function hideHoverPreview() {
  if (hoverPreviewElement) {
    hoverPreviewElement.remove();
    hoverPreviewElement = null;
  }
  if (hoverPreviewTimeout) {
    clearTimeout(hoverPreviewTimeout);
    hoverPreviewTimeout = null;
  }
}

// ====== VIEW-ONLY IMAGE MODAL ======
function openImageModal(itemId, itemName, room, imagePath, event) {
  event.stopPropagation();
  
  // Create modal if it doesn't exist
  let modal = document.getElementById('imageModal');
  if (!modal) {
    const modalHTML = `
      <div id="imageModal" class="image-modal">
        <div class="image-modal-content">
          <div class="image-modal-header">
            <h3 id="imageModalTitle">Item Image</h3>
            <button class="image-modal-close" onclick="closeImageModal()">×</button>
          </div>
          <div class="image-modal-body">
            <div id="imagePreviewContainer" class="image-preview-container">
              <img id="previewImage" class="preview-image" alt="Item image">
              <div id="noImagePlaceholder" class="no-image-placeholder" style="display: flex; flex-direction: column;">
                <div class="icon">📷</div>
                <p><strong>No image available</strong></p>
              </div>
            </div>
            
            <div class="item-details">
              <div class="item-detail-row">
                <span class="item-detail-label">Item:</span>
                <span class="item-detail-value" id="modalItemName">-</span>
              </div>
              <div class="item-detail-row">
                <span class="item-detail-label">Room:</span>
                <span class="item-detail-value" id="modalItemRoom">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Close modal when clicking outside
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target.id === 'imageModal') {
        closeImageModal();
      }
    });
    
    modal = document.getElementById('imageModal');
  }
  
  // Update modal content
  const modalTitle = document.getElementById('imageModalTitle');
  const itemNameEl = document.getElementById('modalItemName');
  const itemRoomEl = document.getElementById('modalItemRoom');
  const previewContainer = document.getElementById('imagePreviewContainer');
  const previewImage = document.getElementById('previewImage');
  const noImagePlaceholder = document.getElementById('noImagePlaceholder');
  
  modalTitle.textContent = 'Item Image';
  itemNameEl.textContent = itemName;
  itemRoomEl.textContent = room;
  
  // Show or hide image/placeholder
  if (imagePath) {
    previewImage.src = imagePath;
    previewImage.style.display = 'block';
    noImagePlaceholder.style.display = 'none';
    previewContainer.classList.add('has-image');
  } else {
    previewImage.style.display = 'none';
    noImagePlaceholder.style.display = 'flex';
    previewContainer.classList.remove('has-image');
  }
  
  modal.classList.add('show');
}

function closeImageModal() {
  const modal = document.getElementById('imageModal');
  if (modal) {
    modal.classList.remove('show');
  }
}

// Store inventory items for autocomplete
let inventoryItems = [];
// Load inventory items for autocomplete 
function loadInventoryForAutocomplete() {
  fetch('api/get_inventory.php')
    .then(response => response.json())
    .then(data => {
      inventoryItems = data.map(item => ({
        name: item.item || item.Item,
        room: item.room || item.Room,
        description: item.description || item.Description
      }));
      console.log('Loaded', inventoryItems.length, 'items for autocomplete');
      
      // Initialize autocomplete on existing inputs
      initializeAllAutocomplete();
    })
    .catch(error => {
      console.error('Error loading inventory for autocomplete:', error);
    });
}

// Initialize autocomplete on ALL existing item inputs
function initializeAllAutocomplete() {
  const allInputs = document.querySelectorAll('.item-name-input');
  console.log('Initializing autocomplete on', allInputs.length, 'inputs');
  
  allInputs.forEach(input => {
    attachAutocompleteToInput(input);
  });
}

// Attach autocomplete to a single input element
function attachAutocompleteToInput(input) {
  // Check if already initialized
  if (input.dataset.autocompleteInitialized === 'true') {
    console.log('Autocomplete already initialized on this input');
    return;
  }
  
  // Mark as initialized
  input.dataset.autocompleteInitialized = 'true';
  
  // Disable browser's native autocomplete
  input.setAttribute('autocomplete', 'off');
  input.setAttribute('autocorrect', 'off');
  input.setAttribute('autocapitalize', 'off');
  input.setAttribute('spellcheck', 'false');
  
  // Create suggestions container for this input
  const suggestionsContainer = document.createElement('div');
  suggestionsContainer.className = 'autocomplete-suggestions';
  
  // Insert after the input's parent div
  const inputWrapper = input.parentElement;
  inputWrapper.style.position = 'relative';
  inputWrapper.appendChild(suggestionsContainer);

  // Listen for input changes
  input.addEventListener('input', function() {
    const value = this.value.toLowerCase().trim();
    
    // Clear suggestions if input is empty
    if (!value) {
      suggestionsContainer.innerHTML = '';
      suggestionsContainer.style.display = 'none';
      return;
    }

    // Filter inventory items that match
    const matches = inventoryItems.filter(item => 
      item.name.toLowerCase().includes(value)
    ).slice(0, 8);

    // Display suggestions
    if (matches.length > 0) {
      let html = '';
      matches.forEach(item => {
        html += `
          <div class="autocomplete-item" data-name="${escapeHtml(item.name)}">
            <div class="autocomplete-item-name">${highlightMatch(item.name, value)}</div>
            <div class="autocomplete-item-details">
              <span class="autocomplete-room">${escapeHtml(item.room)}</span>
              ${item.description ? `<span class="autocomplete-desc">${escapeHtml(item.description)}</span>` : ''}
            </div>
          </div>
        `;
      });
      suggestionsContainer.innerHTML = html;
      suggestionsContainer.style.display = 'block';

      // Add click handlers
      suggestionsContainer.querySelectorAll('.autocomplete-item').forEach(suggestionEl => {
        suggestionEl.addEventListener('click', function() {
          input.value = this.getAttribute('data-name');
          suggestionsContainer.innerHTML = '';
          suggestionsContainer.style.display = 'none';
          input.focus();
        });
      });
    } else {
      suggestionsContainer.innerHTML = `
        <div class="autocomplete-no-match">
          No matches found. You can still type a custom item name.
        </div>
      `;
      suggestionsContainer.style.display = 'block';
    }
  });

  // Close suggestions when clicking outside
  document.addEventListener('click', function(e) {
    if (e.target !== input && !suggestionsContainer.contains(e.target)) {
      suggestionsContainer.style.display = 'none';
    }
  });

  // Handle keyboard navigation
  input.addEventListener('keydown', function(e) {
    const items = suggestionsContainer.querySelectorAll('.autocomplete-item');
    const activeItem = suggestionsContainer.querySelector('.autocomplete-item.active');
    let currentIndex = Array.from(items).indexOf(activeItem);

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (currentIndex < items.length - 1) {
        if (activeItem) activeItem.classList.remove('active');
        items[currentIndex + 1].classList.add('active');
      } else if (items.length > 0) {
        if (activeItem) activeItem.classList.remove('active');
        items[0].classList.add('active');
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (currentIndex > 0) {
        activeItem.classList.remove('active');
        items[currentIndex - 1].classList.add('active');
      } else if (items.length > 0) {
        if (activeItem) activeItem.classList.remove('active');
        items[items.length - 1].classList.add('active');
      }
    } else if (e.key === 'Enter' && activeItem) {
      e.preventDefault();
      input.value = activeItem.getAttribute('data-name');
      suggestionsContainer.style.display = 'none';
    } else if (e.key === 'Escape') {
      suggestionsContainer.style.display = 'none';
    }
  });
  
  console.log('Autocomplete attached to input');
}

// Highlight matching text
function highlightMatch(text, query) {
  const index = text.toLowerCase().indexOf(query.toLowerCase());
  if (index === -1) return escapeHtml(text);
  
  const before = text.substring(0, index);
  const match = text.substring(index, index + query.length);
  const after = text.substring(index + query.length);
  
  return `${escapeHtml(before)}<strong class="highlight">${escapeHtml(match)}</strong>${escapeHtml(after)}`;
}

// ====== UPDATED ADD ITEM FUNCTION ======
let itemInputCount = 1;

function addItemInput() {
  itemInputCount++;
  
  const itemsContainer = document.getElementById('itemsContainer');
  
  const newItemGroup = document.createElement('div');
  newItemGroup.className = 'item-input-group';
  newItemGroup.innerHTML = `
    <label><strong>Item Name:</strong></label>
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px; position: relative;">
      <input 
        type="text" 
        class="item-name-input" 
        name="item_names[]" 
        placeholder="Enter equipment name" 
        required 
        style="flex: 1; padding: 8px;">
      <button type="button" class="remove-item-btn" onclick="removeItemInput(this)" style="background: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        ✕
      </button>
    </div>
  `;
  
  itemsContainer.appendChild(newItemGroup);
  
  // ✅ Attach autocomplete to the NEW input
  const newInput = newItemGroup.querySelector('.item-name-input');
  attachAutocompleteToInput(newInput);
  onclick
}

function removeItemInput(button) {
  const itemGroup = button.closest('.item-input-group');
  itemGroup.remove();
  itemInputCount--;
  updateRemoveButtons();
}

function updateRemoveButtons() {
  const removeButtons = document.querySelectorAll('.remove-item-btn');
  const itemGroups = document.querySelectorAll('.item-input-group');
  
  if (itemGroups.length > 1) {
    removeButtons.forEach(btn => {
      btn.style.display = 'block';
    });
  } else {
    removeButtons.forEach(btn => {
      btn.style.display = 'none';
    });
  }
}



// ====== INITIALIZATION ======
document.addEventListener('DOMContentLoaded', function() {
  loadInventoryForAutocomplete();
  loadMyReservations();
  setupPagination();
});

// ====== DYNAMIC ITEM INPUTS ======

function addItemInput() {
  itemInputCount++;
  
  const itemsContainer = document.getElementById('itemsContainer');
  
  const newItemGroup = document.createElement('div');
  newItemGroup.className = 'item-input-group';
  newItemGroup.innerHTML = `
    <label><strong>Item Name:</strong></label>
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 10px;">
      <input 
        type="text" 
        class="item-name-input" 
        name="item_names[]" 
        placeholder="Enter equipment name" 
        required 
        style="flex: 1; padding: 8px;">
      <button type="button" 
      class="remove-item-btn" 
      onclick="removeItemInput(this)" 
      style="background: #dc3545; 
      color: white; 
      padding: 8px 12px; 
      border: none; 
      border-radius: 4px; 
      cursor: pointer; 
      font-weight: bold;">
        ✕
      </button>
    </div>
  `;
  
  itemsContainer.appendChild(newItemGroup);
  
  // Show remove button on all items when there's more than one
  updateRemoveButtons();
}

function removeItemInput(button) {
  const itemGroup = button.closest('.item-input-group');
  itemGroup.remove();
  itemInputCount--;
  
  // Update remove buttons visibility
  updateRemoveButtons();
}

function updateRemoveButtons() {
  const removeButtons = document.querySelectorAll('.remove-item-btn');
  const itemGroups = document.querySelectorAll('.item-input-group');
  
  // Show remove buttons only if there's more than one item
  if (itemGroups.length > 1) {
    removeButtons.forEach(btn => {
      btn.style.display = 'block';
    });
  } else {
    removeButtons.forEach(btn => {
      btn.style.display = 'none';
    });
  }
}


</script>

<style>
    .reservation-status { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d1ecf1; color: #0c5460; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
/* ====== MY RESERVATIONS FILTER BUTTONS ====== */
.res-filter-btn {
  background: #242424;
  color: #FFD600;
  border: 2px solid #242424;
  padding: 10px 20px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.res-filter-btn:hover {
  background: #FFD600;
  color: #242424;
  border-color: #FFD600;
}

.res-filter-btn.active-filter {
  background: #FFD600;
  color: #1e1e1e;
  border-color: #FFD600;
}


/* CSS for the status buttons on reservations */
  .status-cancelled { background: #f8d7da; color: #721c24; }
  .status-borrowed { background: #d1ecf1; color: #0c5460; }
  .status-returned { background: #d4edda; color: #155724; }
  
/* ====== RESERVATION FORM SPLIT IN HALF ====== */
.reservation-form { 
    background: #f9fcff; 
    padding: 20px; 
    border-radius: 8px; 
    margin-bottom: 20px; 
  }

/* ====== PROFILE AVATAR ====== */
.profile-avatar-wrapper {
  position: relative;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  cursor: pointer;
  flex-shrink: 0;
  border: 3px solid #FFD600;
  box-shadow: 0 2px 10px rgba(0,0,0,0.25);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: hidden;
  background: #1e1e1e;
}

.profile-avatar-wrapper:hover {
  transform: scale(1.08);
  box-shadow: 0 4px 16px rgba(255, 214, 0, 0.5);
}

.profile-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: none; /* shown via JS when image loaded */
}

.profile-avatar-initials {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: bold;
  color: #FFD600;
  background: #1e1e1e;
  border-radius: 50%;
  user-select: none;
}

.profile-avatar-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0,0,0,0.55);
  color: white;
  font-size: 13px;
  text-align: center;
  padding: 3px 0;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.profile-avatar-wrapper:hover .profile-avatar-overlay {
  opacity: 1;
}

/* ====== RESERVATION ROW STYLES ====== */
.reservation-row-editable {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.reservation-row-editable:hover {
  background-color: #f8f9fa !important;
}

.reservation-row-editable.selected-reservation {
  background-color: #fff3cd !important;
  border-left: 4px solid #FFD600;
}

.reservation-row-readonly {
  cursor: not-allowed;
  opacity: 0.7;
}

  /* Selectable row style */
  .reservation-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
  }
  
  .reservation-row:hover {
    background-color: #f8f9fa !important;
  }
  
  .reservation-row.selected {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107;
  }


    /* ====== SORT BUTTON ====== */
#sortTable {
  background: #242424 !important;
  color: #FFD600;
  border: 2px solid #212121;
  padding: 10px 20px;
  border-radius: 30px;
  font-size: 16px;
  font-weight: bold;
  margin-left: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

#reserveBtn {
  background: #d19300ff; 
  color: white; 
  padding: 10px 20px; 
  border: none; 
  border-radius: 4px; 
  cursor: pointer; 
  font-weight: bold;
  transition: all 0.2s ease;
}

#reserveBtn:hover { 
 background: #000000;
 color: #ffd600;
 border-color: #000000;
 font-weight: bold;
}

#addItemBtn {
  background: #d19300ff; 
  color: white; 
  padding: 10px 20px; 
  border: none; 
  border-radius: 4px; 
  cursor: pointer; 
  margin-bottom: 15px; 
  margin-top: 8px; 
  margin-left: 0px; 
  font-size:8px;
}


#sortTable:hover {
  background: #FFD600 !important;
  color: #242424;
}


    /* ====== PAGINATION STYLES CSS====== */
    .pagination-container {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      border: 1px solid #dee2e6;
    }

    .pagination-info {
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
    }

    .pagination-controls {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
    }

    .pagination-btn {
      padding: 8px 16px;
      border: 1px solid #dee2e6;
      background: white;
      color: #a26c07;
      cursor: pointer;
      border-radius: 4px; 
      font-size: 14px;
      transition: all 0.2s ease;
      min-width: 80px;
    }

    .pagination-btn:hover:not(:disabled) {
      background: #000000;
      color: #ffd600;
      border-color: #000000;
      font-weight: bold;
    }

    .pagination-btn:disabled {
      background: #f8f9fa;
      color: #6c757d;
      cursor: not-allowed;
      border-color: #dee2e6;
    }

    .page-numbers {
      display: flex;
      gap: 5px;
    }

    .page-number {
      padding: 8px 12px;
      border: 1px solid #dee2e6;
      background: white;
      color: #a26c07;
      cursor: pointer;
      border-radius: 4px;
      font-size: 14px;
      min-width: 40px;
      text-align: center;
      transition: all 0.2s ease;
    }

    .page-number:hover {
      background: #ffd600;
      color: #000000;
      font-weight: bold;
    }

    .page-number.active {
      background: #000000;
      color: #ffd600;
      border-color: #000000;
    }

    .page-size-selector {
      font-size: 14px;
    }

    .page-size-selector select {
      padding: 4px 8px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      background: white;
    }



/* ====== EYE ICON STYLES ====== */
.item-cell-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 8px;
}

.item-name-text {
  flex: 1;
}

.eye-icon {
  cursor: pointer;
  width: 16px;
  height: 16px;
  opacity: 0.6;
  transition: all 0.3s ease;
  padding: 2px;
  border-radius: 4px;
  flex-shrink: 0;
  vertical-align: middle;
}

.eye-icon:hover {
  opacity: 1;
  background: rgba(255, 214, 0, 0.2);
  transform: scale(1.15);
}

/* ====== HOVER PREVIEW ====== */
.hover-preview {
  position: fixed;
  z-index: 9999;
  background: white;
  border: 3px solid #FFD600;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  opacity: 0;
  transition: opacity 0.2s ease;
  min-width: 250px;
  max-width: 300px;
}

.hover-preview.show {
  opacity: 1;
}

.hover-preview-image {
  width: 100%;
  height: 150px;
  object-fit: contain;
  background: #f5f5f5;
  border-radius: 8px;
  margin-bottom: 10px;
}

.hover-preview-details {
  font-size: 14px;
}

.hover-preview-details .label {
  font-weight: bold;
  color: #1e1e1e;
}

/* ====== IMAGE MODAL (VIEW ONLY) ====== */
.image-modal {
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

.image-modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 1;
}

.image-modal-content {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 90%;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6),
              0 0 0 3px #FFD600;
  transform: scale(0.9);
  transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.image-modal.show .image-modal-content {
  transform: scale(1);
}

.image-modal-header {
  background: linear-gradient(135deg, #FFD600 0%, #f0c000 100%);
  color: #1e1e1e;
  padding: 20px 25px;
  border-radius: 16px 16px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-modal-header h3 {
  font-size: 20px;
  margin: 0;
}

.image-modal-close {
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

.image-modal-close:hover {
  background: #FFD600;
  color: #1e1e1e;
  transform: rotate(90deg);
}

.image-modal-body {
  padding: 30px;
}

.image-preview-container {
  width: 100%;
  height: 300px;
  background: #f5f5f5;
  border: 3px solid #FFD600;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
}

.image-preview-container.has-image {
  background: white;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.no-image-placeholder {
  text-align: center;
  color: #999;
}

.no-image-placeholder .icon {
  font-size: 48px;
  margin-bottom: 10px;
  color: #ddd;
}

.no-image-placeholder p {
  margin: 5px 0;
  font-size: 14px;
}

.item-details {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 12px;
  border-left: 4px solid #FFD600;
}

.item-detail-row {
  display: flex;
  margin-bottom: 12px;
  font-size: 15px;
}

.item-detail-row:last-child {
  margin-bottom: 0;
}

.item-detail-label {
  font-weight: bold;
  color: #1e1e1e;
  min-width: 80px;
}

.item-detail-value {
  color: #666;
  flex: 1;
}

/* ====== AUTOCOMPLETE SUGGESTIONS ====== */
.autocomplete-suggestions {
  position: absolute;
  background: white;
  border: 2px solid #FFD600;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  max-height: 320px;
  overflow-y: auto;
  z-index: 1000;
  display: none;
  margin-top: 5px;
  width: 100%;
  max-width: 400px;
}

.autocomplete-item {
  padding: 12px 15px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.2s ease;
}

.autocomplete-item:last-child {
  border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item.active {
  background: linear-gradient(135deg, #FFD600 0%, #f0c000 100%);
  color: #1e1e1e;
}

.autocomplete-item-name {
  font-weight: bold;
  font-size: 15px;
  margin-bottom: 4px;
}

.autocomplete-item-details {
  font-size: 12px;
  color: #666;
  display: flex;
  gap: 10px;
}

.autocomplete-item:hover .autocomplete-item-details,
.autocomplete-item.active .autocomplete-item-details {
  color: #1e1e1e;
}

.autocomplete-room {
  background: #f5f5f5;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.autocomplete-item:hover .autocomplete-room,
.autocomplete-item.active .autocomplete-room {
  background: rgba(0, 0, 0, 0.1);
}

.autocomplete-desc {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.autocomplete-no-match {
  padding: 15px;
  text-align: center;
  color: #999;
  font-size: 14px;
  font-style: italic;
}

.highlight {
  background: #FFD600;
  color: #1e1e1e;
  padding: 0 2px;
  border-radius: 2px;
}

/* Make the input container position relative */
.reservation-form label {
  position: relative;
  display: block;
}


</style>


</body>
</html>