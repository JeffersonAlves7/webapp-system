<?php
$pageTitle = "Login";
ob_start();
?>
<div class="container mt-4">
    <h1 class="text-center mb-4">Login</h1>

    <form method="post">
        <div class="mb-3">
            <label for="user-email" class="form-label">Email</label>
            <input type="email" name="email" id="user-email" class="form-control" placeholder="example@email.com" required>
        </div>
        <div class="mb-3">
            <label for="user-password" class="form-label">Password</label>
            <input type="password" name="password" id="user-password" class="form-control" placeholder="*********" required>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
        </div>
        <p class="mt-3 text-center">Não possui conta? <a href="/auth/register">Registrar-se</a></p>
    </form>
</div>

<?php
include "Components/StatusMessage.php";
$content = ob_get_clean();
include "Components/Template.php";
?>