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
            <span class="nav-link">
              <i class="bi bi-person-circle"></i>
              <?php echo $_SESSION["username"]; ?>
            </span>
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
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Home") ? 'active' : ''; ?>" href="/">Produtos</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Estoques") ? 'active' : ''; ?>" href="/estoques">Estoques</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Relatórios") ? 'active' : ''; ?>" href="#">Relatórios</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Incluir Lançamento") ? 'active' : ''; ?>" href="/lancamento">Incluir Lançamento</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Embarques") ? 'active' : ''; ?>" href="/embarques">Embarques</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Lista de produtos") ? 'active' : ''; ?>" href="#">Lista de produtos</a></li>
      <li class="nav-item"><a class="nav-link <?php echo (isset($pageTitle) && $pageTitle === "Reservas") ? 'active' : ''; ?>" href="/reservas">Reservas</a></li>
    </ul>
  </nav>
</aside>