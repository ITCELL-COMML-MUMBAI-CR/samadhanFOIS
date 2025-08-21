<!-- Revert Complaint Modal -->
<div class="modal fade" id="revertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-undo text-danger"></i> Revert to Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="revert">
                <input type="hidden" name="complaint_id" id="revertComplaintId">
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action will revert the complaint back to the customer for more information.
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="rejection_reason" placeholder="Rejection Reason" style="height: 100px" required></textarea>
                        <label>Reason for Revert *</label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo"></i> Revert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
