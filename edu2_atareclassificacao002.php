<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

require_once('libs/db_stdlib.php');
require_once('libs/db_conecta.php');
require_once('dbforms/db_funcoes.php');
require_once("libs/JSON.php");
require_once('libs/db_sessoes.php');
require_once('libs/db_usuariosonline.php');
require_once('libs/db_utils.php');
require_once('std/db_stdClass.php');
require_once('libs/db_libsys.php');
require_once('dbagata/classes/core/AgataAPI.class');
require_once('model/documentoTemplate.model.php');

require_once('dbagata/functions/numbers/db_numero_to_palavra.fun');
require_once('dbagata/functions/time/db_transcreve_data.fun');

$oJson            = new Services_JSON();
$oGet             = db_utils::postMemory($_GET);

$aEmissor         = explode("|", $oGet->sEmissor);
$aAlunos          = $oJson->decode(str_replace("\\", "", $oGet->aAlunos)); 

$aAlunosFiltrados = array();

foreach ($aAlunos as $oAluno) {
  $aAlunosFiltrados[] = $oAluno->iAluno;
}

$sAlunos  = "'". implode("', '", $aAlunosFiltrados) . "'";

ini_set("error_reporting","E_ALL & ~NOTICE");

$oAgata           = new cl_dbagata("escola/ata_reclassificacao.agt");
$oApiAgata        = $oAgata->api;
$sCaminhoSalvoSxw = "tmp/__ata_reclassificacao" . date('YmdHis') . "_" . db_getsession("DB_id_usuario") . ".sxw";

$oApiAgata->setOutputPath($sCaminhoSalvoSxw);

$oApiAgata->setParameter('$iEscola', $oGet->iEscola);
$oApiAgata->setParameter('$iCalendario', $oGet->iCalendario);
$oApiAgata->setParameter('$sEmissor', $aEmissor[1]);
$oApiAgata->setParameter('$sAtividadeEmissor', $aEmissor[0]);
$oApiAgata->setParameter('$iAlunos', $sAlunos);


try {
  
  $oDocumentoTemplate = new documentoTemplate(43, $oGet->iModelo);

} catch (Exception $eException){

  $sErroMsg  = $eException->getMessage();
  db_redireciona("db_erros.php?fechar=true&db_erro={$sErroMsg}");
}

if ( $oApiAgata->parseOpenOffice( $oDocumentoTemplate->getArquivoTemplate() ) ) {

  $sNomeRelatorio   = "tmp/ata_reclassificacao" . date('YmdHis') . "_" . db_getsession("DB_id_usuario") . ".pdf";
  $lConversao       = db_stdClass::ex_oo2pdf($sCaminhoSalvoSxw, $sNomeRelatorio);

  if (!$lConversao) {
    db_redireciona("db_erros.php?fechar=true&db_erro=Falha ao gerar PDF !!!");
  } else {
    
    db_redireciona($sNomeRelatorio);
  }
}