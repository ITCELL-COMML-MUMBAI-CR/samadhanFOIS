<?php
/**
 * My Grievances Page (Customer)
 */
?>

<div class="container-fluid">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h1 class="h3 mb-0"><i class="fas fa-list text-primary"></i> My Grievances</h1>
			<a href="<?php echo BASE_URL; ?>grievances/new" class="btn btn-railway-primary btn-sm"><i class="fas fa-plus-circle"></i> New Grievance</a>
		</div>
	</div>

	<div class="card">
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover mb-0">
					<thead>
						<tr class="text-center">
							<th class="text-center align-middle">ID</th>
							<th class="text-center align-middle">Category</th>
							<th class="text-center align-middle">Type</th>
							<th class="text-center align-middle">Location</th>
							<th class="text-center align-middle">Status</th>
							<th class="text-center align-middle">Date</th>
							<th class="text-center align-middle">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($grievances)): ?>
						<tr><td colspan="7" class="text-center align-middle py-4 text-muted">You have not submitted any grievances yet.</td></tr>
						<?php else: foreach ($grievances as $g): ?>
						<tr class="<?php echo in_array($g['status'], ['resolved', 'reverted']) ? 'table-warning' : ''; ?>">
							<td class="text-center align-middle"><small class="text-muted"><?php echo htmlspecialchars($g['complaint_id']); ?></small></td>
							<td class="text-center align-middle">
								<?php if (!empty($g['category'])): ?>
									<span class="badge bg-info"><?php echo htmlspecialchars($g['category']); ?></span>
								<?php else: ?>
									<small class="text-muted">N/A</small>
								<?php endif; ?>
							</td>
							<td class="text-center align-middle">
								<strong><?php echo htmlspecialchars($g['complaint_type']); ?></strong><br>
								<small class="text-muted"><?php echo htmlspecialchars($g['complaint_subtype']); ?></small>
							</td>
							<td class="text-center align-middle"><small class="text-muted"><?php echo htmlspecialchars($g['location'] ?? 'N/A'); ?></small></td>
                            <td class="text-center align-middle">
								<span class="badge status-<?php echo str_replace('_','-',$g['status']); ?>">
									<?php echo $g['status']==='replied'?'Replied':ucfirst(str_replace('_',' ',$g['status'])); ?>
								</span>
								<?php if (in_array($g['status'], ['resolved', 'reverted'])): ?>
									<div class="mt-1">
										<?php if ($g['status'] === 'resolved'): ?>
											<span class="badge bg-success bg-opacity-75 animate-pulse">Action Required</span>
										<?php elseif ($g['status'] === 'reverted'): ?>
											<span class="badge bg-warning bg-opacity-75 animate-pulse">Response Required</span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</td>
							<td class="text-center align-middle"><small><?php echo date('d-M-Y H:i', strtotime($g['date'] . ' ' . ($g['time'] ?? '00:00:00'))); ?></small></td>
							<td class="text-center align-middle">
								<div class="d-flex flex-column gap-1 grievances-actions">
									<a class="btn btn-outline-primary btn-sm" href="<?php echo BASE_URL; ?>complaints/view/<?php echo urlencode($g['complaint_id']); ?>">
										<i class="fas fa-eye"></i> View
									</a>
									<?php if (in_array($g['status'], ['resolved', 'reverted'])): ?>
										<?php if ($g['status'] === 'resolved'): ?>
											<button class="btn btn-success btn-sm animate-pulse" onclick="openFeedbackModal('<?php echo htmlspecialchars($g['complaint_id']); ?>', '<?php echo htmlspecialchars($g['complaint_type']); ?>')">
												<i class="fas fa-star"></i> Give Feedback
											</button>
										<?php elseif ($g['status'] === 'reverted'): ?>
											<button class="btn btn-warning btn-sm animate-pulse" onclick="openMoreInfoModal('<?php echo htmlspecialchars($g['complaint_id']); ?>', '<?php echo htmlspecialchars($g['complaint_type']); ?>')">
												<i class="fas fa-reply"></i> Send Again
											</button>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="feedbackModalLabel">
					<i class="fas fa-star text-warning"></i> Provide Feedback
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="feedbackForm" method="POST" action="<?php echo BASE_URL; ?>api/complaints">
				<div class="modal-body">
					<input type="hidden" name="action" value="submit_feedback">
					<input type="hidden" name="complaint_id" id="feedbackComplaintId">
					<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
					
					<div class="mb-3">
						<label class="form-label">Complaint Type:</label>
						<div class="form-control-plaintext" id="feedbackComplaintType"></div>
					</div>
					
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
					
					<div class="form-floating mb-3">
						<textarea class="form-control" name="feedback_text" id="feedback_text" placeholder="Your feedback" style="height: 120px" required></textarea>
						<label for="feedback_text">Provide Feedback (will close the complaint) *</label>
						<div class="form-text">You can provide any feedback, no minimum length required.</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-success">
						<i class="fas fa-check"></i> Submit Feedback & Close
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- More Information Modal -->
<div class="modal fade" id="moreInfoModal" tabindex="-1" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="moreInfoModalLabel">
					<i class="fas fa-reply text-warning"></i> Provide More Information
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form id="moreInfoForm" method="POST" action="<?php echo BASE_URL; ?>api/complaints" enctype="multipart/form-data">
				<div class="modal-body">
					<input type="hidden" name="action" value="submit_more_info">
					<input type="hidden" name="complaint_id" id="moreInfoComplaintId">
					<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
					
					<div class="mb-3">
						<label class="form-label">Complaint Type:</label>
						<div class="form-control-plaintext" id="moreInfoComplaintType"></div>
					</div>
					
					<div class="form-floating mb-3">
						<textarea class="form-control" name="more_info_text" id="more_info_text" placeholder="Add more information requested" style="height: 120px" required></textarea>
						<label for="more_info_text">Provide More Information (requested by Commercial) *</label>
					</div>
					
					<!-- Image Management Section -->
					<div class="card mb-3" id="imageManagementSection" style="display: none;">
						<div class="card-body">
							<h6 class="card-title">
								<i class="fas fa-images"></i> Manage Existing Images
							</h6>
							<p class="card-text small">Select images to delete before uploading new ones</p>
							
							<div class="row g-2" id="existingImagesContainer">
								<!-- Existing images will be loaded here -->
							</div>
						</div>
					</div>
					
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
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-warning">
						<i class="fas fa-paper-plane"></i> Submit Additional Info
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
// Modal functions
function openFeedbackModal(complaintId, complaintType) {
	document.getElementById('feedbackComplaintId').value = complaintId;
	document.getElementById('feedbackComplaintType').textContent = complaintType;
	
	// Reset form
	document.getElementById('feedbackForm').reset();
	document.getElementById('feedback_rating').value = '';
	document.querySelectorAll('.rating-btn').forEach(btn => btn.classList.remove('selected'));
	
	const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
	modal.show();
}

function openMoreInfoModal(complaintId, complaintType) {
	document.getElementById('moreInfoComplaintId').value = complaintId;
	document.getElementById('moreInfoComplaintType').textContent = complaintType;
	
	// Reset form
	document.getElementById('moreInfoForm').reset();
	document.getElementById('additionalEvidencePreview').innerHTML = '';
	
	// Load existing images for this complaint
	loadExistingImages(complaintId);
	
	const modal = new bootstrap.Modal(document.getElementById('moreInfoModal'));
	modal.show();
}

function loadExistingImages(complaintId) {
	// Fetch existing images for this complaint
	fetch(`<?php echo BASE_URL; ?>api/complaints/view/${complaintId}`)
		.then(response => response.json())
		.then(data => {
			if (data.error === false && data.data && data.data.evidence) {
				const images = data.data.evidence;
				const container = document.getElementById('existingImagesContainer');
				const section = document.getElementById('imageManagementSection');
				
				if (images && images.length > 0) {
					container.innerHTML = '';
					images.forEach((img, index) => {
						const imageDiv = document.createElement('div');
						imageDiv.className = 'col-6';
						imageDiv.innerHTML = `
							<div class="border rounded p-2">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="delete_images[]" value="${img.filename}" id="delete_img_${index}">
									<label class="form-check-label" for="delete_img_${index}">
										<small class="text-muted">Delete this image</small>
									</label>
								</div>
								<div class="ratio ratio-4x3 mt-2">
									<img src="${img.url || '<?php echo BASE_URL; ?>uploads/evidences/' + img.filename}" alt="Evidence" class="w-100 h-100 object-fit-cover">
								</div>
								<small class="text-muted d-block mt-1">${img.filename}</small>
							</div>
						`;
						container.appendChild(imageDiv);
					});
					section.style.display = 'block';
				} else {
					section.style.display = 'none';
				}
			} else {
				document.getElementById('imageManagementSection').style.display = 'none';
			}
		})
		.catch(error => {
			console.error('Error loading existing images:', error);
			document.getElementById('imageManagementSection').style.display = 'none';
		});
}

// Rating button functionality
document.addEventListener('DOMContentLoaded', function() {
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
	
	// Form submission handlers
	document.getElementById('feedbackForm').addEventListener('submit', function(e) {
		e.preventDefault();
		
		const formData = new FormData(this);
		
		fetch('<?php echo BASE_URL; ?>api/complaints', {
			method: 'POST',
			body: formData
		})
		.then(response => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then(data => {
			if (data.error === false) {
				// Close modal
				bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
				
				// Show success message
				showAlert(data.message || 'Feedback submitted successfully!', 'success');
				
				// Reload page after a short delay
				setTimeout(() => {
					window.location.reload();
				}, 1500);
			} else {
				showAlert(data.message || 'Failed to submit feedback', 'danger');
			}
		})
		.catch(error => {
			console.error('Error:', error);
			showAlert('An error occurred while submitting feedback', 'danger');
		});
	});
	
	document.getElementById('moreInfoForm').addEventListener('submit', function(e) {
		e.preventDefault();
		
		const formData = new FormData(this);
		
		fetch('<?php echo BASE_URL; ?>api/complaints', {
			method: 'POST',
			body: formData
		})
		.then(response => {
			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}
			return response.json();
		})
		.then(data => {
			if (data.error === false) {
				// Close modal
				bootstrap.Modal.getInstance(document.getElementById('moreInfoModal')).hide();
				
				// Show success message
				showAlert(data.message || 'Additional information submitted successfully!', 'success');
				
				// Reload page after a short delay
				setTimeout(() => {
					window.location.reload();
				}, 1500);
			} else {
				showAlert(data.message || 'Failed to submit additional information', 'danger');
			}
		})
		.catch(error => {
			console.error('Error:', error);
			showAlert('An error occurred while submitting additional information', 'danger');
		});
	});
});

// Alert function
function showAlert(message, type) {
	const alertDiv = document.createElement('div');
	alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
	alertDiv.innerHTML = `
		${message}
		<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
	`;
	
	// Insert at the top of the container
	const container = document.querySelector('.container-fluid');
	container.insertBefore(alertDiv, container.firstChild);
	
	// Auto-dismiss after 5 seconds
	setTimeout(() => {
		if (alertDiv.parentNode) {
			alertDiv.remove();
		}
	}, 5000);
}
</script>

