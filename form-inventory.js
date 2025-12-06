// form-inventory.js with pagination
let allData = [];
let currentSortAsc = true;
let currentPage = 1;
let pageSize = 10;
let filteredData = [];

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




// Pagination elements
const paginationInfo = document.getElementById('paginationInfo');
const pageNumbers = document.getElementById('pageNumbers');
const firstPageBtn = document.getElementById('firstPage');
const prevPageBtn = document.getElementById('prevPage');
const nextPageBtn = document.getElementById('nextPage');
const lastPageBtn = document.getElementById('lastPage');
const pageSizeSelect = document.getElementById('pageSize');

function getProp(obj, name) {
  if (!obj) return '';
  if (name in obj) return obj[name];
  const key = Object.keys(obj).find(k => k.toLowerCase() === name.toLowerCase());
  return key ? obj[key] : '';
}

function loadJSON() {
  fetch('./api/get_inventory.php')
    .then(res => {
      if (!res.ok) {
        throw new Error("Failed to fetch data from server.");
      }
      return res.json();
    })
    .then(data => {
      allData = Array.isArray(data) ? data : [];
      populateRoomFilter();
      filterAndSortData();
      renderTable();
      setupPagination();
    })
    .catch(err => {
      console.error("Error:", err);
    });
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
    // Room match (if selected)
    if (roomVal) {
      const r = getProp(row, 'Room') || '';
      if (r.toString() !== roomVal) return false;
    }
    // search match
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

  // Sort by Item column
  filteredData.sort((a, b) => {
    const A = (getProp(a, 'Item') || '').toString().toLowerCase();
    const B = (getProp(b, 'Item') || '').toString().toLowerCase();
    return currentSortAsc ? A.localeCompare(B) : B.localeCompare(A);
  });

  // Reset to first page when filtering
  currentPage = 1;
}

function formatCell(v) {
  if (v === null || v === undefined) return '';
  return String(v);
}

function renderTable() {
  if (!filteredData.length) {
    inventoryBody.innerHTML = '<tr><td colspan="8">No records found.</td></tr>';
    updatePaginationInfo();
    return;
  }

  // Calculate pagination
  const totalPages = Math.ceil(filteredData.length / pageSize);
  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, filteredData.length);
  const pageData = filteredData.slice(startIndex, endIndex);

  const rowsHtml = pageData.map(row => {
    const item = formatCell(getProp(row, 'Item'));
    const room = formatCell(getProp(row, 'Room'));
    const desc = formatCell(getProp(row, 'Description'));
    const beg = formatCell(getProp(row, 'Beginning') || getProp(row, 'Quantity (Beg)') || '');
    const acq = formatCell(getProp(row, 'Acquisition') || getProp(row, 'Acquisition/Transfer') || '');
    const ending = formatCell(getProp(row, 'Ending'));
    const pull = formatCell(getProp(row, 'PullOut') || getProp(row, 'Pull-out') || '');
    const remarks = formatCell(getProp(row, 'Remarks'));
    return `
    <tr data-id="${row.id}">
      <td>${escapeHtml(item)}</td>
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

function updatePaginationInfo() {
  if (!filteredData.length) {
    paginationInfo.textContent = 'No records found';
    return;
  }

  const totalItems = filteredData.length;
  const totalPages = Math.ceil(totalItems / pageSize);
  const startIndex = (currentPage - 1) * pageSize + 1;
  const endIndex = Math.min(currentPage * pageSize, totalItems);

  paginationInfo.textContent = `Showing ${startIndex}-${endIndex} of ${totalItems} items (Page ${currentPage} of ${totalPages})`;
}

function updatePaginationButtons(totalPages) {
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
  firstPageBtn.onclick = () => goToPage(1);
  prevPageBtn.onclick = () => goToPage(currentPage - 1);
  nextPageBtn.onclick = () => goToPage(currentPage + 1);
  lastPageBtn.onclick = () => goToPage(Math.ceil(filteredData.length / pageSize));
  
  pageSizeSelect.onchange = (e) => {
    pageSize = parseInt(e.target.value);
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
  
  // Scroll to top of table
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

// Wired to your button onclick
function sortTable() {
  currentSortAsc = !currentSortAsc;
  filterAndSortData();
  renderTable();
}

// searchTable is used by onkeyup
function searchTable() {
  filterAndSortData();
  renderTable();
}

// Wire up filter change
roomFilter.addEventListener('change', () => {
  filterAndSortData();
  renderTable();
});

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
  loadJSON();
  setupPagination();
});

// ... (keep your existing functions for row selection, inline editing, etc. below)
// Make sure to update any functions that modify the table to call renderTable() instead of directly manipulating the DOM

// ===== HISTORY ACTION DROPDOWN =====
document.addEventListener('DOMContentLoaded', () => {
  const historyTable = document.getElementById('historyTable');
  if (!historyTable) return;

  // Create reusable dropdown menu
  const menu = document.createElement('div');
  menu.className = 'action-menu';
  menu.innerHTML = `
    <div>Borrowed</div>
    <div>Returned</div>
    <div>Broken</div>
    <div>Missing</div>
  `;
  document.body.appendChild(menu);

  let currentCell = null;

  // Handle clicks on action cells
  historyTable.addEventListener('click', (e) => {
    const cell = e.target.closest('.action-cell');
    if (!cell) return;

    currentCell = cell;
    const rect = cell.getBoundingClientRect();

    // Position dropdown below clicked cell
    menu.style.top = `${rect.bottom + window.scrollY}px`;
    menu.style.left = `${rect.left + window.scrollX}px`;
    menu.classList.add('show');
  });

  // Handle option selection
  menu.addEventListener('click', (e) => {
    if (!currentCell) return;
    if (e.target.tagName === 'DIV') {
      currentCell.textContent = e.target.textContent;
      menu.classList.remove('show');
      currentCell = null;
    }
  });

  // Hide menu if clicking outside
  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !e.target.classList.contains('action-cell')) {
      menu.classList.remove('show');
    }
  });
});

// ====== ROW SELECTION + DELETE + INLINE EDITING SYSTEM ======
let selectedRow = null;
let selectedRowId = null;

function initializeRowSelection() {
  const inventoryTable = document.getElementById('inventoryTable');
  if (!inventoryTable) return;

  // Add click event to table rows
  inventoryTable.addEventListener('click', function(e) {
    const row = e.target.closest('tr');
    if (!row || !row.dataset.id) return;

    const cell = e.target.closest('td');
    if (!cell) return;

    // Don't select if clicking on delete button or input field
    if (e.target.closest('.delete-btn') || e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
      return;
    }

    // If same row clicked again, deselect it
    if (selectedRow === row) {
      deselectRow();
      return;
    }

    // Deselect previous row
    deselectRow();

    // Select new row
    selectRow(row);
  });

  // Add double-click for inline editing
  inventoryTable.addEventListener('dblclick', function(e) {
    const cell = e.target.closest('td');
    const row = e.target.closest('tr');
    
    if (!cell || !row || !row.dataset.id) return;
    if (e.target.closest('.delete-btn') || e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
      return;
    }

    startInlineEdit(cell, row.dataset.id);
  });

  // Click outside to deselect
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#inventoryTable tbody tr') && !e.target.closest('.delete-btn')) {
      deselectRow();
    }
  });
}

function selectRow(row) {
  selectedRow = row;
  selectedRowId = row.dataset.id;
  
  // Add selected class
  row.classList.add('selected-row');
  
  // Get the last cell (Remarks column)
  const lastCell = row.cells[row.cells.length - 1];
  
  // Create delete button
  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'delete-btn';
  deleteBtn.innerHTML = 'x';
  deleteBtn.title = 'Delete this item';
  deleteBtn.onclick = function(e) {
    e.stopPropagation();
    deleteSelectedItem();
  };
  
  // Append to last cell instead of row
  lastCell.appendChild(deleteBtn);
}

function deselectRow() {
  if (selectedRow) {
    selectedRow.classList.remove('selected-row');
    const lastCell = selectedRow.cells[selectedRow.cells.length - 1];
    const deleteBtn = lastCell.querySelector('.delete-btn');
    if (deleteBtn) {
      deleteBtn.remove();
    }
    selectedRow = null;
    selectedRowId = null;
  }
}

function deleteSelectedItem() {
  if (!selectedRowId) {
    showNotification('No item selected', 'warning');
    return;
  }
  
  showConfirmation(
    'Are you sure you want to delete this item? This action cannot be undone.',
    () => {
      fetch('api/delete_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: selectedRowId })
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          // Remove the selected row
          if (selectedRow && selectedRow.parentNode) {
            selectedRow.remove();
          }
          // Clear selection
          selectedRow = null;
          selectedRowId = null;
          // Reload data
          showNotification('Item deleted successfully!', 'success');
          loadJSON();
        } else {
          showNotification('Failed to delete item: ' + (result.error || 'Unknown error'), 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Connection error: ' + error.message, 'error');
      });
    }
  );
}

// Replace the existing inline editing code with this:
function startInlineEdit(cell, rowId) {
  if (cell.classList.contains('editing')) return;
  
  const row = cell.parentElement;
  const colIndex = cell.cellIndex;
  const header = document.querySelectorAll('#inventoryTable th')[colIndex];
  const colName = header.textContent.trim();
  
  if (!rowId) return;

  cell.classList.add('editing', 'editing-cell');
  
  const oldVal = cell.textContent.trim();
  let inputEl;

  // ROOM DROPDOWN
  if (colName === 'Room') {
    inputEl = document.createElement('select');
    inputEl.className = 'inline-input';
    ['Chemical Room', 'Laboratory 1', 'Laboratory 2', 'Storage Room']
      .forEach(opt => {
        const o = document.createElement('option');
        o.value = opt;
        o.textContent = opt;
        if (opt === oldVal) o.selected = true;
        inputEl.appendChild(o);
      });
  }
  // DESCRIPTION = textarea
  else if (colName === 'Description') {
    inputEl = document.createElement('textarea');
    inputEl.value = oldVal;
    inputEl.className = 'inline-input';
    inputEl.style.minHeight = '60px';
    
    inputEl.addEventListener('input', function() {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });
    
    setTimeout(() => {
      inputEl.style.height = 'auto';
      inputEl.style.height = (inputEl.scrollHeight) + 'px';
    }, 0);
  }
  // Quantity columns
  else if (
    colName.includes('Quantity') ||
    colName.includes('Acquisition') ||
    colName.includes('Ending') ||
    colName.includes('Pull-out')
  ) {
    inputEl = document.createElement('input');
    inputEl.type = 'number';
    inputEl.value = oldVal;
    inputEl.className = 'inline-input';
    inputEl.min = 0;
  }
  // Default: text input
  else {
    inputEl = document.createElement('input');
    inputEl.type = 'text';
    inputEl.value = oldVal;
    inputEl.className = 'inline-input';
  }

  // Clear cell and add input
  cell.innerHTML = '';
  cell.appendChild(inputEl);
  inputEl.focus();

  function finishSave() {
  const newVal = inputEl.value.trim();
  cell.textContent = newVal;
  cell.classList.remove('editing', 'editing-cell');

  // SEND TO SERVER
  fetch('api/update_item.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: rowId,
      column: colName,
      value: newVal
    })
  })
  .then(r => {
    if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
    return r.json();
  })
  .then(res => {
    if (!res.success) {
      console.error("Save failed:", res.error);
      showNotification("Save failed: " + res.error, 'error');
      cell.textContent = oldVal;
    } else {
      showNotification("Changes saved successfully", 'success', 2000);
    }
  })
  .catch(err => {
    console.error("Connection error:", err);
    showNotification("Connection error: " + err.message, 'error');
    cell.textContent = oldVal;
  });
}

  inputEl.addEventListener('blur', finishSave);
  inputEl.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter') {
      ev.preventDefault();
      inputEl.blur();
    }
    if (ev.key === 'Escape') {
      cell.textContent = oldVal;
      cell.classList.remove('editing', 'editing-cell');
    }
  });
}

// Initialize the row selection system when the page loads
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(() => {
    initializeRowSelection();
  }, 1000); // Wait a bit for the table to load
});

// ====== ADD NEW ITEM FEATURE ======
document.addEventListener('DOMContentLoaded', () => {
  const addItemBtn = document.getElementById('addItemBtn');
  const saveNewItemBtn = document.getElementById('saveNewItemBtn');
  const inventoryBody = document.getElementById('inventoryBody');

  if (!addItemBtn || !inventoryBody) return;

  let newItemRow = null;

  addItemBtn.addEventListener('click', () => {
    // Remove any existing unsaved new item
    if (newItemRow) {
      newItemRow.remove();
    }

    // Create new row with editable inputs
    newItemRow = document.createElement('tr');
    newItemRow.innerHTML = `
      <td><input type="text" class="new-item-input" placeholder="Item Name" required style="width: 95%;"></td>
      <td>
        <select class="new-item-input" style="width: 95%;">
          <option value="Chemical Room">Chemical Room</option>
          <option value="Laboratory 1">Laboratory 1</option>
          <option value="Laboratory 2">Laboratory 2</option>
          <option value="Storage Room">Storage Room</option>
        </select>
      </td>
      <td><textarea class="new-item-input" placeholder="Enter Description" style="width: 95%; height: 45px;"></textarea></td>
      <td style="text-align: center;"><input type="number" class="new-item-input" value="0" min="0" style="width: 80%; text-align: center;"></td>
      <td style="text-align: center;"><input type="number" class="new-item-input" value="0" min="0" style="width: 80%; text-align: center;"></td>
      <td style="text-align: center;"><input type="number" class="new-item-input" value="0" min="0" style="width: 80%; text-align: center;"></td>
      <td style="text-align: center;"><input type="number" class="new-item-input" value="0" min="0" style="width: 80%; text-align: center;"></td>
      <td><textarea class="new-item-input" placeholder="Remarks" style="width: 100%; height: 45px"></textarea></td>
    `;

    newItemRow.style.backgroundColor = "rgba(255, 214, 0, 0.15)";
    inventoryBody.prepend(newItemRow);
    
    // Show save button
    saveNewItemBtn.style.display = 'inline-block';
  });

// Save new item to database
saveNewItemBtn.addEventListener('click', () => {
  if (!newItemRow) return;

  const inputs = newItemRow.querySelectorAll('.new-item-input');
  const itemData = {
    item: inputs[0].value.trim(),
    room: inputs[1].value,
    description: inputs[2].value.trim(),
    beginning: inputs[3].value || '0',
    acquisition: inputs[4].value || '0', 
    ending: inputs[5].value || '0',
    pullout: inputs[6].value || '0',
    remarks: inputs[7].value.trim(),
    category: 'EQUIPMENT/APPARATUSES'
  };

  // Validate required fields
  if (!itemData.item) {
    showNotification('Item name is required!', 'warning');
    inputs[0].focus();
    return;
  }

  // Send to server
  fetch('api/add_item.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(itemData)
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      loadJSON();
      newItemRow.remove();
      newItemRow = null;
      saveNewItemBtn.style.display = 'none';
      showNotification('Item added successfully!', 'success');
    } else {
      showNotification('Failed to add item: ' + (result.error || 'Unknown error'), 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Connection error: ' + error.message, 'error');
  });
});
});

// ====== RESERVATION FEATURE ======
document.addEventListener('DOMContentLoaded', () => {
  const reserveItem = document.getElementById('reserveItem');
  const reserveDate = document.getElementById('reserveDate');
  const reserveBtn = document.getElementById('reserveBtn');
  const reservedList = document.getElementById('reservedList');
  const popup = document.getElementById('reservePopup');

  if (!reserveItem || !reserveBtn) return;

  // Enable/disable button based on input
  reserveItem.addEventListener('input', () => {
    reserveBtn.disabled = reserveItem.value.trim() === '';
  });

  // Handle reservation submission
  reserveBtn.addEventListener('click', () => {
    const itemName = reserveItem.value.trim();
    const dateNeeded = reserveDate.value || "No date specified";

    if (itemName === '') return;

    // Add item to reserved list
    const li = document.createElement('li');
    li.textContent = `${itemName} — ${dateNeeded}`;
    reservedList.appendChild(li);

    popup.textContent = `${itemName} has been reserved`;

    // Reset state first (if it was hiding)
    popup.classList.remove('hide');
    popup.style.display = 'block';

    // Fade in
    setTimeout(() => popup.classList.add('show'), 10);

    // Fade out after 2 seconds
    setTimeout(() => {
      popup.classList.remove('show');
      popup.classList.add('hide');
      setTimeout(() => (popup.style.display = 'none'), 400);
    }, 2000);

    // Reset form
    reserveItem.value = '';
    reserveDate.value = '';
    reserveBtn.disabled = true;
  });
});