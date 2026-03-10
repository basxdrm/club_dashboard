<?php
// Auto-detect base path for assets (same as header.php)
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base_path = '../';
}
?>
                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                Copyright &copy;
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> MSJ. All Rights Reserved
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end footer-links d-none d-md-block">
                                    <span class="text-muted me-2" style="font-size: 0.75rem;">v1.1.2</span>
                                    <a href="#" target="_blank"><i class="mdi mdi-facebook"></i></a>
                                    <a href="#" target="_blank"><i class="mdi mdi-instagram"></i></a>
                                    <a href="#" target="_blank"><i class="mdi mdi-music-note"></i></a>
                                    <a href="#" target="_blank"><i class="mdi mdi-web"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

                </div>

                <!-- ============================================================== -->
                <!-- End Page content -->
                <!-- ============================================================== -->

                </div>
                <!-- END wrapper -->

                <!-- bundle -->
                <script src="<?php echo $base_path; ?>assets/js/vendor.min.js"></script>
                <script src="<?php echo $base_path; ?>assets/js/app.min.js"></script>
                
                <!-- SweetAlert2 -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                
                <!-- Academic Year Change Function -->
                <script>
                function changeAcademicYear(yearId) {
                    $.post('<?php echo $base_path; ?>api/set_academic_year.php', { year_id: yearId }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message, 'error');
                        }
                    }, 'json');
                }
                </script>

                <?php if (isset($include_apexcharts) && $include_apexcharts): ?>
                    <!-- Apex Charts -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/apexcharts.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_jvectormap) && $include_jvectormap): ?>
                    <!-- Vector Maps -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
                    <script src="<?php echo $base_path; ?>assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
                <?php endif; ?>

                <?php if (isset($include_datatables) && $include_datatables): ?>
                    <!-- DataTables -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/jquery.dataTables.min.js"></script>
                    <script src="<?php echo $base_path; ?>assets/js/vendor/dataTables.bootstrap5.js"></script>
                    <script src="<?php echo $base_path; ?>assets/js/vendor/dataTables.responsive.min.js"></script>
                    <script src="<?php echo $base_path; ?>assets/js/vendor/responsive.bootstrap5.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_fullcalendar) && $include_fullcalendar): ?>
                    <!-- FullCalendar -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/fullcalendar.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_chartjs) && $include_chartjs): ?>
                    <!-- Chart.js -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/Chart.bundle.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_simplemde) && $include_simplemde): ?>
                    <!-- SimpleMDE -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/simplemde.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_quill) && $include_quill): ?>
                    <!-- Quill Editor -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/quill.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_dropzone) && $include_dropzone): ?>
                    <!-- Dropzone -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/dropzone.min.js"></script>
                <?php endif; ?>

                <?php if (isset($include_select2) && $include_select2): ?>
                    <!-- Select2 -->
                    <script src="<?php echo $base_path; ?>assets/js/vendor/select2.min.js"></script>
                <?php endif; ?>

                </body>

                </html>