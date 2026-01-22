<?php
require_once 'check_session.php';

// Restrict access to admin only
if (!isAdmin()) {
    echo "<script>alert('Access denied! Admin only.'); window.location.href = 'teacher_dashboard.php';</script>";
    exit;
}
$isAdmin = isAdmin();
$userName = $_SESSION['full_name'];
// Temporary debug
echo "<!-- DEBUG: User Type: " . $_SESSION['user_type'] . " -->";
echo "<!-- DEBUG: User ID: " . $_SESSION['user_id'] . " -->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  
  <title>LabTrack - Inventory System</title>
  <link rel="stylesheet" href="form.css">
</head>
<body>


<header>
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <h1 style="margin: 1%;">LabTrack - SHS Laboratory Inventory</h1>
    <div style="display: flex; align-items: center; gap: 20px;">
      <button id="layoutToggle" onclick="toggleLayout()">
       Toggle View
      </button>
      <div id= "welcome" style="text-align: left;">
        Welcome, <br> <?php echo $userName; ?>
      </div>
    </div>
  </div>
</header>

<!-- Main App -->
<div id="app">
  <nav>
  <button onclick="showSection('inventory')">Inventory</button>
  <button onclick="showSection('reservationRequests')" id="reservationRequestsBtn">
    Reservation Requests
    <span class="notification-badge" id="reservationNotification" style="display: none;">0</span>
  </button>
  <button onclick="window.location.href='calendar.php'">📅 Calendar</button>
  <button onclick="logout()">Logout</button>
</nav>

<!-- Inventory Section -->
<section id="inventory" class="active">
  <h2>Inventory</h2>
  <div class="search-bar">
    <label>Room:
      <select id="roomFilter"><option value="">All</option></select>
    </label>
    <input type="text" id="searchInput" placeholder="Search equipment..." onkeyup="searchTable()">
  </div>

  <button id="sortTable" onclick="sortTable()">Sort A–Z</button>
  <button id="addItemBtn" type="button">+ Add New Item</button>
  <button id="saveNewItemBtn" type="button" style="display:none;">Save New Item</button>


  <div class="table-container">
  <table id="inventoryTable">
    <thead>
      <tr>
        <th>Item</th>
        <th>Room</th>
        <th>Description</th>
        <th>Quantity (Beginning)</th>
        <th>Acquisition<br>/Transfer</th>
        <th>Quantity (End)</th>
        <th>Pull-out</th>
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody id="inventoryBody">
      <!-- rows will be injected by JS -->
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

<!-- Reservation Requests Section (Admin Only) -->
<section id="reservationRequests">
  <h2>Reservation Requests</h2>
  <p>Approve or reject equipment reservation requests from teachers.</p>
  
  <div class="search-bar">
    <label>Filter by status:
      <select id="statusFilter" onchange="filterReservations()">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </label>
  </div>
  
  <div id="adminReservationsList">
    <p>Loading reservation requests...</p>
  </div>
  
  <!-- Pagination Controls for Reservations -->
  <div class="pagination-container">
    <div class="pagination-info" id="reservationsPaginationInfo"></div>
    <div class="pagination-controls">
      <button id="reservationsFirstPage" class="pagination-btn">First</button>
      <button id="reservationsPrevPage" class="pagination-btn">Previous</button>
      <div id="reservationsPageNumbers" class="page-numbers"></div>
      <button id="reservationsNextPage" class="pagination-btn">Next</button>
      <button id="reservationsLastPage" class="pagination-btn">Last</button>
    </div>
    <div class="page-size-selector">
      <label>Items per page:
        <select id="reservationsPageSize">
          <option value="10" selected>10</option>
          <option value="15">15</option>
          <option value="20">20</option>
          <option value="50">50</option>
        </select>
      </label>
    </div>
  </div>
</section>

  <!-- Reservation Section -->
<section id="reservations">
  <h2>Reserve Equipment</h2>
  <p>Select items to reserve for lab activities.</p>

  <form id="reservationForm">
    <label>Item:</label>
    <input type="text" id="reserveItem" placeholder="Enter equipment name"><br><br>
    <label>Date Needed:</label>
    <input type="date" id="reserveDate"><br><br>
    <button id="reserveBtn" type="button" disabled>Submit Reservation</button>
  </form>

  <!-- Popup Message -->
  <div id="reservePopup" class="popup"></div>

  <!-- List of Reserved Items -->
  <div id="reservedListContainer">
    <h3>Reserved Items</h3>
    <ul id="reservedList"></ul>
  </div>
</section>


  <!-- History Section -->
<section id="history">
  <h2>Usage History</h2>
  <table id="historyTable">
    <thead>
      <tr><th>Date</th><th>User</th><th>Item</th><th>Action</th></tr>
    </thead>
    <tbody>
      <tr><td>2025-08-18</td><td>Mr. Santos</td><td>Microscope</td><td class="action-cell">Borrowed</td></tr>
      <tr><td>2025-08-19</td><td>Ms. Cruz</td><td>Beaker 250 mL</td><td class="action-cell">Returned</td></tr>
    </tbody>
  </table>
</section>

</div>

<script>
// Navigation
function showSection(sectionId) {
  document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
  document.getElementById(sectionId).classList.add("active");
  
  // Load reservations when the reservationRequests section is shown
  if (sectionId === 'reservationRequests') {
    loadAdminReservations();
    
    // Refresh notification count when viewing requests
    checkForPendingReservations();
  }
}

// Handle hash navigation from calendar
window.addEventListener('DOMContentLoaded', function() {
  const hash = window.location.hash.substring(1); // Remove the #
  if (hash) {
    // Show the section based on hash
    showSection(hash);
  }
});

function logout() {
  showConfirmation(
    'Are you sure you want to logout?',
    () => {
      window.location.href = 'hp1.html';
    }
  );
}

// Notification System
function checkForPendingReservations() {
  fetch('get_pending_count.php')
    .then(response => response.json())
    .then(data => {
      const notificationBadge = document.getElementById('reservationNotification');
      
      if (data.count > 0) {
        notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
        notificationBadge.style.display = 'flex';
        
        // Add visual emphasis to the button
        const requestBtn = document.getElementById('reservationRequestsBtn');
        requestBtn.style.fontWeight = 'bold';
      } else {
        notificationBadge.style.display = 'none';
        
        // Reset button style
        const requestBtn = document.getElementById('reservationRequestsBtn');
        requestBtn.style.background = '';
        requestBtn.style.fontWeight = '';
      }
    })
    .catch(error => {
      console.error('Error checking pending reservations:', error);
    });
}

// Periodically check for new pending reservations
function startNotificationPolling() {
  // Check immediately
  checkForPendingReservations();
  
  // Then check every 30 seconds
  setInterval(checkForPendingReservations, 30000);
}

// Reservation Requests Pagination and Sorting
let allReservations = [];
let filteredReservations = [];
let currentReservationsPage = 1;
let reservationsPageSize = 10;
let currentSortColumn = null;
let currentSortDirection = 'asc'; // 'asc' or 'desc'

// Enhanced reservation loading with sorting
function loadAdminReservations(statusFilter = '') {
  console.log('Loading admin reservations...');
  
  const container = document.getElementById('adminReservationsList');
  container.innerHTML = '<p>Loading reservation requests...</p>';
  
  fetch('get_reservations.php')
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then(reservations => {
      console.log('Reservations loaded:', reservations);
      allReservations = Array.isArray(reservations) ? reservations : [];
      filterReservationsData(statusFilter);
      renderReservationsTable();
      setupReservationsPagination();
    })
    .catch(error => {
      console.error('Error loading reservations:', error);
      document.getElementById('adminReservationsList').innerHTML = 
        '<p style="color: red;">Error loading reservation requests. Check console for details.</p>';
    });
}

function filterReservationsData(statusFilter = '') {
  if (statusFilter) {
    filteredReservations = allReservations.filter(reservation => 
      reservation.status === statusFilter
    );
  } else {
    filteredReservations = [...allReservations];
  }
  
  // Apply current sorting
  sortReservationsData();
  
  // Reset to first page when filtering
  currentReservationsPage = 1;
}

function sortReservationsData() {
  if (!currentSortColumn) return;
  
  filteredReservations.sort((a, b) => {
    let valueA, valueB;
    
    switch(currentSortColumn) {
      case 'teacher':
        valueA = (a.teacher_name || a.teacher_username || '').toLowerCase();
        valueB = (b.teacher_name || b.teacher_username || '').toLowerCase();
        break;
      case 'date_needed':
        valueA = new Date(a.date_needed);
        valueB = new Date(b.date_needed);
        break;
      case 'item':
        valueA = (a.item_name || '').toLowerCase();
        valueB = (b.item_name || '').toLowerCase();
        break;
      case 'status':
        valueA = (a.status || '').toLowerCase();
        valueB = (b.status || '').toLowerCase();
        break;
      case 'submitted':
        valueA = new Date(a.created_at);
        valueB = new Date(b.created_at);
        break;
      default:
        return 0;
    }
    
    if (currentSortDirection === 'asc') {
      return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
    } else {
      return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
    }
  });
}

function renderReservationsTable() {
  const container = document.getElementById('adminReservationsList');
  
  if (!filteredReservations || filteredReservations.length === 0) {
    const statusFilter = document.getElementById('statusFilter').value;
    if (statusFilter) {
      container.innerHTML = `<p>No ${statusFilter} reservation requests found.</p>`;
    } else {
      container.innerHTML = '<p>No reservation requests found.</p>';
    }
    updateReservationsPaginationInfo();
    return;
  }
  
  // Calculate pagination
  const totalPages = Math.ceil(filteredReservations.length / reservationsPageSize);
  const startIndex = (currentReservationsPage - 1) * reservationsPageSize;
  const endIndex = Math.min(startIndex + reservationsPageSize, filteredReservations.length);
  const pageData = filteredReservations.slice(startIndex, endIndex);

  let html = `
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
      <thead>
        <tr style="background: #f8f9fa;">
          <th class="sortable-header" data-column="teacher" style="padding: 12px; border: 1px solid #ddd;">
            Teacher
            <span class="sort-arrow ${currentSortColumn === 'teacher' ? currentSortDirection : ''}"></span>
          </th>
          <th class="sortable-header" data-column="item" style="padding: 12px; border: 1px solid #ddd;">
            Item (A-Z)
            <span class="sort-arrow ${currentSortColumn === 'item' ? currentSortDirection : ''}"></span>
          </th>
          <th class="sortable-header" data-column="date_needed" style="padding: 12px; border: 1px solid #ddd;">
            Date Needed
            <span class="sort-arrow ${currentSortColumn === 'date_needed' ? currentSortDirection : ''}"></span>
          </th>
          <th style="padding: 12px; border: 1px solid #ddd;">Purpose</th>
          <th class="sortable-header" data-column="status" style="padding: 12px; border: 1px solid #ddd;">
            Status
            <span class="sort-arrow ${currentSortColumn === 'status' ? currentSortDirection : ''}"></span>
          </th>
          <th class="sortable-header" data-column="submitted" style="padding: 12px; border: 1px solid #ddd;">
            Submitted
            <span class="sort-arrow ${currentSortColumn === 'submitted' ? currentSortDirection : ''}"></span>
          </th>
          <th style="padding: 12px; border: 1px solid #ddd;">Actions</th>
        </tr>
      </thead>
      <tbody>
  `;
  
  pageData.forEach(reservation => {
    const statusClass = `reservation-status status-${reservation.status}`;
    const dateNeeded = new Date(reservation.date_needed).toLocaleDateString();
    const submitted = new Date(reservation.created_at).toLocaleDateString();
    
    html += `
      <tr>
        <td style="padding: 12px; border: 1px solid #ddd;">${reservation.teacher_name || reservation.teacher_username}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${reservation.item_name}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${dateNeeded}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">${reservation.purpose || 'N/A'}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">
          <span class="${statusClass}">${reservation.status.toUpperCase()}</span>
        </td>
        <td style="padding: 12px; border: 1px solid #ddd;">${submitted}</td>
        <td style="padding: 12px; border: 1px solid #ddd;">
          ${reservation.status === 'pending' ? `
            <div class="admin-actions">
              <button class="approve-btn" onclick="updateReservation(${reservation.id}, 'approved')">Approve</button>
              <button class="reject-btn" onclick="updateReservation(${reservation.id}, 'rejected')">Reject</button>
            </div>
          ` : '<span class="completed-badge">Action completed</span>'}
        </td>
      </tr>
    `;
  });
  
  html += '</tbody></table>';
  container.innerHTML = html;
  
  // Add click event listeners to sortable headers
  addSortableHeaderListeners();
  
  updateReservationsPaginationInfo();
  updateReservationsPaginationButtons(totalPages);
}

function addSortableHeaderListeners() {
  const sortableHeaders = document.querySelectorAll('.sortable-header');
  sortableHeaders.forEach(header => {
    header.addEventListener('click', function() {
      const column = this.dataset.column;
      
      // If clicking the same column, toggle direction
      if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        // New column, default to ascending
        currentSortColumn = column;
        currentSortDirection = 'asc';
      }
      
      sortReservationsData();
      renderReservationsTable();
    });
  });
}

function updateReservationsPaginationInfo() {
  const paginationInfo = document.getElementById('reservationsPaginationInfo');
  
  if (!filteredReservations.length) {
    paginationInfo.textContent = 'No records found';
    return;
  }

  const totalItems = filteredReservations.length;
  const totalPages = Math.ceil(totalItems / reservationsPageSize);
  const startIndex = (currentReservationsPage - 1) * reservationsPageSize + 1;
  const endIndex = Math.min(currentReservationsPage * reservationsPageSize, totalItems);

  paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${totalItems} requests (Page ${currentReservationsPage} of ${totalPages})`;
}

function updateReservationsPaginationButtons(totalPages) {
  const pageNumbers = document.getElementById('reservationsPageNumbers');
  const firstPageBtn = document.getElementById('reservationsFirstPage');
  const prevPageBtn = document.getElementById('reservationsPrevPage');
  const nextPageBtn = document.getElementById('reservationsNextPage');
  const lastPageBtn = document.getElementById('reservationsLastPage');

  // Clear page numbers
  pageNumbers.innerHTML = '';

  // Calculate which page numbers to show
  let startPage = Math.max(1, currentReservationsPage - 2);
  let endPage = Math.min(totalPages, currentReservationsPage + 2);

  // Adjust if we're near the start or end
  if (currentReservationsPage <= 3) {
    endPage = Math.min(5, totalPages);
  }
  if (currentReservationsPage >= totalPages - 2) {
    startPage = Math.max(1, totalPages - 4);
  }

  // Add page number buttons
  for (let i = startPage; i <= endPage; i++) {
    const pageBtn = document.createElement('button');
    pageBtn.className = `page-number ${i === currentReservationsPage ? 'active' : ''}`;
    pageBtn.textContent = i;
    pageBtn.onclick = () => goToReservationsPage(i);
    pageNumbers.appendChild(pageBtn);
  }

  // Update button states
  firstPageBtn.disabled = currentReservationsPage === 1;
  prevPageBtn.disabled = currentReservationsPage === 1;
  nextPageBtn.disabled = currentReservationsPage === totalPages;
  lastPageBtn.disabled = currentReservationsPage === totalPages;
}

function setupReservationsPagination() {
  const firstPageBtn = document.getElementById('reservationsFirstPage');
  const prevPageBtn = document.getElementById('reservationsPrevPage');
  const nextPageBtn = document.getElementById('reservationsNextPage');
  const lastPageBtn = document.getElementById('reservationsLastPage');
  const pageSizeSelect = document.getElementById('reservationsPageSize');

  firstPageBtn.onclick = () => goToReservationsPage(1);
  prevPageBtn.onclick = () => goToReservationsPage(currentReservationsPage - 1);
  nextPageBtn.onclick = () => goToReservationsPage(currentReservationsPage + 1);
  lastPageBtn.onclick = () => goToReservationsPage(Math.ceil(filteredReservations.length / reservationsPageSize));
  
  pageSizeSelect.onchange = (e) => {
    reservationsPageSize = parseInt(e.target.value);
    currentReservationsPage = 1;
    renderReservationsTable();
  };
}

function goToReservationsPage(page) {
  const totalPages = Math.ceil(filteredReservations.length / reservationsPageSize);
  if (page < 1 || page > totalPages) return;
  
  currentReservationsPage = page;
  renderReservationsTable();
  
  // Scroll to top of table
  document.getElementById('adminReservationsList').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Update the existing filterReservations function
function filterReservations() {
  const statusFilter = document.getElementById('statusFilter').value;
  filterReservationsData(statusFilter);
  renderReservationsTable();
}

// Update the existing updateReservation function to refresh the table
function updateReservation(reservationId, status) {
  const action = status === 'approved' ? 'approve' : 'reject';
  
  showConfirmation(
    `Are you sure you want to ${action} this reservation?`,
    () => {
      const formData = new FormData();
      formData.append('reservation_id', reservationId);
      formData.append('status', status);
      
      fetch('update_reservation_status.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const currentFilter = document.getElementById('statusFilter').value;
          loadAdminReservations(currentFilter);
          checkForPendingReservations();
        } else {
          showNotification(data.message || 'Failed to update reservation', 'error');
        }
      })
      .catch(error => {
        showNotification('Error updating reservation: ' + error.message, 'error');
      });
    }
  );
}

// Update the DOMContentLoaded to setup reservations pagination
document.addEventListener('DOMContentLoaded', function() {
  // Load reservations if we're on that section
  if (document.getElementById('reservationRequests').classList.contains('active')) {
    loadAdminReservations();
  }
  
  // Start checking for pending reservations
  startNotificationPolling();
  
  // Setup reservations pagination
  setupReservationsPagination();
});

let isCompactLayout = false;

function toggleLayout() {
  const header = document.querySelector('header');
  const nav = document.querySelector('nav');
  const app = document.getElementById('app');
  const toggleBtn = document.getElementById('layoutToggle');
  
  isCompactLayout = !isCompactLayout;
  
  if (isCompactLayout) {
    // Sidebar layout
    document.body.classList.add('compact-layout');
    toggleBtn.innerHTML = 'Standard View'; // Changed
    toggleBtn.style.background = 'rgba(255, 251, 234, 0.9)';
  } else {
    // Standard layout
    document.body.classList.remove('compact-layout');
    toggleBtn.innerHTML = 'Sidebar View'; // Changed
    toggleBtn.style.background = 'rgba(255, 251, 234, 0.9)';
  }
  
  // Save preference
  localStorage.setItem('layoutPreference', isCompactLayout ? 'compact' : 'standard');
}

// Load saved preference on page load
window.addEventListener('DOMContentLoaded', () => {
  const savedLayout = localStorage.getItem('layoutPreference');
  if (savedLayout === 'compact') {
    toggleLayout();
  }
});


</script>

<!-- link to the new file that handles inventory display -->
<script src="form-inventory.js"></script>

<!-- this is popup confirmtation-->
<div id="confirmationPopup" class="confirmation-popup">
  <div class="confirmation-content">
    <h3>⚠️ Confirm Deletion</h3>
    <p id="confirmationMessage">Are you sure you want to delete this item?</p>
    <div class="confirmation-buttons">
      <button id="confirmYes" class="confirm-yes">Yes, Delete</button>
      <button id="confirmNo" class="confirm-no">Cancel</button>
    </div>
  </div>
</div>



</body>
</html>