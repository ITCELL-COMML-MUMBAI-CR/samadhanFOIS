<?php $currentUser = SessionManager::getCurrentUser(); ?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-6">
			<div class="card">
				<div class="card-body">
					<h1 class="h3 mb-3"><i class="fas fa-user text-primary"></i> Profile</h1>
					<p><strong>Name:</strong> <?php echo htmlspecialchars($currentUser['name']); ?></p>
					<p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($currentUser['role'])); ?></p>
					<p><strong>Department:</strong> <?php echo htmlspecialchars($currentUser['department']); ?></p>
					<p><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

