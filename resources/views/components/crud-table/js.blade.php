{{-- Shared CRUD Table JavaScript --}}
@php
    $prefix = $prefix ?? 'crud';
    $route = $route ?? '';
    $deleteValidation = $deleteValidation ?? false;
    $customJS = $customJS ?? '';
@endphp

<script>
    // ====== MODAL FUNCTIONS ======
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close modal when clicking overlay
    document.querySelectorAll('.{{ $prefix }}-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // ====== FORM FUNCTIONS ======
    function submitForm(formId, modalId, action, method = 'POST') {
        const form = document.getElementById(formId);
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg> Menyimpan...';

        const formData = new FormData(form);
        
        // Add _method for PUT requests (Laravel method spoofing)
        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        fetch(action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => {
            if (response.ok) {
                closeModal(modalId);
                showToast('Data berhasil disimpan', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                return response.json().then(data => {
                    showToast(data.message || 'Gagal menyimpan data', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }).catch(() => {
                    showToast('Gagal menyimpan data', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        });
    }

    // ====== DELETE FUNCTIONS ======
    let deleteData = null;

    function confirmDelete(data, itemName) {
        deleteData = data;
        document.getElementById('{{ $prefix }}-delete-item-name').textContent = itemName;
        document.getElementById('{{ $prefix }}-delete-error-container').style.display = 'none';
        openModal('{{ $prefix }}DeleteModal');
    }

    function executeDelete() {
        if (!deleteData) return;

        const deleteBtn = document.getElementById('{{ $prefix }}-delete-btn');
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Menghapus...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const deleteUrl = '{{ $route }}'.replace(':id', deleteData.id);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                closeModal('{{ $prefix }}DeleteModal');
                showToast('Data berhasil dihapus', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                return response.json().then(data => {
                    throw new Error(data.error || 'Gagal menghapus data');
                });
            }
        })
        .catch(error => {
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = '❌ Hapus Data';
            }

            const errorContainer = document.getElementById('{{ $prefix }}-delete-error-container');
            const errorMessage = document.getElementById('{{ $prefix }}-delete-error-message');
            if (errorContainer && errorMessage) {
                errorMessage.innerHTML = '<strong>Tidak dapat menghapus!</strong><br>' + error.message;
                errorContainer.style.display = 'block';
            }

            showToast('Tidak dapat menghapus data ini', 'error');
        });
    }

    // ====== SEARCH/FILTER FUNCTIONS ======
    function filterTable(searchInputId, tableId, columnCount) {
        const input = document.getElementById(searchInputId).value.toLowerCase();
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length - 1; j++) {
                if (cells[j].textContent.toLowerCase().includes(input)) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }

    // ====== TOAST NOTIFICATION ======
    function showToast(message, type = 'success') {
        const toast = document.getElementById('{{ $prefix }}-toast');
        if (!toast) return;

        toast.textContent = message;
        toast.className = '{{ $prefix }}-toast ' + type + ' show';
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ====== DETAIL VIEW ======
    function viewDetail(data, modalId, fields) {
        fields.forEach(field => {
            const element = document.getElementById('{{ $prefix }}-detail-' + field);
            if (element) {
                element.textContent = data[field] || '-';
            }
        });
        openModal(modalId);
    }

    {{ $customJS }}
</script>
