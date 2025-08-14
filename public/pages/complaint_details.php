<?php
/**
 * Complaint Details Page
 * Unified detailed view for admin, viewer, controller, and customer (owner)
 */
?>

<div class="container-fluid">
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
						<div class="col-md-6 mb-3">
							<label class="text-muted small">Priority</label>
							<div><span class="badge priority-<?php echo $complaint['priority']; ?>"><?php echo ucfirst($complaint['priority']); ?></span></div>
						</div>
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
						<div><strong><?php echo htmlspecialchars($complaint['assigned_to_name'] ?? 'Unassigned'); ?></strong></div>
						<small class="text-muted"><?php echo htmlspecialchars($complaint['assigned_to'] ?? '-'); ?></small>
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

