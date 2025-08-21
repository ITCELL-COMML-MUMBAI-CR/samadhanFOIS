<!-- Close Complaint Modal -->
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success"></i> Close Complaint
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="close">
                <input type="hidden" name="complaint_id" id="closeComplaintId">
                
                <div class="modal-body">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="action_taken" placeholder="Action Taken" style="height: 100px" required></textarea>
                        <label>Action Taken *</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="remarks" placeholder="Remarks" style="height: 80px" required></textarea>
                        <label>Internal Remarks *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Send for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
