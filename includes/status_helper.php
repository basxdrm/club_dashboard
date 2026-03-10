<?php
/**
 * Status Translation Helper Functions
 * แปลงสถานะจากภาษาอังกฤษเป็นภาษาไทยสำหรับการแสดงผล
 */

function getTaskStatusText($status) {
    $statusMap = [
        'pending' => 'รอดำเนินการ',
        'in_progress' => 'กำลังดำเนินการ',
        'under_review' => 'รอตรวจสอบ',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก'
    ];
    
    return $statusMap[$status] ?? $status;
}

function getTaskStatusClass($status) {
    $statusClass = [
        'pending' => 'secondary',
        'in_progress' => 'primary',
        'under_review' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    
    return $statusClass[$status] ?? 'secondary';
}

function getTaskStatusBadge($status) {
    $class = getTaskStatusClass($status);
    $text = getTaskStatusText($status);
    return "<span class=\"badge bg-{$class}\">{$text}</span>";
}

function getAssignmentStatusText($status) {
    $statusMap = [
        'pending' => 'รอการอนุมัติ',
        'approved' => 'อนุมัติแล้ว',
        'rejected' => 'ปฏิเสธ',
        'cancelled' => 'ยกเลิก'
    ];
    
    return $statusMap[$status] ?? $status;
}

function getAssignmentStatusClass($status) {
    $statusClass = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary'
    ];
    
    return $statusClass[$status] ?? 'secondary';
}
?>