<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php helper('auth'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    .dashboard-shell {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .welcome-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 20px;
        padding: 1.75rem 2rem;
        color: #ffffff;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.25);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .welcome-card::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, transparent 70%);
        pointer-events: none;
    }

    .welcome-title {
        font-size: 1.65rem;
        font-weight: 800;
        margin: 0 0 0.4rem;
        letter-spacing: -0.02em;
    }

    .welcome-subtitle {
        color: #94a3b8;
        font-size: 0.92rem;
        margin: 0;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1.25rem;
    }

    .metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.1);
    }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.85rem;
    }

    .metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .icon-forms { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .icon-asr   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .icon-sub   { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .icon-draft { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .icon-rev   { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .icon-app   { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .metric-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.03em;
    }

    .metric-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-top: 0.35rem;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 1.5rem;
    }

    @media (max-width: 1024px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    .chart-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .chart-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .chart-subtitle {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 0.2rem;
    }

    .chart-container {
        position: relative;
        flex: 1;
        min-height: 260px;
    }

    .progress-list {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
        margin-top: 0.5rem;
    }

    .progress-item-head {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.35rem;
    }

    .progress-bar-bg {
        height: 10px;
        background: #f1f5f9;
        border-radius: 99px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<div class="dashboard-shell">
    <!-- Welcome Header -->
    <div class="welcome-card">
        <div>
            <h2 class="welcome-title">Welcome to SMS Lab Dashboard</h2>
            <p class="welcome-subtitle">Validation metrics, form section tracking, and workflow status overview.</p>
        </div>
        <div style="text-align: right; flex-shrink: 0;">
            <div style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #10b981;">System Status</div>
            <div style="font-weight: 700; font-size: 0.95rem; margin-top: 2px;">Active & Operational</div>
        </div>
    </div>

    <!-- 6 Metric Cards -->
    <div class="metrics-grid">
        <!-- 1. Forms Created -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-forms">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($formsCount) ?></div>
            <div class="metric-label">Forms Created</div>
        </div>

        <!-- 2. ASR Created -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-asr">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($asrCount) ?></div>
            <div class="metric-label">ASRs Created</div>
        </div>

        <!-- 3. ASR Forms Submissions -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-sub">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($asrFormsCount) ?></div>
            <div class="metric-label">ASR Forms Created</div>
        </div>

        <!-- 4. ASR Forms in Draft -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-draft">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($draftCount) ?></div>
            <div class="metric-label">Forms in Draft</div>
        </div>

        <!-- 5. ASR Forms in Review -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-rev">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($reviewCount) ?></div>
            <div class="metric-label">Forms in Review</div>
        </div>

        <!-- 6. ASR Forms Approved -->
        <div class="metric-card">
            <div class="metric-header">
                <div class="metric-icon icon-app">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="metric-value"><?= number_format($approvedCount) ?></div>
            <div class="metric-label">Forms Approved</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Doughnut Chart: Workflow Status Breakdown -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Status Breakdown</h3>
                    <div class="chart-subtitle">Distribution of ASR section statuses</div>
                </div>
            </div>
            <div class="chart-container" style="display: flex; align-items: center; justify-content: center;">
                <canvas id="statusDoughnutChart" style="max-height: 250px;"></canvas>
            </div>
        </div>

        <!-- Bar Chart: System Metrics Overview -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">System Metrics Comparison</h3>
                    <div class="chart-subtitle">Total volume across forms, ASRs, and status states</div>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="metricsBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Workflow Stage Progress & Quick Actions -->
    <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem;">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Workflow Completion Progress</h3>
                    <div class="chart-subtitle">Section states transition ratio</div>
                </div>
            </div>
            <div class="progress-list">
                <?php
                    $totalSections = max(1, $asrFormsCount);
                    $draftPct = round(($draftCount / $totalSections) * 100);
                    $reviewPct = round(($reviewCount / $totalSections) * 100);
                    $approvedPct = round(($approvedCount / $totalSections) * 100);
                    $rejectedPct = round(($rejectedCount / $totalSections) * 100);
                ?>
                <div>
                    <div class="progress-item-head">
                        <span>✏️ Draft Sections</span>
                        <span><?= $draftCount ?> (<?= $draftPct ?>%)</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $draftPct ?>%; background: #f59e0b;"></div>
                    </div>
                </div>

                <div>
                    <div class="progress-item-head">
                        <span>📩 Pending Review</span>
                        <span><?= $reviewCount ?> (<?= $reviewPct ?>%)</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $reviewPct ?>%; background: #0284c7;"></div>
                    </div>
                </div>

                <div>
                    <div class="progress-item-head">
                        <span>✅ Approved Sections</span>
                        <span><?= $approvedCount ?> (<?= $approvedPct ?>%)</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?= $approvedPct ?>%; background: #10b981;"></div>
                    </div>
                </div>

                <?php if ($rejectedCount > 0): ?>
                    <div>
                        <div class="progress-item-head">
                            <span>❌ Rejected Sections</span>
                            <span><?= $rejectedCount ?> (<?= $rejectedPct ?>%)</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: <?= $rejectedPct ?>%; background: #ef4444;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chart-card" style="justify-content: space-between;">
            <div>
                <div class="chart-header">
                    <div>
                        <h3 class="chart-title">Quick Actions</h3>
                        <div class="chart-subtitle">Direct navigation shortcuts</div>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem;">
                    <a href="<?= base_url('asr-mapping') ?>" class="btn btn-primary" style="justify-content: center; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 700;">
                        🔖 Manage ASR Mapping Directory
                    </a>
                    <a href="<?= base_url('forms') ?>" class="btn btn-outline-secondary" style="justify-content: center; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 700; color: #0f172a; border-color: #cbd5e1;">
                        📋 Open Validation Forms
                    </a>
                    <?php if (has_permission('manage_users')): ?>
                        <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary" style="justify-content: center; padding: 0.75rem 1rem; border-radius: 10px; font-weight: 700; color: #0f172a; border-color: #cbd5e1;">
                            👥 Manage Users
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const draftVal = <?= (int) $draftCount ?>;
        const reviewVal = <?= (int) $reviewCount ?>;
        const approvedVal = <?= (int) $approvedCount ?>;
        const rejectedVal = <?= (int) $rejectedCount ?>;

        const formsVal = <?= (int) $formsCount ?>;
        const asrVal = <?= (int) $asrCount ?>;
        const asrFormsVal = <?= (int) $asrFormsCount ?>;

        // 1. Doughnut Chart
        const ctxDoughnut = document.getElementById('statusDoughnutChart');
        if (ctxDoughnut) {
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Draft', 'In Review', 'Approved', 'Rejected'],
                    datasets: [{
                        data: [draftVal, reviewVal, approvedVal, rejectedVal],
                        backgroundColor: ['#f59e0b', '#0284c7', '#10b981', '#ef4444'],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
                                padding: 16,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // 2. Bar Chart
        const ctxBar = document.getElementById('metricsBarChart');
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Forms', 'ASRs', 'Total Forms Created', 'Draft', 'In Review', 'Approved'],
                    datasets: [{
                        label: 'Count',
                        data: [formsVal, asrVal, asrFormsVal, draftVal, reviewVal, approvedVal],
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#8b5cf6',
                            '#f59e0b',
                            '#0284c7',
                            '#16a34a'
                        ],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' }, color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' }, color: '#64748b', precision: 0 }
                        }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

