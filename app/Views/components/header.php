<!-- Top Header Component -->
<?php helper('auth'); $user = current_user(); ?>
<style>
    .user-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 5px 14px 5px 6px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }
    .user-dropdown:hover, .user-dropdown:focus {
        background: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .user-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12);
        min-width: 160px;
        z-index: 1000;
        overflow: hidden;
    }
    .user-dropdown:focus-within .user-dropdown-menu {
        display: block;
    }
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.7rem 1.1rem;
        color: #0f172a;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
        text-align: left;
    }
    .dropdown-item:hover {
        background: #f1f5f9;
        color: #10b981;
    }
</style>
<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
            <span>☰</span>
        </button>
        <div class="breadcrumb">
            <span>SMS Central Lab</span>
            <span class="separator">/</span>
            <span style="color: #0f172a; font-weight: 600;"><?= esc($breadcrumb ?? 'Dashboard') ?></span>
        </div>
    </div>
    <div class="header-right">
        <div class="header-date">
            📅 <?= date('d-m-Y') ?>
        </div>
        <?php if ($user): ?>
            <div class="user-dropdown" tabindex="0">
                <span class="user-avatar" style="background: linear-gradient(135deg, #10b981, #059669); color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);">
                    <?= esc(substr($user['name'], 0, 1)) ?>
                </span>
                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 1px; margin-right: 2px;">
                    <span class="user-name" style="line-height: 1.1; font-weight: 700; font-size: 0.85rem; color: #0f172a;"><?= esc($user['name']) ?></span>
                    <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;"><?= esc($user['role_name']) ?></span>
                </div>
                <span class="dropdown-icon" style="font-size: 0.65rem; color: #64748b; margin-left: 2px;">▼</span>
                <div class="user-dropdown-menu">
                    <a href="<?= base_url('logout') ?>" class="dropdown-item">🚪 Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
