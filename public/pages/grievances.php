<?php
/**
 * All Grievances Page (Admin/Controller)
 */
?>

<div class="container-fluid">
	<div class="row mb-3">
		<div class="col-12 d-flex justify-content-between align-items-center">
			<h1 class="h3 mb-0"><i class="fas fa-clipboard-list text-primary"></i> All Grievances</h1>
			<a href="<?php echo BASE_URL; ?>grievances/new" class="btn btn-railway-primary btn-sm"><i class="fas fa-plus-circle"></i> New</a>
		</div>
	</div>

	<div class="card mb-3">
		<div class="card-body">
			<form method="GET" class="row g-2">
				<div class="col-md-2">
					<select class="form-select" name="status">
						<option value="">All Status</option>
						<?php foreach (['pending','in_progress','resolved','closed'] as $s): ?>
						<option value="<?php echo $s; ?>" <?php echo ($status??'')===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-2">
					<select class="form-select" name="priority">
						<option value="">All Priorities</option>
						<?php foreach (['low','medium','high','critical'] as $p): ?>
						<option value="<?php echo $p; ?>" <?php echo ($priority??'')===$p?'selected':''; ?>><?php echo ucfirst($p); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-2">
					<input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom??''); ?>">
				</div>
				<div class="col-md-2">
					<input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo??''); ?>">
				</div>
				<div class="col-md-3">
					<input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search??''); ?>">
				</div>
				<div class="col-md-1">
					<button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i></button>
				</div>
			</form>
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
							<th>Customer</th>
							<th>Priority</th>
							<th>Status</th>
							<th>Date</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($grievances)): ?>
						<tr><td colspan="7" class="text-center py-4 text-muted">No grievances found.</td></tr>
						<?php else: foreach ($grievances as $g): ?>
						<tr>
							<td><small class="text-muted"><?php echo htmlspecialchars($g['complaint_id']); ?></small></td>
							<td>
								<strong><?php echo htmlspecialchars($g['complaint_type']); ?></strong><br>
								<small class="text-muted"><?php echo htmlspecialchars($g['complaint_subtype']); ?></small>
							</td>
							<td><?php echo htmlspecialchars($g['customer_name'] ?? 'Unknown'); ?></td>
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

