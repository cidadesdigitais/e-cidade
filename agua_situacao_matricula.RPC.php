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

require("libs/db_stdlib.php");
require("libs/db_utils.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("dbforms/db_funcoes.php");
include("libs/JSON.php");

$oJson    = new services_json();

$oParam   = $oJson->decode(str_replace("\\","",$_POST["json"]));

$oRetorno = new stdClass();

$oRetorno->status  = 1;

$oRetorno->message = 1;

switch($oParam->exec) {
  case 'getStatus':
    
    require_once('classes/db_aguacoletorexporta_classe.php');
    
    try{
    
      $oDaoAguaColetorExporta = new cl_aguacoletorexporta;
      
      $sWhere = "x50_matric = $oParam->matricula and x49_situacao = 1";
      
      $sSql  = $oDaoAguaColetorExporta->sql_query_status_leitura(null, 'count(*)', null, $sWhere);
      
      $rsSql = $oDaoAguaColetorExporta->sql_record($sSql);
      
      $oFieldsMemory = db_utils::fieldsMemory($rsSql, 0);
      
      if($oFieldsMemory->count > 0) {
  
        $oRetorno->situacao = 1;
        
      }else {
        
        $oRetorno->situacao = 0;
         
      }
      
    }catch (Exception $eErro) {
      
      $oRetorno->status  = 2;
      $oRetorno->message = urlencode($eErro->getMessage());
      
    }
     
    echo $oJson->encode($oRetorno);
    break;
}

?>