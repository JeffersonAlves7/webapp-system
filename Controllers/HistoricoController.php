<?php
require_once "Models/Historico.php";

class HistoricoController
{
    public $historicoModel;

    public function __construct()
    {
        $this->historicoModel = new Historico();
    }

    public function transferencias()
    {
        $transferencias = $this->historicoModel->getTransferencias();
        include "Views/Historico/Transferencias.php";
    }
}
