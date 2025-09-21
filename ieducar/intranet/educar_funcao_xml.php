<?php

header('Content-type: text/xml');

Portabilis_Utils_DeprecatedXmlApi::returnEmptyQueryUnlessUserIsLoggedIn();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<query xmlns=\"sugestoes\">\n";
if (is_numeric($_GET['ins'])) {
    $db = new clsBanco;
    $db->Consulta("SELECT cod_funcao, nm_funcao,professor FROM pmieducar.funcao WHERE ref_cod_instituicao = {$_GET['ins']} AND ativo = 1 ORDER BY nm_funcao ASC");
    while ($db->ProximoRegistro()) {
        [$cod, $nome, $professor] = $db->Tupla();

        $conc = $_GET['professor'] ? "-{$professor}" : '';

        echo "  <funcao cod_funcao=\"{$cod}{$conc}\">" . htmlspecialchars($nome, ENT_XML1, 'UTF-8') . "</funcao>\n";
    }
}
echo '</query>';
