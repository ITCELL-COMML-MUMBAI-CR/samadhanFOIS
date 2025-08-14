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
						<tr>
							<th>ID</th>
							<th>Type</th>
							<th>Priority</th>
							<th>Status</th>
							<th>Date</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($grievances)): ?>
						<tr><td colspan="6" class="text-center py-4 text-muted">You have not submitted any grievances yet.</td></tr>
						<?php else: foreach ($grievances as $g): ?>
						<tr>
							<td><small class="text-muted"><?php echo htmlspecialchars($g['complaint_id']); ?></small></td>
							<td>
								<strong><?php echo htmlspecialchars($g['complaint_type']); ?></strong><br>
								<small class="text-muted"><?php echo htmlspecialchars($g['complaint_subtype']); ?></small>
							</td>
							<td><span class="badge priority-<?php echo $g['priority']; ?>"><?php echo ucfirst($g['priority']); ?></span></td>
							<td><span class="badge status-<?php echo str_replace('_','-',$g['status']); ?>"><?php echo ucfirst(str_replace('_',' ',$g['status'])); ?></span></td>
							<td><small><?php echo date('d-M-Y', strtotime($g['date'])); ?></small></td>
							<td class="text-end">
								<a class="btn btn-outline-primary btn-sm" href="<?php echo BASE_URL; ?>complaints/view/<?php echo urlencode($g['complaint_id']); ?>">
									<i class="fas fa-eye"></i>
								</a>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

