<?php
/**
 * My Account Page
 * หน้าบัญชีของฉัน - แก้ไขข้อมูลส่วนตัวและเปลี่ยนรหัสผ่าน
 */

define('APP_ACCESS', true);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

require_once '../config/security.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$db = new Database();
$conn = $db->getConnection();

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.id, u.email, u.role, u.status,
           p.student_id, p.prefix, p.first_name_th, p.last_name_th,
           p.first_name_en, p.last_name_en, p.nickname_th, p.birth_date,
           p.bio, p.profile_picture,
           me.academic_year_id, me.academic_grade, me.academic_room, me.academic_status,
           me.agama_grade, me.agama_room, me.agama_status,
           me.class_academic, me.class_agama,
           mc.phone_number, mc.line_id
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    LEFT JOIN member_education me ON u.id = me.user_id AND me.is_current = 1
    LEFT JOIN member_contacts mc ON u.id = mc.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$page_title = 'แก้ไขโปรไฟล์';
require_once '../includes/header.php';
?>

<!-- ========== Left Sidebar Start ========== -->
<?php include_once('../includes/sidebar.php'); ?>
<!-- Left Sidebar End -->

<div class="content-page">
    <div class="content">

        <!-- Topbar Start -->
        <?php include_once('../includes/topbar.php'); ?>
        <!-- end Topbar -->

        <!-- Start Content-->
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">แก้ไขโปรไฟล์</h4>
                            <a href="profile.php" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>กลับ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Profile Card -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="avatar-container mb-3" style="width: 150px; height: 150px; margin: 0 auto; position: relative;">
                                <?php
                                $avatar = !empty($user['profile_picture'])
                                    ? '../assets/images/users/' . $user['profile_picture'] . '?t=' . time()
                                    : '../assets/images/users/avatar-1.jpg';
                                ?>
                                <img id="profileImagePreview" src="<?php echo htmlspecialchars($avatar); ?>"
                                     class="rounded-circle avatar-lg"
                                     alt="profile"
                                     style="width: 100%; height: 100%; object-fit: cover; border: 4px solid #e9ecef;">
                                
                                <!-- Upload Button Overlay -->
                                <label for="profileImageInput" class="btn btn-primary btn-sm rounded-circle" 
                                       style="position: absolute; bottom: 5px; right: 5px; width: 35px; height: 35px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <i class="mdi mdi-camera"></i>
                                </label>
                                <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                            </div>
                            
                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="progress mb-2" style="height: 5px; display: none;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                            </div>

                            <h4 class="mb-0 mt-2"><?php echo htmlspecialchars($user['prefix'] . $user['first_name_th'] . ' ' . $user['last_name_th']); ?></h4>
                            <p class="text-muted font-14"><?php echo htmlspecialchars($user['email']); ?></p>

                            <div class="text-start mt-3">
                                <p class="text-muted mb-2 font-13"><strong>รหัสนักเรียน:</strong> <span class="ms-2"><?php echo htmlspecialchars($user['student_id']); ?></span></p>
                                <p class="text-muted mb-2 font-13"><strong>ชื่อเล่น:</strong> <span class="ms-2"><?php echo htmlspecialchars($user['nickname_th'] ?? '-'); ?></span></p>
                                <p class="text-muted mb-2 font-13"><strong>วันเกิด:</strong> <span class="ms-2">
                                    <?php
                                    if (!empty($user['birth_date'])) {
                                        $date = new DateTime($user['birth_date']);
                                        echo $date->format('d/m/Y');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </span></p>
                                <p class="text-muted mb-2 font-13"><strong>ระดับชั้น:</strong> <span class="ms-2"><?php echo htmlspecialchars($user['class_academic'] ?? '-'); ?></span></p>
                                <p class="text-muted mb-2 font-13"><strong>เบอร์โทร:</strong> <span class="ms-2"><?php echo htmlspecialchars($user['phone_number'] ?? '-'); ?></span></p>
                                <p class="text-muted mb-1 font-13"><strong>Line ID:</strong> <span class="ms-2"><?php echo htmlspecialchars($user['line_id'] ?? '-'); ?></span></p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Edit Forms -->
                <div class="col-xl-8 col-lg-7">
                    <!-- Edit Profile Form -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">แก้ไขข้อมูลส่วนตัว</h4>

                            <form id="updateProfileForm">
                                <!-- Read-only Fields -->
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    ข้อมูลที่มีพื้นหลังสีเทาไม่สามารถแก้ไขได้ กรุณาติดต่อแอดมินหากต้องการเปลี่ยนแปลง
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_id" class="form-label">รหัสนักเรียน</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['student_id']); ?>" disabled readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">อีเมล</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="prefix" class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                                        <select class="form-select" id="prefix" name="prefix" required>
                                            <option value="นาย" <?php echo $user['prefix'] === 'นาย' ? 'selected' : ''; ?>>นาย</option>
                                            <option value="นางสาว" <?php echo $user['prefix'] === 'นางสาว' ? 'selected' : ''; ?>>นางสาว</option>
                                            <option value="นาง" <?php echo $user['prefix'] === 'นาง' ? 'selected' : ''; ?>>นาง</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="first_name_th" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name_th" name="first_name_th" 
                                               value="<?php echo htmlspecialchars($user['first_name_th']); ?>" required>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label for="last_name_th" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name_th" name="last_name_th" 
                                               value="<?php echo htmlspecialchars($user['last_name_th']); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nickname_th" class="form-label">ชื่อเล่น</label>
                                        <input type="text" class="form-control" id="nickname_th" name="nickname_th" 
                                               value="<?php echo htmlspecialchars($user['nickname_th'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="birth_date" class="form-label">วันเกิด</label>
                                        <input type="date" class="form-control" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>" disabled readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone_number" class="form-label">เบอร์โทรศัพท์</label>
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                               value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="line_id" class="form-label">Line ID</label>
                                        <input type="text" class="form-control" id="line_id" name="line_id" 
                                               value="<?php echo htmlspecialchars($user['line_id'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="form-label">เกี่ยวกับฉัน</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save me-1"></i>บันทึกการเปลี่ยนแปลง
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- container -->

    </div>
    <!-- content -->

    <?php include_once('../includes/footer.php'); ?>

</div>
<!-- content-page -->

<!-- Image Crop Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ปรับแต่งรูปโปรไฟล์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="imageToCrop" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i>ยกเลิก
                </button>
                <button type="button" class="btn btn-primary" id="cropAndUpload">
                    <i class="mdi mdi-crop me-1"></i>ครอปและบันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper.js -->
<link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let cropper = null;
let selectedFile = null;

// Profile Image Preview
$('#profileImageInput').on('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        Swal.fire('ข้อผิดพลาด!', 'กรุณาเลือกไฟล์รูปภาพเท่านั้น', 'error');
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire('ข้อผิดพลาด!', 'ขนาดไฟล์ต้องไม่เกิน 5MB', 'error');
        return;
    }
    
    // Show crop modal
    selectedFile = file;
    const reader = new FileReader();
    reader.onload = function(e) {
        $('#imageToCrop').attr('src', e.target.result);
        $('#cropModal').modal('show');
    };
    reader.readAsDataURL(file);
});

// Initialize cropper when modal is shown
$('#cropModal').on('shown.bs.modal', function() {
    const image = document.getElementById('imageToCrop');
    if (cropper) {
        cropper.destroy();
    }
    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 2,
        dragMode: 'move',
        autoCropArea: 1,
        restore: false,
        guides: true,
        center: true,
        highlight: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
});

// Destroy cropper when modal is hidden
$('#cropModal').on('hidden.bs.modal', function() {
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    // Reset file input
    $('#profileImageInput').val('');
});

// Crop and upload
$('#cropAndUpload').on('click', function() {
    if (!cropper) return;
    
    cropper.getCroppedCanvas({
        width: 500,
        height: 500,
        imageSmoothingQuality: 'high'
    }).toBlob(function(blob) {
        const croppedFile = new File([blob], selectedFile.name, {
            type: selectedFile.type,
            lastModified: Date.now()
        });
        
        $('#cropModal').modal('hide');
        uploadProfileImage(croppedFile);
    }, selectedFile.type);
});

function uploadProfileImage(file) {
    const formData = new FormData();
    formData.append('profile_image', file);
    
    $('#uploadProgress').show();
    $('.progress-bar').css('width', '0%');
    
    $.ajax({
        url: '../api/account_update_profile.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    $('.progress-bar').css('width', percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            $('#uploadProgress').fadeOut();
            console.log('Response:', response);
            
            // Handle string response (not JSON)
            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch(e) {
                    console.error('Parse error:', e, response);
                    Swal.fire('ข้อผิดพลาด!', 'รูปแบบข้อมูลไม่ถูกต้อง: ' + response.substring(0, 100), 'error');
                    return;
                }
            }
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: 'อัปโหลดรูปโปรไฟล์สำเร็จ',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message || 'ไม่สามารถอัปโหลดได้', 'error');
            }
        },
        error: function(xhr, status, error) {
            $('#uploadProgress').fadeOut();
            console.error('Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            let errorMsg = 'ไม่สามารถอัปโหลดรูปภาพได้';
            if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText.substring(0, 200);
                }
            }
            
            Swal.fire('ข้อผิดพลาด!', errorMsg, 'error');
        }
    });
}

// Update Profile
$('#updateProfileForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    
    $.ajax({
        url: '../api/account_update_profile.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด!', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error, xhr.responseText);
            Swal.fire('ข้อผิดพลาด!', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
