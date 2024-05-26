<?php
require_once "Controllers/_Controller.php";
require_once "Models/Relatorios.php";

class RelatoriosController extends _Controller
{
    private $relatorios;

    public function __construct()
    {
        parent::__construct();
        $this->relatorios = new Relatorios();
    }

    public function index()
    {
        $this->view("Relatorios/index");
    }

    public function saidasDiarias()
    {
        $dataSaida = null;
        $where = "1";

        if (isset($_GET["dataSaida"]) && !empty($_GET["dataSaida"])) {
            $dataSaida = $_GET["dataSaida"];
        }

        if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
            $cliente = $_GET["cliente"];
            $where = "t.client_name LIKE '%$cliente%'";
        }

        $dados = $this->relatorios->saidasDiarias($where, $dataSaida);

        $this->view("Relatorios/saidasDiarias", ["dados" => $dados]);
    }

    public function estoqueMinimo(){
        $page = 1;
        $limit = 30;
        $porcentagem = 0.50;
        $quantidadeDePaginas = 1;

        // if (isset($_GET["page"]) && !empty($_GET["page"])) {
        //     $page = $_GET["page"];
        // }

        // if (isset($_GET["limit"]) && !empty($_GET["limit"])) {
        //     $limit = $_GET["limit"];
        // }

        if (isset($_COOKIE["porcentagemParaAlerta"]) && !empty($_GET["porcentagemParaAlerta"])) {
            $porcentagem = $_COOKIE["porcentagemParaAlerta"];
        }

        // $quantidadeDePaginasCookie = "Relatorios:estoqueMinimo:quantidadeDePaginas";
        // if (isset($_COOKIE[$quantidadeDePaginasCookie]) && !empty($_COOKIE[$quantidadeDePaginasCookie])) {
        //     $quantidadeDePaginas = $_COOKIE[$quantidadeDePaginasCookie];
        // }

        $dados = $this->relatorios->estoqueMinimo($page, $limit, $porcentagem, $quantidadeDePaginas);

        $this->view("Relatorios/estoqueMinimo", [
            "dados" => $dados["dados"],
            "quantidadeDePaginas" => $dados["quantidadeDePaginas"],
            "page" => $page,
            "porcentagem" => $porcentagem,
        ]);
    }

    public function movimentacoes()
    {
        // Exemplo: ?dataMovimentacao=2021-09
        $dataMovimentacao = null;
        $page = 1;
        $limit = 30;

        if (isset($_GET["dataMovimentacao"]) && !empty($_GET["dataMovimentacao"])) {
            $dataMovimentacao = $_GET["dataMovimentacao"];
        }
        else{
            $dataMovimentacao = date("Y-m");
        }

        if (isset($_GET["page"]) && !empty($_GET["page"])) {
            $page = $_GET["page"];
        }

        $dados = $this->relatorios->movimentacoes(
            $dataMovimentacao,
            $page,
            $limit
        );

        $this->view("Relatorios/movimentacoes", ["dados" => $dados]);
    }
}
