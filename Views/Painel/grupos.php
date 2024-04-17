<?php
$pageTitle = "Painel";

ob_start();

require "Components/Header.php";
?>

<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Grupos</h1>
    </div>

    <!-- Adicionar também um botão para adicionar um novo grupo -->
    <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#editGroupModal">Adicionar grupo
        <i class="bi bi-plus"></i>
    </button>

    <!-- List groups without their permissions -->
    <div class="table-responsive">
        <table class="table table-striped" id="table-groups">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group) : ?>
                    <tr data-id="<?= $group["ID"] ?>">
                        <td><?= $group["name"] ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-warning edit-group" data-id="<?= $group["ID"] ?>" title="Editar grupo">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger delete-group" data-id="<?= $group["ID"] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include "Components/StatusMessage.php"; ?>
</main>

<!-- Modal para criar um novo group -->
<!-- <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGroupModalLabel">Adicionar grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editGroupForm" method="post" action="/painel/grupos">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">Nome do grupo</label>
                        <input type="text" class="form-control" id="groupName" name="name" required>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Funcionalidade</th>
                                    <th>Leitura</th>
                                    <th>Escrita</th>
                                    <th>Deleção</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($controllers as $controller) : ?>
                                    <tr>
                                        <td><?= $controller["controller"] ?></td>
                                        <td>
                                            <input type="checkbox" name="permissions[<?= $controller["controller"] ?>][read]">
                                        </td>
                                        <td>
                                            <input type="checkbox" name="permissions[<?= $controller["controller"] ?>][write]">
                                        </td>
                                        <td>
                                            <input type="checkbox" name="permissions[<?= $controller["controller"] ?>][delete]">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal para aparecer as permissoes do grpo quando clicar em alterar e tambem o nome do grupo que pode ser alterado -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGroupModalLabel">Editar grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editGroupForm" method="post" action="/painel/updateGroupPermissions">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">Nome do grupo</label>
                        <input type="text" class="form-control" id="groupName" name="name" required>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Funcionalidade</th>
                                    <th>Leitura</th>
                                    <th>Escrita</th>
                                    <th>Deleção</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsTable">
                                <!-- Permissions will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" name="group_ID" id="group_ID">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener("load", () => {
        const editGroupModal = new bootstrap.Modal(document.getElementById("editGroupModal"));
        const editGroupForm = document.getElementById("editGroupForm");
        const groupNameInput = document.getElementById("groupName");
        const permissionsTable = document.getElementById("permissionsTable");
        const group_ID = document.getElementById("group_ID");

        const editGroup = (groupName, groupId, groupObj) => {
            groupNameInput.value = groupName;
            group_ID.value = groupId;
            permissionsTable.innerHTML = "";

            for (const controller in groupObj) {
                const controllerPermissions = groupObj[controller];

                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${controllerPermissions.controller}</td>
                    <td>
                        <input type="checkbox" name="permissions[${controller}][read]" ${controllerPermissions.read ? "checked" : ""}>
                    </td>
                    <td>
                        <input type="checkbox" name="permissions[${controller}][write]" ${controllerPermissions.write ? "checked" : ""}>
                    </td>
                    <td>
                        <input type="checkbox" name="permissions[${controller}][delete]" ${controllerPermissions.delete ? "checked" : ""}>
                    </td>
                    <input type="hidden" name="permissions[${controller}][ID]" value="${controllerPermissions.ID}">
                `;

                permissionsTable.appendChild(tr);
            }


            editGroupModal.show();
        }

        document.querySelectorAll(".edit-group").forEach((button) => {
            button.addEventListener("click", async (event) => {
                const group_ID = event.currentTarget.dataset.id;

                const response = await fetch("/painel/grupos", {
                    method: "POST",
                    body: new URLSearchParams({
                        group_ID,
                        _method: "GET"
                    })
                });

                const groupName = document.querySelector(`#table-groups tr[data-id="${group_ID}"] td:first-child`).innerText;
                const group = await response.json();
                editGroup(groupName, group_ID, group);
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>