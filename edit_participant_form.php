<div class="card-panel">
    <h2 class="h4 mb-3">Participant details</h2>
    <form action="edit_participant.php" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="participant_id" class="form-label">Participant ID</label>
                <p class="form-control-plaintext" id="participant_id"><?php echo (int)$participant['id']; ?></p>
            </div>
            <div class="col-md-6">
                <span class="form-label d-block">Club</span>
                <p class="form-control-plaintext"><?php echo htmlspecialchars((string)($participant['club_name'] ?? 'Individual'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-md-6">
                <span class="form-label d-block">First name</span>
                <p class="form-control-plaintext"><?php echo htmlspecialchars((string)$participant['firstname'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-md-6">
                <span class="form-label d-block">Surname</span>
                <p class="form-control-plaintext"><?php echo htmlspecialchars((string)$participant['surname'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-12">
                <span class="form-label d-block">Email</span>
                <p class="form-control-plaintext"><?php echo htmlspecialchars((string)$participant['email'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col-md-6">
                <label for="power_output" class="form-label">Power output in watts</label>
                <input type="number" min="0" max="5000" step="0.01" class="form-control" id="power_output" name="power_output" value="<?php echo htmlspecialchars((string)$formValues['power_output'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="distance" class="form-label">Distance in KM</label>
                <input type="number" min="0" max="1000" step="0.01" class="form-control" id="distance" name="distance" value="<?php echo htmlspecialchars((string)$formValues['distance'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        </div>

        <input type="hidden" name="id" value="<?php echo (int)$participant['id']; ?>">

        <div class="d-flex flex-wrap gap-3 mt-4">
            <button type="submit" class="btn btn-primary">Update this rider</button>
            <a href="view_participants_edit_delete.php" class="btn btn-outline-secondary">Back to Participant List</a>
            <a href="admin_menu.php" class="btn btn-outline-primary">Back to Dashboard</a>
        </div>
    </form>
</div>