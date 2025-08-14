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
							<label class="text-muted small">Type / Subtype</label>
							<div><strong><?php echo htmlspecialchars($complaint['complaint_type']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($complaint['complaint_subtype']); ?></small></div>
						</div>
						<?php if (($currentUser['role'] ?? '') !== 'customer'): ?>
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Priority</label>
							<div><span class="badge priority-<?php echo $complaint['priority']; ?>"><?php echo ucfirst($complaint['priority']); ?></span></div>
						</div>
						<?php endif; ?>
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Location</label>
							<div><i class="fas fa-map-marker-alt text-muted"></i> <?php echo htmlspecialchars($complaint['location']); ?></div>
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
								<div class="ratio ratio-4x3 border rounded overflow-hidden">
									<img src="<?php echo htmlspecialchars($img['url'] ?? (BASE_URL . 'uploads/evidences/' . $img['filename'])); ?>" alt="Evidence" class="w-100 h-100 object-fit-cover">
								</div>
								<div class="d-flex justify-content-between mt-1">
									<small class="text-muted"><?php echo round(($img['size'] ?? 0) / 1024); ?> KB</small>
									<a class="small" href="<?php echo htmlspecialchars($img['url'] ?? (BASE_URL . 'uploads/evidences/' . $img['filename'])); ?>" target="_blank">Open</a>
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
									<strong class="text-danger">Rejection (<?php echo str_replace('_',' ', htmlspecialchars($rej['rejection_stage'])); ?>)</strong>
									<small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($rej['created_at'])); ?></small>
								</div>
								<div class="small text-muted mb-1">
									by <?php echo htmlspecialchars($rej['rejected_by_name'] ?? $rej['rejected_by']); ?>
									<?php if (!empty($rej['rejected_to_name'])): ?> → <?php echo htmlspecialchars($rej['rejected_to_name']); ?><?php endif; ?>
								</div>
								<div><?php echo nl2br(htmlspecialchars($rej['rejection_reason'] ?? '')); ?></div>
							</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php else: ?>
			<!-- Customer-only section: Status info -->
			<div class="card mb-3">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-info-circle"></i> Status Information</h5>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<label class="text-muted small">Current Status</label>
						<div>
							<span class="badge status-<?php echo str_replace('_','-',$complaint['status']); ?>">
								<?php echo ucfirst(str_replace('_',' ', $complaint['status'])); ?>
							</span>
						</div>
					</div>
					<div class="mb-0">
						<label class="text-muted small">Last Updated</label>
						<div><?php echo date('d-M-Y H:i', strtotime($complaint['updated_at'] ?? $complaint['created_at'])); ?></div>
					</div>
				</div>
			</div>

			<!-- Customer Transaction History -->
			<div class="card">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-history"></i> Activity</h5>
				</div>
				<div class="card-body">
					<?php 
					// Filter transactions for customers - show only customer-relevant activities
					$customerTransactions = [];
					foreach ($history as $event) {
						$transactionType = $event['transaction_type'] ?? '';
						$remarks = $event['remarks'] ?? '';
						
						// Include customer's own feedback
						if (strpos($remarks, 'Customer feedback:') === 0) {
							$customerTransactions[] = [
								'type' => 'Your Feedback',
								'date' => $event['created_at'],
								'content' => str_replace('Customer feedback: ', '', $remarks),
								'icon' => 'comment'
							];
						}
						// Include customer providing more information
						elseif (strpos($remarks, 'Customer provided more information:') === 0) {
							$customerTransactions[] = [
								'type' => 'Additional Information Provided',
								'date' => $event['created_at'],
								'content' => str_replace('Customer provided more information: ', '', $remarks),
								'icon' => 'info-circle'
							];
						}
						// Include action taken by staff (when status changes to replied/resolved)
						elseif ($transactionType === 'status_update' && 
								(strpos($remarks, 'Closed by controller') === 0 || 
								 strpos($remarks, 'Commercial approval granted') === 0 ||
								 strpos($remarks, 'Action taken:') !== false)) {
							$customerTransactions[] = [
								'type' => 'Action Taken',
								'date' => $event['created_at'],
								'content' => $remarks,
								'icon' => 'check-circle'
							];
						}
						// Include rejection/revert messages to customer
						elseif (strpos($remarks, 'Reverted to customer') === 0) {
							$customerTransactions[] = [
								'type' => 'More Information Requested',
								'date' => $event['created_at'],
								'content' => $remarks,
								'icon' => 'question-circle'
							];
						}
					}

					// Also check rejections for customer-relevant content
					foreach ($rejections as $rej) {
						if ($rej['rejection_stage'] === 'commercial_to_customer') {
							$customerTransactions[] = [
								'type' => 'More Information Requested',
								'date' => $rej['created_at'],
								'content' => $rej['rejection_reason'] ?? '',
								'icon' => 'question-circle'
							];
						}
					}

					// Sort by date (newest first)
					usort($customerTransactions, function($a, $b) {
						return strtotime($b['date']) - strtotime($a['date']);
					});
					?>

					<?php if (empty($customerTransactions)): ?>
						<p class="text-muted mb-0">No activity yet.</p>
					<?php else: ?>
						<div class="timeline">
							<?php foreach ($customerTransactions as $transaction): ?>
							<div class="timeline-item">
								<div class="d-flex justify-content-between align-items-start">
									<div class="d-flex align-items-center">
										<i class="fas fa-<?php echo $transaction['icon']; ?> text-primary me-2"></i>
										<strong><?php echo htmlspecialchars($transaction['type']); ?></strong>
									</div>
									<small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($transaction['date'])); ?></small>
								</div>
								<div class="mt-2"><?php echo nl2br(htmlspecialchars($transaction['content'])); ?></div>
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
                    <?php if (in_array($complaint['status'], ['replied','resolved'])): ?>
						<form method="POST" class="mb-3">
							<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
							<input type="hidden" name="action" value="submit_feedback">
							<div class="form-floating mb-2">
								<textarea class="form-control" name="feedback_text" placeholder="Your feedback" style="height: 90px"></textarea>
                                <label>Provide Feedback (will close the complaint)</label>
							</div>
							<button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Submit Feedback & Close</button>
						</form>
					<?php endif; ?>

					<?php if ($complaint['status'] === 'rejected'): ?>
						<form method="POST">
							<input type="hidden" name="csrf_token" value="<?php echo SessionManager::generateCSRFToken(); ?>">
							<input type="hidden" name="action" value="submit_more_info">
							<div class="form-floating mb-2">
								<textarea class="form-control" name="more_info_text" placeholder="Add more information requested" style="height: 120px"></textarea>
								<label>Provide More Information (requested by Commercial)</label>
							</div>
							<button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-paper-plane"></i> Submit Additional Info</button>
						</form>
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
</style>

