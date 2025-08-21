<!-- Forward Complaint Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-share text-warning"></i> Forward Complaint
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="forward">
                <input type="hidden" name="complaint_id" id="forwardComplaintId">
                
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <select class="form-select" name="to_department" id="toDepartment" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Forward to Department *</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <select class="form-select" name="to_user" id="toUser">
                            <option value="">Select User (Optional)</option>
                        </select>
                        <label>Assign to User</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="forward_remarks" placeholder="Forward Remarks" style="height: 100px" required></textarea>
                        <label>Forward Remarks *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-share"></i> Forward
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
