<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login');
    exit;
}
if (!isset($pdo)) {
    require_once __DIR__ . '/../db_connect.php';
}
$sidebar_unread = 0;
try {
    $sidebar_unread = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
} catch (Exception $e) {};
$page_title = $page_title ?? 'Admin';
$active_page = $active_page ?? '';
$get_msg = $_GET['msg'] ?? '';
$get_error = $_GET['error'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Mtaita Tech Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/img/jj.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <!-- Sidebar -->
    <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard">
            <img src="/assets/img/jj.png" alt="" height="32" class="me-2">
            <span class="sidebar-brand-text">Mtaita Tech</span>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <a class="nav-link" href="dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item<?= $active_page === 'users' ? ' active' : '' ?>">
            <a class="nav-link" href="manage-users">
                <i class="fas fa-fw fa-users"></i>
                <span>Users</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Content</div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProjects" aria-expanded="true" aria-controls="collapseProjects">
                <i class="fas fa-fw fa-folder"></i>
                <span>Projects</span>
            </a>
            <div id="collapseProjects" class="collapse" aria-labelledby="headingProjects" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Projects</h6>
                    <a class="collapse-item" href="projects">All Projects</a>
                    <a class="collapse-item" href="add_project">Add Project</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseServices" aria-expanded="true" aria-controls="collapseServices">
                <i class="fas fa-fw fa-th-large"></i>
                <span>Services</span>
            </a>
            <div id="collapseServices" class="collapse" aria-labelledby="headingServices" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Services</h6>
                    <a class="collapse-item" href="manage-services">Manage Services</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCourses" aria-expanded="true" aria-controls="collapseCourses">
                <i class="fas fa-fw fa-graduation-cap"></i>
                <span>Courses</span>
            </a>
            <div id="collapseCourses" class="collapse" aria-labelledby="headingCourses" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Courses</h6>
                    <a class="collapse-item" href="manage-courses">Manage Courses</a>
                    <a class="collapse-item" href="course_builder">Course Builder</a>
                    <a class="collapse-item" href="manage-lessons">Lessons</a>
                    <a class="collapse-item" href="manage-enrollments">Enrollments</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseMedia" aria-expanded="true" aria-controls="collapseMedia">
                <i class="fas fa-fw fa-images"></i>
                <span>Media</span>
            </a>
            <div id="collapseMedia" class="collapse" aria-labelledby="headingMedia" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Media</h6>
                    <a class="collapse-item" href="posters">Posters</a>
                    <a class="collapse-item" href="thumbnails">Thumbnails</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCommerce" aria-expanded="true" aria-controls="collapseCommerce">
                <i class="fas fa-fw fa-shopping-cart"></i>
                <span>Commerce</span>
            </a>
            <div id="collapseCommerce" class="collapse" aria-labelledby="headingCommerce" data-bs-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Commerce</h6>
                    <a class="collapse-item" href="products">Products</a>
                    <a class="collapse-item" href="orders">Orders</a>
                    <a class="collapse-item" href="payments">Payments</a>
                </div>
            </div>
        </li>

        <li class="nav-item<?= $active_page === 'announcements' ? ' active' : '' ?>">
            <a class="nav-link" href="announcements">
                <i class="fas fa-fw fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>

        <li class="nav-item<?= $active_page === 'news' ? ' active' : '' ?>">
            <a class="nav-link" href="news">
                <i class="fas fa-fw fa-newspaper"></i>
                <span>News & Updates</span>
            </a>
        </li>

        <li class="nav-item<?= $active_page === 'partners' ? ' active' : '' ?>">
            <a class="nav-link" href="partners">
                <i class="fas fa-fw fa-handshake"></i>
                <span>Partners</span>
            </a>
        </li>

        <li class="nav-item<?= $active_page === 'testimonials' ? ' active' : '' ?>">
            <a class="nav-link" href="testimonials">
                <i class="fas fa-fw fa-star"></i>
                <span>Testimonials</span>
            </a>
        </li>

        <li class="nav-item<?= $active_page === 'solutions' ? ' active' : '' ?>">
            <a class="nav-link" href="solutions">
                <i class="fas fa-fw fa-cubes"></i>
                <span>Solutions</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item<?= $active_page === 'contacts' ? ' active' : '' ?><?= $sidebar_unread > 0 ? ' msg-unread' : '' ?>">
            <a class="nav-link" href="contacts">
                <i class="fas fa-fw fa-envelope"></i>
                <span>Messages</span>
                <?php if ($sidebar_unread > 0): ?><span class="badge badge-danger badge-counter"><?= $sidebar_unread ?></span><?php endif; ?>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item<?= $active_page === 'notifications' ? ' active' : '' ?>">
            <a class="nav-link" href="send-notification">
                <i class="fas fa-fw fa-bell"></i>
                <span>Send Notification</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item<?= $active_page === 'settings' ? ' active' : '' ?>">
            <a class="nav-link" href="settings">
                <i class="fas fa-fw fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">

        <div class="version">Version 1.0</div>

        <div class="sidebar-footer d-flex align-items-center justify-content-between px-3 py-3 mt-auto">
            <a class="text-white-50 small" href="../index"><i class="fas fa-arrow-left me-1"></i> Back to Site</a>
            <a class="text-white-50 small" href="logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </ul>
    <!-- End Sidebar -->

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                    <i class="fa fa-bars"></i>
                </button>

                <div class="d-none d-sm-inline-block fw-semibold text-white-50 small">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?>
                </div>

                <form class="d-none d-sm-inline-block me-auto ms-3" action="search" method="GET" style="max-width:320px;width:100%;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control bg-light border-0 small" placeholder="Search projects, messages, news..." aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button class="btn btn-cyan" type="submit"><i class="fas fa-search fa-sm"></i></button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto">

                    <!-- Alerts -->
                    <li class="nav-item dropdown no-arrow mx-1 notif-bell<?= $sidebar_unread > 0 ? ' has-unread' : '' ?>">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw bell-icon"></i>
                            <!-- Notification dot always present, pulses when unread -->
                            <span class="badge badge-danger badge-counter"><?= $sidebar_unread ?: '' ?></span>
                        </a>
                        <div class="dropdown-list dropdown-menu shadow animated--grow-in dropdown-menu-center" aria-labelledby="alertsDropdown">
                            <h6 class="dropdown-header">Alerts Center</h6>
                            <a class="dropdown-item d-flex align-items-center" href="contacts">
                                <div class="me-3"><div class="icon-circle bg-primary"><i class="fas fa-envelope text-white"></i></div></div>
                                <div><div class="small text-gray-500">New</div><span class="font-weight-bold"><?= $sidebar_unread ?> unread message(s)</span></div>
                            </a>
                            <a class="dropdown-item text-center small text-gray-500" href="contacts">Show All Alerts</a>
                        </div>
                    </li>

                    <!-- User -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php
                            $avatar_img = '';
                            if (isset($_SESSION['admin_id'])) {
                                try {
                                    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                                    $stmt->execute([$_SESSION['admin_id']]);
                                    $av = $stmt->fetchColumn();
                                    if ($av) $avatar_img = '/assets/img/uploads/avatars/' . rawurlencode($av);
                                } catch (Exception $e) {}
                            }
                            ?>
                            <?php if ($avatar_img): ?>
                                <img src="<?= $avatar_img ?>" alt="" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
                            <?php endif; ?>
                            <span class="me-2 d-none d-lg-inline text-white small"><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="profile"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- End Topbar -->

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-gray-800"><?= htmlspecialchars($page_title) ?></h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($page_title) ?></li>
                    </ol>
                </div>

<?php if ($get_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($get_msg) ?></div><?php endif; ?>
<?php if ($get_error): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($get_error) ?></div><?php endif; ?>
