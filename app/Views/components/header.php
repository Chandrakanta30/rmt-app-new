<!-- Top Header Component -->
<?php helper('auth'); $user = current_user(); ?>
<style>
    .user-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 5px 12px;
        border-radius: 20px;
        background: #f0f2f5;
        transition: background 0.2s;
        outline: none;
    }
    .user-dropdown:hover, .user-dropdown:focus {
        background: #e2e8f0;
    }
    .user-dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        min-width: 140px;
        z-index: 1000;
        overflow: hidden;
    }
    .user-dropdown:focus-within .user-dropdown-menu {
        display: block;
    }
    .dropdown-item {
        display: block;
        padding: 0.6rem 1rem;
        color: #1e4668;
        text-decoration: none;
        font-size: 0.85rem;
        transition: background 0.2s;
        text-align: left;
    }
    .dropdown-item:hover {
        background: #f8fafc;
        color: #289672;
    }
</style>
<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <span>☰</span>
        </button>
        <div class="breadcrumb">
            <span>SMS Central Lab</span>
            <span class="separator">/</span>
            <span><?= esc($breadcrumb ?? 'Dashboard') ?></span>
        </div>
    </div>
    <div class="header-right">
        <div class="header-date">
            <?= date('d-m-Y') ?>
        </div>
        <?php if ($user): ?>
            <div class="user-dropdown" tabindex="0">
                <span class="user-avatar" style="background: #289672; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 600; font-size: 0.85rem; margin-right: 0;">
                    <?= esc(substr($user['name'], 0, 1)) ?>
                </span>
                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 1px; margin-right: 2px;">
                    <span class="user-name" style="line-height: 1; font-weight: 600; font-size: 0.82rem; color: #0b1f2a;"><?= esc($user['name']) ?></span>
                    <span style="font-size: 0.7rem; color: #7f8c8d; font-weight: 500;"><?= esc($user['role_name']) ?></span>
                </div>
                <span class="dropdown-icon" style="font-size: 0.7rem; margin-left: 2px;">▼</span>
                <div class="user-dropdown-menu">
                    <a href="<?= base_url('logout') ?>" class="dropdown-item">Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
