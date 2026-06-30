<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>SMS Lab - <?= $this->renderSection('title') ?? 'Dashboard' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            background: #eef3f6;
            color: #172033;
            overflow-x: hidden;
        }

        /* ========== LAYOUT WITH SIDEBAR ========== */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR STYLES ========== */
        .sidebar {
            width: 280px;
            background: #0b1f2a;
            color: #e0e0e0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .logo-icon {
            background: #18a06b;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .logo-text {
            color: white;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.92rem;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .nav-link.active {
            background: rgba(40,150,114,0.2);
            border-left: 3px solid #289672;
            color: white;
        }

        .nav-icon {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            font-size: 0.78rem;
            font-weight: 700;
            width: 28px;
        }

        .nav-divider {
            padding: 0.75rem 1.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c8a9c;
            margin-top: 0.5rem;
        }

        /* ========== MAIN CONTENT AREA ========== */
        .main-wrapper {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ========== TOP HEADER ========== */
        .top-header {
            background: rgba(255,255,255,0.9);
            padding: 0.875rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 0 rgba(15,23,42,0.08);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: #1e4668;
            display: none;
        }

        .breadcrumb {
            font-size: 0.9rem;
            color: #5a6e7c;
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
            color: #5a6e7c;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            background: #f0f2f5;
        }

        .user-avatar {
            font-size: 1.1rem;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: radial-gradient(circle at top left, rgba(40,150,114,0.12), transparent 30rem), #f6f8fb;
        }

        /* ========== CENTERED LOGO BANNER ========== */
        .hero-banner {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .hero-banner h1 {
            font-size: 1.8rem;
            color: #1e4668;
            margin-bottom: 0.5rem;
        }

        .hero-banner .tagline {
            color: #289672;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        /* ========== METRICS CARDS ========== */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .metric-card h4 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #7f8c8d;
            margin-bottom: 0.5rem;
        }

        .metric-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e4668;
        }

        /* ========== PROTOCOL SECTION ========== */
        .protocol-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .protocol-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eef2f6;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .prep-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 0.75rem;
        }

        .step-item {
            background: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #1e4668;
            border-left: 3px solid #289672;
        }

        /* ========== DYNAMIC FORM STYLES ========== */
        .dynamic-form-container {
            width: min(1180px, 100%);
            /* margin: 0 auto; */
        }

        .form-title {
            font-size: 1.3rem;
            color: #1e4668;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #289672;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.85rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #289672;
            box-shadow: 0 0 0 2px rgba(40,150,114,0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.72rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            min-height: 42px;
        }

        .btn-primary {
            background: #153e5c;
            color: white;
        }

        .btn-primary:hover {
            background: #0b2b3b;
        }

        .btn-success {
            background: #289672;
            color: white;
        }

        .btn-success:hover {
            background: #1e6f5c;
        }

        .btn-ghost {
            background: white;
            color: #153e5c;
            border: 1px solid #d7e0e8;
        }

        .btn-ghost:hover {
            border-color: #9fb2c3;
            box-shadow: 0 8px 22px rgba(15,23,42,0.08);
        }

        .page-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .eyebrow,
        .section-kicker {
            color: #289672;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .page-heading h1 {
            color: #12263a;
            font-size: clamp(1.7rem, 3vw, 2.55rem);
            line-height: 1.1;
            margin-top: 0.2rem;
        }

        .page-subtitle {
            color: #607184;
            margin-top: 0.5rem;
            max-width: 46rem;
        }

        .alert {
            border-radius: 8px;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            border: 1px solid;
        }

        .alert-success {
            background: #ecfdf5;
            border-color: #b7e4cf;
            color: #126246;
        }

        .alert-error {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #9f1239;
        }

        .form-sections,
        .forms-grid {
            display: grid;
            gap: 1rem;
        }

        .section-panel,
        .form-card,
        .empty-state {
            background: white;
            border: 1px solid #dfe7ee;
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(15,23,42,0.06);
        }

        .section-panel {
            padding: 1.25rem;
        }

        .section-panel-header,
        .form-card-top,
        .forms-toolbar,
        .section-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .section-panel-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #e6edf3;
            margin-bottom: 1rem;
        }

        .section-panel-header h2,
        .empty-state h2,
        .form-card h2 {
            color: #12263a;
            font-size: 1.05rem;
            line-height: 1.3;
        }

        .section-count,
        .status-pill,
        .forms-total {
            background: #edf8f3;
            color: #15704e;
            border-radius: 999px;
            padding: 0.34rem 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            margin-bottom: 0;
        }

        .form-group span,
        .template-field span,
        .search-box span {
            color: #46596b;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .form-group input,
        .template-field input,
        .search-box input {
            width: 100%;
            min-height: 42px;
            padding: 0.68rem 0.75rem;
            border: 1px solid #cbd7e2;
            border-radius: 8px;
            background: #fbfdff;
            color: #172033;
            font-size: 0.92rem;
        }

        .form-group input:focus,
        .template-field input:focus,
        .search-box input:focus {
            outline: none;
            border-color: #289672;
            box-shadow: 0 0 0 3px rgba(40,150,114,0.14);
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

        .table-template {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e1e8ef;
            border-radius: 8px;
            background: #fbfdff;
        }

        .table-template table {
            width: 100%;
            min-width: 720px;
            border-collapse: separate;
            border-spacing: 0;
            color: #27384a;
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
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
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
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
