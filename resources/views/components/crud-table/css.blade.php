{{-- Shared CRUD Table Styles --}}
@php
    $prefix = $prefix ?? 'crud';
    $customCss = $customCss ?? '';
@endphp

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Container */
    .{{ $prefix }}-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0;
    }

    /* Header */
    .{{ $prefix }}-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .{{ $prefix }}-title {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
    }

    .{{ $prefix }}-subtitle {
        font-size: 14px;
        color: #64748b;
        margin-top: 4px;
    }

    /* Buttons */
    .{{ $prefix }}-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .{{ $prefix }}-btn-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    .{{ $prefix }}-btn-primary:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transform: translateY(-1px);
    }

    .{{ $prefix }}-btn-secondary {
        background: white;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .{{ $prefix }}-btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .{{ $prefix }}-btn-icon {
        padding: 8px;
        border-radius: 6px;
        background: transparent;
        border: none;
        cursor: pointer;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .{{ $prefix }}-btn-icon:hover {
        background: #f1f5f9;
        color: #2563eb;
    }

    .{{ $prefix }}-btn-icon.btn-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Stats Cards */
    .{{ $prefix }}-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .{{ $prefix }}-stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .{{ $prefix }}-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .{{ $prefix }}-stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .{{ $prefix }}-stat-label {
        font-size: 14px;
        color: #64748b;
    }

    /* Table Card */
    .{{ $prefix }}-table-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .{{ $prefix }}-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 16px;
    }

    .{{ $prefix }}-table-title {
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
    }

    .{{ $prefix }}-table-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Search */
    .{{ $prefix }}-search-box {
        position: relative;
    }

    .{{ $prefix }}-search-box input {
        width: 280px;
        padding: 10px 16px 10px 40px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: #f8fafc;
    }

    .{{ $prefix }}-search-box input:focus {
        outline: none;
        border-color: #2563eb;
        background: white;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .{{ $prefix }}-search-box svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        width: 18px;
        height: 18px;
    }

    /* Table */
    .{{ $prefix }}-table-wrapper {
        overflow-x: auto;
    }

    .{{ $prefix }}-table {
        width: 100%;
        border-collapse: collapse;
    }

    .{{ $prefix }}-table thead {
        background: #f8fafc;
    }

    .{{ $prefix }}-table th {
        padding: 14px 20px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }

    .{{ $prefix }}-table td {
        padding: 16px 20px;
        font-size: 14px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .{{ $prefix }}-table tbody tr:hover {
        background: #f8fafc;
    }

    .{{ $prefix }}-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge */
    .{{ $prefix }}-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .{{ $prefix }}-badge-active {
        background: #dcfce7;
        color: #16a34a;
    }

    .{{ $prefix }}-badge-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .{{ $prefix }}-badge-success {
        background: #dcfce7;
        color: #16a34a;
    }

    .{{ $prefix }}-badge-secondary {
        background: #f1f5f9;
        color: #64748b;
    }

    .{{ $prefix }}-badge-primary {
        background: #dbeafe;
        color: #0284c7;
    }

    .{{ $prefix }}-badge-light {
        background: #f3f4f6;
        color: #4b5563;
    }

    .{{ $prefix }}-badge-info {
        background: #dbeafe;
        color: #0284c7;
    }

    .{{ $prefix }}-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Actions */
    .{{ $prefix }}-actions-cell {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pagination */
    .{{ $prefix }}-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 16px;
    }

    .pagination-info {
        font-size: 14px;
        color: #64748b;
    }

    .pagination {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .pagination button {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        font-size: 14px;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination button:hover:not(:disabled) {
        background: #f8fafc;
        border-color: #2563eb;
        color: #2563eb;
    }

    .pagination button.active {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
    }

    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Modal */
    .{{ $prefix }}-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .{{ $prefix }}-modal-overlay.active {
        display: flex;
    }

    .{{ $prefix }}-modal {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        animation: {{ $prefix }}ModalIn 0.3s ease;
    }

    @keyframes {{ $prefix }}ModalIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .{{ $prefix }}-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .{{ $prefix }}-modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
    }

    .{{ $prefix }}-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        background: #f1f5f9;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .{{ $prefix }}-modal-close:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .{{ $prefix }}-modal-body {
        padding: 24px;
        overflow-y: auto;
        max-height: calc(90vh - 180px);
    }

    .{{ $prefix }}-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 24px;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    /* Form */
    .{{ $prefix }}-form-group {
        margin-bottom: 20px;
    }

    .{{ $prefix }}-form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .{{ $prefix }}-form-label .required {
        color: #dc2626;
    }

    .{{ $prefix }}-form-input,
    .{{ $prefix }}-form-select,
    .{{ $prefix }}-form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: white;
    }

    .{{ $prefix }}-form-input:focus,
    .{{ $prefix }}-form-select:focus,
    .{{ $prefix }}-form-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .{{ $prefix }}-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Detail View */
    .{{ $prefix }}-detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .{{ $prefix }}-detail-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .{{ $prefix }}-detail-value {
        font-size: 15px;
        color: #0f172a;
        font-weight: 500;
    }

    /* Delete Modal */
    .{{ $prefix }}-delete-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #fef2f2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .{{ $prefix }}-delete-message {
        text-align: center;
    }

    .{{ $prefix }}-delete-message h4 {
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .{{ $prefix }}-delete-message p {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .{{ $prefix }}-delete-message .error-info {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 12px;
        text-align: left;
        font-size: 14px;
        color: #991b1b;
        margin-top: 12px;
    }

    .{{ $prefix }}-btn-danger {
        background: #dc2626;
        color: white;
    }

    .{{ $prefix }}-btn-danger:hover {
        background: #b91c1c;
    }

    /* Toast */
    .{{ $prefix }}-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0f172a;
        color: white;
        padding: 16px 24px;
        border-radius: 10px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 2000;
    }

    .{{ $prefix }}-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .{{ $prefix }}-toast.success { border-left: 4px solid #16a34a; }
    .{{ $prefix }}-toast.error { border-left: 4px solid #dc2626; }

    /* Responsive */
    @media (max-width: 768px) {
        .{{ $prefix }}-form-row {
            grid-template-columns: 1fr;
        }

        .{{ $prefix }}-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .{{ $prefix }}-table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .{{ $prefix }}-search-box input {
            width: 100%;
        }

        .{{ $prefix }}-stats-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    {{ $customCss }}
</style>
