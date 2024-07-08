<?php
$pageTitle = "Registrar-se";
ob_start();
?>
<div class="container mt-4">
    <h1 class="text-center mb-4">Registrar-se</h1>
    <form method="post" class="">
        <div class="mb-3">
            <label for="username" class="form-label">Nome de usuário</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Seu Nome de Usuário" pattern="[^\s]+" title="O nome de usuário não pode conter espaços em branco" required>
            <div class="invalid-feedback">O nome de usuário não pode conter espaços em branco.</div>
        </div>
        <div class="mb-3">
            <label for="user-email" class="form-label">Email</label>
            <input type="email" name="email" id="user-email" class="form-control" placeholder="example@email.com" required>
        </div>
        <div class="mb-3">
            <label for="user-password" class="form-label">Password</label>
            <input type="password" name="password" id="user-password" class="form-control" placeholder="******" required>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-custom btn-lg">Entrar</button>
        </div>
        <p class="mt-3 text-center">Já possuí conta? <a href="/auth/login">Faça seu login!</a></p>
    </form>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>