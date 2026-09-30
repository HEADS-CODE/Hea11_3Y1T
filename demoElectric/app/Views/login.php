<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-bolt text-warning fa-3x mb-3"></i>
                            <h2 class="fw-bold text-primary-custom">Welcome back</h2>
                            <p class="text-muted">Log in to access customer accounts.</p>
                        </div>
                        <?php if ($error = session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= esc($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success = session()->getFlashdata('success')): ?>
                            <div class="alert alert-success"><?= esc($success) ?></div>
                        <?php endif; ?>
                        <form method="post" action="<?= base_url('login') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email address</label>
                                <input class="form-control form-control-lg" type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="password">Password</label>
                                <input class="form-control form-control-lg" type="password" id="password" name="password" required>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Log in</button>
                        </form>
                        <p class="text-center mt-4 mb-0">No account yet? <a href="<?= base_url('register') ?>">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
