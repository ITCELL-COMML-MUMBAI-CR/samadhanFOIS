
<?php
/**
 * Evidence API Endpoints
 * Handles evidence file upload and management
 */

require_once dirname(__DIR__) . '/utils/SessionManager.php';
require_once dirname(__DIR__) . '/models/Evidence.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!empty($id)) {
            getEvidence($id);
        } else {
            getEvidenceList();
        }
        break;
        
    case 'POST':
        uploadEvidence();
        break;
        
    case 'DELETE':
        deleteEvidence($id);
        break;
        
    default:
        sendError('Method not allowed', 405);
        break;
}

function getEvidenceList() {
    $complaintId = $_GET['complaint_id'] ?? '';
    
    if (empty($complaintId)) {
        sendError('Complaint ID is required', 400);
    }
    
    try {
        $evidenceModel = new Evidence();
        $evidence = $evidenceModel->getEvidenceByComplaintId($complaintId);
        sendSuccess($evidence, 'Evidence retrieved successfully');
    } catch (Exception $e) {
        sendError('Failed to retrieve evidence: ' . $e->getMessage(), 500);
    }
}

function getEvidence($id) {
    if (empty($id)) {
        sendError('Evidence ID is required', 400);
    }
    
    try {
        $evidenceModel = new Evidence();
        $evidence = $evidenceModel->getEvidenceById($id);
        
        if ($evidence) {
            sendSuccess($evidence, 'Evidence retrieved successfully');
        } else {
            sendError('Evidence not found', 404);
        }
    } catch (Exception $e) {
        sendError('Failed to retrieve evidence: ' . $e->getMessage(), 500);
    }
}

function uploadEvidence() {
    if (!isset($_FILES['evidence']) || $_FILES['evidence']['error'] !== UPLOAD_ERR_OK) {
        sendError('No file uploaded or upload error', 400);
    }
    
    $complaintId = $_POST['complaint_id'] ?? '';
    if (empty($complaintId)) {
        sendError('Complaint ID is required', 400);
    }
    
    try {
        $evidenceModel = new Evidence();
        $evidenceId = $evidenceModel->uploadEvidence($_FILES['evidence'], $complaintId);
        
        if ($evidenceId) {
            sendSuccess(['id' => $evidenceId], 'Evidence uploaded successfully');
        } else {
            sendError('Failed to upload evidence', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to upload evidence: ' . $e->getMessage(), 500);
    }
}

function deleteEvidence($id) {
    if (empty($id)) {
        sendError('Evidence ID is required', 400);
    }
    
    try {
        $evidenceModel = new Evidence();
        $success = $evidenceModel->deleteEvidence($id);
        
        if ($success) {
            sendSuccess([], 'Evidence deleted successfully');
        } else {
            sendError('Failed to delete evidence', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to delete evidence: ' . $e->getMessage(), 500);
    }
}
?>
