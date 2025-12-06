<?php
require_once 'check_session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Teacher Dashboard - LabTrack</title>
  <link rel="stylesheet" href="form.css">
  <style>
    .reservation-status { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d1ecf1; color: #0c5460; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .reservation-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
  </style>
</head>
<body>

<header>
  <h1>LabTrack - Teacher Dashboard</h1>
  <div style="float: center;">
    Welcome, <?php echo $_SESSION['full_name']; ?>  
  </div>
</header>

<div id="app">
  <nav>
    <button onclick="window.location.href='calendar.php'">📅 Calendar</button>
    <button onclick="showSection('reservations')">Send Requests</button>
    <button onclick="showSection('myReservations')">My Reservations</button>
    <button onclick="showSection('inventory')">View Inventory</button>
    <button onclick="logout()">Logout</button>
  </nav>

  <section id="reservations" class="active">
    <h2>Reserve Equipment</h2>
    
    <div class="reservation-form">
      <form id="reservationForm">
        <label><strong>Item Name:</strong></label>
        <input 
        type="text" 
        id="reserveItem" 
        name="item_name" 
        placeholder="Enter equipment name" 
        required style="width: 300px; padding: 8px;">
        
        <br><br>
        
        <label><strong>Date Needed:</strong></label>
        <input 
        type="date" 
        id="reserveDate" 
        name="date_needed" 
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
        
        <button 
        type="submit" 
        id="reserveBtn" 
        style="background: #d19300ff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
          Submit Reservation Request
        </button>
      </form>
    </div>

    <div id="reserveMessage" style="margin-top: 15px;"></div>
  </section>

  <!-- My Reservations Section -->
  <section id="myReservations">
    <h2>My Reservation Requests</h2>
    <div id="teacherReservationsList">
      <p>Loading your reservations...</p>
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

// Reservation System
document.getElementById('reservationForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const submitBtn = document.getElementById('reserveBtn');
  
  submitBtn.textContent = 'Submitting...';
  submitBtn.disabled = true;
  
  fetch('submit_reservation.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification(data.message, 'success');
      this.reset();
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

function loadMyReservations() {
  fetch('get_reservations.php')
    .then(response => response.json())
    .then(reservations => {
      const container = document.getElementById('teacherReservationsList');
      
      if (reservations.length === 0) {
        container.innerHTML = '<p>No reservation requests found.</p>';
        return;
      }
      
      let html = `
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 12px; border: 1px solid #ddd;">Item</th>
              <th style="padding: 12px; border: 1px solid #ddd;">Date Needed</th>
              <th style="padding: 12px; border: 1px solid #ddd;">Purpose</th>
              <th style="padding: 12px; border: 1px solid #ddd;">Status</th>
              <th style="padding: 12px; border: 1px solid #ddd;">Submitted</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      reservations.forEach(reservation => {
        const statusClass = `reservation-status status-${reservation.status}`;
        const dateNeeded = new Date(reservation.date_needed).toLocaleDateString();
        const submitted = new Date(reservation.created_at).toLocaleDateString();
        
        html += `
          <tr>
            <td style="padding: 12px; border: 1px solid #ddd;">${reservation.item_name}</td>
            <td style="padding: 12px; border: 1px solid #ddd;">${dateNeeded}</td>
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
    })
    .catch(error => {
      console.error('Error loading reservations:', error);
      document.getElementById('teacherReservationsList').innerHTML = '<p>Error loading reservations.</p>';
    });
}

// Inventory System with Pagination
let allInventoryData = [];
let filteredInventoryData = [];
let currentPage = 1;
let pageSize = 10;

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
  const roomVal = document.getElementById('roomFilter').value;
  const q = (document.getElementById('searchInput').value || '').toLowerCase().trim();

  filteredInventoryData = allInventoryData.filter(item => {
    // Room match (if selected)
    if (roomVal) {
      const room = item.room || item.Room || '';
      if (room.toString() !== roomVal) return false;
    }
    // search match
    if (q) {
      const itemName = item.item || item.Item || '';
      const description = item.description || item.Description || '';
      const remarks = item.remarks || item.Remarks || '';
      const searchText = (itemName + ' ' + description + ' ' + remarks).toLowerCase();
      if (!searchText.includes(q)) return false;
    }
    return true;
  });

  // Reset to first page when filtering
  currentPage = 1;
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
    
    html += `
      <tr>
        <td>${escapeHtml(itemName)}</td>
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
</script>

<style>
    .reservation-status { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d1ecf1; color: #0c5460; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .reservation-form { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    
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
</style>


</body>
</html>