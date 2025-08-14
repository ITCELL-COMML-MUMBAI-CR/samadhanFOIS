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
                    <?php foreach (['pending','replied','closed'] as $s): ?>
						<option value="<?php echo $s; ?>" <?php echo ($status??'')===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-2">
					<select class="form-select" name="priority">
						<option value="">All Priorities</option>
						<?php foreach (['normal','medium','high','critical'] as $p): ?>
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
						<tr class="text-center">
							<th class="text-center align-middle">ID</th>
							<th class="text-center align-middle">Category</th>
							<th class="text-center align-middle">Type</th>
							<th class="text-center align-middle">Customer</th>
							<th class="text-center align-middle">Priority</th>
							<th class="text-center align-middle">Status</th>
							<th class="text-center align-middle">Date</th>
							<th class="text-center align-middle">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($grievances)): ?>
						<tr><td colspan="8" class="text-center align-middle py-4 text-muted">No grievances found.</td></tr>
						<?php else: foreach ($grievances as $g): ?>
						<tr>
							<td class="text-center align-middle"><small class="text-muted"><?php echo htmlspecialchars($g['complaint_id']); ?></small></td>
							<td class="text-center align-middle">
								<?php if (!empty($g['category'])): ?>
									<span class="badge bg-warning text-dark"><?php echo htmlspecialchars($g['category']); ?></span>
								<?php else: ?>
									<small class="text-muted">N/A</small>
								<?php endif; ?>
							</td>
							<td class="text-center align-middle">
								<strong><?php echo htmlspecialchars($g['complaint_type']); ?></strong><br>
								<small class="text-muted"><?php echo htmlspecialchars($g['complaint_subtype']); ?></small>
							</td>
							<td class="text-center align-middle"><?php echo htmlspecialchars($g['customer_name'] ?? 'Unknown'); ?></td>
							<td class="text-center align-middle"><span class="badge priority-<?php echo $g['display_priority'] ?? $g['priority']; ?>"><?php echo ucfirst($g['display_priority'] ?? $g['priority']); ?></span></td>
                            <td class="text-center align-middle"><span class="badge status-<?php echo str_replace('_','-',$g['status']); ?>"><?php echo $g['status']==='replied'?'Replied':ucfirst(str_replace('_',' ',$g['status'])); ?></span></td>
							<td class="text-center align-middle"><small><?php echo date('d-M-Y', strtotime($g['date'])); ?></small></td>
							<td class="text-center align-middle">
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

