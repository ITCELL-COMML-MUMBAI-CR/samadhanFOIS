<?php
$trackId = $_GET['id'] ?? '';
?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="card">
				<div class="card-body">
					<h1 class="h3 mb-3"><i class="fas fa-search text-primary"></i> Track Status</h1>
					<form method="GET" class="row g-2">
						<div class="col-md-9">
							<input type="text" class="form-control" name="id" placeholder="Enter Complaint ID (e.g., CMP202401010001)" value="<?php echo htmlspecialchars($trackId); ?>">
						</div>
						<div class="col-md-3">
							<button class="btn btn-railway-primary w-100"><i class="fas fa-search"></i> Track</button>
						</div>
					</form>
					<?php if (!empty($trackId)): ?>
						<hr>
						<p>Open details: <a href="<?php echo BASE_URL; ?>complaints/view/<?php echo urlencode($trackId); ?>" target="_blank">View Complaint</a></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

