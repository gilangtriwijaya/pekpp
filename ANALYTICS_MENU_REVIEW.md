# 📊 Analisis Menu Analitik - Review Komprehensif

**Last Updated:** 2026-04-20  
**Status:** Operational

---

## 1. 🎯 Akses & Navigasi

### Lokasi Menu
- **Route**: `/analytics`
- **Label Sidebar**: "Analisis & Laporan" 
- **Icon**: Trend Chart
- **Access Level**: Superadmin & Admin Organisasi Only

### URL Struktur
```
Main Dashboard:     GET /analytics
API Exports:        POST /api/analytics/exports
Export Status:      GET /api/analytics/exports/{id}/status
Download Export:    GET /api/analytics/exports/{id}/download
Retry Export:       POST /api/analytics/exports/{id}/retry
```

---

## 2. 🏗️ Arsitektur Teknis

### Controllers (Request Handling)
```
AnalyticsController
├── index()              - Load dashboard utama
├── getChartData()       - Ambil data chart (F02, F03, IPP, Aspek)
├── getSummaryCards()    - KPI summary metrics
└── filterData()         - Handle filter changes

AnalyticsReportController
├── schedule()           - Buat scheduled report
├── listSchedules()      - List reports terjadwal
└── deleteSchedule()     - Hapus report

AnalyticsExportController
├── create()             - Inisiasi export
├── getStatus()          - Cek progress export
├── download()           - Download file export
└── retry()              - Ulangi export gagal
```

### Livewire Component (Real-time UI)
```
Panel Component
├── Property: $periode, $upp, $selectedChart
├── Listener: on-filter-change
├── Method: loadChartData()
├── Method: exportData()
└── Real-time reactivity on filter updates
```

### Models & Database
```
AnalyticsAggregate
├── periode (ID)
├── upp (ID)
├── aspek (ID)
├── indikator (ID)
├── nilai (calculated metric)
├── count (aggregated count)

AnalyticsExport
├── user_id, tenant_id
├── export_type (csv|pdf)
├── status (pending|processing|completed|failed)
├── progress_percentage
├── file_path
├── error_message

AnalyticsReportSchedule
├── user_id, tenant_id
├── frequency (daily|weekly|monthly)
├── export_type
├── recipients (email list)
├── is_active
```

---

## 3. 📈 Fitur-Fitur Utama

### A. Summary Cards (KPI Display)
Menampilkan 10+ metrik kunci:
- Total IPP Score (rata-rata)
- Total Submissions (jumlah pengisian)
- Completion Rate (%)
- F03 Response Count
- Outstanding Reviews
- Dan lainnya...

### B. Interactive Charts (4 Chart Types)
1. **F02 Chart** - Visualisasi F02 data trend
2. **F03 Chart** - Distribution F03 responses
3. **IPP Chart** - IPP scoring distribution
4. **Aspek Chart** - Breakdown by Aspek categories

**Features:**
- Responsive design (mobile → desktop)
- Zoom & pan capabilities
- Hover tooltips dengan details
- Legend toggle for series

### C. Filtering System
```
Periode Filter
├── Dropdown selection
├── Single/Multiple UPP selection
└── Real-time chart reload on change

Export Filters
├── Date range selection
├── Format selection (Excel, CSV, PDF)
├── Include aggregated data option
```

### D. Export Functionality
**Supported Formats**: CSV, Excel, PDF

**Features:**
- Progress tracking dengan percentage
- Idempotency key (prevent duplicate exports)
- Rate limiting: 5 exports per user per day
- Async processing dengan job queue
- Error retry mechanism dengan exponential backoff
- Download dengan automatic cleanup after 7 days

**Export Data Included**:
- Periode details
- UPP information
- Scoring metrics (F01, F02, F03)
- Aspek breakdown
- Indikator details
- Timestamp & submission info

---

## 4. 🔄 Alur Data (Data Flow)

### User Request Flow
```
1. User akses /analytics
   ↓
2. AnalyticsController@index load halaman
   ↓
3. Livewire Panel component initialize
   ↓
4. Dashboard render dengan default filters
   ↓
5. Summary cards & charts display

User mengubah filter
   ↓
1. Livewire update-filter event
   ↓
2. Panel@loadChartData() triggered
   ↓
3. Query AnalyticsAggregate table
   ↓
4. Return filtered data JSON
   ↓
5. Charts & cards re-render real-time
```

### Export Request Flow
```
1. User click "Export" button
   ↓
2. AnalyticsExportController@create() called
   ↓
3. Validate request + idempotency check
   ↓
4. Create AnalyticsExport record (status: pending)
   ↓
5. Queue ExportAnalyticsJob
   ↓
6. Return export ID untuk polling

Async Job Processing:
   ↓
1. ExportAnalyticsJob start
   ↓
2. Generate file (CSV/PDF)
   ↓
3. Update progress_percentage
   ↓
4. Save file ke storage disk
   ↓
5. Update status: completed
   ↓
6. User bisa download

Error Handling:
   ↓
1. Job fails → status: failed
   ↓
2. User bisa click "Retry"
   ↓
3. Exponential backoff + retry counter
```

---

## 5. 🛡️ Security & Access Control

### Authentication & Authorization
```
Middleware Chain:
1. auth              - User authenticated?
2. EnsureUserUpp     - User punya UPP assigned?
3. role:admin        - Role-based access (Superadmin/Admin Org)

Access Denied → Redirect to /unauthorized
```

### Rate Limiting
```
Export Rate Limit: 5 exports per user per day
IP-based fallback: 100 requests per minute
Queue size limit: 1000 concurrent jobs
```

### Data Security
```
- Tenant isolation via ScopeContext
- File encryption for PDF exports (optional)
- CORS headers untuk API endpoints
- CSRF token validation
- Input sanitization pada filters
```

### Audit Trail
```
AnalyticsExport model track:
- User ID (siapa yang export)
- Tenant ID (organisasi mana)
- Export type & format
- Status changes
- Error logs (jika ada)
```

---

## 6. ⚙️ Konfigurasi

**File**: `config/analytics.php`

```php
'storage_disk'      => 'exports',      // Disk untuk simpan file
'sync_threshold'    => 50000,          // Row limit sebelum async
'rate_limit'        => 5,              // Exports per user per day
'pdf_engine'        => 'dompdf',       // PDF generator
'cache_ttl'         => 3600,           // Cache duration (seconds)
'cleanup_days'      => 7,              // Delete files after X days
'queue_timeout'     => 300,            // Job timeout (seconds)
```

---

## 7. 🚀 Performance Optimization

### Pre-computed Metrics
```
AnalyticsAggregate Table
- Pre-calculated aggregate values
- Indexed on: periode_id, upp_id, aspek_id
- Updated via: Analytics refresh job (daily/on-demand)
- Query optimization: Select hanya needed columns
```

### Caching Strategy
```
Cache Key Pattern: analytics:period:{periode_id}:upp:{upp_id}
- Duration: 1 hour (configurable)
- Invalidate on: Data changes
- Fallback: Direct DB query
```

### Database Indexes
```
AnalyticsAggregate:
- INDEX (periode_id, upp_id)
- INDEX (aspek_id)
- INDEX (created_at) untuk pagination

AnalyticsExport:
- INDEX (user_id, tenant_id)
- INDEX (status)
- INDEX (created_at) untuk cleanup
```

---

## 8. 📱 User Interface Layout

### Dashboard Structure
```
┌─────────────────────────────────────────────┐
│  HEADER: "Analisis & Laporan"              │
├─────────────────────────────────────────────┤
│ [Periode ▼] [UPP Select ▼] [Export ▼]     │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │  Summary Cards (4 columns)          │   │
│  │  IPP Score | Submissions | Rate | ? │   │
│  └─────────────────────────────────────┘   │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │  Charts Grid (Responsive)           │   │
│  │  ┌──────────┬──────────┐            │   │
│  │  │ F02 Chart  │ F03 Chart  │            │
│  │  ├──────────┼──────────┤            │   │
│  │  │ IPP Chart  │ Aspek Chart │            │
│  │  └──────────┴──────────┘            │   │
│  └─────────────────────────────────────┘   │
├─────────────────────────────────────────────┤
│  Responsive Breakdown:                      │
│  - Desktop: 4 charts grid, 2x2             │
│  - Tablet: 2 charts per row                │
│  - Mobile: 1 chart per row (fullwidth)     │
└─────────────────────────────────────────────┘
```

---

## 9. 🔌 API Endpoints

### REST API Structure

#### GET /api/v1/analytics/summary
Ambil summary cards data
```json
Response: {
  "ipp_average": 85.5,
  "total_submissions": 245,
  "completion_rate": 92.3,
  "f03_responses": 450,
  "outstanding": 8
}
```

#### POST /api/v1/analytics/exports
Buat export request
```json
Request: {
  "type": "csv",
  "periode_id": 1,
  "upp_ids": [1, 2, 3],
  "include_details": true
}

Response: {
  "export_id": "uuid",
  "status": "pending",
  "created_at": "2026-04-20T10:30:00Z"
}
```

#### GET /api/v1/analytics/exports/{id}/status
Check export progress
```json
Response: {
  "status": "processing",
  "progress": 65,
  "message": "Processing 250 of 380 records"
}
```

---

## 10. 📋 Scheduled Reports

### Fitur
- Daily, weekly, monthly frequency
- Otomatis di-generate sesuai schedule
- Auto-email ke recipients list
- Attachments dalam format CSV/PDF

### Configuration
```php
// Create scheduled report
AnalyticsReportSchedule::create([
    'user_id' => auth()->id(),
    'frequency' => 'weekly',
    'day_of_week' => 'Monday',
    'recipients' => 'admin@org.com,director@org.com',
    'export_type' => 'pdf',
    'is_active' => true
]);
```

---

## 11. ⚠️ Limitasi & Catatan

### Current Limitations
1. **Data sync**: Aggregate table update hanya manual/scheduled, bukan real-time
2. **Export time**: Large dataset (>50K rows) bisa take 5-10 minutes
3. **Storage**: File disimpan 7 hari sebelum auto-deleted
4. **Concurrent exports**: Max 5 per user per hari

### Performance Considerations
- Chart rendering di-optimize dengan data aggregation
- Large dataset queries dipaginate (100 records per load)
- Mobile view memiliki simpler chart untuk save bandwidth

---

## 12. 🎬 Next Steps / Maintenance

### Recommended Monitoring
- Track export queue length
- Monitor API response time (target: <2 sec)
- Verify daily aggregate refresh completion
- Check storage disk usage (cleanup schedule)

### Potential Improvements
1. Real-time aggregate sync menggunakan events
2. Advanced filtering (multi-select aspek, indikator)
3. Custom report builder
4. Scheduled report templates
5. Dashboard widget customization

---

## 📚 Related Files

- **Controller**: `app/Http/Controllers/AnalyticsController.php`
- **Routes**: `routes/web.php` (analytics group)
- **Livewire**: `app/Livewire/Analytics/Panel.php`
- **Views**: `resources/views/analytics/index.blade.php`
- **Models**: `app/Models/Analytics*.php`
- **Config**: `config/analytics.php`
- **Jobs**: `app/Jobs/ExportAnalyticsJob.php`
- **Tests**: `tests/Feature/AnalyticsTest.php`

---

**End of Document**
