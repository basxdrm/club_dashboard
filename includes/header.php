<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?> &#8211; MSJ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <?php
    // Auto-detect base path for assets
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
        $base_path = '../';
    }
    ?>
    <!-- App favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $base_path; ?>assets/images/logos/MSJ logo new 512.png">

    <?php if (isset($include_jvectormap) && $include_jvectormap): ?>
        <!-- Vector Map css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_datatables) && $include_datatables): ?>
        <!-- DataTables css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $base_path; ?>assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_fullcalendar) && $include_fullcalendar): ?>
        <!-- FullCalendar css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/fullcalendar.min.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_simplemde) && $include_simplemde): ?>
        <!-- SimpleMDE css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/simplemde.min.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_quill) && $include_quill): ?>
        <!-- Quill css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/quill.snow.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_dropzone) && $include_dropzone): ?>
        <!-- Dropzone css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/dropzone.min.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <?php if (isset($include_select2) && $include_select2): ?>
        <!-- Select2 css -->
        <link href="<?php echo $base_path; ?>assets/css/vendor/select2.min.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>

    <!-- App css -->
    <link href="<?php echo $base_path; ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $base_path; ?>assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Custom CSS (สำหรับเปลี่ยนฟอนต์และ style อื่นๆ) -->
    <link href="<?php echo $base_path; ?>assets/css/custom.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body class="loading" data-layout-color="light" data-leftbar-theme="light" data-layout-mode="fluid" data-rightbar-onstart="true">
    <!-- Begin page -->
    <div class="wrapper">