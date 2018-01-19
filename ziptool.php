<?php
/**
 * Ziptool - Browse files developed in PHP
 * By Ebenézer Rangel Botelho
 * Contatos:  @bene20rj(Twiter) / www.facebook.com/bene20 (facebook) / www.linkedin.com/bene20
 *
*/
date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('memory_limit', '1024m');
set_time_limit ( 0 );

//A constante ROOTDIR indica o nível mínimo de acesso permitido a um path
//Para um acesso a partir da pasta do script php, use define('ROOTDIR',dirname(__FILE__));
//Para um acesso global (MUITO PERIGOSO!), use define('ROOTDIR',"");
//É imperativo, por questões de segurança, que não se use caminho relativo para essa constante; apenas caminhos absolutos
define('ROOTDIR',realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".."));

//A constante STARTDIR indica o diretório inicial de operação do sistema (por onde ele começa a listagem dos arquivos).
//É imperativo, por questões de funcionamento, que não se use caminho relativo para essa constante; apenas caminhos absolutos
define('STARTDIR',realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".."));

////////////////////////////////////////////////////////
// As constantes abaixo não devem ser alteradas ////////
////////////////////////////////////////////////////////
define('APPLIST','ls');
define('APPZIP','zp');
define('APPUNZIP','uz');
define('APPSENDFILE','sf');
define('APPDELFILE','df');
define('APPPROPERTYESFILE','pf');
define('APPRENAMEFILE','rnf');
define('APPNEWFOLDER','nf');
define('APPPHPINFO','pi');
define('APPREAD','rf');
define('APPOPEN','of');
define('APPSAVEFILE','safi');
define('WORDCODE',date("dmy")."gg6t5k-çap= aorn*tb6n897310cmrmr552b6w65f5h,bp");
define('FIXEDWORDCODE',"gg6t5k-çap= aorn*tb6n897310cmrmr552b6w65f5h,bp");
define('SEGFILE',"segfile.seg");
define('ZIPTOOLDIR',"%2Fziptool");
define('ZIPSDIR',realpath("..".DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR."zips"); //Caminho onde serão armazenados os zips feitos pelo script

////////////////////////////////////////////////////////

$primeiroAcesso = true;
$app         = APPLIST;
$directory   = safedir(STARTDIR); //Determinando o diretório inicial
$ferramentas = array();
$mensagem    = "";
$camposExtra = "";

$ferramentas[] = "<a href='".$_SERVER["PHP_SELF"]."'><img src='ziptoolimgs/exit.png' alt='Sair' title='Sair' width='20px'/></a>";

if(!empty($_POST["acao"])){
  $rc = validarHashParametros($_POST["acao"]);

  if(empty($rc)){
    $mensagem .= "<br><br>Acesso inválido!<br>";
    showHtml($mensagem);
    return;
  }

  $app = $rc["app"];
  $directory = $rc["p"];
  $primeiroAcesso = false;
}

/*
 * Alternativa de usao para o acesso restrito
 * if($primeiroAcesso) acessoRestrito( sha1(md5("123456".FIXEDWORDCODE)) ); //Este uso é referente à senha '123456'
 * Para descobrir o hash da senha '123456': echo "Senha: ".sha1(md5("123456".FIXEDWORDCODE))."<br>";
*/
if($primeiroAcesso){
  acessoRestrito( "9c94b2175a46d87ca86fa25351b121b349112451" );
}

if($app != APPPHPINFO){
  $parametros = "app=".APPPHPINFO."&p=".urlencode(safedir(dirname(__FILE__)));
  $acao = gerarHashParametros($parametros);
  $ferramentas[] = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/phpinfo.png' alt='PHPInfo' title='Visualizar PHPInfo' width='20px'/></a>";
}

$mensagem .= "<div align='center'>\n";

switch($app){
  case APPLIST:
    $mensagem .= listDir(ROOTDIR.$directory);
    break;

  case APPSENDFILE:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir($directory));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "Envio de arquivo para diretório '".safedir($directory)."'.<br>";
    $mensagem .= uploadFile(ROOTDIR.$directory);
    $mensagem .= $linkvoltar;
    break;

  case APPDELFILE:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/delete.png' alt='Exclude' width='20px'/> Exclusão do arquivo/diretório '".safedir($directory)."'.<br>";
    delfile(ROOTDIR.$directory);
    $mensagem .= $linkvoltar;
    break;

  case APPZIP:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $relativedirectory = safedir(realpath(ROOTDIR.$directory));
    $zipfolderdestino = ZIPSDIR;

    $mensagem .= "<img src='ziptoolimgs/arrow_in.png' alt='Zip' width='20px'/> Compactando '".safedir(realpath(ROOTDIR.$directory))."'.<br>";

    //Montando o nome do arquivo 'zip'
    $info = pathinfo(realpath(ROOTDIR.$directory));
    $file_name = empty($info['extension'])? basename(realpath(ROOTDIR.$directory)).".zip":basename(realpath(ROOTDIR.$directory),'.'.$info['extension']).".zip";
    $zipfilenamedestino = "$zipfolderdestino".DIRECTORY_SEPARATOR."$file_name";

    //Criando $zipfolderdestino caso não exista
    @mkdir($zipfolderdestino, 0777, true);

    $rc = zip(realpath(ROOTDIR.$directory), $zipfilenamedestino, true);
    if($rc){
      $mensagem .= "$relativedirectory compactado para o arquivo <a href='".getRelativePath($zipfilenamedestino)."'>".getRelativePath($zipfilenamedestino)."</a><br>";
    }
    else{
      $mensagem .= "Falha tentando compactar $relativedirectory para o arquivo ".safedir($zipfilenamedestino)."<br>";
      var_dump($rc);
    }
    $mensagem .= $linkvoltar;
    break;

  case APPUNZIP:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/arrow_out.png' alt='Unzip' width='20px'/> Descompactando '$directory'.<br>";
    unzip(ROOTDIR.$directory);
    $mensagem .= $linkvoltar;
    break;

  case APPREAD:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/read_file.png' alt='Read' width='20px'/> Lendo conteúdo de '$directory'.<br>";
    $mensagem .= "</center>"; //Anulando o center global (antes do switch)
    $mensagem .= leArquivo(ROOTDIR.$directory);
    $mensagem .= "<center>\n";
    $mensagem .= $linkvoltar;
    break;

  case APPSAVEFILE:
    $mensagem .= salvaArquivo(ROOTDIR.$directory, $_POST["conteudo_arquivo"]);
    $mensagem .= "<script>alert('".salvaArquivo(ROOTDIR.$directory, $_POST["conteudo_arquivo"])."');</script>";
    //Não incluo o break aqui para manter a tela de edição do arquivo aberta! Devo executar o case APPOPEN, abaixo

  case APPOPEN:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $rc = abreArquivo(ROOTDIR.$directory);
    $camposExtra = isset($rc["campos"])? $rc["campos"]:"";

    $mensagem .= "<img src='ziptoolimgs/open_file.jpg' alt='Open' width='20px'/> Editando '$directory'.<br>";
    $mensagem .= $rc["mensagem"];
    $mensagem .= $linkvoltar;
    break;

  case APPPROPERTYESFILE:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/properties.png' alt='Properties' width='20px'/> Propriedades de '$directory'.<br>";
    $mensagem .= properties(ROOTDIR.$directory);
    $mensagem .= $linkvoltar;
    break;

  case APPRENAMEFILE:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname($directory)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/rename.jpg' alt='Rename' width='20px'/> Renomeação de '$directory'.<br>";
    $mensagem .=  implode("<br/>",renomeiaArquivo(ROOTDIR.$directory, $_POST["param"]))."<br>";
    $mensagem .= $linkvoltar;
    break;

  case APPNEWFOLDER:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir($directory));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "<img src='ziptoolimgs/newfolder.jpg' alt='New folder' width='20px'/> Criação da pasta '".$_POST["param"]."'.<br>";
    $mensagem .=  implode("<br/>",criaPasta(ROOTDIR.$directory, $_POST["param"]))."<br>";
    $mensagem .= $linkvoltar;
    break;

  case APPPHPINFO:
    //Link para voltar para a tela anterior
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(dirname(__FILE__)));
    $acao = gerarHashParametros($parametros);
    $linkvoltar = "<a href=\"#\" onClick=\"executar('$acao');\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    $ferramentas[] = $linkvoltar;

    $mensagem .= "Exibição do PHPINFO.<br>";
    ob_start();
    phpinfo();
    $mensagem .=  ob_get_contents();
    ob_end_clean();
    $mensagem .= $linkvoltar;
    break;

  default:
    $mensagem .= "Mau uso do script.";
    break;
}
$mensagem .= "</div>\n";

showHtml($ferramentas, $mensagem, $camposExtra);
return;

function listDir($_directory){
  $mensagem = "";
  $directory = strlen(realpath($_directory))? realpath($_directory):realpath(DIRECTORY_SEPARATOR);

  //Impedindo acesso a pastas mães ou irmãs da ROOTDIR
  $rootdir = dirname(__FILE__);
  if((strlen(ROOTDIR)>0) &&
     //(strcmp(realpath(ROOTDIR.DIRECTORY_SEPARATOR.".."), substr($directory,0, strlen(dirname(__FILE__))))==0)
     (strpos(getRelativePath(ROOTDIR, $directory), "..") !== false) //Se entre ROOTDIR e $directory existe '..' então estou tentando ir além de ROOTDIR
    ){
    $mensagem .= "diretorio '$directory' não permitido!<br>";
    $mensagem .= "<a href=\"#\" onClick=\"window.history.back();\" ><img src='ziptoolimgs/voltar.jpg' alt='Voltar' width='20px'/></a>";
    return $mensagem;
  }

  //Exibindo o diretório corrente mas ocultando o path completo por razões de segurança
  $currentDirectory = str_replace(ROOTDIR, "", $directory);
  $mensagem .= "<h1>Diretório corrente: ".(empty($currentDirectory)? DIRECTORY_SEPARATOR:$currentDirectory)."</h1>";

  //Formulário para upload de arquivo
  $parametros = "app=".APPSENDFILE."&p=".urlencode(safedir($_directory));
  $acao = gerarHashParametros($parametros);
  $mensagem .=
  	'<form  method="post" enctype="multipart/form-data" id="fupload" name="fupload">
       <input type="hidden" name="acao" id="acao" value="'.$acao.'">
       <input type="hidden" name="param" id="param" value="">

       <label for="file">Upload:</label>
       <input type="file" name="file" id="file">
       <input type="submit" name="submit" value="Enviar">
     </form>';

  //Botão para pasta raiz (home)
  $parametros = "app=".APPLIST."&p=".urlencode(safedir(ROOTDIR));
  $acao = gerarHashParametros($parametros);
  $mensagem .= "<a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/home.jpg' alt='Home' title='Home' width='20px'/></a>&nbsp\n";

  //Botão para pasta de zips
  if(is_dir(ZIPSDIR)){
    $parametros = "app=".APPLIST."&p=".urlencode(safedir(ZIPSDIR));
    $acao = gerarHashParametros($parametros);
    $mensagem .= "<a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/zip.jpg' alt='Go to Zips folder' title='Go to Zips folder' width='20px'/></a>&nbsp\n";  
  }
  else{
    $mensagem .= "<a href=\"#\" onClick=\"alert('A pasta de zips não existe. Será criada automaticamente quando algo for zipado.');\"><img src='ziptoolimgs/zip.jpg' alt='Go to Zips folder' title='Go to Zips folder' width='20px'/></a>&nbsp\n";  
  }
  

  //Separador (barra vertical)
  $mensagem .= "&nbsp;|&nbsp;";

  //Botão para criação de nova pasta
  $parametros = "app=".APPNEWFOLDER."&p=".urlencode(safedir($_directory));
  $acao = gerarHashParametros($parametros);
  $mensagem .= "<a href=\"#\" onClick=\"novaPasta('$acao');\"><img src='ziptoolimgs/newfolder.jpg' alt='New folder' title='New folder' width='20px'/></a>&nbsp;";

  $mensagem .= "<br/><br/>";

  //http://www.php.net/manual/pt_BR/class.directoryiterator.php
   $iterator = new DirectoryIterator($directory);
  
  $mensagem .="<table border='1' bordercolor='#000'  class='filestable' cellpadding='4'>\n";
  foreach($iterator as $entry) {
    //O arquivo de segurança não pode estar disponível ao internauta
    if(strcmp($entry->getFilename(), SEGFILE) == 0) continue;

    //O próprio script não pode estar disponível ao internauta
    //if(strcmp($entry->getFilename(), basename(__FILE__)) == 0) continue;
    if(strcmp($entry->getPathName(), __FILE__) == 0) continue;

    //O diretório do próprio script não pode estar disponível ao internauta
    if(strcmp($entry->getPathName(), dirname(__FILE__)) == 0) continue;

    $mensagem .="<tr>\n";

    //Exibindo o nome do arquivo/diretório
    if($entry->isFile()){
      $currDir = str_replace("\\", "/", $currentDirectory);
      $mensagem .="<td class='filecol'>
                     <span class=\"arquivo\">
                       <a href='$currDir/".$entry->getFilename()."' title='Abrir/baixar arquivo.' target='_blank'>
                         <img src='ziptoolimgs/file.png' width='15px'/> ".$entry->getFilename()."
                       </a>
                     </span>
                   </td>\n";
    }
    elseif($entry->isDir()) {
      if( (strcmp($entry->getFilename(),".")==0) || //Não permito navegação para a pasta corrente (.)
          ( (strcmp($entry->getFilename(),"..")==0)&& empty($currentDirectory)) //Não permito navegação para pastas acima do ROOTDIR
        ){
        $mensagem .= "<td class='filecol'>".$entry->getFilename()."</td>\n";
      }
      elseif(strcmp(ZIPTOOLDIR, urlencode($currentDirectory.DIRECTORY_SEPARATOR.$entry->getFilename()))==0){ //Impedindo listagem da pasta ziptool
        continue; //$mensagem .= "<td class='filecol'>".$entry->getFilename()."</td>\n";
      }
      else{
        $parametros = "app=".APPLIST."&p=".urlencode(safedir($currentDirectory.DIRECTORY_SEPARATOR.$entry->getFilename()));
        $acao = gerarHashParametros($parametros);
        $mensagem .= "<td class='filecol'>
                        <span class=\"pasta\">
                          <a href=\"#\" onClick=\"executar('$acao');\" title='Explorar pasta'><img src='ziptoolimgs/folder.png' alt='&raquo;&raquo;' width='15px'/> ".$entry->getFilename()."</a>
                        </span>
                      </td>\n";

      }
    }
    else{
      $mensagem .= "<td bgcolor=gray class='filecol'></td>\n";
    }

    //Coluna de informação do tamanho do arquivo (em bytes)
    try{
      $mensagem .= ($entry->isDir())? "<td bgcolor=gray class='sizecol'></td>\n":"<td class='sizecol'>".formatBytes($entry->getSize())."</td>\n";
    }
    catch(Exception $e){
      $mensagem .= "<td bgcolor=gray class='sizecol'></td>\n";
    }

    //Coluna de informação da data da última alteração do arquivo
    try{
      $mensagem .= "<td class='datecol'>".date('d/m/Y h:i', $entry->getCTime())."</td>\n";
    }
    catch(Exception $e){
      $mensagem .= "<td  class='datecol' bgcolor=gray></td>\n";
    }

    //Disponibilizando o conteúdo do arquivo
    if($entry->isDir()){
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }
    else{
      $parametros = "app=".APPREAD."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/read_file.png' alt='Read' title='Read' width='20px'/></a></td>\n";
    }

    //Disponibilizando a abertura do arquivo
    if($entry->isDir()){
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }
    else{
      $parametros = "app=".APPOPEN."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/open_file.jpg' alt='Open' title='Open' width='20px'/></a></td>\n";
    }

    //Coluna de compactação/descompactação de arquivos e diretórios
    $extensao = "";
    
    if( method_exists( $entry , 'getExtension' )){ //Versões antigas do PHP não implementam o método getExtension
      $extensao = $entry->getExtension();
    }
    else{
      $path_parts = pathinfo($directory.DIRECTORY_SEPARATOR.$entry->getFilename());
      if(isset($path_parts["extension"])){
        $extensao = $path_parts["extension"];
      }
    }
    if($entry->isFile() && (strcmp(strtoupper($extensao),"ZIP")==0)){
      $parametros = "app=".APPUNZIP."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"unzipItem('$acao');\"><img src='ziptoolimgs/arrow_out.png' alt='Unzip' title='Unzip' width='20px'/></a></td>\n";
    }
    elseif(strcmp($entry->getFilename(),"..")!=0){
      $parametros = "app=".APPZIP."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/arrow_in.png' alt='Zip' title='Zip' width='20px'/></a></td>\n";
    }
    else{
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }


    //Coluna de exclusão de arquivos/diretórios
    //Obs: Não estará disponível para pastas com nome '.' nem para a pasta deste script php
    if((!$entry->isDot()) && (strcmp(dirname(__FILE__), $directory.DIRECTORY_SEPARATOR.$entry->getFilename()) != 0)){
      $parametros = "app=".APPDELFILE."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"excluirItem('$acao');\"><img src='ziptoolimgs/delete.png' alt='Exclude' title='Exclude' width='20px'/></a></td>\n";
    }
    else{
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }

    //Coluna de propriedades de arquivos/diretórios
    if(strcmp($entry->getFilename(), "..")!=0){
      $parametros = "app=".APPPROPERTYESFILE."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"executar('$acao');\"><img src='ziptoolimgs/properties.png' alt='Properties' title='Properties' width='20px'/></a></td>\n";
    }
    else{
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }

    //Coluna de renomeação de arquivos/diretórios
    if(!$entry->isDot()){
      $parametros = "app=".APPRENAMEFILE."&p=".urlencode(safedir($directory.DIRECTORY_SEPARATOR.$entry->getFilename()));
      $acao = gerarHashParametros($parametros);
      $mensagem .= "<td class='toolcol'><a href=\"#\" onClick=\"renomearItem('$acao', '".$entry->getFilename()."');\"><img src='ziptoolimgs/rename.jpg' alt='Rename' title='Rename' width='20px'/></a></td>\n";
    }
    else{
      $mensagem .= "<td bgcolor=gray class='toolcol'></td>\n";
    }

    $mensagem .= "</tr>\n";
  }
  $mensagem .= "</table>\n";
  return $mensagem;
}

function properties($directory){
  $mensagem = "";

  $basename = basename($directory);
  $dirname = dirname($directory);

  //Procurando o arquivo/diretório $directory para exibir suas informações
  $iterator = new DirectoryIterator($dirname);
  foreach ($iterator as $fileInfo) {
    if(strcmp($fileInfo->getFilename(), $basename)==0){
      $mensagem .="<table border='1' bordercolor='#000' style='border-collapse: collapse;' cellpadding='4'>\n";
      try{$mensagem .= "<tr><td>Data de último acesso do arquivo</td><td class=\"colorblue\">".date('d/m/Y h:i:s', $fileInfo->getATime())."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Dome do item corrente do DirectoryIterator.</td><td class=\"colorblue\">".$fileInfo->getBasename()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Data de modificação do inode do arquivo</td><td class=\"colorblue\">".date('d/m/Y h:i:s', $fileInfo->getCTime())."</td></tr>";}catch(Exception $e){}
      if( method_exists( $fileInfo , 'getExtension' ))
        try{$mensagem .= "<tr><td>Extensão do arquivo do item corrente do DirectoryIterator</td><td class=\"colorblue\">".$fileInfo->getExtension()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Nome do arquivo do elemento atual do diretório</td><td class=\"colorblue\">".$fileInfo->getFilename()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Grupo do arquivo</td><td class=\"colorblue\">".$fileInfo->getGroup()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Inode do arquivo</td><td class=\"colorblue\">".$fileInfo->getInode()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Data da última modificação do arquivo</td><td class=\"colorblue\">".date('d/m/Y h:i:s', $fileInfo->getMTime())."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Proprietário do arquivo</td><td class=\"colorblue\">".$fileInfo->getOwner()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Caminho do diretório</td><td class=\"colorblue\">".$fileInfo->getPath()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Caminho e o nome do arquivo do elemento atual do diretório</td><td class=\"colorblue\">".$fileInfo->getPathname()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Permissões do arquivo</td><td class=\"colorblue\">".$fileInfo->getPerms()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Tamanho do arquivo</td><td class=\"colorblue\">".formatBytes($fileInfo->getSize())."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Tipo do arquivo</td><td class=\"colorblue\">".$fileInfo->getType()."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Elemento atual é um diretório?</td><td class=\"colorblue\">".($fileInfo->isDir()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Arquivo é executável?</td><td class=\"colorblue\">".($fileInfo->isExecutable()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Elemento atual é um arquivo</td><td class=\"colorblue\">".($fileInfo->isFile()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Elemento atual é um link simbólico?</td><td class=\"colorblue\">".($fileInfo->isLink()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Arquivo pode ser lido?</td><td class=\"colorblue\">".($fileInfo->isReadable()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Arquivo pode ser modificado?</td><td class=\"colorblue\">".($fileInfo->isWritable()? "true":"false")."</td></tr>";}catch(Exception $e){}
      try{$mensagem .= "<tr><td>Elemento atual do diretório</td><td class=\"colorblue\">".$fileInfo->key()."</td></tr>";}catch(Exception $e){}
      $mensagem .= "</table>";
      break;
    }
    continue;
  }
  return $mensagem;
}

function uploadFile($directory){
  $mensagem = "Não foi possível fazer o upload.";
  if(isset($_POST['submit']))
  {
    if ($_FILES["file"]["error"] > 0)
    {
      $mensagem = "Error: " . $_FILES["file"]["error"] . "<br>";
    }
    else
    {
      $mensagem = "Upload: " . $_FILES["file"]["name"] . "<br>";
      $mensagem .= "Type: " . $_FILES["file"]["type"] . "<br>";
      $mensagem .= "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
      move_uploaded_file($_FILES["file"]["tmp_name"], "$directory".DIRECTORY_SEPARATOR.$_FILES["file"]["name"]);
    }
  }
  return $mensagem;
}

function delfile($directory){
  if(is_file($directory)){
    unlink($directory);
  }
  else{
    foreach (scandir($directory) as $item) {
        if ($item == '.' || $item == '..') continue;
        delfile($directory.DIRECTORY_SEPARATOR.$item);
    }
    rmdir($directory);
  }
}

function zip($source, $destination, $include_dir = false)
{
  if (!extension_loaded('zip') || !file_exists($source)) {
    return false;
  }

  if (file_exists($destination)) {
    unlink ($destination);
  }

  $zip = new ZipArchive();
  if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
    return false;
  }

  $source = realpath($source);
  if (is_dir($source) === true)
  {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    if ($include_dir) {
      $arr = explode(DIRECTORY_SEPARATOR, $source);
      $maindir = $arr[count($arr)- 1];

      $source = "";
      for ($i=0; $i < count($arr) - 1; $i++) {
        $source .= DIRECTORY_SEPARATOR . $arr[$i];
      }

      $source = substr($source, 1);

      $zip->addEmptyDir($maindir);
    }

    foreach ($files as $file)
    {
      // Ignore "." and ".." folders
      if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
          continue;

      $file = realpath($file);

      if (is_dir($file) === true)
      {
        $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
      }
      else if (is_file($file) === true)
      {
        $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
      }
    }
  }
  else if (is_file($source) === true)
  {
    $zip->addFromString(basename($source), file_get_contents($source));
  }

  return $zip->close();
}

function unzip($zipfile){
  $mensagem = "";
  // get the absolute path to $file
  $filepath = pathinfo(realpath($zipfile), PATHINFO_DIRNAME);

  $zip = new ZipArchive;
  $res = $zip->open($zipfile);
  if ($res === TRUE) {
    // extract it to the path we determined above
    $zip->extractTo($filepath);
    $zip->close();
    $mensagem .= "$zipfile extracted to $filepath";
  } else {
    $mensagem .= "I couldn't open $zipfile";
  }

  return $mensagem;
}

//Função usada para, a partir de uma lista de parâmetros de URL,
//gerar um hash entre esta e o código interno ($wordcode) para evitar
//mal uso dos scripts de processamento de feedbacks dos congressistas
//de emails enviados pelo sistema.
//Exemplo de uso:
//$parametros = "idi=1775&ide=3&idc=29";
//<a href="testejob.php?$Util->gerarHashParametros($parametros)">GO HERE</a>
//Produzirá o seguinte link:
//testejob.php?idi=1775&ide=3&idc=29&hsp=bc2e33fb788c5ac049b8
function gerarHashParametros( $parametros, $hashsize=20 )
{
  //echo "geraHash: ".$parametros."&hsp=".substr(md5($parametros.WORDCODE),0,$hashsize);
  return $parametros."&hsp=".substr(md5($parametros.WORDCODE),0,$hashsize);
}

//Função usada para analisar o código hash da URL da página atual, verificando se este é
//válido e gerado segundo o método gerarHashParametros.
//Exemplo de uso:
//$rc = $Util->validarHashParametros( $_SERVER["QUERY_STRING"]);
//echo (count($rc)>0)? "Processado com sucesso!<br>":"HASH INVÁLIDO!<br>";
function validarHashParametros( $parametros, $hashsize=20 )
{
  $paramoriginal = substr( $parametros, 0, strpos( $parametros, "&hsp="));
  $params        = explode("&", $parametros);
  $paramsvector  = array();

  $calculoHash = gerarHashParametros($paramoriginal);
  $calculoHash = substr($calculoHash, strrpos($calculoHash,"hsp=")+4);

  foreach($params as $param)
  {
    $p = explode("=", $param);
    if(count($p)==2) $paramsvector[$p[0]]=urldecode($p[1]);
  }

  if( !isSet($paramsvector["hsp"]) ||
      (strcmp($paramsvector["hsp"], $calculoHash)!=0)
    )
  {
    //echo "<br>Hashes diferem: (calculado) $calculoHash != (recebido)".$paramsvector["hsp"]."<br>";
    $paramsvector = array();
  }
  return $paramsvector;
}

/*
 * Função que faz controle de acesso ao script PHP atual.
 * Para usar esta função, obtenha o hash da senha que se quer utilizar na forma sha1(md5($minhasenha)).
 * Este hash é que deve ser passado para esta função.
*/
function acessoRestrito( $senha, $maxErros = 3 )
{
  //Contabilizando os logins sem sucesso (medida de segurança)
  $countErrors = 0;

  clearstatcache(); //Limpa o cache para saber a resposta atualizada de file_exists (executado abaixo)
  if(file_exists(SEGFILE)){
    $arquivo = fopen(SEGFILE,'r+');
    if ($arquivo == false) die('Não foi possível abrir o arquivo de segurança.');
    $rc = fgets($arquivo);
    if (strlen(trim($rc))==0) die('Não foi possível obter dados do arquivo de segurança.');
    $countErrors = sprintf("%d", $rc);
    fclose($arquivo);
  }

  if($countErrors >= $maxErros-1){
    die("Limite de tentativas excedido. Sistema desativado.");
    //Para reativar o sistema, exclua o arquivo SEGFILE
  }

  //Se estou vindo da tela de login e a senha está correta, retorno dessa função (apago o arquivo SEGFILE caso 
  //exista, pois o login foi feito com sucesso dentro da quantidade de tentativas aceitas)
  if( isSet($_POST["txtsenhaacesso"]) && (strcmp(sha1(md5($_POST["txtsenhaacesso"].FIXEDWORDCODE)),$senha)==0) )
  {
    if(file_exists(SEGFILE)) unlink(SEGFILE);
    return;
  }
  else //Exibo a tela de login ( com a quantidade de tentativas erradas de senha, caso haja registro de tentativas mal-sucedidas)
  {
    $tentativas = "Você tem $maxErros tentativas.<br>";
    if((isSet($_POST["txtsenhaacesso"])) && (!empty($_POST["txtsenhaacesso"]))){
      if(file_exists(SEGFILE)) unlink(SEGFILE);
      $arquivo = fopen(SEGFILE,'w+');
      if ($arquivo == false) die('Não foi possível abrir o arquivo de segurança.');
      fwrite($arquivo, ++$countErrors);
      fclose($arquivo);

      echo "<b><font color=\"red\">Senha inválida!</font></b><br>";
      $tentativas = "Você ainda tem ".($maxErros-$countErrors)." tentativas.<br>";
    }

    $texto = "<title>:::: Controle de acesso ::::</title>
              <body onLoad=\"document.formcsenha.txtsenhaacesso.focus();\">
                <div width='50%' align='center' style=\"background-color: #eee;\">
                  $tentativas
                  Charada: <INPUT type=\"text\" size=20 name=\"txtcharada\" value=".rand(1,100).date("s")."><br>
                  <form name=\"formcsenha\" id=\"formcsenha\" method=\"post\" action=\"".$_SERVER["REQUEST_URI"]."\">
                    Senha: <INPUT type=\"password\" title=\"Digite aqui a senha para acessar o conteúdo desta página\"
                            maxLength=20 size=20 name=\"txtsenhaacesso\"><br>
                    <input type=\"submit\" id=\"btEnviar\" name=\"btEnviar\" value=\"Acessar conteúdo\" class=\"botao2\" >
                  </form>
                </div>
              </body>";
    echo $texto;
    exit();
  }
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    $bytes /= (1 << (10 * $pow)); //Equivalente a $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function safedir($directory){
  return str_replace(ROOTDIR, "", $directory);
}

/*getRelativePath obtém o caminho relativo entre dois caminhos de diretórios.
  Ex: $path1 = "/a/b/c/d/e/f/g";
      $path2 = "/a/b/c/d/x/y/z";
      echo  "Relative = ".getRelative($path1, $path2);
      //Resultado: ..\..\..\x\y\z
*/
function getRelativePath($path1, $path2=null){
  if(is_null($path2)){
    $path2 = $path1;
    $path1 = dirname(__FILE__);
  }
  
  $p1 = $p2 = "";
  
  //Obtendo o ponto de diferença entre os paths
  $i=0;
  while(isset($path1[$i]) && isset($path2[$i]) && ($path1[$i] == $path2[$i])) $i++;
  
  $p1 = substr($path1, $i);
  $p1 = preg_replace("@[^\\\/]+@", "..", $p1);
  $p1 = preg_replace("@[\\\/]@", DIRECTORY_SEPARATOR, $p1);
  
  $p2 = substr($path2, $i);
  $p2 = preg_replace("@[\\\/]@", DIRECTORY_SEPARATOR, $p2);
  
  $retorno = $p1.DIRECTORY_SEPARATOR.$p2;
  $retorno = preg_replace("@\\".DIRECTORY_SEPARATOR."+@", DIRECTORY_SEPARATOR, $retorno); //Remove barras duplas, se houverem
  $retorno = preg_replace("@^\\".DIRECTORY_SEPARATOR."+@", "", $retorno); //Remove barra do início, se houver
  $retorno = preg_replace("@\\".DIRECTORY_SEPARATOR."+$@", "", $retorno); //Remove barra do fim, se houver
  
  return $retorno;
}

/*
  Esta função lê um arquivo e exibe seu conteúdo.
  Ideal para exibir o conteúdo de um arquivo php sem que o apache o processe
*/
function leArquivo($nomearquivo)
{
  $rc = array();

  if(!file_exists($nomearquivo)){
    $mensagem[]= "Arquivo '$nomearquivo' não encontrado!";
    return $rc;
  }

  $rc[] = "<hr>INÍCIO DO ARQUIVO ".safedir($nomearquivo)."<hr>";

	$conteudo = implode(file($nomearquivo));
	$conteudo = htmlspecialchars($conteudo);
  $rc[] = "<pre>$conteudo</pre>";

  $rc[] = "<hr>FINAL DO ARQUIVO ".safedir($nomearquivo)."<hr>";

  $rc  = implode("<br>",$rc);
  return $rc;
}

/*
 * Esta função abre um arquivo para edição de seu conteúdo.
*/
function abreArquivo($nomearquivo)
{
  $rc = array();
  $campos = array();
  $mensagem = array();

  if(!file_exists($nomearquivo)){
    $mensagem[]= "Arquivo '$nomearquivo' não encontrado!";
    $rc["mensagem"] = implode("<br>",$mensagem);
    return $rc;
  }

  if(!is_writable($nomearquivo))
  {
    $mensagem[]= "Arquivo '$nomearquivo' não tem permissão de escrita!";
    $rc["mensagem"] = implode("<br>",$mensagem);
    return $rc;
  }

  //Lendo o conteúdo do arquivo
	$conteudo = implode(file($nomearquivo));
	$conteudo = htmlspecialchars($conteudo);

  $parametros = "app=".APPSAVEFILE."&p=".urlencode(safedir($nomearquivo));
  $acao = gerarHashParametros($parametros);

	$campos[] = "<hr>";
	$campos[] = "<textarea name=\"conteudo_arquivo\" rows=\"18\" cols=\"100\">$conteudo</textarea>";
	$campos[] = "<br>";
	$campos[] = "<input type=\"button\" onClick=\"if(confirm('Tem certeza que deseja salvar as alterações?'))executar('$acao');\" value=\"Salvar\" title=\"Salva as alterações no arquivo.\">".
							"<input type=\"reset\" value=\"Resetar\" title=\"Desfaz alterações não salvas.\">";
	$campos[] = "<hr>";

  $rc["campos"]   = implode("<br>",$campos);
  $rc["mensagem"] = implode("<br>",$mensagem);
  return $rc;
}

function salvaArquivo($nomearquivo, $conteudo){
  $rc = array();

  if(!file_exists($nomearquivo)){
    $rc[] = "Arquivo '$nomearquivo' não encontrado!";
    return implode("<br>",$rc);
  }

  $conteudo = stripslashes($conteudo);
  if($file = @fopen($nomearquivo,"w")){
  	if(@fwrite($file,$conteudo)!== false){
  		$rc[] = "Arquivo salvo com sucesso!";
  	}else{
  		$rc[] = "Erro: Não foi possível salvar o Arquivo!";
  	}
    fclose($file);
  }else{
  	$rc[] = "Erro: Não foi possível abrir o arquivo para salvar!";
  }

  $rc  = implode("<br>",$rc);
  return $rc;
}

function renomeiaArquivo($oldname, $newname){
  $rc = array();

  $nomeitem = is_dir($oldname)? "diretório":"arquivo";

  if(!file_exists($oldname)){
    $rc[] = "O $nomeitem $oldname não encontrado!";
    return $rc;
  }

  if(file_exists(dirname($oldname).DIRECTORY_SEPARATOR.$newname)){
    $rc[] = "O $nomeitem ".dirname($oldname).DIRECTORY_SEPARATOR.$newname." já existe!";
    return $rc;
  }

	if(@rename($oldname,dirname($oldname).DIRECTORY_SEPARATOR.$newname) )
		$rc[] = "O $nomeitem \"".safedir($oldname)."\" foi renomeado para \"".safedir(dirname($oldname).DIRECTORY_SEPARATOR.$newname)."\"";
	else
		$rc[] = "Erro: Problemas ao tentar renomear o $nomeitem \"$oldname\"";

  return $rc;
}

function criaPasta($directory, $foldername){
  $rc = array();

  $newfolder = $directory.DIRECTORY_SEPARATOR.$foldername;

  if(!file_exists($newfolder) && is_dir($newfolder)){
    $rc[] = "Pasta $directory não encontrada!";
    return $rc;
  }

  if(file_exists($newfolder) && is_dir($newfolder)){
    $rc[] = "A pasta $newfolder já existe!";
    return $rc;
  }

	if (@mkdir($newfolder)){
		$rc[] = "Pasta \"$foldername\" criada com sucesso!";
	}
	else{
		$rc[] = "Erro: Problemas ao tentar criar a pasta \"$foldername\"!";
		$rc[] = "Erro: Problemas ao tentar criar a pasta \"$newfolder\"!";
  }

  return $rc;
}

function showHtml($ferramentas=null, $mensagem="", $camposExtra=""){
  if(is_null($ferramentas) || empty($mensagem)){
    die("Violação de segurança! Abortado!");
  }
  ?>
  <html>
    <head>
      <style type="text/css">
        A:link {text-decoration: none; color: blue;}
        A:visited {text-decoration: none; color: blue;}
        A:active {text-decoration: none; color: blue;}
        A:hover {text-decoration: underline; color: red;}

        .arquivo A:link {color: green;}
        .arquivo A:hover {color: red;}

        .pasta {color: blueviolet;}

        .colorblue {color: blue;}
        .colorblueviolet {color: blueviolet;}
        .colorgreen {color: green;}
        .colorred {color: red;}
        
        table.filestable{width: 70%; border-collapse: collapse;}
        table.filestable tr:hover{background-color: #eee}
        td.filecol{}
        td.sizecol{width: 80px; text-align: right;}
        td.datecol{width: 120px; text-align: center;}
        td.toolcol{width: 30px; text-align: center;}


        h1{
          text-shadow: 2pt 2pt 10pt blue;
        }
        table{
          box-shadow: 2pt 2pt 10pt blue;
        }
      </style>
      <script language="javaScript">
        function novaPasta(acao){
          var resp = prompt("Digite o nome da pasta sem espaços em branco","nova_pasta");
          if(resp != null && resp != ""){
            document.f.acao.value = acao;
            document.f.param.value=resp;
            document.f.submit();
          }else{
            document.f.param.value="";
          }
        }

        function excluirItem(acao){
          if(confirm("Atenção! Você não poderá desfazer essa operação.\nDeseja realmente excluir esse item?")){
            document.f.acao.value = acao;
            document.f.submit();
          }
        }

        function unzipItem(acao){
          if(confirm("Atenção! Você está prestes a descompactar um zip com todos os arquivos e pastas nele existentes.\nDeseja realmente efetuar essa operação?")){
            document.f.acao.value = acao;
            document.f.submit();
          }
        }

        function renomearItem(acao, filename){
          var resp = prompt("Renomear arquivo selecionado",filename);
          if(resp != null && resp != ""){
            document.f.acao.value = acao;
            document.f.param.value=resp;
            document.f.submit();
          }
        }

        function executar(acao){
          document.f.acao.value = acao;
          document.f.submit();
        }
      </script>
    </head>
    <body>
      <form method="post" name="f" id="f">
        <?=implode("&nbsp;&nbsp;|&nbsp;&nbsp;", $ferramentas)?>
        <input type="hidden" name="acao" id="acao" value="">
        <input type="hidden" name="param" id="param" value="">
        <?=$camposExtra?>
      </form>
        <?=$mensagem?>
    </body>
  </html>
  <?php
}

// Esta função é semelhante à explode do PHP, exceto por retornar um array
// vazio caso a string esteja vazia. O explode do PHP retorna um array com um elemento e esse
// elemento é vazio: (Array[0]=>); Esta função retorna um array vazio(return array()).
function myExplode($delimiter, $string, $limit="")
{
  if(strlen(trim($limit))>0)
    $rc = (strlen(trim($string))>0)? explode($delimiter,$string, $limit):array();
  else
    $rc = (strlen(trim($string))>0)? explode($delimiter,$string):array();
  return $rc;
} 

//Função para auxiliar debug. Imprime mensagem na tela cercada pela tag html <hr>
function msgDebug($mensagem, $variavel=null){
  $backtrace = debug_backtrace();
  $linha = $backtrace[0]["line"];
  $arquivo = pathinfo($backtrace[0]["file"]);
  $arquivo = $arquivo['filename'];
  $mensagem = "[$arquivo-$linha] $mensagem";

  echo "<hr>";
  echo "<span style=\"background-color:red; color: yellow; font-weight: bold;\">$mensagem</span>";
  if(! is_null($variavel)) var_dump($variavel);
  echo "<hr>";
}
?>
