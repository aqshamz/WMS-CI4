<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <button id="toggleBtn" class="btn btn-outline-light">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="<?= base_url('dashboard') ?>">HEHE</a>
        <div>
            <span class="text-white me-3">
                <?= session()->get('user_email') ?? 'Guest' ?>
            </span>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </div>
</nav>
