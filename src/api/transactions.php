<?php
/**
 * Transactions API Endpoints
 * Handles transaction-related operations
 */

require_once dirname(__DIR__) . '/utils/SessionManager.php';
require_once dirname(__DIR__) . '/models/Transaction.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (!empty($id)) {
            getTransaction($id);
        } else {
            getTransactions();
        }
        break;
        
    case 'POST':
        createTransaction();
        break;
        
    case 'PUT':
        updateTransaction($id);
        break;
        
    case 'DELETE':
        deleteTransaction($id);
        break;
        
    default:
        sendError('Method not allowed', 405);
        break;
}

function getTransactions() {
    try {
        $transactionModel = new Transaction();
        $filters = $_GET;
        $transactions = $transactionModel->getTransactions($filters);
        sendSuccess($transactions, 'Transactions retrieved successfully');
    } catch (Exception $e) {
        sendError('Failed to retrieve transactions: ' . $e->getMessage(), 500);
    }
}

function getTransaction($id) {
    if (empty($id)) {
        sendError('Transaction ID is required', 400);
    }
    
    try {
        $transactionModel = new Transaction();
        $transaction = $transactionModel->getTransactionById($id);
        
        if ($transaction) {
            sendSuccess($transaction, 'Transaction retrieved successfully');
        } else {
            sendError('Transaction not found', 404);
        }
    } catch (Exception $e) {
        sendError('Failed to retrieve transaction: ' . $e->getMessage(), 500);
    }
}

function createTransaction() {
    $input = getJsonInput();
    
    $requiredFields = ['complaint_id', 'action', 'details'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            sendError("Field '$field' is required", 400);
        }
    }
    
    try {
        $transactionModel = new Transaction();
        $transactionId = $transactionModel->createTransaction($input);
        
        if ($transactionId) {
            sendSuccess(['id' => $transactionId], 'Transaction created successfully');
        } else {
            sendError('Failed to create transaction', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to create transaction: ' . $e->getMessage(), 500);
    }
}

function updateTransaction($id) {
    if (empty($id)) {
        sendError('Transaction ID is required', 400);
    }
    
    $input = getJsonInput();
    
    try {
        $transactionModel = new Transaction();
        $success = $transactionModel->updateTransaction($id, $input);
        
        if ($success) {
            sendSuccess([], 'Transaction updated successfully');
        } else {
            sendError('Failed to update transaction', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to update transaction: ' . $e->getMessage(), 500);
    }
}

function deleteTransaction($id) {
    if (empty($id)) {
        sendError('Transaction ID is required', 400);
    }
    
    try {
        $transactionModel = new Transaction();
        $success = $transactionModel->deleteTransaction($id);
        
        if ($success) {
            sendSuccess([], 'Transaction deleted successfully');
        } else {
            sendError('Failed to delete transaction', 500);
        }
    } catch (Exception $e) {
        sendError('Failed to delete transaction: ' . $e->getMessage(), 500);
    }
}
?>
