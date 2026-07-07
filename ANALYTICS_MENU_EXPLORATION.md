# Analytics Menu Implementation - Comprehensive Overview

**Date:** April 20, 2026  
**Application:** PEKPP (Penilaian Kinerja Pencapaian Pembangunan)  
**Framework:** Laravel + Livewire

---

## 1. ROUTES & ACCESSIBILITY

### Web Routes
```
GET /analytics → View: analytics.index (Main Analytics Dashboard)
Middleware: auth, EnsureUserUpp
Access Control: Superadmin & Admin Organisasi only
Route Name: analytics.index
```

**Web API Endpoint (for test suite):**
```
POST /api/analytics/exports → AnalyticsExportController::store
```

### API Routes (`routes/api.php`)
All require `auth:sanctum` middleware:

| Endpoint | Method | Purpose | Route Name |
|----------|--------|---------|-----------|
| `/api/analytics/exports` | POST | Create CSV/PDF export | `api.analytics.exports.store` |
| `/api/analytics/exports/{id}/status` | GET | Check export progress | `api.analytics.exports.status` |
| `/api/analytics/exports/{id}/download` | GET | Download completed file | `api.analytics.exports.download` |
| `/api/analytics/exports/{id}/retry` | POST | Retry failed export | `api.analytics.exports.retry` |

---

## 2. CONTROLLERS & METHODS

### HTTP Controllers

#### **AnalyticsController** (`app/Http/Controllers/AnalyticsController.php`)
- **Middleware:** `role:admin|analyst`
- **Methods:**
  - `index(Request $request)` → Render analytics.index view with summary & filters
  - `aspek(Request $request)` → JSON: Aspek aggregates by periode/upp
  - `indikator(Request $request)` → JSON: Indikator aggregates by periode/upp/aspek

#### **AnalyticsReportController** (`app/Http/Controllers/AnalyticsReportController.php`)
- **Middleware:** `role:admin|analyst`
- **Methods:**
  - `schedule(Request $request)` → Create scheduled report (name, frequency, params)

#### **AnalyticsExportController** (`app/Http/Controllers/AnalyticsExportController.php`)
- **Middleware:** `role:admin|analyst`
- **Methods:**
  - `exportCsv(Request $request)` → Handle CSV export
  - `enqueuePdf(Request $request)` → Queue PDF generation
  - `status(AnalyticsExport $export)` → Get export status JSON
  - `download(AnalyticsExport $export)` → Download file or return signed URL
  - `retry(AnalyticsExport $export)` → Retry failed export

### API Controllers (V1)

#### **Api/V1/AnalyticsController** 
- `index(Request $request)` → Summary + charts + table metadata

#### **Api/V1/AnalyticsExportController**
- `store(Request $request)` → Create export with idempotency checking
- `status($id)` → Get export status
- `download($id)` → Stream or redirect to signed URL
- `retry(Request $request, $id)` → Requeue failed export

---

## 3. LIVEWIRE COMPONENTS

### **Analytics\Panel** (`app/Livewire/Analytics/Panel.php`)

**Properties:**
- `periode_id` - Selected period filter
- `upp_id[]` - Selected UPP(s) - supports multiple selections
- Chart data: `f02_labels`, `f02_data`, `f03_labels`, `f03_data`, `ipp_labels`, `ipp_data`
- Aspek data: `aspek_labels`, `aspek_values`, `aspek_tabs`, `aspek_indikator_scores`
- Options: `periode_options[]`, `upp_options[]`
- Summary: `summary_cards[]`, `summary_card_details[]`

**Methods:**
- `mount()` - Initialize filters and load data
- `updatedUppId()` - Reactive: reload charts when UPP changes
- `updatedPeriodeId()` - Reactive: reload options and charts when period changes
- `loadFilterOptions()` - Load periode and UPP dropdowns
- `loadAllChartData()` - Load F02, F03, IPP, Aspek metrics
- `exportToExcel()` - Generate Excel export with multiple sheets
- `exportF02Data()` - Export F02 validation data
- `dispatchChartDataUpdated()` - Emit event to update frontend charts
- `normalizeUppIds()` - Handle single/multiple UPP selections

**Events Dispatched:**
- `analytics-charts-updated` - Triggers chart redraw in JavaScript
- `analytics-export-success` / `analytics-export-failed` - Export notifications

---

## 4. VIEWS & BLADE TEMPLATES

### **Analytics Entry Point**
```
resources/views/analytics/index.blade.php
├── @livewire('analytics.panel')
└── Extends: layouts.app
```

### **Livewire Panel Template**
```
resources/views/livewire/analytics/panel.blade.php
├── Styles: Custom CSS for dashboard layout
├── Filter Section
│   ├── UPP Select dropdown
│   ├── Periode Select dropdown
│   └── Export buttons (CSV, PDF, Excel)
├── Action Buttons
│   ├── Filter button
│   ├── Export buttons with rate limit info
│   └── Summary text
├── Summary Cards Grid
│   ├── Total UPP
│   ├── Average F02 Score
│   ├── Average F03 Score
│   ├── IPP Average & Status
│   └── Status counts (Submitted, Validated, Pending)
└── Charts Section
    ├── F02 Chart
    ├── F03 Chart
    ├── IPP Chart
    └── Aspek Breakdown
```

**Key Styling Classes:**
- `.dash-main` - Main container
- `.filter-section` - Filter UI area
- `.chart-section` - Chart display area
- `.summary-card` - KPI card with hover effects
- `.chart-card` - Individual chart container

---

## 5. MODELS & DATABASE STRUCTURE

### **Model: AnalyticsAggregate**
```php
Table: analytics_aggregates
Purpose: Pre-computed aggregated metrics for performance

Columns:
- id (PK)
- periode_id (FK, indexed)
- tenant_id (nullable, indexed)
- scope_key (nullable, indexed)
- level (enum: indicator|aspek|upp|opd|provinsi, indexed)
- dimension_hash (nullable, indexed for uniqueness)
- aggregate_version (default: 1)
- upp_id, aspek_id, indikator_id (nullable, indexed)
- total_responses (int, default: 0)
- avg_score (decimal 6,2, default: 0.00)
- median_score (decimal 6,2, default: 0.00)
- pct_validated (decimal 5,2, default: 0.00)
- pct_empty (decimal 5,2, default: 0.00)
- computed_at (timestamp)
- created_at, updated_at (timestamps)

Unique: (periode_id, scope_key, dimension_hash) as 'analytics_agg_unique'
```

### **Model: AnalyticsExport**
```php
Table: analytics_exports
Purpose: Track export generation and status

Columns:
- id (PK)
- user_id (nullable, FK indexed)
- tenant_id (nullable, FK indexed)
- scope_key (nullable, indexed)
- idempotency_key (nullable, indexed) - Prevents duplicate exports
- correlation_id (nullable, indexed) - Trace export requests
- type (enum: csv|pdf)
- params (json) - Export filters & options
- file_path (nullable) - S3 or local path
- file_size (nullable)
- status (enum: pending|processing|ready|failed, indexed)
- error_message (text, nullable)
- processed_rows, total_rows_estimate (int)
- progress_percent (decimal 5,2)
- idempotency_attempts (int)
- last_attempted_at, started_at, finished_at (timestamps)
- created_at, updated_at (timestamps)
```

### **Model: AnalyticsReportSchedule**
```php
Table: analytics_report_schedules
Purpose: Schedule recurring report generation

Columns:
- id (PK)
- name (string)
- user_id (FK indexed)
- frequency (enum: daily|weekly|monthly)
- params (json) - Report configuration
- enabled (boolean, default: true)
- last_run_at, next_run_at (timestamps)
- last_run_status (enum: success|failed|skipped, nullable)
- last_export_id (nullable, FK indexed)
- created_at, updated_at (timestamps)
```

---

## 6. CONFIGURATION

### **File:** `config/analytics.php`

```php
return [
    // Storage Configuration
    'storage_disk' => env('ANALYTICS_STORAGE_DISK', 'local'),  // 'local' or 's3'
    
    // Sync vs Queue Decision
    'sync_threshold' => env('ANALYTICS_SYNC_THRESHOLD', 50000),  // Row count threshold
    
    // File Management
    'export_retention_days' => env('ANALYTICS_EXPORT_RETENTION_DAYS', 30),
    'export_ttl_hours' => env('ANALYTICS_EXPORT_TTL_HOURS', 48),  // Signed URL lifetime
    
    // Processing Configuration
    'median_in_memory_threshold' => env('ANALYTICS_MEDIAN_IN_MEMORY_THRESHOLD', 500000),
    'pdf_engine' => env('ANALYTICS_PDF_ENGINE', 'snappy'),  // 'snappy'|'dompdf'|'chrome'
    
    // Idempotency & Caching
    'idempotency_window_minutes' => env('ANALYTICS_IDEMPOTENCY_WINDOW_MINUTES', 60),
    'cache_ttl_minutes' => env('ANALYTICS_CACHE_TTL_MINUTES', 10),
    
    // Rate Limiting
    'rate_limits' => [
        'per_user_per_day' => env('ANALYTICS_EXPORT_RATE_PER_USER', 5),
        'per_tenant_per_day' => env('ANALYTICS_EXPORT_RATE_PER_TENANT', 100),
        'bypass_roles' => array_map('trim', explode(',', env('ANALYTICS_RATE_BYPASS_ROLES', 'admin'))),
    ],
    
    // Progress Reporting
    'progress_update_every_rows' => env('ANALYTICS_PROGRESS_UPDATE_EVERY_ROWS', 1000),
];
```

---

## 7. SERVICES & SERVICE LAYER

### **AnalyticsReadService**
- `buildAggregateQuery(array $params)` - Build query against analytics_aggregates
- `getSummary(array $filters)` - Get KPI summary
- `getAspekAggregates(array $filters)` - Aspek-level aggregates
- `getIndikatorAggregates(array $filters)` - Indikator-level aggregates

### **AnalyticsExportService**
- `estimateRows(array $params)` - Row count estimate
- `checkRateLimits($userId, $tenantId, $roles)` - Enforce rate limits
- `createExportRecord(array $data)` - Create export record

### **AnalyticsExportOrchestrator**
- `handleCsvRequest($user, array $params)` - CSV export orchestration
- `handlePdfRequest($user, array $params)` - PDF export orchestration
- `downloadExport(AnalyticsExport $export)` - Download handling
- `retryExport(AnalyticsExport $export)` - Retry logic

### **AnalyticsAggregationService**
- Data aggregation pipeline for computing analytics_aggregates

### **AnalyticsAggregateWriter**
- Write aggregated data to database

### **ScopeContext**
```php
final class ScopeContext {
    public readonly ?int $tenantId;
    public readonly ?string $scopeKey;
    public readonly ?int $userId;
    public readonly array $roles;
    public readonly ?string $correlationId;
    
    public static function fromRequest(Request $r): self
    public function toArray(): array
    public static function fromArray(array $data): self
}
```
- Tenant and scope isolation for multi-tenant support

### **AnalyticsPdfService**
- PDF generation for exports

---

## 8. SIDEBAR MENU INTEGRATION

### **Location:** `resources/views/partials/sidebar.blade.php`

**Menu Item:**
```blade
{{-- ANALISIS & LAPORAN - Superadmin & Admin Organisasi --}}
@if($isSuperadmin || $isAdminOrganisasi)
  <div class="nav-section">
    <a href="{{ url('/analytics') }}" 
       class="nav-item {{ $isActive('analytics') ? 'active' : '' }}" 
       title="Analisis & Laporan Komprehensif">
      <span class="nav-icon">{!! $sidebarIcon('trend') !!}</span>
      <span class="label">Analisis & Laporan</span>
    </a>
  </div>
@endif
```

**Access Control:**
- **Visible to:** Superadmin (`role_sso == 'superadmin'`)
- **Visible to:** Admin Organisasi (`role_sso` contains 'admin_organisasi', 'org_admin', 'org-admin', 'admin_bagian_organisasi')
- **Icon:** Trend chart SVG icon
- **Label:** "Analisis & Laporan" (Analytics & Reports)

---

## 9. MIDDLEWARE & SECURITY

### **Middleware Chain**
1. **auth** - Require authenticated user
2. **EnsureUserUpp** - Ensure user has UPP assignment
3. **role:admin|analyst** - Role-based access (for specific controllers)

### **Authorization**
- Export download: `can:download,App\Models\AnalyticsExport`
- Export retry: `can:download,App\Models\AnalyticsExport`

### **Idempotency**
- Duplicate export requests prevented via `idempotency_key` header
- 60-minute window for idempotency checking
- Idempotency-Key header required for export API requests

---

## 10. DATA FLOW DIAGRAM

```
User (Superadmin/Admin Organisasi)
    ↓
URL: /analytics
    ↓
Route: analytics.index
    ↓
View: analytics/index.blade.php
    ├── @livewire('analytics.panel')
    ↓
Livewire Component: Analytics\Panel
    ├─→ mount() - Load filters & data
    ├─→ loadFilterOptions() - Query periode, upp_options
    ├─→ loadAllChartData() - Compute chart data
    │   ├─→ DB queries on f02_validasi, f03_jawaban, f03_pengisian
    │   ├─→ Compute aggregates: F02, F03, IPP, Aspek scores
    │   ├─→ Load summary card data (KPIs)
    │   └─→ dispatchChartDataUpdated() - Emit event
    │
    ├─→ updatedPeriodeId() / updatedUppId() - Reactive updates
    │
    ├─→ exportToExcel() / exportF02Data() - Export operations
    │   └─→ AnalyticsExportOrchestrator::handleCsvRequest()
    │
    └─→ Blade template rendering
        ├─→ Display filter UI
        ├─→ Display summary cards
        ├─→ Display chart containers
        └─→ Chart.js visualization (client-side)
```

---

## 11. KEY FEATURES & FUNCTIONALITY

### **Filtering**
- **Periode Filter** - Select active period (tahun, nama)
- **UPP Filter** - Single or multiple UPP selection
  - Shows email username or UPP name
  - Shows F02 & F03 averages per UPP

### **Summary Metrics (Summary Cards)**
1. **Total UPP** - Count of UPPs in filtered scope
2. **Average F02 Score** - Mean F02 validation score
3. **Average F03 Score** - Mean F03 response score
4. **Average IPP** - Integrated Performance Index (75% F02 + 25% F03)
5. **IPP Status & Category** - Qualitative assessment (Baik/Perlu Pembinaan)
6. **Submission Status** - Sudah Submit / Belum Validasi / Sudah Selesai
7. **F03 Response Metrics** - Response count vs minimum threshold

### **Charts**
1. **F02 Chart** - Score distribution by UPP
2. **F03 Chart** - Response scores by UPP
3. **IPP Chart** - Integrated index by UPP
4. **Aspek Breakdown** - Scores aggregated by aspect

### **Export Functionality**
- **CSV Export** - Stream for small datasets (<50K rows), queue for large
- **PDF Export** - Always queued
- **Excel Export** - Multi-sheet workbook (F02, Aspek data)
- **Rate Limiting** - 5 exports/user/day, 100/tenant/day
- **Idempotency** - Prevents duplicate exports
- **Progress Tracking** - Real-time progress updates
- **File Retention** - 30-day expiration
- **Signed URLs** - 48-hour S3 URL validity

### **UI/UX Features**
- Responsive grid layout (1col mobile → 4col desktop)
- Summary card hover effects
- Filter update triggers reactive reloads
- Action buttons with disabled state
- Color-coded status indicators
- Pagination support (WithPagination trait)

---

## 12. KNOWN ISSUES & DOCUMENTATION

### **Issues Documented in Codebase**
- Livewire-based filtering may not properly update `window.chartDataFromServer`
- Analytics panel filtering requires Livewire event dispatch handling
- See: `FILTER_IMPLEMENTATION_ANALYSIS.md` for detailed analysis

### **Configuration Environment Variables**
```env
ANALYTICS_STORAGE_DISK=local              # or 's3'
ANALYTICS_SYNC_THRESHOLD=50000
ANALYTICS_EXPORT_RETENTION_DAYS=30
ANALYTICS_EXPORT_TTL_HOURS=48
ANALYTICS_MEDIAN_IN_MEMORY_THRESHOLD=500000
ANALYTICS_PDF_ENGINE=snappy
ANALYTICS_IDEMPOTENCY_WINDOW_MINUTES=60
ANALYTICS_CACHE_TTL_MINUTES=10
ANALYTICS_EXPORT_RATE_PER_USER=5
ANALYTICS_EXPORT_RATE_PER_TENANT=100
ANALYTICS_RATE_BYPASS_ROLES=admin
ANALYTICS_PROGRESS_UPDATE_EVERY_ROWS=1000
```

---

## 13. MIGRATION FILES

| File | Purpose |
|------|---------|
| `2026_04_16_000001_create_analytics_aggregates_table.php` | Create aggregates table for pre-computed metrics |
| `2026_04_16_000002_create_analytics_exports_table.php` | Create exports table for tracking export jobs |
| `2026_04_16_000003_create_analytics_report_schedules_table.php` | Create schedules table for recurring reports |
| `2026_04_16_000010_create_analytics_export_quota_table.php` | Create quota tracking table |

---

## 14. SUMMARY

The **Analytics Menu** (labeled "Analisis & Laporan") is a comprehensive reporting dashboard for Superadmins and Admin Organisasi users. It provides:

- **Interactive filtering** by period and UPP(s)
- **Real-time metrics** displayed in summary cards (KPIs)
- **Data visualizations** via Chart.js
- **Export capabilities** (CSV, PDF, Excel) with rate limiting
- **Responsive design** optimized for mobile to desktop
- **Multi-tenant support** via scope context
- **Idempotent exports** preventing duplicates
- **Scheduled reports** capability

The implementation uses **Livewire** for reactive UI components, **Laravel** services for business logic, and **pre-computed aggregates** in the database for performance. Access is strictly controlled via role-based authorization middleware.

---

**End of Document**
