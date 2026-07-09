<!-- Side Navigation Bar -->
<?php helper('auth'); ?>
<nav class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon">SMS</span>
            <span class="logo-text">SMS Lab</span>
        </div>
    </div>
    
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= (url_is('dashboard*') || url_is('/')) ? 'active' : '' ?>">
                <span class="nav-icon">D</span>
                <span class="nav-title">Dashboard</span>
            </a>
        </li>
        
        <?php if (has_permission('view_forms')): ?>
            <li class="nav-item">
                <a href="<?= base_url('forms') ?>" class="nav-link <?= (url_is('forms*') || url_is('form*')) ? 'active' : '' ?>">
                    <span class="nav-icon">F</span>
                    <span class="nav-title">Forms</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (has_permission('manage_users') || has_permission('manage_roles')): ?>
            <li class="nav-divider">Administration</li>
            
            <?php if (has_permission('manage_users')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('users') ?>" class="nav-link <?= (url_is('users*')) ? 'active' : '' ?>">
                        <span class="nav-icon">U</span>
                        <span class="nav-title">Users</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (has_permission('manage_roles') || has_permission('manage_permissions')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('roles') ?>" class="nav-link <?= (url_is('roles*') || url_is('permissions*')) ? 'active' : '' ?>">
                        <span class="nav-icon">R</span>
                        <span class="nav-title">Roles & Perms</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</nav>
