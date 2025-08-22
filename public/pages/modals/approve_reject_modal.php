<!-- Approve/Reject Modal -->
<div class="modal fade" id="approveRejectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gavel"></i> Review Action Taken</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="text-muted">Complaint ID: <span id="modalComplaintId" class="fw-bold text-dark"></span></h6>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Action Taken by Department:</label>
                    <div class="p-3 bg-light border rounded" id="modalActionTaken" style="min-height: 100px; white-space: pre-wrap;">
                        <!-- Action taken content will be populated here -->
                    </div>
                </div>
                <hr>
                <!-- Rejection Form -->
                <form id="rejectForm" method="POST" action="<?php echo BASE_URL; ?>grievances/hub">
                    <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="complaint_id" id="rejectComplaintId">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="rejection_reason" id="rejectionReason" placeholder="Rejection reason" style="height: 120px" required></textarea>
                        <label for="rejectionReason">Rejection Reason *</label>
                    </div>
                </form>
                <!-- Approval Form -->
                 <form id="approveForm" method="POST" action="<?php echo BASE_URL; ?>grievances/hub">
                    <input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="complaint_id" id="approveComplaintId">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="remarks" placeholder="Optional remarks" style="height: 120px"></textarea>
                        <label>Remarks (optional)</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="rejectForm" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                <button type="submit" form="approveForm" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
            </div>
        </div>
    </div>
</div>