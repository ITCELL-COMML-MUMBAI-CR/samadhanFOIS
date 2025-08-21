<?php
/**
 * Complaint Details Page
 * Unified detailed view for admin, viewer, controller, and customer (owner)
 */
?>

<div class="container-fluid">
	<!-- Alert Messages -->
	<?php if (!empty($alert_message)): ?>
		<div class="alert alert-<?php echo $alert_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
			<i class="fas fa-<?php echo $alert_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
			<?php echo htmlspecialchars($alert_message); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>
	
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h1 class="h3 mb-0">
				<i class="fas fa-file-alt text-primary"></i>
				Complaint Details
				<small class="text-muted">#<?php echo htmlspecialchars($complaint['complaint_id']); ?></small>
			</h1>
			<div>
				<a href="<?php echo BASE_URL; ?>complaints" class="btn btn-outline-secondary btn-sm">
					<i class="fas fa-arrow-left"></i> Back to List
				</a>
			</div>
		</div>
	</div>

	<div class="row g-3">
		<!-- Left Column: Core Details -->
		<div class="col-lg-8">
			<div class="card mb-3">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="mb-0"><i class="fas fa-info-circle"></i> Complaint Summary</h5>
					<span class="badge status-<?php echo str_replace('_','-',$complaint['status']); ?>">
						<?php echo ucfirst(str_replace('_',' ', $complaint['status'])); ?>
					</span>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Category / Type / Subtype</label>
							<div>
								<?php if (!empty($complaint['category'])): ?>
									<span class="badge bg-primary"><?php echo htmlspecialchars($complaint['category']); ?></span><br>
								<?php endif; ?>
								<strong><?php echo htmlspecialchars($complaint['Type'] ?? 'Not specified'); ?></strong><br>
								<small class="text-muted"><?php echo htmlspecialchars($complaint['Subtype'] ?? 'Not specified'); ?></small>
							</div>
						</div>
						<?php if (($currentUser['role'] ?? '') !== 'customer'): ?>
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Priority</label>
							<div><span class="badge priority-<?php echo $complaint['priority']; ?>"><?php echo ucfirst($complaint['priority']); ?></span></div>
						</div>
						<?php endif; ?>
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Location</label>
							<div><i class="fas fa-map-marker-alt text-muted"></i> <?php echo htmlspecialchars($complaint['Location'] ?? 'Not specified'); ?></div>
						</div>
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Date / Time</label>
							<div><?php echo date('d-M-Y', strtotime($complaint['date'])); ?>, <?php echo date('H:i', strtotime($complaint['time'])); ?></div>
						</div>
						<div class="col-12 mb-3">
							<label class="text-muted small">Description</label>
							<div class="border rounded p-2 bg-light"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></div>
						</div>
						<?php if (!empty($complaint['action_taken'])): ?>
						<div class="col-12 mb-3">
							<label class="text-muted small">Action Taken</label>
							<div class="border rounded p-2"><?php echo nl2br(htmlspecialchars($complaint['action_taken'])); ?></div>
						</div>
						<?php endif; ?>
						
						<?php 
						// Show reverted remarks for customers when complaint is reverted
						if (($currentUser['role'] ?? '') === 'customer' && $complaint['status'] === 'reverted'): 
							$revertedRemarks = '';
							foreach ($rejections as $rej) {
								if ($rej['revert_stage'] === 'commercial_to_customer') {
									$revertedRemarks = $rej['revert_reason'] ?? '';
									break;
								}
							}
							if (!empty($revertedRemarks)):
						?>
						<div class="col-12 mb-3">
							<label class="text-muted small">Information Requested</label>
							<div class="border rounded p-2 bg-warning bg-opacity-10 border-warning">
								<i class="fas fa-exclamation-triangle text-warning me-2"></i>
								<?php echo nl2br(htmlspecialchars($revertedRemarks)); ?>
							</div>
						</div>
						<?php endif; endif; ?>
					</div>
				</div>
			</div>

			<!-- Evidence -->
			<div class="card mb-3">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="mb-0"><i class="fas fa-paperclip"></i> Evidence</h5>
					<?php if (in_array($currentUser['role'], ['admin','controller'])): ?>
					<small class="text-muted">Max 3 images</small>
					<?php endif; ?>
				</div>
				<div class="card-body">
					<?php if (empty($images)): ?>
						<p class="text-muted mb-0">No evidence uploaded.</p>
					<?php else: ?>
						<div class="row g-3">
							<?php foreach ($images as $img): ?>
							<div class="col-md-4 col-sm-6">
								<?php if (isset($img['missing']) && $img['missing']): ?>
								<div class="ratio ratio-4x3 border rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
									<div class="text-center text-muted">
										<i class="fas fa-file-image fa-2x mb-2"></i>
										<p class="small mb-0">File not found</p>
										<small><?php echo htmlspecialchars($img['filename']); ?></small>
									</div>
								</div>
								<?php else: ?>
								<div class="ratio ratio-4x3 border rounded overflow-hidden">
									<img src="<?php echo htmlspecialchars($img['url'] ?? (BASE_URL . 'uploads/evidences/' . $img['filename'])); ?>" alt="Evidence" class="w-100 h-100 object-fit-cover">
								</div>
								<?php endif; ?>
								<div class="d-flex justify-content-between mt-1">
									<small class="text-muted"><?php echo round(($img['size'] ?? 0) / 1024); ?> KB</small>
									<?php if (!isset($img['missing']) || !$img['missing']): ?>
									<a class="small" href="<?php echo htmlspecialchars($img['url'] ?? (BASE_URL . 'uploads/evidences/' . $img['filename'])); ?>" target="_blank">Open</a>
									<?php else: ?>
									<small class="text-muted">Missing</small>
									<?php endif; ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Right Column: Parties & Timeline -->
		<div class="col-lg-4">
			<?php if (($currentUser['role'] ?? '') !== 'customer'): ?>
			<!-- Only non-customers can see parties and timeline -->
			<div class="card mb-3">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-user"></i> Parties</h5>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<label class="text-muted small">Customer</label>
						<div><strong><?php echo htmlspecialchars($complaint['customer_name'] ?? 'Unknown'); ?></strong></div>
						<small class="text-muted"><?php echo htmlspecialchars($complaint['customer_id']); ?></small>
					</div>
					<div class="mb-3">
						<label class="text-muted small">Assigned To</label>
                        <div><strong><?php echo htmlspecialchars($complaint['assigned_to_name'] ?? 'Commercial Controller'); ?></strong></div>
                        <small class="text-muted"><?php echo htmlspecialchars($complaint['assigned_to'] ?? 'commercial_controller'); ?></small>
					</div>
					<div class="mb-0">
						<label class="text-muted small">Department</label>
						<div><?php echo htmlspecialchars($complaint['department']); ?></div>
					</div>
				</div>
			</div>

			<?php if (!empty($complaint['rating'])): ?>
			<div class="card mb-3">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-star"></i> Customer Rating</h5>
				</div>
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="me-3">
							<?php 
							$ratingClass = '';
							$ratingIcon = '';
							switch($complaint['rating']) {
								case 'Excellent':
									$ratingClass = 'text-success';
									$ratingIcon = 'fas fa-star';
									break;
								case 'Satisfactory':
									$ratingClass = 'text-warning';
									$ratingIcon = 'fas fa-star';
									break;
								case 'Unsatisfactory':
									$ratingClass = 'text-danger';
									$ratingIcon = 'fas fa-star';
									break;
							}
							?>
							<i class="<?php echo $ratingIcon . ' ' . $ratingClass; ?> fa-2x"></i>
						</div>
						<div>
							<strong class="<?php echo $ratingClass; ?>"><?php echo htmlspecialchars($complaint['rating']); ?></strong>
							<?php if (!empty($complaint['rating_remarks'])): ?>
								<p class="mb-0 text-muted mt-1"><?php echo htmlspecialchars($complaint['rating_remarks']); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="card">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-stream"></i> Timeline</h5>
				</div>
				<div class="card-body">
					<?php if (empty($history) && empty($rejections)): ?>
						<p class="text-muted mb-0">No activity yet.</p>
					<?php else: ?>
						<div class="timeline">
							<?php foreach ($history as $event): ?>
							<div class="timeline-item">
								<div class="d-flex justify-content-between">
									<strong class="text-capitalize"><?php echo str_replace('_',' ', htmlspecialchars($event['transaction_type'])); ?></strong>
									<small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($event['created_at'])); ?></small>
								</div>
								<div class="small text-muted mb-1">
									by <?php echo htmlspecialchars($event['created_by_name'] ?? $event['created_by']); ?>
									<?php if (!empty($event['from_department']) || !empty($event['to_department'])): ?>
										— <?php echo htmlspecialchars($event['from_department'] ?? ''); ?>
										<?php if (!empty($event['to_department'])): ?>
											<i class="fas fa-arrow-right mx-1"></i>
											<?php echo htmlspecialchars($event['to_department']); ?>
										<?php endif; ?>
									<?php endif; ?>
								</div>
								<div><?php echo nl2br(htmlspecialchars($event['remarks'] ?? '')); ?></div>
							</div>
							<?php endforeach; ?>

							<?php foreach ($rejections as $rej): ?>
							<div class="timeline-item">
								<div class="d-flex justify-content-between">
									<strong class="text-danger">Revert (<?php echo str_replace('_',' ', htmlspecialchars($rej['revert_stage'])); ?>)</strong>
									<small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($rej['created_at'])); ?></small>
								</div>
								<div class="small text-muted mb-1">
									by <?php echo htmlspecialchars($rej['reverted_by_name'] ?? $rej['reverted_by']); ?>
									<?php if (!empty($rej['reverted_to_name'])): ?> → <?php echo htmlspecialchars($rej['reverted_to_name']); ?><?php endif; ?>
								</div>
								<div><?php echo nl2br(htmlspecialchars($rej['revert_reason'] ?? '')); ?></div>
							</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if (($currentUser['role'] ?? '') === 'customer' && ($complaint['customer_id'] ?? '') === ($currentUser['customer_id'] ?? '')): ?>
			<!-- Customer Actions: Feedback or Provide More Information -->
			<div class="card">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-reply"></i> Your Action</h5>
				</div>
				<div class="card-body">
                    <?php if (in_array($complaint['status'], ['replied'])): ?>
						<div id="feedback">
						<form method="POST" class="mb-3">
							<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
							<input type="hidden" name="action" value="submit_feedback">
							
							<!-- Feedback Rating -->
							<div class="mb-3">
								<label class="form-label">Rate your experience:</label>
								<div class="rating-buttons">
									<button type="button" class="btn btn-outline-success rating-btn" data-rating="Excellent">
										<i class="fas fa-star"></i> Excellent
									</button>
									<button type="button" class="btn btn-outline-warning rating-btn" data-rating="Satisfactory">
										<i class="fas fa-star"></i> Satisfactory
									</button>
									<button type="button" class="btn btn-outline-danger rating-btn" data-rating="Unsatisfactory">
										<i class="fas fa-star"></i> Unsatisfactory
									</button>
								</div>
								<input type="hidden" name="feedback_rating" id="feedback_rating" required>
							</div>
							
							<div class="form-floating mb-2">
								<textarea class="form-control" name="feedback_text" placeholder="Your feedback" style="height: 90px" required></textarea>
                                <label>Provide Feedback (will close the complaint) *</label>
							</div>
							<button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Submit Feedback & Close</button>
						</form>
						</div>
					<?php endif; ?>

					<?php if ($complaint['status'] === 'reverted'): ?>
						<div id="more-info">
						<form method="POST" enctype="multipart/form-data">
							<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
							<input type="hidden" name="action" value="submit_more_info">
							<div class="form-floating mb-3">
								<textarea class="form-control" name="more_info_text" placeholder="Add more information requested" style="height: 120px" required></textarea>
								<label>Provide More Information (requested by Commercial) *</label>
							</div>
							
							<!-- Image Management Section -->
							<?php if (!empty($images)): ?>
							<div class="card mb-3">
								<div class="card-body">
									<h6 class="card-title">
										<i class="fas fa-images"></i> Manage Existing Images
									</h6>
									<p class="card-text small">Select images to delete before uploading new ones</p>
									
									<div class="row g-2">
										<?php foreach ($images as $index => $img): ?>
										<div class="col-6">
											<div class="border rounded p-2">
												<div class="form-check">
													<input class="form-check-input" type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($img['filename']); ?>" id="delete_img_<?php echo $index; ?>">
													<label class="form-check-label" for="delete_img_<?php echo $index; ?>">
														<small class="text-muted">Delete this image</small>
													</label>
												</div>
												<div class="ratio ratio-4x3 mt-2">
													<img src="<?php echo htmlspecialchars($img['url'] ?? (BASE_URL . 'uploads/evidences/' . $img['filename'])); ?>" alt="Evidence" class="w-100 h-100 object-fit-cover">
												</div>
												<small class="text-muted d-block mt-1"><?php echo htmlspecialchars($img['filename']); ?></small>
											</div>
										</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
							<?php endif; ?>
							
							<!-- Additional Evidence Upload -->
							<div class="card mb-3">
								<div class="card-body">
									<h6 class="card-title">
										<i class="fas fa-paperclip"></i> Additional Evidence (Optional)
									</h6>
									<p class="card-text small">Upload additional supporting images if needed</p>
									
									<div class="file-upload-area" id="additionalEvidenceArea" style="cursor: pointer; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; text-align: center;">
										<i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
										<p class="mb-2">Click to select additional evidence files</p>
										<input type="file" class="form-control" id="additional_evidence" name="additional_evidence[]" 
											   multiple accept="image/*" style="display: none;">
										<button type="button" class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); document.getElementById('additional_evidence').click();">
											<i class="fas fa-upload"></i> Select Files
										</button>
									</div>
									
									<div class="row mt-3" id="additionalEvidencePreview"></div>
									
									<div class="form-text">
										Supported formats: JPG, JPEG, PNG, GIF. Maximum 3 images, 2MB each.
									</div>
								</div>
							</div>
							
							<button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-paper-plane"></i> Submit Additional Info</button>
						</form>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<style>
.timeline {
	position: relative;
}
.timeline-item {
	border-left: 2px solid #e2e8f0;
	padding-left: 1rem;
	margin-bottom: 1rem;
	position: relative;
}
.timeline-item::before {
	content: '';
	position: absolute;
	left: -5px;
	top: 5px;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: var(--railway-blue);
}
.object-fit-cover { object-fit: cover; }
@media (max-width: 768px) {
	.card-body { padding: 1rem; }
}

/* Additional Evidence Upload Styles */
.file-upload-area:hover {
	border-color: #007bff !important;
	background-color: #f8f9fa;
}

/* Rating Button Styles */
.rating-buttons {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}

.rating-btn {
	transition: all 0.3s ease;
}

.rating-btn.selected {
	transform: scale(1.05);
}

.rating-btn[data-rating="Excellent"].selected {
	background-color: #28a745;
	border-color: #28a745;
	color: white;
}

.rating-btn[data-rating="Satisfactory"].selected {
	background-color: #ffc107;
	border-color: #ffc107;
	color: white;
}

.rating-btn[data-rating="Unsatisfactory"].selected {
	background-color: #dc3545;
	border-color: #dc3545;
	color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll to anchor if present in URL
    if (window.location.hash) {
        const targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetElement.style.backgroundColor = 'rgba(255, 193, 7, 0.1)';
                setTimeout(() => {
                    targetElement.style.backgroundColor = '';
                }, 2000);
            }, 500);
        }
    }
    
    // Rating button functionality
    const ratingBtns = document.querySelectorAll('.rating-btn');
    const ratingInput = document.getElementById('feedback_rating');
    
    if (ratingBtns.length > 0 && ratingInput) {
        ratingBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove selected class from all buttons
                ratingBtns.forEach(b => b.classList.remove('selected'));
                
                // Add selected class to clicked button
                this.classList.add('selected');
                
                // Set the hidden input value
                ratingInput.value = this.getAttribute('data-rating');
            });
        });
    }
    
    // Additional Evidence Upload Functionality
    const additionalEvidenceArea = document.getElementById('additionalEvidenceArea');
    const additionalEvidenceInput = document.getElementById('additional_evidence');
    const additionalEvidencePreview = document.getElementById('additionalEvidencePreview');
    
    if (additionalEvidenceArea && additionalEvidenceInput) {
        // Click to select files
        additionalEvidenceArea.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
                additionalEvidenceInput.click();
            }
        });
        
        // Handle file selection
        additionalEvidenceInput.addEventListener('change', function() {
            if (this.files.length === 0) {
                additionalEvidencePreview.innerHTML = '';
                return;
            }
            
            // Validate files
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB
            const errors = [];
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                
                if (!allowedTypes.includes(file.type)) {
                    errors.push(`File "${file.name}" has invalid type. Allowed: JPG, JPEG, PNG, GIF`);
                }
                
                if (file.size > maxSize) {
                    errors.push(`File "${file.name}" is too large. Maximum size: 2MB`);
                }
            }
            
            if (errors.length > 0) {
                alert('File validation errors:\n' + errors.join('\n'));
                this.value = '';
                additionalEvidencePreview.innerHTML = '';
                return;
            }
            
            // Show preview
            additionalEvidencePreview.innerHTML = '';
            for (let i = 0; i < this.files.length && i < 3; i++) {
                const file = this.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'col-md-4 col-sm-6 mb-2';
                    previewDiv.innerHTML = `
                        <div class="ratio ratio-4x3 border rounded overflow-hidden">
                            <img src="${e.target.result}" alt="Preview" class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                            <small class="text-muted">${file.name}</small>
                        </div>
                    `;
                    additionalEvidencePreview.appendChild(previewDiv);
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
