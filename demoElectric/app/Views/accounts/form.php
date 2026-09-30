<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="section-padding bg-light-custom">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-8">
        <div class="card border-0 shadow-lg"><div class="card-body p-5">
            <h2 class="text-primary-custom mb-4"><?= esc($title) ?></h2>
            <?php if (! empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <form method="post" action="<?= esc($formAction) ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Account Number *</label><input class="form-control" name="account_number" value="<?= old('account_number', $account['account_number'] ?? '') ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Customer Name *</label><input class="form-control" name="customer_name" value="<?= old('customer_name', $account['customer_name'] ?? '') ?>" required></div>
                    <div class="col-12"><label class="form-label">Address *</label><textarea class="form-control" name="address" rows="2" required><?= old('address', $account['address'] ?? '') ?></textarea></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= old('phone', $account['phone'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= old('email', $account['email'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Meter Number</label><input class="form-control" name="meter_number" value="<?= old('meter_number', $account['meter_number'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Connection Type *</label><select class="form-select" name="connection_type" required><?php foreach (['residential', 'commercial', 'industrial'] as $type): ?><option value="<?= $type ?>" <?= old('connection_type', $account['connection_type'] ?? 'residential') === $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Status *</label><select class="form-select" name="status" required><?php foreach (['active', 'inactive', 'suspended'] as $status): ?><option value="<?= $status ?>" <?= old('status', $account['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit">Save Account</button><a class="btn btn-outline-secondary" href="<?= base_url('accounts') ?>">Cancel</a></div>
            </form>
        </div></div>
    </div></div></div>
</section>
<?= $this->endSection() ?>
