<aside class="app-sidebar bg-body-secondary shadow">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>index.php" class="brand-link">
            <img src="<?= $settings['system_logo'] ?>" alt="Logo" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light"><?= $settings['system_name'] ?></span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <?php
                function buildMenu($pdo, $parentId = 0, $userRole, $currentUrl)
                {
                    // Fetch pages visible to this role
                    $sql = "
                        SELECT p.* FROM sys_pages p
                        JOIN role_access ra ON p.id = ra.page_id
                        WHERE p.parent_id = ? AND ra.role_key = ?
                        ORDER BY p.sort_order ASC
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$parentId, $userRole]);
                    $items = $stmt->fetchAll();

                    foreach ($items as $item) {
                        // Check children
                        $childStmt = $pdo->prepare("SELECT COUNT(*) FROM sys_pages WHERE parent_id = ?");
                        $childStmt->execute([$item['id']]);
                        $hasChildren = $childStmt->fetchColumn() > 0;

                        // Check Active State (Logic: Is current URL this page OR a child of this page?)
                        // Simplify: Check if URL matches
                        $isActive = (strpos($currentUrl, $item['page_url']) !== false && $item['page_url'] !== '#');
                        $menuOpen = $isActive ? 'menu-open' : '';
                        $activeClass = $isActive ? 'active' : '';

                        echo '<li class="nav-item ' . $menuOpen . '">';
                        echo '<a href="' . ($hasChildren ? '#' : BASE_URL . $item['page_url']) . '" class="nav-link ' . $activeClass . '">';
                        echo '<i class="nav-icon ' . $item['icon_class'] . '"></i>';
                        echo '<p>' . htmlspecialchars($item['page_name']);
                        if ($hasChildren) {
                            echo '<i class="nav-arrow bi bi-chevron-right"></i>';
                        }
                        echo '</p></a>';

                        if ($hasChildren) {
                            echo '<ul class="nav nav-treeview">';
                            buildMenu($pdo, $item['id'], $userRole, $currentUrl);
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }

                // Get current relative URL for highlighting
                $cur = substr($_SERVER['SCRIPT_NAME'], strlen('/universal/'));
                buildMenu($pdo, 0, $_SESSION['role'], $cur);
                ?>

            </ul>
        </nav>
    </div>
</aside>