<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>SMS Lab - <?= $this->renderSection('title') ?? 'Dashboard' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-900: #0f172a;
            --primary-800: #1e293b;
            --primary-700: #334155;
            --accent-emerald: #10b981;
            --accent-emerald-dark: #059669;
            --accent-emerald-light: #d1fae5;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ========== LAYOUT WITH SIDEBAR ========== */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR STYLES ========== */
        .sidebar {
            width: 260px;
            background: var(--primary-900);
            color: #e2e8f0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 1rem;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .logo-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }

        .logo-text {
            color: white;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 0.75rem;
            margin: 0;
        }

        .nav-item {
            margin: 0.35rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 10px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: white;
            transform: translateX(2px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.25));
            color: #10b981;
            font-weight: 600;
            border-left: 3px solid #10b981;
        }

        .nav-icon {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .nav-divider {
            padding: 1.25rem 1rem 0.5rem;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
        }

        /* ========== MAIN CONTENT AREA ========== */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ========== TOP HEADER ========== */
        .top-header {
            background: rgba(255,255,255,0.85);
            padding: 0.875rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: var(--primary-900);
            display: none;
            padding: 4px;
            border-radius: 6px;
        }

        .breadcrumb {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .breadcrumb .separator {
            margin: 0 8px;
            color: #cbd5e1;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-date {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 50%), radial-gradient(at 100% 0%, rgba(59, 130, 246, 0.04) 0px, transparent 50%), var(--bg-main);
        }

        /* ========== BUTTONS & CONTROLS ========== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.68rem 1.25rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            min-height: 42px;
            box-shadow: var(--shadow-sm);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.3);
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: white;
            color: var(--primary-900);
            border: 1px solid var(--border-color);
        }

        .btn-ghost:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .eyebrow,
        .section-kicker {
            color: var(--accent-emerald-dark);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .page-heading h1 {
            color: var(--text-main);
            font-size: clamp(1.6rem, 3vw, 2.25rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-top: 0.2rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            margin-top: 0.4rem;
            font-size: 0.95rem;
            max-width: 46rem;
        }

        /* ========== CARDS & SECTION PANELS ========== */
        .section-panel,
        .form-card,
        .empty-state {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .section-panel {
            padding: 1.5rem;
        }

        .section-panel:hover {
            box-shadow: 0 10px 30px -4px rgba(15, 23, 42, 0.08);
        }

        .section-panel-header {
            padding-bottom: 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 1.25rem;
        }

        .section-panel-header h2 {
            color: var(--text-main);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .section-count,
        .status-pill {
            background: var(--accent-emerald-light);
            color: var(--accent-emerald-dark);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .form-group input:focus,
        .template-field input:focus,
        .search-box input:focus {
            outline: none;
            border-color: var(--accent-emerald);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
            background: white;
        }

        .inline-template {
            color: #28394b;
            line-height: 2.8;
            overflow-wrap: anywhere;
        }

        .template-field {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin: 0 0.35rem 0.5rem;
            vertical-align: middle;
        }

        .template-field input {
            width: 9rem;
        }

        .template-field--textarea {
            vertical-align: top;
            display: inline-block;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .template-field--textarea textarea {
            width: 24rem;
            max-width: 100%;
            min-height: 6rem;
            padding: 0.68rem 0.75rem;
            border: 1px solid #cbd7e2;
            border-radius: 8px;
            background: #fbfdff;
            color: #172033;
            font-size: 0.92rem;
            font-family: inherit;
            resize: both;
            box-sizing: border-box;
        }

        .template-field--textarea textarea:focus {
            outline: none;
            border-color: #289672;
            box-shadow: 0 0 0 3px rgba(40,150,114,0.14);
            background: white;
        }

        /* ========== DYNAMIC FORM CONTAINER & OVERFLOW FIX ========== */
        .dynamic-form-container {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .form-sections {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .section-panel {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
        }

        .table-template,
        .repeatable-table {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            margin-bottom: 0.75rem;
        }

        .table-template table,
        .repeatable-table table {
            width: 100%;
            min-width: 720px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-template table thead tr,
        .repeatable-table table thead tr {
            background: #0f172a;
            color: #f8fafc;
        }

        .table-template table thead th,
        .repeatable-table table thead th {
            padding: 0.95rem 1.1rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #cbd5e1;
            border-bottom: 1px solid #1e293b;
        }

        .table-template table tbody td,
        .repeatable-table table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        /* ========== BOOTSTRAP OVERRIDES & STYLING ========== */
        .form-control, .form-select {
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            font-family: inherit;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background-color: #fbfdff;
            color: #0f172a;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus, .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18);
            background-color: #ffffff;
        }

        .form-check-input {
            width: 1.25em;
            height: 1.25em;
            margin-top: 0.15em;
            vertical-align: top;
            border: 1px solid #cbd5e1;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .table-template th,
        .table-template td {
            padding: 0.72rem;
            border-right: 1px solid #e1e8ef;
            border-bottom: 1px solid #e1e8ef;
            text-align: left;
            vertical-align: top;
        }

        .table-template th {
            background: #eef5f8;
            color: #1f3b52;
            font-size: 0.8rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .table-template tr:last-child td {
            border-bottom: 0;
        }

        .table-template th:last-child,
        .table-template td:last-child {
            border-right: 0;
        }

        .table-template .template-field {
            display: flex;
            margin: 0;
            min-width: 9rem;
        }

        .table-template .template-field span {
            display: none;
        }

        .table-template .template-field input {
            width: 100%;
            min-width: 8rem;
        }

        .section-actions {
            justify-content: flex-end;
            border-top: 1px solid #e6edf3;
            margin-top: 1.1rem;
            padding-top: 1rem;
        }

        .forms-toolbar {
            flex-wrap: wrap;
            background: white;
            border: 1px solid #dfe7ee;
            border-radius: 8px;
            padding: 1rem;
        }

        .search-box {
            display: flex;
            flex: 1 1 280px;
            flex-direction: column;
            gap: 0.45rem;
        }

        .forms-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .form-card {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            padding: 1rem;
        }

        .form-avatar {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #153e5c;
            color: white;
            border-radius: 8px;
            font-weight: 800;
        }

        .form-card p {
            color: #607184;
            min-height: 1.4rem;
        }

        .form-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            color: #526476;
            font-size: 0.82rem;
            margin-top: auto;
        }

        .form-card-meta span {
            background: #f4f7fa;
            border-radius: 999px;
            padding: 0.32rem 0.58rem;
        }

        .empty-state {
            padding: 2rem;
            text-align: center;
        }

        .empty-state p {
            color: #607184;
            margin-top: 0.35rem;
        }

        /* ========== FOOTER ========== */
        .main-footer {
            background: #0d2c3b;
            color: #cbd5e1;
            text-align: center;
            padding: 1rem;
            font-size: 0.75rem;
            margin-top: auto;
        }

        .footer-protocol {
            font-size: 0.7rem;
            margin-top: 0.25rem;
            opacity: 0.7;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .main-content {
                padding: 1rem;
            }

            .top-header {
                padding: 0.75rem 1rem;
            }

            .header-date,
            .user-name,
            .dropdown-icon {
                display: none;
            }

            .page-heading,
            .section-panel-header {
                flex-direction: column;
                align-items: stretch;
            }

            .page-heading .btn,
            .section-actions .btn {
                width: 100%;
            }

            .inline-template {
                line-height: 1.8;
            }

            .template-field {
                display: flex;
                align-items: stretch;
                flex-direction: column;
                margin: 0 0 0.75rem;
            }

            .template-field input {
                width: 100%;
            }
            
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* ========== PAGE LOADER ========== */
        #page-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(238, 243, 246, 0.75);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 5000;
        }

        #page-loader-overlay.active {
            display: flex;
        }

        .page-loader-spinner {
            width: 46px;
            height: 46px;
            border: 4px solid rgba(21, 62, 92, 0.15);
            border-top-color: #289672;
            border-radius: 50%;
            animation: page-loader-spin 0.7s linear infinite;
        }

        @keyframes page-loader-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
<div id="page-loader-overlay" aria-hidden="true">
    <div class="page-loader-spinner"></div>
</div>
<div class="app-container">
    <!-- Include Sidebar -->
    <?= view('components/sidebar') ?>
    
    <div class="main-wrapper">
        <!-- Include Header -->
        <?= view('components/header', ['breadcrumb' => $breadcrumb ?? null]) ?>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Hero Banner -->
            <!-- <div class="hero-banner">
                <h1>SMS Central Lab</h1>
                <div class="tagline">Precision • Accuracy • Reliability</div>
            </div> -->
            
            <!-- Metrics Cards (Dashboard) -->
            <?php if($this->renderSection('show_metrics') !== false): ?>
            <!-- <div class="metrics-grid">
                <div class="metric-card"><h4>Accuracy</h4><div class="metric-value">98.7%</div></div>
                <div class="metric-card"><h4>System Precision</h4><div class="metric-value">1.2% RSD</div></div>
                <div class="metric-card"><h4>Specificity</h4><div class="metric-value">Pass</div></div>
                <div class="metric-card"><h4>Intermediate Precision</h4><div class="metric-value">1.8%</div></div>
                <div class="metric-card"><h4>LOD</h4><div class="metric-value">0.05 µg/mL</div></div>
                <div class="metric-card"><h4>Linearity</h4><div class="metric-value">r² = 0.999</div></div>
                <div class="metric-card"><h4>Method Precision</h4><div class="metric-value">0.92%</div></div>
                <div class="metric-card"><h4>Solution Stability</h4><div class="metric-value">48h</div></div>
                <div class="metric-card"><h4>System Suitability</h4><div class="metric-value">✔️ Pass</div></div>
            </div> -->
            <?php endif; ?>
            
            <!-- Protocol Information -->
            <!-- <div class="protocol-card">
                <div class="protocol-header">
                    <span><strong>Product Name:</strong> Hydroxychloroquine Sulfate (HCQ)</span>
                    <span><strong>Protocol No.:</strong> VP-LCMS-247-00</span>
                    <span><strong>Page No.:</strong> 01 of 20</span>
                </div>
                <div style="font-weight:600; margin-bottom:12px;">📋 Analytical Method Validation Record of Analysis</div>
                <div class="prep-steps">
                    <div class="step-item">📌 Preparation of Standard stock Solution</div>
                    <div class="step-item">🧪 Preparation of Sample solutions</div>
                    <div class="step-item">🎯 Preparation of Accuracy level solutions</div>
                    <div class="step-item">🔬 Level-1 Solution (LOQ level)</div>
                    <div class="step-item">📊 Level-2 Solution (50% level)</div>
                    <div class="step-item">📈 Level-3 Solution (100% level)</div>
                    <div class="step-item">🚀 Level-4 Solution (200% level)</div>
                </div>
                <div style="margin-top:16px; font-size:0.75rem; display:flex; justify-content:space-between; border-top:1px solid #eef2f6; padding-top:12px;">
                    <span><strong>Issued by (Sign & Date):</strong> Signature / Name ________</span>
                    <span><strong>Date:</strong> dd-mm-yyyy</span>
                </div>
            </div> -->
            
            <!-- Dynamic Content Section (Your Forms Will Go Here) -->
             <div class="dynamic-form-container">
                <?= $this->renderSection('content') ?>
            </div> 
        </main>
        
        <!-- Include Footer -->
        <?= view('components/footer') ?>
    </div>
</div>

<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar')?.classList.toggle('open');
    });

    // Global page loader: shown on real link navigations and form submits,
    // hidden again automatically when the new page loads (or on bfcache restore).
    (function() {
        const overlay = document.getElementById('page-loader-overlay');
        if (!overlay) return;

        const showLoader = () => overlay.classList.add('active');
        const hideLoader = () => overlay.classList.remove('active');

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            const isHash = !href || href.startsWith('#');
            const isJsLink = href.startsWith('javascript:');
            const opensNewTab = link.target && link.target !== '_self';
            const isModified = e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;

            if (!isHash && !isJsLink && !opensNewTab && !isModified) {
                showLoader();
            }
        });

        document.addEventListener('submit', function(e) {
            if (!e.defaultPrevented) {
                showLoader();
            }
        }, true);

        window.addEventListener('pageshow', hideLoader);
        window.addEventListener('pagehide', hideLoader);
    })();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
