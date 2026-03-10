<?php
define('APP_ACCESS', true);
header('Content-Type: application/json');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized',
        'debug' => [
            'has_user_id' => isset($_SESSION['user_id']),
            'has_email' => isset($_SESSION['email']),
            'session_id' => session_id()
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $action = $_GET['action'] ?? '';
    
    if ($action === 'calendar_events') {
        // Get tasks
        $sql = "SELECT t.id, t.title, t.due_date, t.start_date, t.status, t.priority,
                       p.name as project_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.due_date IS NOT NULL
                ORDER BY t.due_date";
        
        $stmt = $pdo->query($sql);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $events = [];
        
        foreach ($tasks as $task) {
            // Set color based on status
            $color = '#17a2b8'; // default
            switch($task['status']) {
                case 'completed':
                case 'เสร็จสิ้น':
                    $color = '#28a745';
                    break;
                case 'in_progress':
                case 'กำลังดำเนินการ':
                    $color = '#0d6efd';
                    break;
                case 'pending':
                case 'รอดำเนินการ':
                    $color = '#ffc107';
                    break;
                case 'cancelled':
                case 'ยกเลิก':
                    $color = '#dc3545';
                    break;
            }
            
            // FullCalendar uses exclusive end dates, so add 1 day to due_date
            $endDate = $task['due_date'];
            if ($endDate) {
                $endDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
            }
            
            $events[] = [
                'id' => $task['id'],
                'title' => $task['title'] ?? 'ไม่มีชื่องาน',
                'start' => $task['start_date'] ?? $task['due_date'],
                'end' => $endDate,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'url' => 'task_view.php?id=' . $task['id'],
                'extendedProps' => [
                    'type' => 'task',
                    'status' => $task['status'],
                    'priority' => $task['priority'],
                    'project' => $task['project_name']
                ]
            ];
        }
        
        // Get projects
        $sql = "SELECT id, name, start_date, end_date, status
                FROM projects
                WHERE start_date IS NOT NULL AND end_date IS NOT NULL
                ORDER BY start_date";
        
        $stmt = $pdo->query($sql);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($projects as $project) {
            // Add start marker
            $events[] = [
                'id' => 'project_start_' . $project['id'],
                'title' => '🚀 ' . $project['name'],
                'start' => $project['start_date'],
                'backgroundColor' => '#198754',
                'borderColor' => '#198754',
                'url' => 'project_view.php?id=' . $project['id'],
                'extendedProps' => [
                    'type' => 'project_start',
                    'projectId' => $project['id']
                ]
            ];
            
            // Add end marker
            $events[] = [
                'id' => 'project_end_' . $project['id'],
                'title' => '🏁 ' . $project['name'],
                'start' => $project['end_date'],
                'backgroundColor' => '#6c757d',
                'borderColor' => '#6c757d',
                'url' => 'project_view.php?id=' . $project['id'],
                'extendedProps' => [
                    'type' => 'project_end',
                    'projectId' => $project['id']
                ]
            ];
        }
        
        echo json_encode($events);
        
    } elseif ($action === 'monthly') {
        // Get tasks for specific month
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $sql = "SELECT t.id, t.title, t.start_date, t.due_date as end_date, t.status, t.priority,
                       p.name as project_name
                FROM tasks t
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.due_date BETWEEN ? AND ?
                ORDER BY t.due_date";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($tasks);
        
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
