<?php
require_once "Models/Historico.php";
require_once "Controllers/_Controller.php";

class HistoricoController extends _Controller
{
    public $historicoModel;

    public function __construct()
    {
        parent::__construct();
        $this->historicoModel = new Historico();
    }

    public function transferencias()
    {
        $transferencias = $this->historicoModel->getTransferencias();
        include "Views/Historico/Transferencias.php";
    }
}
