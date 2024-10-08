<link rel="stylesheet" href="/public/grid-template.css">

<header>
  <nav class="navbar navbar-expand-md p-0">
    <div id="logoArea">
      <a class="navbar-brand p-0 m-0" href="/">ATTUS SYSTEM</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse w-100" id="navbarSupportedContent">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Estoques") ? 'active' : ''; ?>" href="/">Estoques</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Relatórios") ? 'active' : ''; ?>" href="/relatorios">Relatórios</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Incluir Lançamento") ? 'active' : ''; ?>" href="/lancamento">Incluir Lançamento</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Embarques") ? 'active' : ''; ?>" href="/embarques">Embarques</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Produtos") ? 'active' : ''; ?>" href="/produtos">Lista de produtos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Reservas") ? 'active' : ''; ?>" href="/reservas">Reservas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Painel") ? 'active' : ''; ?>" href="/painel">Painel</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Histórico") ? 'active' : ''; ?>" href="/historico">Histórico</a>
        </li>
        <li class="nav-item">
          <p class="nav-link" id="user-info">
            <i class="bi bi-person-circle"></i>
            <?php
            $user = AuthManager::getUser();
            echo $user === null ? '' : ' ' . $user['name'];
            ?>
            <a id="logout-button" style="display: none;" href="/auth/logout">Logout</a>
          </p>
        </li>
      </ul>
    </div>
  </nav>
</header>