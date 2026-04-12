/**
 * Multi-Column Sorting Manager
 * Supports up to 3 simultaneous sorts
 * Removes oldest sort when 4th column is clicked
 */
class MultiSortManager {
  constructor(tableSelector, maxSorts = 3) {
    this.table = document.querySelector(tableSelector);
    if (!this.table) {
      console.warn('Table not found:', tableSelector);
      return;
    }
    this.maxSorts = maxSorts;
    this.sortStack = []; // Array of {column, direction}
    this.sortableColumns = {};
    this.init();
  }

  init() {
    // Load sort state from URL
    this.loadSortFromUrl();
    
    // Make all th sortable (except Aksi)
    const headers = this.table.querySelectorAll('thead th');
    headers.forEach((th, index) => {
      const text = th.textContent.trim();
      if (text.toLowerCase() === 'aksi') return; // Skip action column
      
      th.style.cursor = 'pointer';
      th.style.userSelect = 'none';
      th.setAttribute('data-column-index', index);
      
      const columnName = this.getColumnNameFromHeader(th);
      this.sortableColumns[index] = columnName;
      
      th.addEventListener('click', (e) => this.handleHeaderClick(th, index));
    });

    // Initial render of sort indicators
    this.renderSortIndicators();
    
    console.log('[MultiSort] Initialized with columns:', this.sortableColumns);
  }

  getColumnNameFromHeader(th) {
    let text = th.textContent.trim().toLowerCase();
    
    // Map human-readable names to database columns
    const columnMappings = {
      'periode': 'periode',
      'kode': 'kode',
      'nama aspek': 'nama',
      'nama indikator': 'nama',
      'aspek': 'aspek_id',
      'indikator': 'indikator_id',
      'domain': 'domain',
      'bobot': 'bobot',
      'status': 'aktif',
      'pertanyaan': 'label',
      'tipe': 'tipe_input',
      'wajib': 'wajib'
    };

    // Try exact match first
    for (let key of Object.keys(columnMappings)) {
      if (text === key) {
        return columnMappings[key];
      }
    }

    // Try partial match
    for (let [key, value] of Object.entries(columnMappings)) {
      if (text.includes(key)) {
        return value;
      }
    }
    
    // Default: use the text as-is (with underscores instead of spaces)
    return text.replace(/\s+/g, '_');
  }

  handleHeaderClick(th, columnIndex) {
    const columnName = this.sortableColumns[columnIndex];
    
    if (!columnName) {
      console.warn('Column name not found for index', columnIndex);
      return;
    }
    
    console.log('[MultiSort] Clicked column:', columnName);

    // Check if already sorted
    const existingSort = this.sortStack.find(s => s.column === columnName);
    
    if (existingSort) {
      // Toggle direction
      existingSort.direction = existingSort.direction === 'asc' ? 'desc' : 'asc';
      console.log('[MultiSort] Toggled direction to:', existingSort.direction);
    } else {
      // Add new sort
      if (this.sortStack.length >= this.maxSorts) {
        // Remove oldest (first) sort
        const removed = this.sortStack.shift();
        console.log('[MultiSort] Removed oldest sort:', removed.column);
      }
      this.sortStack.push({ column: columnName, direction: 'asc' });
      console.log('[MultiSort] Added new sort:', columnName);
    }

    console.log('[MultiSort] Sort stack:', this.sortStack);

    // Update URL and reload
    this.updateUrl();
  }

  loadSortFromUrl() {
    const params = new URLSearchParams(window.location.search);
    
    for (let i = 1; i <= this.maxSorts; i++) {
      const column = params.get(`sort${i}`);
      const direction = params.get(`dir${i}`);
      
      if (column) {
        this.sortStack.push({ column, direction: direction || 'asc' });
      }
    }
    
    console.log('[MultiSort] Loaded from URL:', this.sortStack);
  }

  updateUrl() {
    const params = new URLSearchParams(window.location.search);
    
    // Clear existing sort params
    for (let i = 1; i <= this.maxSorts; i++) {
      params.delete(`sort${i}`);
      params.delete(`dir${i}`);
    }

    // Add new sort params
    this.sortStack.forEach((sort, index) => {
      params.set(`sort${index + 1}`, sort.column);
      params.set(`dir${index + 1}`, sort.direction);
    });

    // Remove page param to go back to page 1
    params.delete('page');

    // Update URL
    const newUrl = window.location.pathname + '?' + params.toString();
    console.log('[MultiSort] Navigating to:', newUrl);
    window.location.href = newUrl;
  }

  renderSortIndicators() {
    const headers = this.table.querySelectorAll('thead th');
    
    headers.forEach((th, index) => {
      // Remove existing indicators
      th.querySelectorAll('.sort-indicator').forEach(el => el.remove());
      
      const columnName = this.sortableColumns[index];
      if (!columnName) return;
      
      const sortIndex = this.sortStack.findIndex(s => s.column === columnName);
      
      if (sortIndex !== -1) {
        const sort = this.sortStack[sortIndex];
        const indicator = document.createElement('span');
        indicator.className = 'sort-indicator';
        indicator.style.marginLeft = '4px';
        
        const badge = document.createElement('span');
        badge.style.display = 'inline-block';
        badge.style.backgroundColor = '#2196f3';
        badge.style.color = 'white';
        badge.style.padding = '2px 6px';
        badge.style.borderRadius = '3px';
        badge.style.marginRight = '4px';
        badge.style.fontSize = '0.75em';
        badge.style.fontWeight = 'bold';
        badge.textContent = (sortIndex + 1).toString();
        
        const arrow = document.createElement('span');
        arrow.style.fontSize = '0.9em';
        arrow.style.fontWeight = 'bold';
        arrow.textContent = sort.direction === 'asc' ? ' ↑' : ' ↓';
        
        indicator.appendChild(badge);
        indicator.appendChild(arrow);
        th.appendChild(indicator);
        
        console.log(`[MultiSort] Rendered indicator: ${columnName} (#${sortIndex + 1}) ${sort.direction}`);
      }
    });
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  console.log('[MultiSort] DOMContentLoaded event fired');
  
  if (document.getElementById('aspekTable')) {
    console.log('[MultiSort] Initializing aspekSorter');
    window.aspekSorter = new MultiSortManager('#aspekTable');
  }
  if (document.getElementById('indikatorTable')) {
    console.log('[MultiSort] Initializing indikatorSorter');
    window.indikatorSorter = new MultiSortManager('#indikatorTable');
  }
  if (document.getElementById('pertanyaanTable')) {
    console.log('[MultiSort] Initializing pertanyaanSorter');
    window.pertanyaanSorter = new MultiSortManager('#pertanyaanTable');
  }
});
