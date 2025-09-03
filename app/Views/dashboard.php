<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <h1 class="h3 mb-4">Welcome, <?= session()->get('user_name') ?>!</h1>
    <p>This is your dashboard.</p>
<?= $this->endSection() ?>
