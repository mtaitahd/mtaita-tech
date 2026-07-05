<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$active_page = $active_page ?? '';
$get_msg = $_GET['msg'] ?? '';
$get_error = $_GET['error'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Mtaita Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin/assets/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/img/jj.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard">
            <img src="/assets/img/jj.png" alt="" height="32" class="me-2">
            <span class="sidebar-brand-text">Mtaita Tech</span>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <a class="nav-link" href="dashboard"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <hr class="sidebar-divider">
        <li class="nav-item <?= $active_page === 'my-courses' ? 'active' : '' ?>">
            <a class="nav-link" href="my-courses"><i class="fas fa-fw fa-book"></i><span>My Courses</span></a>
        </li>
        <li class="nav-item <?= $active_page === 'profile' ? 'active' : '' ?>">
            <a class="nav-link" href="profile"><i class="fas fa-fw fa-user"></i><span>My Profile</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="courses"><i class="fas fa-fw fa-search"></i><span>Browse Courses</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="digital_products"><i class="fas fa-fw fa-box"></i><span>Digital Products</span></a>
        </li>
        <hr class="sidebar-divider">
        <li class="nav-item">
            <a class="nav-link" href="logout"><i class="fas fa-fw fa-sign-out-alt"></i><span>Logout</span></a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="d-none d-sm-inline-block fw-semibold text-white-50 small">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['public_user_name'] ?? 'Learner') ?>
                </div>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="me-2 d-none d-lg-inline text-white small"><?= htmlspecialchars($_SESSION['public_user_name'] ?? 'Learner') ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                            <a class="dropdown-item" href="profile"><i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a>
                            <a class="dropdown-item" href="my-courses"><i class="fas fa-book fa-sm fa-fw me-2 text-gray-400"></i> My Courses</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-gray-800"><?= htmlspecialchars($page_title) ?></h4>
                </div>

<?php if ($get_msg): ?><div class="alert alert-success d-none swal-msg" data-type="success"><?= htmlspecialchars($get_msg) ?></div><?php endif; ?>
<?php if ($get_error): ?><div class="alert alert-danger d-none swal-msg" data-type="error"><?= htmlspecialchars($get_error) ?></div><?php endif; ?>
