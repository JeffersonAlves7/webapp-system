<link rel="stylesheet" href="/public/grid-template.css">

<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light p-0">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">
        ATTUS
      </a>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link" href="/auth/info">
              <i class="bi bi-person-circle"></i>
              <?= $_SESSION["username"]; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/auth/logout">
              <i class="bi bi-box-arrow-right"></i>
              Sair
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>

<aside class="p-3">
  <nav>
    <ul class="nav flex-column flex-sm-row">
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Estoques") ? 'active' : ''; ?>" href="/">Estoques</a></li>
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Relatórios") ? 'active' : ''; ?>" href="#">Relatórios</a></li>
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Incluir Lançamento") ? 'active' : ''; ?>" href="/lancamento">Incluir Lançamento</a></li>
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Embarques") ? 'active' : ''; ?>" href="/embarques">Embarques</a></li>
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Produtos") ? 'active' : ''; ?>" href="/produtos">Lista de produtos</a></li>
      <li class="nav-item"><a class="nav-link <?= (isset($pageTitle) && $pageTitle === "Reservas") ? 'active' : ''; ?>" href="/reservas">Reservas</a></li>
    </ul>
  </nav>
</aside>