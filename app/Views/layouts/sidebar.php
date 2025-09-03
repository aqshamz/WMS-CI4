<?php 
$sidebar = session('sidebar');
$currentUrl = uri_string(); // e.g. "uom", "user/role"
?>

<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white">
    <ul class="nav nav-pills flex-column mb-auto">
        <?php if (isset($sidebar)): ?>
            <?php foreach ($sidebar as $menu): ?>
                <?php 
                    // check if any submenu active
                    $hasActiveSubmenu = false;
                    if (!empty($menu['submenus'])) {
                        foreach ($menu['submenus'] as $submenu) {
                            $submenuUrl = ltrim($submenu['sub_menu_url'], '/');
                            if ($submenuUrl === $currentUrl) {
                                $hasActiveSubmenu = true;
                                break;
                            }
                        }
                    }
                ?>

                <li class="nav-item mb-1">

                    <a href="<?= base_url('/') ?>" class="nav-link d-flex align-items-center <?= ($currentUrl === '') ? 'active' : 'text-white' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="link-text">Dashboard</span>
                    </a>

                    
                    <?php if (!empty($menu['submenus'])): ?>
                        <!-- Parent Menu -->
                        <a class="nav-link d-flex align-items-center <?= $hasActiveSubmenu ? 'active' : 'text-white' ?>" 
                           data-bs-toggle="collapse" 
                           href="#menu-<?= $menu['menu_id'] ?>" 
                           role="button" 
                           aria-expanded="<?= $hasActiveSubmenu ? 'true' : 'false' ?>">
                            <?= $menu['menu_icon'] ?>
                            <span class="link-text"><?= esc($menu['menu_name']) ?></span>
                        </a>

                        <!-- Submenus -->
                        <div class="collapse ps-3 <?= $hasActiveSubmenu ? 'show' : '' ?>" id="menu-<?= $menu['menu_id'] ?>">
                            <ul class="nav flex-column">
                                <?php foreach ($menu['submenus'] as $submenu): ?>
                                    <li>
                                        <a href="<?= base_url($submenu['sub_menu_url']) ?>" 
                                           class="nav-link d-flex align-items-center <?= (ltrim($submenu['sub_menu_url'], '/') === $currentUrl) ? 'active' : 'text-white-50' ?>">
                                            <?= $submenu['sub_menu_icon'] ?>
                                            <span class="link-text"><?= esc($submenu['sub_menu_name']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Single Menu -->
                        <a href="<?= base_url($menu['menu_url'] ?? '#') ?>" 
                           class="nav-link d-flex align-items-center <?= (($menu['menu_url'] ?? '') === $currentUrl) ? 'active' : 'text-white' ?>">
                            <?= $menu['menu_icon'] ?>
                            <span class="link-text"><?= esc($menu['menu_name']) ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
