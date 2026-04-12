# Quick Reference Guide - Periode Menu Implementation

## 🚀 Getting Started

### View the Result
```
Access: http://yourapp.local/admin/periode
```

### Required User Role
- `superadmin` or `admin_organisasi`

---

## 📌 Key File Locations

```
/resources/views/periode/
├── index.blade.php              ← Main view with all styling & JS
└── modals/
    ├── create.blade.php         ← Create form modal
    ├── edit.blade.php           ← Edit form modal
    ├── detail.blade.php         ← Detail view modal
    └── delete.blade.php         ← Delete confirmation modal

/app/Http/Controllers/
└── PeriodeController.php        ← Updated controller with enhanced delete logic

/routes/
└── web.php                      ← Existing route configuration (no changes needed)
```

---

## 🔧 JavaScript Functions Reference

### Modal Operations

```javascript
// Open any modal
openModal('modalId')

// Close any modal
closeModal('modalId')

// Open create form
openCreateModal()

// Open edit form with data
openEditModal({
    id: 1,
    nama: 'Periode 2026',
    tahun: 2026,
    tanggal_mulai: '2026-01-01',
    tanggal_selesai: '2026-12-31',
    is_aktif: 1
})

// Show detail view
viewDetail({
    id: 1,
    nama: 'Periode 2026',
    tahun: 2026,
    tanggal_mulai: '2026-01-01',
    tanggal_selesai: '2026-12-31',
    is_aktif: 1
})

// Show delete confirmation
confirmDelete({
    id: 1,
    nama: 'Periode 2026',
    tahun: 2026,
    tanggal_mulai: '2026-01-01',
    tanggal_selesai: '2026-12-31',
    is_aktif: 1
})
```

### Form Submission

```javascript
// These are auto-called from form onsubmit
submitCreateForm(event)
submitEditForm(event)
deleteperiode()
```

### Notifications

```javascript
// Show toast notification
showToast('Success message', 'success')   // Green
showToast('Error message', 'error')       // Red
```

### Search

```javascript
// Called on keyup in search input
filterTable()
```

---

## 📋 Delete Validation Logic

### What Gets Checked
1. **F01Pengisian** - Staff assessment submissions
2. **Aspek** - Assessment aspects/criteria
3. **F03Token** - Assessment access tokens

### Error Message Format
```
"Tidak dapat menghapus periode karena masih terkait dengan: 
[Data Pengisian F01, Aspek, Token F03]. 
Silakan hapus data terkait terlebih dahulu."
```

### Implementation Location
```php
// /app/Http/Controllers/PeriodeController.php
public function destroy(Periode $periode)
{
    // Validation logic here
    $hasF01 = ...
    $hasAspek = ...
    $hasF03Token = ...
    
    if ($hasF01 || $hasAspek || $hasF03Token) {
        // Build error message
        // Return JSON for AJAX or redirect for form submission
    }
    
    // If no conflicts, delete
    $periode->delete();
}
```

---

## 🎨 CSS Classes Reference

### Modal Classes
```css
.periode-modal-overlay       /* Modal background overlay */
.periode-modal              /* Modal container */
.periode-modal-header       /* Modal header */
.periode-modal-title        /* Modal title */
.periode-modal-body         /* Modal content area */
.periode-modal-footer       /* Modal action buttons */
.periode-modal-close        /* Close button */
```

### Form Classes
```css
.periode-form-group         /* Form field container */
.periode-form-label         /* Field label */
.periode-form-input         /* Text input field */
.periode-form-select        /* Select dropdown */
.periode-form-row           /* Two-column layout */
```

### Table Classes
```css
.periode-table-card         /* Table container */
.periode-table-header       /* Table title area */
.periode-table-title        /* Table title text */
.periode-table-action       /* Action buttons area */
.periode-table              /* Table element */
.periode-table-wrapper      /* Table scrollable container */
.periode-table-footer       /* Pagination area */
```

### Button Classes
```css
.periode-btn                /* Base button */
.periode-btn-primary        /* Blue primary button */
.periode-btn-secondary      /* White secondary button */
.periode-btn-danger         /* Red danger button */
.periode-btn-icon           /* Small icon button */
```

### Component Classes
```css
.periode-badge              /* Status badge */
.periode-badge-active       /* Green active badge */
.periode-badge-inactive     /* Gray inactive badge */
.periode-stat-card          /* Statistics card */
.periode-toast              /* Notification toast */
```

---

## 🔄 API Endpoints

### RESTful Routes (Resource-based)

| Method | Route | Controller Method | Returns |
|--------|-------|-------------------|---------|
| GET | /admin/periode | index() | View with periode list |
| GET | /admin/periode/create | create() | Create form view |
| POST | /admin/periode | store() | Redirect or JSON |
| GET | /admin/periode/{id}/edit | edit() | Edit form view |
| PUT/POST | /admin/periode/{id} | update() | Redirect or JSON |
| DELETE | /admin/periode/{id} | destroy() | Redirect or JSON |

### AJAX Request Example

```javascript
// Create
fetch('/admin/periode', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
})

// Update
fetch('/admin/periode/1', {
    method: 'POST',
    body: formData
})

// Delete
fetch('/admin/periode/1', {
    method: 'DELETE',
    headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
    }
})
```

---

## 🛡️ Security Features

### CSRF Protection
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">

// In JavaScript
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
```

### Authorization
```php
Route::middleware(['auth'])->group(function () {
    Route::resource('periode', PeriodeController::class);
})
```

### Role-Based Access
```php
// In controller constructor
if (! in_array($role, ['superadmin', 'admin_organisasi'])) {
    abort(403);
}
```

---

## 📱 Responsive Breakpoints

```css
/* Desktop (default) */
@media (min-width: 769px) {
    /* 2 column grids, full width elements */
}

/* Tablet & Mobile */
@media (max-width: 768px) {
    /* 1 column grids, stacked elements */
}
```

---

## 🧪 Testing Checklist

### Create Operation
- [ ] Form opens in modal
- [ ] Fields are empty
- [ ] Required fields validated
- [ ] Successful submission reloads page
- [ ] Toast shows success message

### Edit Operation
- [ ] Form opens in modal
- [ ] Fields are pre-populated
- [ ] Changes are saved
- [ ] Page reloads after save
- [ ] Toast shows success message

### Delete Operation - Success
- [ ] Modal shows periode name
- [ ] Click delete confirms in modal
- [ ] Period deleted from table
- [ ] Page reloads
- [ ] Toast shows success

### Delete Operation - Error (with related data)
- [ ] Modal shows periode name
- [ ] Error message appears in modal
- [ ] Lists which tables have related data
- [ ] Modal stays open
- [ ] User can close and fix related data

### Search/Filter
- [ ] Type in search box
- [ ] Table filters in real-time
- [ ] Results match all columns
- [ ] Clear search shows all rows

### Responsive Design
- [ ] Mobile: 375px width works
- [ ] Tablet: 768px width works
- [ ] Desktop: 1920px width works
- [ ] Modals center properly
- [ ] Text is readable

---

## 🐛 Troubleshooting

### Modal not opening?
```javascript
// Check browser console for errors
// Verify modal element exists in DOM
// Check CSS for display: none conflicting with .active class
```

### Delete always shows error?
```php
// Check database for related records
// Verify relationships in models

// Test in Tinker
\App\Models\F01Pengisian::where('periode_id', 1)->count()
\App\Models\Aspek::where('periode_id', 1)->count()
\App\Models\F03Token::where('periode_id', 1)->count()
```

### Toast not showing?
```javascript
// Verify element exists: document.getElementById('toast')
// Check CSS is not hidden: display: none
// Verify showToast() is being called
```

### Form not submitting?
```javascript
// Check browser console for CSRF token error
// Verify X-CSRF-TOKEN header is correct
// Check FormData is being used correctly
```

---

## 📚 Additional Resources

### Blade Template Syntax
- `@include()` - Include partial template
- `@section()` - Define content section
- `@extends()` - Extend parent template
- `{{ $variable }}` - Echo variable
- `@forelse()` - Loop with empty clause
- `@if()` - Conditional

### HTTP Status Codes Used
- `200` - Success
- `201` - Created
- `204` - No Content (delete)
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity (validation error)

### JavaScript Fetch API
```javascript
fetch(url, {
    method: 'POST|GET|PUT|DELETE',
    headers: { ... },
    body: formData
})
.then(response => response.json())
.then(data => { ... })
.catch(error => { ... })
```

---

## 🔗 Related Documentation

- [PERIODE_UI_UPGRADE.md](./PERIODE_UI_UPGRADE.md) - Detailed feature documentation
- [IMPLEMENTATION_SUMMARY.md](./IMPLEMENTATION_SUMMARY.md) - Complete implementation overview
- [BEFORE_AFTER_COMPARISON.md](./BEFORE_AFTER_COMPARISON.md) - Comparison of old vs new

---

## ✅ Deployment Checklist

- [ ] All files in correct locations
- [ ] Database migrations run
- [ ] User roles configured
- [ ] Routes registered
- [ ] CSRF token meta tag present
- [ ] Delete validation working
- [ ] Toast notifications working
- [ ] Search/filter working
- [ ] Responsive design tested
- [ ] Error messages displaying correctly
- [ ] No console errors
- [ ] Security headers configured

---

**Last Updated:** February 11, 2026
**Version:** 1.0
**Status:** Production Ready
