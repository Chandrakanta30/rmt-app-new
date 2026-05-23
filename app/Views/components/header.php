<!-- Top Header Component -->
<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <span>☰</span>
        </button>
        <div class="breadcrumb">
            <span>SMS Central Lab</span>
            <span class="separator">/</span>
            <span><?= $breadcrumb ?? 'Dashboard' ?></span>
        </div>
    </div>
    <div class="header-right">
        <div class="header-date">
            <?= date('d-m-Y') ?>
        </div>
        <div class="user-dropdown">
            <span class="user-avatar">A</span>
            <span class="user-name">Analyst</span>
            <span class="dropdown-icon">▼</span>
        </div>
    </div>
</header>
