// form-inventory.js with pagination + requested-item highlighting
let allData = [];
let currentSortAsc = true;
let currentPage = 1;
let pageSize = 10;
let filteredData = [];

// NEW: toggle for "show only requested items"
let activeRequestedFilter = false;

const roomFilter = document.getElementById('roomFilter');
const searchInput = document.getElementById('searchInput');
const inventoryBody = document.getElementById('inventoryBody');

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
    case 'success': icon = '✓'; break;
    case 'error':   icon = '✕'; break;
    case 'warning': icon = '⚠'; break;
    case 'info':    icon = 'ℹ'; break;
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
  
  const yesBtn    = confirmation.querySelector('.confirm-yes-btn');
  const cancelBtn = confirmation.querySelector('.confirm-cancel-btn');
  const backdrop  = confirmation.querySelector('.modern-confirmation-backdrop');
  
  const closeConfirmation = () => {
    confirmation.classList.remove('show');
    setTimeout(() => confirmation.remove(), 300);
  };
  
  yesBtn.addEventListener('click', () => { closeConfirmation(); onConfirm(); });
  cancelBtn.addEventListener('click', () => { closeConfirmation(); if (onCancel) onCancel(); });
  backdrop.addEventListener('click', () => { closeConfirmation(); if (onCancel) onCancel(); });
}

// Pagination elements
const paginationInfo  = document.getElementById('paginationInfo');
const pageNumbers     = document.getElementById('pageNumbers');
const firstPageBtn    = document.getElementById('firstPage');
const prevPageBtn     = document.getElementById('prevPage');
const nextPageBtn     = document.getElementById('nextPage');
const lastPageBtn     = document.getElementById('lastPage');
const pageSizeSelect  = document.getElementById('pageSize');

function getProp(obj, name) {
  if (!obj) return '';
  if (name in obj) return obj[name];
  const key = Object.keys(obj).find(k => k.toLowerCase() === name.toLowerCase());
  return key ? obj[key] : '';
}

function loadJSON() {
  fetch('./api/get_inventory.php')
    .then(res => {
      if (!res.ok) throw new Error("Failed to fetch data from server.");
      return res.json();
    })
    .then(data => {
      allData = Array.isArray(data) ? data : [];
      populateRoomFilter();
      filterAndSortData();
      renderTable();
      setupPagination();
      // NEW: update the Requested filter button badge after load
      updateRequestedBadge();
    })
    .catch(err => console.error("Error:", err));
}

// ====== NEW: Update the "🔔 Requested" button badge count ======
function updateRequestedBadge() {
  const badge = document.getElementById('requestedFilterBadge');
  if (!badge) return;

  const count = allData.filter(row => parseInt(getProp(row, 'is_requested') || 0) === 1).length;

  if (count > 0) {
    badge.textContent = count > 99 ? '99+' : count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

// ====== NEW: Toggle the Requested filter ======
function filterByRequested(btn) {
  activeRequestedFilter = !activeRequestedFilter;

  // Deactivate room buttons when requested filter is on
  if (activeRequestedFilter) {
    document.querySelectorAll('.room-filter-btn').forEach(b => b.classList.remove('active-room-filter'));
    btn.classList.add('active-room-filter');
    if (typeof activeRoomFilter !== 'undefined') activeRoomFilter = '';
    const dropdown = document.getElementById('roomFilter');
    if (dropdown) dropdown.value = '';
  } else {
    btn.classList.remove('active-room-filter');
    // Re-activate "All Rooms"
    const allBtn = document.querySelector('.room-filter-btn[data-room=""]');
    if (allBtn) allBtn.classList.add('active-room-filter');
  }

  filterAndSortData();
  renderTable();
}

function populateRoomFilter() {
  const rooms = [...new Set(allData.map(row => (getProp(row, 'Room') || '').toString().trim()).filter(Boolean))];
  roomFilter.innerHTML = '<option value="">All</option>';
  rooms.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r;
    opt.textContent = r;
    roomFilter.appendChild(opt);
  });
}

function filterAndSortData() {
  const roomVal = roomFilter.value;
  const q = (searchInput.value || '').toLowerCase().trim();

  filteredData = allData.filter(row => {
    const r = (getProp(row, 'Room') || '').toString();

    // NEW: requested filter takes priority over everything
    if (activeRequestedFilter) {
      if (parseInt(getProp(row, 'is_requested') || 0) !== 1) return false;
    } else if (typeof activeRoomFilter !== 'undefined' && activeRoomFilter) {
      if (r !== activeRoomFilter) return false;
    } else if (roomVal) {
      if (r !== roomVal) return false;
    }

    if (q) {
      const text = [
        getProp(row, 'Item'),
        getProp(row, 'Description'),
        getProp(row, 'Remarks'),
        getProp(row, 'Category'),
        getProp(row, 'Room')
      ].join(' ').toLowerCase();
      if (!text.includes(q)) return false;
    }
    return true;
  });

  filteredData.sort((a, b) => {
    const A = (getProp(a, 'Item') || '').toString().toLowerCase();
    const B = (getProp(b, 'Item') || '').toString().toLowerCase();
    return currentSortAsc ? A.localeCompare(B) : B.localeCompare(A);
  });

  currentPage = 1;
}

function formatCell(v) {
  if (v === null || v === undefined) return '';
  return String(v);
}

// ====== IMAGE UPLOAD SYSTEM ======
let currentImageItemId = null;
let currentImageData   = null;

// ====== UPDATED renderTable — adds requested-row class + request count badge ======
function renderTable() {
  if (!filteredData.length) {
    inventoryBody.innerHTML = '<tr><td colspan="8">No records found.</td></tr>';
    updatePaginationInfo();
    return;
  }

  const totalPages = Math.ceil(filteredData.length / pageSize);
  const startIndex = (currentPage - 1) * pageSize;
  const endIndex   = Math.min(startIndex + pageSize, filteredData.length);
  const pageData   = filteredData.slice(startIndex, endIndex);

  const rowsHtml = pageData.map(row => {
    const item        = formatCell(getProp(row, 'Item'));
    const room        = formatCell(getProp(row, 'Room'));
    const desc        = formatCell(getProp(row, 'Description'));
    const beg         = formatCell(getProp(row, 'Beginning')    || getProp(row, 'Quantity (Beg)') || '');
    const acq         = formatCell(getProp(row, 'Acquisition')  || getProp(row, 'Acquisition/Transfer') || '');
    const ending      = formatCell(getProp(row, 'Ending'));
    const pull        = formatCell(getProp(row, 'PullOut')      || getProp(row, 'Pull-out') || '');
    const remarks     = formatCell(getProp(row, 'Remarks'));
    const rowId       = row.id || row.ID || row.item_id || getProp(row, 'id') || getProp(row, 'ID');
    const imagePath   = getProp(row, 'image_path') || '';

    // NEW: requested fields
    const isRequested  = parseInt(getProp(row, 'is_requested')  || 0) === 1;
    const requestCount = parseInt(getProp(row, 'request_count') || 0);

    const rowClass = isRequested ? 'requested-row' : '';

    // Badge shown inside item cell when requested
    const requestBadgeHtml = isRequested
      ? `<span class="request-count-badge" title="${requestCount} pending request${requestCount !== 1 ? 's' : ''}">${requestCount}</span>`
      : '';

    return `
    <tr data-id="${rowId}" data-image="${escapeHtml(imagePath)}" class="${rowClass}">
      <td>
        <div class="item-cell-container">
          <span class="item-name-text">${escapeHtml(item)}</span>
          ${requestBadgeHtml}
          <img src="uploads/eye.png" class="eye-icon"
               onclick="openImageModal(${rowId}, '${escapeHtml(item)}', '${escapeHtml(room)}', '${escapeHtml(imagePath)}', event)"
               onmouseenter="showHoverPreview(this, '${escapeHtml(imagePath)}', '${escapeHtml(item)}', '${escapeHtml(room)}')"
               onmouseleave="hideHoverPreview()"
               title="View/Upload Image" alt="View Image">
        </div>
      </td>
      <td>${escapeHtml(room)}</td>
      <td>${escapeHtml(desc)}</td>
      <td>${escapeHtml(beg)}</td>
      <td>${escapeHtml(acq)}</td>
      <td>${escapeHtml(ending)}</td>
      <td>${escapeHtml(pull)}</td>
      <td>${escapeHtml(remarks)}</td>
    </tr>`;
  }).join('\n');

  inventoryBody.innerHTML = rowsHtml;
  updatePaginationInfo();
  updatePaginationButtons(totalPages);
}

// ---- All existing image modal / hover preview functions unchanged below ----

function openImageModal(itemId, itemName, room, imagePath, event) {
  event.stopPropagation();
  currentImageItemId = itemId;
  
  const modal              = document.getElementById('imageModal');
  const modalTitle         = document.getElementById('imageModalTitle');
  const itemNameEl         = document.getElementById('modalItemName');
  const itemRoomEl         = document.getElementById('modalItemRoom');
  const previewContainer   = document.getElementById('imagePreviewContainer');
  const previewImage       = document.getElementById('previewImage');
  const noImagePlaceholder = document.getElementById('noImagePlaceholder');
  const deleteBtn          = document.getElementById('deleteImageBtn');
  
  modalTitle.textContent = 'Item Image';
  itemNameEl.textContent = itemName;
  itemRoomEl.textContent = room;
  
  if (imagePath) {
    previewImage.src              = imagePath;
    previewImage.style.display    = 'block';
    noImagePlaceholder.style.display = 'none';
    previewContainer.classList.add('has-image');
    deleteBtn.style.display       = 'block';
  } else {
    previewImage.style.display    = 'none';
    noImagePlaceholder.style.display = 'flex';
    previewContainer.classList.remove('has-image');
    deleteBtn.style.display       = 'none';
  }
  
  modal.classList.add('show');
}

function closeImageModal() {
  const modal = document.getElementById('imageModal');
  modal.classList.remove('show');
  currentImageItemId = null;
  const fileInput       = document.getElementById('imageFileInput');
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  fileInput.value       = '';
  fileNameDisplay.textContent = 'No file selected';
}

function handleFileSelect(event) {
  const file            = event.target.files[0];
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  const uploadBtn       = document.getElementById('uploadImageBtn');
  
  if (file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize      = 2 * 1024 * 1024;
    
    if (!allowedTypes.includes(file.type)) {
      showNotification('Invalid file type. Only JPG, PNG, and WebP are allowed.', 'error');
      event.target.value        = '';
      fileNameDisplay.textContent = 'No file selected';
      return;
    }
    if (file.size > maxSize) {
      showNotification('File is too large. Maximum size is 2MB.', 'error');
      event.target.value        = '';
      fileNameDisplay.textContent = 'No file selected';
      return;
    }
    fileNameDisplay.textContent = file.name;
    uploadBtn.disabled          = false;
  } else {
    fileNameDisplay.textContent = 'No file selected';
    uploadBtn.disabled          = true;
  }
}

function uploadImage() {
  const fileInput = document.getElementById('imageFileInput');
  const file      = fileInput.files[0];
  const uploadBtn = document.getElementById('uploadImageBtn');
  
  if (!file || !currentImageItemId) {
    showNotification('Please select a file', 'warning');
    return;
  }
  
  const formData = new FormData();
  formData.append('image',   file);
  formData.append('item_id', currentImageItemId);
  
  const originalText    = uploadBtn.textContent;
  uploadBtn.innerHTML   = '<span class="loading-spinner"></span> Uploading...';
  uploadBtn.disabled    = true;
  
  fetch('upload_image.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification(data.message, 'success');
        const previewImage       = document.getElementById('previewImage');
        const noImagePlaceholder = document.getElementById('noImagePlaceholder');
        const previewContainer   = document.getElementById('imagePreviewContainer');
        const deleteBtn          = document.getElementById('deleteImageBtn');
        previewImage.src              = data.image_path + '?t=' + new Date().getTime();
        previewImage.style.display    = 'block';
        noImagePlaceholder.style.display = 'none';
        previewContainer.classList.add('has-image');
        deleteBtn.style.display       = 'block';
        const row = document.querySelector(`tr[data-id="${currentImageItemId}"]`);
        if (row) row.dataset.image = data.image_path;
        fileInput.value = '';
        document.getElementById('fileNameDisplay').textContent = 'No file selected';
        loadJSON();
      } else {
        showNotification(data.message || 'Upload failed', 'error');
      }
      uploadBtn.textContent = originalText;
      uploadBtn.disabled    = false;
    })
    .catch(error => {
      showNotification('Upload failed: ' + error.message, 'error');
      uploadBtn.textContent = originalText;
      uploadBtn.disabled    = false;
    });
}

function deleteImage() {
  if (!currentImageItemId) return;
  showConfirmation('Are you sure you want to delete this image?', () => {
    const deleteBtn      = document.getElementById('deleteImageBtn');
    const originalText   = deleteBtn.textContent;
    deleteBtn.innerHTML  = '<span class="loading-spinner"></span> Deleting...';
    deleteBtn.disabled   = true;
    
    fetch('delete_image.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_id: currentImageItemId })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const previewImage       = document.getElementById('previewImage');
          const noImagePlaceholder = document.getElementById('noImagePlaceholder');
          const previewContainer   = document.getElementById('imagePreviewContainer');
          previewImage.style.display       = 'none';
          noImagePlaceholder.style.display = 'flex';
          previewContainer.classList.remove('has-image');
          deleteBtn.style.display          = 'none';
          const row = document.querySelector(`tr[data-id="${currentImageItemId}"]`);
          if (row) row.dataset.image = '';
          loadJSON();
        } else {
          showNotification(data.message || 'Delete failed', 'error');
        }
        deleteBtn.textContent = originalText;
        deleteBtn.disabled    = false;
      })
      .catch(error => {
        showNotification('Delete failed: ' + error.message, 'error');
        deleteBtn.textContent = originalText;
        deleteBtn.disabled    = false;
      });
  });
}

// ====== HOVER PREVIEW ======
let hoverPreviewElement = null;
let hoverPreviewTimeout = null;

function showHoverPreview(eyeIcon, imagePath, itemName, room) {
  if (hoverPreviewTimeout) clearTimeout(hoverPreviewTimeout);
  hideHoverPreview();
  
  hoverPreviewElement = document.createElement('div');
  hoverPreviewElement.className = 'hover-preview';
  
  const imageHtml = imagePath
    ? `<img src="${imagePath}" class="hover-preview-image" alt="${itemName}">`
    : `<div class="hover-preview-image" style="display:flex;align-items:center;justify-content:center;color:#999;"><span style="font-size:48px;">📷</span></div>`;
  
  hoverPreviewElement.innerHTML = `
    ${imageHtml}
    <div class="hover-preview-details">
      <div><span class="label">Item:</span> ${itemName}</div>
      <div><span class="label">Room:</span> ${room}</div>
    </div>
  `;
  
  hoverPreviewElement.addEventListener('mouseenter', () => {
    if (hoverPreviewTimeout) { clearTimeout(hoverPreviewTimeout); hoverPreviewTimeout = null; }
  });
  hoverPreviewElement.addEventListener('mouseleave', hideHoverPreview);
  
  document.body.appendChild(hoverPreviewElement);
  
  const iconRect   = eyeIcon.getBoundingClientRect();
  hoverPreviewElement.style.position = 'fixed';
  const previewTop = iconRect.top - hoverPreviewElement.offsetHeight - 10;
  hoverPreviewElement.style.top  = (previewTop < 10 ? iconRect.bottom + 10 : previewTop) + 'px';
  hoverPreviewElement.style.left = (iconRect.left - (hoverPreviewElement.offsetWidth / 2) + (iconRect.width / 2)) + 'px';
  
  setTimeout(() => { if (hoverPreviewElement) hoverPreviewElement.classList.add('show'); }, 50);
}

function hideHoverPreview() {
  if (hoverPreviewElement) { hoverPreviewElement.remove(); hoverPreviewElement = null; }
  if (hoverPreviewTimeout) { clearTimeout(hoverPreviewTimeout); hoverPreviewTimeout = null; }
}

// ====== IMAGE MODAL INIT ======
document.addEventListener('DOMContentLoaded', function() {
  if (!document.getElementById('imageModal')) {
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
              <div id="noImagePlaceholder" class="no-image-placeholder" style="display:flex;flex-direction:column;">
                <div class="icon">📷</div>
                <p><strong>No image uploaded</strong></p>
                <p>Upload an image below</p>
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
            <div class="image-upload-section">
              <div class="upload-form">
                <div class="file-input-wrapper">
                  <input type="file" id="imageFileInput" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="handleFileSelect(event)">
                  <label for="imageFileInput" class="file-input-label">
                    <span>📁</span><span>Choose Image File</span>
                  </label>
                </div>
                <div id="fileNameDisplay" class="file-name-display">No file selected</div>
                <div class="upload-buttons">
                  <button id="uploadImageBtn" class="upload-btn" onclick="uploadImage()" disabled>Upload Image</button>
                  <button id="deleteImageBtn" class="delete-image-btn" onclick="deleteImage()" style="display:none;">Delete Image</button>
                </div>
                <div class="upload-info">Supported formats: JPG, PNG, WebP • Max size: 2MB</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target.id === 'imageModal') closeImageModal();
    });
  }
});

// ====== PAGINATION ======
function updatePaginationInfo() {
  if (!filteredData.length) { paginationInfo.textContent = 'No records found'; return; }
  const totalItems = filteredData.length;
  const totalPages = Math.ceil(totalItems / pageSize);
  const startIndex = (currentPage - 1) * pageSize + 1;
  const endIndex   = Math.min(currentPage * pageSize, totalItems);
  paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${totalItems} items (Page ${currentPage} of ${totalPages})`;
}

function updatePaginationButtons(totalPages) {
  pageNumbers.innerHTML = '';
  let startPage = Math.max(1, currentPage - 2);
  let endPage   = Math.min(totalPages, currentPage + 2);
  if (currentPage <= 3) endPage   = Math.min(5, totalPages);
  if (currentPage >= totalPages - 2) startPage = Math.max(1, totalPages - 4);
  for (let i = startPage; i <= endPage; i++) {
    const pageBtn = document.createElement('button');
    pageBtn.className = `page-number ${i === currentPage ? 'active' : ''}`;
    pageBtn.textContent = i;
    pageBtn.onclick = () => goToPage(i);
    pageNumbers.appendChild(pageBtn);
  }
  firstPageBtn.disabled = currentPage === 1;
  prevPageBtn.disabled  = currentPage === 1;
  nextPageBtn.disabled  = currentPage === totalPages;
  lastPageBtn.disabled  = currentPage === totalPages;
}

function setupPagination() {
  firstPageBtn.onclick = () => goToPage(1);
  prevPageBtn.onclick  = () => goToPage(currentPage - 1);
  nextPageBtn.onclick  = () => goToPage(currentPage + 1);
  lastPageBtn.onclick  = () => goToPage(Math.ceil(filteredData.length / pageSize));
  pageSizeSelect.onchange = (e) => {
    pageSize    = parseInt(e.target.value);
    currentPage = 1;
    filterAndSortData();
    renderTable();
  };
}

function goToPage(page) {
  const totalPages = Math.ceil(filteredData.length / pageSize);
  if (page < 1 || page > totalPages) return;
  currentPage = page;
  renderTable();
  inventoryBody.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function escapeHtml(s) {
  if (s === null || s === undefined) return '';
  return String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

function sortTable() {
  currentSortAsc = !currentSortAsc;
  filterAndSortData();
  renderTable();
}

function searchTable() {
  filterAndSortData();
  renderTable();
}

roomFilter.addEventListener('change', () => {
  // Deactivate requested filter when using room dropdown
  activeRequestedFilter = false;
  const reqBtn = document.getElementById('requestedFilterBtn');
  if (reqBtn) reqBtn.classList.remove('active-room-filter');
  filterAndSortData();
  renderTable();
});

window.addEventListener('DOMContentLoaded', () => {
  loadJSON();
  setupPagination();
});

// ====== HISTORY ACTION DROPDOWN ======
document.addEventListener('DOMContentLoaded', () => {
  const historyTable = document.getElementById('historyTable');
  if (!historyTable) return;

  const menu = document.createElement('div');
  menu.className = 'action-menu';
  menu.innerHTML = `<div>Borrowed</div><div>Returned</div><div>Broken</div><div>Missing</div>`;
  document.body.appendChild(menu);

  let currentCell = null;

  historyTable.addEventListener('click', (e) => {
    const cell = e.target.closest('.action-cell');
    if (!cell) return;
    currentCell = cell;
    const rect = cell.getBoundingClientRect();
    menu.style.top  = `${rect.bottom + window.scrollY}px`;
    menu.style.left = `${rect.left + window.scrollX}px`;
    menu.classList.add('show');
  });

  menu.addEventListener('click', (e) => {
    if (!currentCell) return;
    if (e.target.tagName === 'DIV') {
      currentCell.textContent = e.target.textContent;
      menu.classList.remove('show');
      currentCell = null;
    }
  });

  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !e.target.classList.contains('action-cell')) {
      menu.classList.remove('show');
    }
  });
});

// ====== ROW SELECTION + DELETE + INLINE EDITING ======
let selectedRow   = null;
let selectedRowId = null;

function initializeRowSelection() {
  const inventoryTable = document.getElementById('inventoryTable');
  if (!inventoryTable) return;

  inventoryTable.addEventListener('click', function(e) {
    const row  = e.target.closest('tr');
    if (!row || !row.dataset.id) return;
    const cell = e.target.closest('td');
    if (!cell) return;
    if (e.target.closest('.delete-btn') || e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
    if (selectedRow === row) { deselectRow(); return; }
    deselectRow();
    selectRow(row);
  });

  inventoryTable.addEventListener('dblclick', function(e) {
    const cell = e.target.closest('td');
    const row  = e.target.closest('tr');
    if (!cell || !row || !row.dataset.id) return;
    if (e.target.closest('.delete-btn') || e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
    startInlineEdit(cell, row.dataset.id);
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#inventoryTable tbody tr') && !e.target.closest('.delete-btn')) deselectRow();
  });
}

function selectRow(row) {
  selectedRow   = row;
  selectedRowId = row.dataset.id;
  row.classList.add('selected-row');
  const lastCell  = row.cells[row.cells.length - 1];
  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'delete-btn';
  deleteBtn.innerHTML = 'x';
  deleteBtn.title     = 'Delete this item';
  deleteBtn.onclick   = function(e) { e.stopPropagation(); deleteSelectedItem(); };
  lastCell.appendChild(deleteBtn);
}

function deselectRow() {
  if (selectedRow) {
    selectedRow.classList.remove('selected-row');
    const lastCell  = selectedRow.cells[selectedRow.cells.length - 1];
    const deleteBtn = lastCell.querySelector('.delete-btn');
    if (deleteBtn) deleteBtn.remove();
    selectedRow   = null;
    selectedRowId = null;
  }
}

function deleteSelectedItem() {
  if (!selectedRowId) { showNotification('No item selected', 'warning'); return; }
  showConfirmation('Are you sure you want to delete this item? This action cannot be undone.', () => {
    fetch('api/delete_item.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: selectedRowId })
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          if (selectedRow && selectedRow.parentNode) selectedRow.remove();
          selectedRow = null; selectedRowId = null;
          showNotification('Item deleted successfully!', 'success');
          loadJSON();
        } else {
          showNotification('Failed to delete item: ' + (result.error || 'Unknown error'), 'error');
        }
      })
      .catch(error => showNotification('Connection error: ' + error.message, 'error'));
  });
}

function startInlineEdit(cell, rowId) {
  if (cell.classList.contains('editing')) return;
  const row      = cell.parentElement;
  const colIndex = cell.cellIndex;
  const header   = document.querySelectorAll('#inventoryTable th')[colIndex];
  const colName  = header.textContent.trim();
  if (!rowId) return;

  cell.classList.add('editing', 'editing-cell');
  const oldVal = cell.textContent.trim();
  let inputEl;

  if (colName === 'Room') {
    inputEl = document.createElement('select');
    inputEl.className = 'inline-input';
    ['Chemical Room', 'Laboratory 1', 'Laboratory 2', 'Storage Room'].forEach(opt => {
      const o = document.createElement('option');
      o.value = opt; o.textContent = opt;
      if (opt === oldVal) o.selected = true;
      inputEl.appendChild(o);
    });
  } else if (colName === 'Description') {
    inputEl = document.createElement('textarea');
    inputEl.value = oldVal;
    inputEl.className = 'inline-input';
    inputEl.style.minHeight = '60px';
    inputEl.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });
    setTimeout(() => { inputEl.style.height = 'auto'; inputEl.style.height = (inputEl.scrollHeight) + 'px'; }, 0);
  } else if (colName.includes('Quantity') || colName.includes('Acquisition') || colName.includes('Ending') || colName.includes('Pull-out')) {
    inputEl = document.createElement('input');
    inputEl.type = 'number'; inputEl.value = oldVal;
    inputEl.className = 'inline-input'; inputEl.min = 0;
  } else {
    inputEl = document.createElement('input');
    inputEl.type = 'text'; inputEl.value = oldVal;
    inputEl.className = 'inline-input';
  }

  cell.innerHTML = '';
  cell.appendChild(inputEl);
  inputEl.focus();

  function finishSave() {
    const newVal   = inputEl.value.trim();
    const colIndex = cell.cellIndex;
    if (colIndex === 0) {
      const row        = cell.parentElement;
      const imagePath  = row.dataset.image || '';
      const rowId      = row.dataset.id;
      const roomCell   = row.cells[1];
      const room       = roomCell.textContent.trim();
      cell.innerHTML = `
        <div class="item-cell-container">
          <span class="item-name-text">${escapeHtml(newVal)}</span>
          <img src="uploads/eye.png" class="eye-icon"
               onclick="openImageModal(${rowId}, '${escapeHtml(newVal)}', '${escapeHtml(room)}', '${escapeHtml(imagePath)}', event)"
               onmouseenter="showHoverPreview(this, '${escapeHtml(imagePath)}', '${escapeHtml(newVal)}', '${escapeHtml(room)}')"
               onmouseleave="hideHoverPreview()"
               title="View/Upload Image" alt="View Image">
        </div>
      `;
    } else {
      cell.textContent = newVal;
    }
    cell.classList.remove('editing', 'editing-cell');

    fetch('api/update_item.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: rowId, column: colName, value: newVal })
    })
      .then(r => { if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`); return r.json(); })
      .then(res => {
        if (!res.success) {
          showNotification("Save failed: " + res.error, 'error');
          cell.textContent = oldVal;
        } else {
          showNotification("Changes saved successfully", 'success', 2000);
        }
      })
      .catch(err => {
        showNotification("Connection error: " + err.message, 'error');
        cell.textContent = oldVal;
      });
  }

  inputEl.addEventListener('blur', finishSave);
  inputEl.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter')  { ev.preventDefault(); inputEl.blur(); }
    if (ev.key === 'Escape') { cell.textContent = oldVal; cell.classList.remove('editing', 'editing-cell'); }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  setTimeout(() => initializeRowSelection(), 1000);
});

// ====== ADD NEW ITEM FEATURE ======
document.addEventListener('DOMContentLoaded', () => {
  const addItemBtn    = document.getElementById('addItemBtn');
  const saveNewItemBtn = document.getElementById('saveNewItemBtn');
  const inventoryBody = document.getElementById('inventoryBody');
  if (!addItemBtn || !inventoryBody) return;
  let newItemRow = null;

  addItemBtn.addEventListener('click', () => {
    if (newItemRow) newItemRow.remove();
    newItemRow = document.createElement('tr');
    newItemRow.innerHTML = `
      <td><input type="text" class="new-item-input" placeholder="Item Name" required style="width:95%;"></td>
      <td>
        <select class="new-item-input" style="width:95%;">
          <option value="Chemical Room">Chemical Room</option>
          <option value="Laboratory 1">Laboratory 1</option>
          <option value="Laboratory 2">Laboratory 2</option>
          <option value="Storage Room">Storage Room</option>
        </select>
      </td>
      <td><textarea class="new-item-input" placeholder="Enter Description" style="width:95%;height:45px;"></textarea></td>
      <td style="text-align:center;"><input type="number" class="new-item-input" value="0" min="0" style="width:80%;text-align:center;"></td>
      <td style="text-align:center;"><input type="number" class="new-item-input" value="0" min="0" style="width:80%;text-align:center;"></td>
      <td style="text-align:center;"><input type="number" class="new-item-input" value="0" min="0" style="width:80%;text-align:center;"></td>
      <td style="text-align:center;"><input type="number" class="new-item-input" value="0" min="0" style="width:80%;text-align:center;"></td>
      <td><textarea class="new-item-input" placeholder="Remarks" style="width:100%;height:45px"></textarea></td>
    `;
    newItemRow.style.backgroundColor = "rgba(255, 214, 0, 0.15)";
    inventoryBody.prepend(newItemRow);
    saveNewItemBtn.style.display = 'inline-block';
  });

  saveNewItemBtn.addEventListener('click', () => {
    if (!newItemRow) return;
    const inputs   = newItemRow.querySelectorAll('.new-item-input');
    const itemData = {
      item: inputs[0].value.trim(), room: inputs[1].value,
      description: inputs[2].value.trim(), beginning: inputs[3].value || '0',
      acquisition: inputs[4].value || '0', ending: inputs[5].value || '0',
      pullout: inputs[6].value || '0', remarks: inputs[7].value.trim(),
      category: 'EQUIPMENT/APPARATUSES'
    };
    if (!itemData.item) { showNotification('Item name is required!', 'warning'); inputs[0].focus(); return; }
    fetch('api/add_item.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(itemData)
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          loadJSON(); newItemRow.remove(); newItemRow = null;
          saveNewItemBtn.style.display = 'none';
          showNotification('Item added successfully!', 'success');
        } else {
          showNotification('Failed to add item: ' + (result.error || 'Unknown error'), 'error');
        }
      })
      .catch(error => showNotification('Connection error: ' + error.message, 'error'));
  });
});

// ====== RESERVATION FEATURE ======
document.addEventListener('DOMContentLoaded', () => {
  const reserveItem = document.getElementById('reserveItem');
  const reserveDate = document.getElementById('reserveDate');
  const reserveBtn  = document.getElementById('reserveBtn');
  const reservedList = document.getElementById('reservedList');
  const popup        = document.getElementById('reservePopup');
  if (!reserveItem || !reserveBtn) return;

  reserveItem.addEventListener('input', () => {
    reserveBtn.disabled = reserveItem.value.trim() === '';
  });

  reserveBtn.addEventListener('click', () => {
    const itemName   = reserveItem.value.trim();
    const dateNeeded = reserveDate.value || "No date specified";
    if (itemName === '') return;
    const li = document.createElement('li');
    li.textContent = `${itemName} — ${dateNeeded}`;
    reservedList.appendChild(li);
    popup.textContent = `${itemName} has been reserved`;
    popup.classList.remove('hide');
    popup.style.display = 'block';
    setTimeout(() => popup.classList.add('show'), 10);
    setTimeout(() => {
      popup.classList.remove('show'); popup.classList.add('hide');
      setTimeout(() => (popup.style.display = 'none'), 400);
    }, 2000);
    reserveItem.value = ''; reserveDate.value = ''; reserveBtn.disabled = true;
  });
});
