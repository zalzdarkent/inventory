
<?php
if (!isset($current_page)) $current_page = 'dashboard';
include __DIR__ . '/../../module/md_navbar.php';
$menus = get_navbar_menu();
?>

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.html" class="b-brand">
                <img src="assets/images/logo-full.png" alt="" class="logo logo-lg" />
                <img src="assets/images/logo-abbr.png" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Panel</label>
                </li>
                <?php foreach ($menus as $menu): ?>
                    <?php
                    $active = is_menu_active($current_page, $menu) ? ' active' : '';
                    $hasmenu = !empty($menu['children']) ? ' nxl-hasmenu' : '';
                    $show = (isset($menu['children']) && is_menu_active($current_page, $menu)) ? ' show' : '';
                    ?>
                    <li class="nxl-item<?= $hasmenu . $active . $show ?>">
                        <a href="<?= $menu['url'] ?>" class="nxl-link">
                            <span class="nxl-micon"><i class="<?= $menu['icon'] ?>"></i></span>
                            <span class="nxl-mtext"><?= htmlspecialchars($menu['title']) ?></span>
                            <?php if (!empty($menu['children'])): ?><span class="nxl-arrow"><i class="feather-chevron-right"></i></span><?php endif; ?>
                        </a>
                        <?php if (!empty($menu['children'])): ?>
                                <ul class="nxl-submenu"<?= is_menu_active($current_page, $menu) ? ' style="display:block;"' : '' ?> >
                                <?php foreach ($menu['children'] as $child): ?>
                                    <li class="nxl-item<?= is_submenu_active($current_page, $child) ? ' active' : '' ?>">
                                        <a class="nxl-link" href="<?= $child['url'] ?>"><?= htmlspecialchars($child['title']) ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>