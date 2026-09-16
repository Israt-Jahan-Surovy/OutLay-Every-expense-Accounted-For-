<?php
require_once __DIR__ . '/../../controllers/adminCommon.php';
requireAdminRole();

function ae(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function money($value): string { return '৳' . number_format((float)$value, 2); }
function adminPageStart(string $title, string $active='dashboard'): void {
    $flash = adminConsumeFlash();
    $name = $_SESSION['user_name'] ?? 'Admin';
    // Nav matches the approved design exactly: Dashboard, Expenses, Budget,
    // Categories, Users, Reports, My profile. Log Out is a separate button
    // pinned to the bottom of the sidebar, not part of this list.
    $items = [
        'dashboard'=>['adminDashboard.php','Dashboard'],
        'expenses'=>['expenses.php','Expenses'],
        'budgets'=>['budgets.php','Budget'],
        'categories'=>['categories.php','Categories'],
        'users'=>['users.php','Users'],
        'reports'=>['reports.php','Reports'],
        'profile'=>['profile.php','My profile'],
    ];
    ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=ae($title)?> | Expense Tracker</title><link rel="stylesheet" href="../css/admin.css"></head><body>
<div class="admin-shell">
<aside class="admin-sidebar" id="adminSidebar">
    <div class="brand">
    <div class="brand-text">
        <span class="brand-title">OutLay</span>
        <span class="brand-tagline">Every Expense, Accounted For</span>
    </div>
    <button class="hamburger" aria-hidden="true">&#9776;</button>
</div>
    <nav class="admin-nav">
    <?php foreach($items as $key=>$item): ?><a class="nav-item <?=$active===$key?'active':''?>" href="<?=ae($item[0])?>"><?=ae($item[1])?></a><?php endforeach; ?>
    </nav>
    <div class="sidebar-foot">
        <button class="btn-logout" type="button" data-open-logout>Log Out</button>
    </div>
</aside>
<main class="admin-main">
<header class="admin-topbar"><button class="mobile-menu-btn" id="sidebarToggle" type="button" aria-label="Menu">&#9776;</button><div class="topbar-user"><span class="avatar"><?=ae(strtoupper(substr($name,0,1)))?></span><span><?=ae($name)?></span></div></header>
<div class="page-content">
<?php if($flash): ?><div class="toast <?=$flash['type']==='success'?'toast-success':'toast-error'?>"><?=ae($flash['message'])?></div><?php endif; ?>
<?php
}
function adminPageEnd(): void { ?>
</div></main></div>
<div class="modal-backdrop" id="logoutModal" hidden><div class="confirm-modal"><div class="warning-circle">!</div><h2>Confirm Logout</h2><p>Are you sure you want to log out?</p><div class="modal-actions"><button class="btn btn-outline" data-close-logout>Cancel</button><a class="btn btn-danger" href="../../controllers/logoutcontroller.php">Logout</a></div></div></div>
<script src="../js/admin.js"></script></body></html>
<?php }
?>
