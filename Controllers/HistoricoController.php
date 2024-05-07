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

    public function index(){
        $this->view("Historico/Index");
    }

    public function transferencias()
    {
        $transferencias = $this->historicoModel->getTransferencias();
        $this->view("Historico/Transferencias", ["transferencias" => $transferencias]);
    }
}
