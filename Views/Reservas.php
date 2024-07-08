<?php
$pageTitle = "Reservas";
ob_start();
?>

<?php require "Components/Header.php" ?>

<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>

    <!-- Estoques -->
    <div class="d-flex gap-3">
        <form method="get">
            <button type="submit" class="btn btn-custom <?= isset($_GET["estoque"]) ? "" : "active" ?>">Geral</button>
            <input type="hidden" name="client" value="<?= isset($_GET["client"]) ? $_GET["client"] : "" ?>">
            <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
        </form>

        <?php
        if (isset($stocks) && $stocks->num_rows > 0) {
            while ($estoque = $stocks->fetch_assoc()) {
                $name = $estoque["name"];
                $ID = $estoque["ID"];
                $active = (isset($_GET["estoque"]) && $_GET["estoque"] == "$ID" ? "active" : "");
        ?>
                <form method='get'>
                    <input type='hidden' name='estoque' value='<?= $ID ?>' />
                    <button type='submit' class='btn btn-custom <?= $active ?>'><?= $name ?></button>
                    <input type="hidden" name="client" value="<?= isset($_GET["client"]) ? $_GET["client"] : "" ?>">
                    <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
                </form>
        <?php
            }
        }
        ?>
    </div>

    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <input type="hidden" name="estoque" value="<?= isset($_GET["estoque"]) ? $_GET["estoque"] : "" ?>">

        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index">
                <tr>
                    <th colspan="2"><input type="search" class="form-control" name="code" placeholder="Filtrar por código" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>"></th>
                    <th colspan="2"><input type="search" class="form-control" name="client" placeholder="Filtrar por cliente" value="<?= isset($_GET["client"]) ? $_GET["client"] : "" ?>"></th>
                    <th><button type="submit" class="btn btn-custom">Pesquisar</button></th>
                    <th colspan="3"></th>
                </tr>
                <tr>
                    <th>CÓDIGO</th>
                    <th>QUANTIDADE</th>
                    <th>ESTOQUE</th>
                    <th>CLIENTE</th>
                    <th>DATA DA RESERVA</th>
                    <th colspan="2">AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($reserves) && $reserves->num_rows > 0) {
                    while ($row = $reserves->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= $row['origin_container'] ?></td>
                            <td><?= $row['client_name'] ?></td>
                            <td><?= $row['rescue_date'] ?></td>
                            <td>
                                <button type='button' class='btn btn-danger cancel-reserve' data-id='<?= $row['ID'] ?>' data-bs-toggle='modal' data-bs-target='#cancelModal'>Cancelar</button>
                            </td>
                            <td>
                                <button type='button' class='btn btn-success confirm-reserve' data-id='<?= $row['ID'] ?>' data-bs-toggle='modal' data-bs-target='#confirmModal'>Confirmar</button>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan='7'>Nenhuma reserva encontrada.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>

        <button type="submit" hidden></button>
    </form>

    <div class="d-flex justify-content-between mt-3">
        <form method="GET">
            <input type="hidden" name="estoque" value="<?= isset($_GET["estoque"]) ? $_GET["estoque"] : "" ?>">
            <input type="hidden" name="client" value="<?= isset($_GET["client"]) ? $_GET["client"] : "" ?>">
            <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
            <input type="hidden" name="page" value="<?= ($_GET['page'] ?? 1) - 1; ?>">
            <button class="btn btn-custom" <?php if (!isset($_GET["page"]) || intval($_GET["page"]) <= 1) {
                                                echo "disabled";
                                            } ?>>Voltar</button>
        </form>

        <form method="GET">
            <input type="hidden" name="estoque" value="<?= isset($_GET["estoque"]) ? $_GET["estoque"] : "" ?>">
            <input type="hidden" name="client" value="<?= isset($_GET["client"]) ? $_GET["client"] : "" ?>">
            <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
            <input type="hidden" name="page" value="<?= ($_GET['page'] ?? 1) + 1; ?>">
            <button class="btn btn-custom" <?php if (!isset($reserves) || !$reserves->num_rows > 0) {
                                                echo "disabled";
                                            } ?>>Próxima</button>
        </form>
    </div>
</main>

<!-- Modal para confirmar cancelamento da reserva -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancelar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelForm" method="post" action="/reservas/cancelar">
                <div class="modal-body">
                    <input type="hidden" name="ID" id="cancelReserveId" value="">
                    <p>Tem certeza de que deseja cancelar esta reserva?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelBtn">Confirmar Cancelamento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para confirmar reserva -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="confirmForm" method="post" action="/reservas/confirmar">
                <div class="modal-body">
                    <input type="hidden" name="ID" id="confirmReserveId" value="">
                    <p>Tem certeza de que deseja confirmar esta reserva?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="confirmReserveBtn">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>

<!-- JavaScript para adicionar funcionalidade aos modais -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Adiciona manipuladores de eventos para os botões de cancelamento
        document.querySelectorAll('.cancel-reserve').forEach(function(element) {
            element.addEventListener('click', function() {
                var reserveID = this.getAttribute('data-id');
                document.getElementById('cancelReserveId').value = reserveID;
            });
        });

        // Adiciona manipuladores de eventos para os botões de confirmação
        document.querySelectorAll('.confirm-reserve').forEach(function(element) {
            element.addEventListener('click', function() {
                var reserveID = this.getAttribute('data-id');
                document.getElementById('confirmReserveId').value = reserveID;
            });
        });
    });
</script>