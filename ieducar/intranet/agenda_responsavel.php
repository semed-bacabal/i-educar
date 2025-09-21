<?php

return new class extends clsListagem
{
    public $cd_agenda;

    public $nm_agenda;

    public function Gerar()
    {
        $this->pessoa = $this->pessoa_logada;

        $this->titulo = 'Agendas que eu posso editar';

        $this->addCabecalhos(coluna: ['Agenda']);

        $this->campoTexto(nome: 'pesquisa', campo: 'Agenda', valor: '', tamanhovisivel: 50, tamanhomaximo: 255);

        $and = '';

        if (!empty($_GET['pesquisa'])) {
            $pesquisa = str_replace(search: ' ', replace: '%', subject: $_GET['pesquisa']);
            $and = "AND nm_agenda ilike ('%{$pesquisa}%')";
            $pesquisa = str_replace(search: '%', replace: ' ', subject: $_GET['pesquisa']);
        }

        $db = new clsBanco;
        $total = $db->UnicoCampo(consulta: "SELECT COUNT(0) + (SELECT COUNT(*) FROM portal.agenda_responsavel WHERE ref_ref_cod_pessoa_fj = {$this->pessoa} ) FROM portal.agenda WHERE ref_ref_cod_pessoa_own = {$this->pessoa} {$and} ");

        // Paginador
        $limite = 15;
        $sql = "SELECT cod_agenda, 1 AS minha FROM agenda WHERE ref_ref_cod_pessoa_own = {$this->pessoa} {$and} UNION SELECT ref_cod_agenda, 0 AS minha FROM agenda_responsavel WHERE ref_ref_cod_pessoa_fj = {$this->pessoa} ORDER BY minha DESC";

        $db1 = new clsBanco;
        $db1->Consulta(consulta: $sql);
        while ($db1->ProximoRegistro()) {
            [$cd_agenda, $propriedade] = $db1->Tupla();

            $db2 = new clsBanco;
            $db2->Consulta(consulta: "SELECT nm_agenda, ref_ref_cod_pessoa_own FROM agenda WHERE cod_agenda = {$cd_agenda} {$and}");
            while ($db2->ProximoRegistro()) {
                [$nm_agenda, $cod_pessoa_own] = $db2->Tupla();
                $this->addLinhas(linha: [
                    "<a href='agenda.php?cod_agenda={$cd_agenda}'><img src='imagens/noticia.jpg' border=0>$nm_agenda</a>"]);
            }
        }

        // Paginador
        $this->addPaginador2(strUrl: 'agenda_responsavel.php', intTotalRegistros: $total, mixVariaveisMantidas: $_GET, nome: $this->nome, intResultadosPorPagina: $limite);

        $this->largura = '100%';

        $this->breadcrumb(currentPage: 'Agendas');
    }

    public function Formular()
    {
        $this->title = 'Agenda';
        $this->processoAp = '341';
    }
};
