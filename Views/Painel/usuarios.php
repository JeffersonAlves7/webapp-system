<?php
$pageTitle = "Painel";

ob_start();

require "Components/Header.php";
?>

<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom" data-url="/painel">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Usuários</h1>
    </div>

    <table class="table table-striped">
        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Grupo</th>
                <th>Ativo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <?php if ($user["ID"] == 1) continue; ?>
                <tr data-id="<?= $user["ID"] ?>">
                    <td><?= $user["username"] ?></td>
                    <td><?= $user["email"] ?></td>
                    <td>
                        <?php
                        $group = array_filter($groups, function ($group) use ($user) {
                            return $group["ID"] == $user["group_ID"];
                        });
                        $hasGroup = !empty($group);
                        $groupName = array_values($group)[0]["name"] ?? "Sem permisões";
                        ?>

                        <select class="form-select" data-value="<?= !$hasGroup ? 0 : $user["group_ID"]  ?>">
                            <?php foreach ($groups as $group) : ?>
                                <option value="<?= $group["ID"] ?>" <?= $group["ID"] == $user["group_ID"] ? "selected" : "" ?>>
                                    <?= $group["name"] ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (!$hasGroup) : ?>
                                <option value="0" selected>
                                    <?= $groupName ?>
                                </option>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" <?= $user["active"] ? "checked" : "" ?> data-value="<?= $user["active"] ? 'true' : 'false' ?>">
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <!-- <button class="btn bg-secondary text-white" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button> -->
                            <button class="btn bg-success text-white btn-save" title="Salvar" disabled>
                                <i class="bi bi-check"></i>
                            </button>

                            <button class="btn bg-danger text-white btn-cancel" title="Cancelar" disabled>
                                <i class="bi bi-x"></i>
                            </button>

                            <form method="post">
                                <input type="hidden" name="user_ID" value="<?= $user["ID"] ?>">
                                <input type="hidden" name="_method" value="delete">
                                <button class="btn bg-danger text-white" title="Apagar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($users)) : ?>
                <tr>
                    <td colspan="4" class="text-center">Nenhum usuário</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($pageCount > 1) : ?>
        <?php
        function isButtonDisabled($condition)
        {
            return $condition ? 'disabled' : '';
        }

        $currentPage = $_GET['page'] ?? 1;
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $isPrevDisabled = !isset($_GET["page"]) || intval($_GET["page"]) <= 1;
        $isNextDisabled = !isset($products) || $products->num_rows <= 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php include "Components/StatusMessage.php"; ?>
</main>

<script>
    // Quando uma alteração é feita, habilita o botão de salvar e cancelar (Se cancelar, volta ao estado anterior) daquela linha
    document.querySelectorAll("select, input[type=checkbox]").forEach((element) => {
        element.addEventListener("change", (event) => {
            const tr = event.target.closest("tr");
            const btns = tr.querySelectorAll(".btn-save, .btn-cancel");

            btns.forEach((btn) => {
                btn.disabled = false;
            });
        });
    });

    // Quando o botão de cancelar é clicado, volta ao estado anterior
    document.querySelectorAll(".btn-cancel").forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const tr = event.target.closest("tr");
            const select = tr.querySelector("select");
            const checkbox = tr.querySelector("input[type=checkbox]");
            const btns = tr.querySelectorAll(".btn-save, .btn-cancel");


            select.value = select.getAttribute("data-value");
            checkbox.checked = checkbox.getAttribute("data-value") === "true";

            console.log({
                selectValue: select.getAttribute("data-value"),
                checkboxValue: checkbox.getAttribute("data-value")
            })

            btns.forEach((btn) => {
                btn.disabled = true;
            });
        });
    });

    // Quando o botão de salvar é clicado, salva as alterações na rota /painel via POST
    document.querySelectorAll(".btn-save").forEach((btn) => {
        btn.addEventListener("click", (event) => {
            const tr = event.target.closest("tr");
            const select = tr.querySelector("select");
            const checkbox = tr.querySelector("input[type=checkbox]");
            const btns = tr.querySelectorAll(".btn-save, .btn-cancel");

            const data = new FormData();
            data.append("user_ID", tr.getAttribute("data-id"));
            data.append("group_ID", select.value);
            data.append("active", checkbox.checked ? 1 : 0);

            fetch("/painel/updateUser", {
                method: "POST",
                body: data
            }).then(() => {
                btns.forEach((btn) => {
                    btn.disabled = true;
                });

                // Mostrar mensagem de sucesso
                const successAlert = document.createElement("div");
                successAlert.classList.add("alert", "alert-success", "alert-dismissible", "fade", "show", "fixed-bottom", "mx-auto", "my-3");
                successAlert.setAttribute("role", "alert");
                successAlert.style.maxWidth = "600px";
                successAlert.innerHTML = `
                    <strong>Sucesso!</strong> ao realizar ação.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(successAlert);

                setTimeout(() => {
                    successAlert.remove();
                }, 5000);
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>