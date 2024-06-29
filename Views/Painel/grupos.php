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
    <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#criarGroupModal">
        Adicionar grupo <i class="bi bi-plus"></i>
    </button>

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
                                <?php if ($group["ID"] == 1) : ?>
                                    <button class="btn btn-primary" disabled>
                                        <i class="bi bi-shield"></i>
                                    </button>
                                <?php else : ?>
                                    <button class="btn btn-warning edit-group" data-id="<?= $group["ID"] ?>" title="Editar grupo">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger delete-group" data-id="<?= $group["ID"] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include "Components/StatusMessage.php"; ?>
</main>

<div class="modal fade" id="criarGroupModal" tabindex="-1" aria-labelledby="criarGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="criarGroupModalLabel">Criar grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <form id="criarGroupForm" method="post" action="/painel/createGroup">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">Nome do grupo</label>
                        <input type="text" class="form-control" id="groupName" name="groupName" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
                                    <th>Edição</th>
                                </tr>
                                <tr>
                                    <td>
                                        <button id="selectAll" type="button" style="border: none; background: none; cursor: pointer;">
                                            <strong>
                                                <i class="bi bi-check2-all"></i> Selecionar todos
                                            </strong>
                                        </button>
                                    </td>
                                    <td>
                                        <input type="checkbox" id="selectAllLeitura">
                                    </td>
                                    <td>
                                        <input type="checkbox" id="selectAllEscrita">
                                    </td>
                                    <td>
                                        <input type="checkbox" id="selectAllDelecao">
                                    </td>
                                    <td>
                                        <input type="checkbox" id="selectAllEdicao">
                                    </td>
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

<div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteGroupModalLabel">Deletar grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Deseja realmente deletar este grupo?</p>
                <form id="deleteGroupForm" method="post" action="/painel/deleteGroup">
                    <input type="hidden" name="group_ID" id="group_ID">
                    <button type="submit" class="btn btn-danger">Deletar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener("load", () => {
        const editGroupModal = new bootstrap.Modal(document.getElementById("editGroupModal"));
        const editGroupForm = document.getElementById("editGroupForm");
        const groupNameInput = document.querySelector("#editGroupModal #groupName");
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
                    <td>
                        <input type="checkbox" name="permissions[${controller}][edit]" ${controllerPermissions.edit ? "checked" : ""}>
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
                // Descelecionar inputs de selectAll
                document.getElementById("selectAllLeitura").checked = false;
                document.getElementById("selectAllEscrita").checked = false;
                document.getElementById("selectAllDelecao").checked = false;
                document.getElementById("selectAllEdicao").checked = false;

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

        const deleteGroupModal = new bootstrap.Modal(document.getElementById("deleteGroupModal"));
        const deleteGroupForm = document.getElementById("deleteGroupForm");

        const deleteGroup = (group_ID) => {
            document.querySelector("#deleteGroupModal #group_ID").value = group_ID;
            deleteGroupModal.show();
        }

        document.querySelectorAll(".delete-group").forEach((button) => {
            button.addEventListener("click", (event) => {
                const group_ID = event.currentTarget.dataset.id;
                deleteGroup(group_ID);
            });
        });

        // Selecionar todos os checkboxes de leitura
        document.getElementById("selectAllLeitura").addEventListener("change", (event) => {
            const checkboxes = document.querySelectorAll("#permissionsTable input[name$='[read]']");
            checkboxes.forEach((checkbox) => {
                checkbox.checked = event.currentTarget.checked;
            });
        });

        // Selecionar todos os checkboxes de escrita
        document.getElementById("selectAllEscrita").addEventListener("change", (event) => {
            const checkboxes = document.querySelectorAll("#permissionsTable input[name$='[write]']");
            checkboxes.forEach((checkbox) => {
                checkbox.checked = event.currentTarget.checked;
            });
        });

        // Selecionar todos os checkboxes de deleção
        document.getElementById("selectAllDelecao").addEventListener("change", (event) => {
            const checkboxes = document.querySelectorAll("#permissionsTable input[name$='[delete]']");
            checkboxes.forEach((checkbox) => {
                checkbox.checked = event.currentTarget.checked;
            });
        });

        // Selecionar todos os checkboxes de edição
        document.getElementById("selectAllEdicao").addEventListener("change", (event) => {
            const checkboxes = document.querySelectorAll("#permissionsTable input[name$='[edit]']");
            checkboxes.forEach((checkbox) => {
                checkbox.checked = event.currentTarget.checked;
            });
        });

        // Selecionar todos os checkboxes
        document.getElementById("selectAll").addEventListener("click", (event) => {
            document.getElementById("selectAllLeitura").click();
            document.getElementById("selectAllEscrita").click();
            document.getElementById("selectAllDelecao").click();
            document.getElementById("selectAllEdicao").click();
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>