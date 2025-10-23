// form-inventory.js
// Place this file in the same folder as form.html and shs_lab_inventory_clean.json

let allData = [];           // full dataset loaded from JSON
let currentSortAsc = true;  // toggles sort order

const roomFilter = document.getElementById('roomFilter');
const searchInput = document.getElementById('searchInput');
const inventoryBody = document.getElementById('inventoryBody');

// safe helper to get a prop regardless of capitalization
function getProp(obj, name) {
  if (!obj) return '';
  if (name in obj) return obj[name];
  // fallback to case-insensitive match
  const key = Object.keys(obj).find(k => k.toLowerCase() === name.toLowerCase());
  return key ? obj[key] : '';
}

function loadJSON() {
  fetch('Lab_inventory_masterlist.json')
    .then(r => {
      if (!r.ok) throw new Error('JSON file not found on server');
      return r.json();
    })
    .then(json => {
      if (!Array.isArray(json)) {
        console.error('Expected JSON array');
        allData = [];
      } else {
        allData = json;
      }
      populateRoomFilter();
      renderTable();
    })
    .catch(err => {
      console.error('Error loading JSON:', err);
      // If fetch fails (local file), leave table empty — user should use local server
    });
}

function populateRoomFilter() {
  const rooms = [...new Set(allData.map(row => (getProp(row, 'Room') || '').toString().trim()).filter(Boolean))];
  // clear existing options but keep "All"
  roomFilter.innerHTML = '<option value="">All</option>';
  rooms.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r;
    opt.textContent = r;
    roomFilter.appendChild(opt);
  });
}

// Convert numbers that are strings to display consistently
function formatCell(v) {
  if (v === null || v === undefined) return '';
  return String(v);
}

// Render filtered table
function renderTable() {
  const roomVal = roomFilter.value;
  const q = (searchInput.value || '').toLowerCase().trim();

  let filtered = allData.filter(row => {
    // Room match (if selected)
    if (roomVal) {
      const r = getProp(row, 'Room') || '';
      if (r.toString() !== roomVal) return false;
    }
    // search match across visible fields (item, description, remarks)
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

  // Sort by Item column respecting currentSortAsc
  filtered.sort((a, b) => {
    const A = (getProp(a, 'Item') || '').toString().toLowerCase();
    const B = (getProp(b, 'Item') || '').toString().toLowerCase();
    return currentSortAsc ? A.localeCompare(B) : B.localeCompare(A);
  });

  // Build HTML
  if (!filtered.length) {
    inventoryBody.innerHTML = '<tr><td colspan="8">No records found.</td></tr>';
    return;
  }

  const rowsHtml = filtered.map(row => {
    const item = formatCell(getProp(row, 'Item'));
    const room = formatCell(getProp(row, 'Room'));
    const desc = formatCell(getProp(row, 'Description'));
    const beg = formatCell(getProp(row, 'Beginning') || getProp(row, 'Quantity (Beg)') || '');
    const acq = formatCell(getProp(row, 'Acquisition') || getProp(row, 'Acquisition/Transfer') || '');
    const ending = formatCell(getProp(row, 'Ending'));
    const pull = formatCell(getProp(row, 'PullOut') || getProp(row, 'Pull-out') || '');
    const remarks = formatCell(getProp(row, 'Remarks'));
    return `<tr>
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
}

// simple HTML escape
function escapeHtml(s) {
  if (s === null || s === undefined) return '';
  return String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

// wired to your button onclick
function sortTable() {
  currentSortAsc = !currentSortAsc; // toggle
  renderTable();
}

// searchTable is used by onkeyup (keeps same name)
function searchTable() {
  renderTable();
}

// Wire up filter change
roomFilter.addEventListener('change', renderTable);

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
  loadJSON();
});

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
// ====== INLINE EDITING FEATURE ======
document.addEventListener('DOMContentLoaded', () => {
  const inventoryTable = document.getElementById('inventoryTable');
  if (!inventoryTable) return;

  // Editable columns by name
  const editableColumns = {
    'Description': 'text',
    'Remarks': 'text',
    'Quantity (Beg)': 'number',
    'Ending': 'number'
  };

  inventoryTable.addEventListener('click', (e) => {
    const cell = e.target.closest('td');
    const header = cell && cell.closest('table').querySelectorAll('th')[cell.cellIndex];
    if (!cell || !header) return;

    const colName = header.textContent.trim();
    const inputType = editableColumns[colName];
    if (!inputType) return; // not editable

    // Prevent multiple editors
    if (cell.classList.contains('editing')) return;

    const oldValue = cell.textContent.trim();
    cell.classList.add('editing');
    cell.classList.add('editable');
    cell.innerHTML = `<input type="${inputType === 'number' ? 'number' : 'text'}" 
                        class="inline-input" value="${oldValue}">`;

    const input = cell.querySelector('input');
    input.focus();

    // Save on Enter or blur
    const save = () => {
      let newValue = input.value.trim();
      if (inputType === 'number' && newValue !== '' && isNaN(newValue)) {
        alert('Please enter a valid number.');
        return;
      }
      cell.textContent = newValue || '';
      cell.classList.remove('editing');
    };

    input.addEventListener('blur', save);
    input.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter') {
        ev.preventDefault();
        input.blur();
      } else if (ev.key === 'Escape') {
        cell.textContent = oldValue;
        cell.classList.remove('editing');
      }
    });
  });
});

