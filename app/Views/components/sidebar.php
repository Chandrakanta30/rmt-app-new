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
            <a href="<?= base_url('dashboard') ?>"
                class="nav-link <?= (url_is('dashboard*') || url_is('/')) ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                </span>
                <span class="nav-title">Dashboard</span>
            </a>
        </li>

        <?php if (has_permission('view_forms')): ?>
            <li class="nav-item">
                <a href="<?= base_url('forms') ?>"
                    class="nav-link <?= (url_is('forms*') || url_is('form*')) ? 'active' : '' ?>">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="M8 13h8M8 17h8"></path></svg>
                    </span>
                    <span class="nav-title">Forms</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (has_permission('create_asrno')): ?>
            <li class="nav-item">
                <a href="<?= base_url('asr-mapping') ?>"
                    class="nav-link <?= (url_is('asr-mapping*')) ? 'active' : '' ?>">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    </span>
                    <span class="nav-title">ASR No.</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (has_permission('manage_users') || has_permission('manage_roles')): ?>
            <li class="nav-divider">Administration</li>

            <?php if (has_permission('manage_users')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('users') ?>" class="nav-link <?= (url_is('users*')) ? 'active' : '' ?>">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </span>
                        <span class="nav-title">Users</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (has_permission('manage_roles') || has_permission('manage_permissions')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('roles') ?>"
                        class="nav-link <?= (url_is('roles*') || url_is('permissions*')) ? 'active' : '' ?>">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3z"></path><path d="m9 12 2 2 4-4"></path></svg>
                        </span>
                        <span class="nav-title">Roles & Perms</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</nav>
