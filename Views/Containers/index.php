<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>

    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th><i class="bi bi-info-circle"></i></th>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-calendar-date"></i> Data</th>
                    <th><i class='bi bi-trash'></i></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($containers) && count($containers) > 0) {
                    foreach ($containers as $row) {
                ?>
                        <tr>
                            <td>
                                <a href="/embarques/produtos/<?= $row['ID'] ?>">
                                    <i class='bi bi-zoom-in'></i>
                                </a>
                            </td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="/embarques/deletar/<?= $row['ID'] ?>">
                                    <button type='button' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan='10'>Nenhum container encontrado.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>