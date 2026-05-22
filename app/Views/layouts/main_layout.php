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
            background: #f0f2f5;
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
            background: linear-gradient(180deg, #0b2b3b 0%, #0a1e2a 100%);
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
            background: rgba(255,255,255,0.15);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.2rem;
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
            font-size: 0.9rem;
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
            font-size: 1.2rem;
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
            background: white;
            padding: 0.875rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
            background: #f8fafc;
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
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
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
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1e4668;
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
            <!-- <div class="dynamic-form-container">
                <?= $this->renderSection('content') ?>
            </div> -->
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