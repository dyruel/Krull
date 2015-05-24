<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*							krullXml.class.php
*  --------------------------------------------------------------------------
*
*	   Site Web :		
*	   Fait par :		
*	   Commencé le :	
*	   Modifié le :		
*
*  --------------------------------------------------------------------------
*	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier
*	selon les termes de la Licence Publique Générale GNU publiée par la Free
*	Software Foundation (version 2). Reportez-vous à la Licence Publique
*	Générale GNU pour plus de détails. Vous devez avoir reçu une copie de
*	la Licence Publique Générale GNU en même temps que ce programme ; si ce
*	n'est pas le cas, écrivez à la Free Software Foundation, Inc., 59 Temple
*	Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*  --------------------------------------------------------------------------
*
*******************************************************************************/


/**
* @package   copix
* @subpackage copixtools
* @version   $Id: CopixSimpleXml.class.php,v 1.2 2006/03/01 10:36:13 gcroes Exp $
* @author   Jouanneau Laurent
* @copyright 2001-2005 CopixTeam
* @link      http://copix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**

Attention

Ces classes sont censées respecter les spécifications XML.
Les modifier requiert donc de vérifier le respect de ces specs


*/



/**
 * Implemente les propriétés d'une balise XML
 * @package   copix
 * @subpackage xmltools
 */
class krullXmlTag
{
   /**
    * nom de la balise
    * @var $string
    * @access private
    */
   var $__name;
   /**
    * liste des attributs de la balise
    * @var array
    * @access private
    */
   var $__attributes=array();
   /**
    * contenu texte (entre balise ouvrante/fermante)
    * @access private
    */
   var $__content='';


   var $__IsCdataContent=false;

   /**
    * reference vers le tag parent dans une arborescence XML
    * @var krullXmlTag
    * @access private
    */
   var $__parentTag=null;
   /**
    * liste des balise enfants
    * @var array of krullXmlTag
    * @access private
    */
   var $__childs=array();

   /**
    * constructeur
    * @param   string   $name nom de la balise
    * @param   array    $attributes liste des attributs
    */
   function krullXmlTag($name, $attributes=array()){
      $this->__name       = $name;
      $this->__attributes = $attributes;
   }

   /**
    * @return  array liste des attributs
    */
   function attributes(){ return $this->__attributes; }

   /**
    * @return array la valeur d'un attribut
    */
   function getAttribute($id){
      if ( isset($this->__attributes[$id]))
         return $this->__attributes[$id] ;
      else return null;
   }
   /**
    * @return string nom de la balise
    */
   function name(){ return $this->__name; }
   /**
    * @return string  contenu texte
    */
   function content($normalize=false){
       if($normalize)
           return krullSimpleXml::normalizeString($this->__content);
       else
           return $this->__content;
   }
   /**
    * @return array   liste des balises enfants
    */
   function & childs(){ return $this->__childs; }

   /**
    * ajoute une balise fille
    * @param   krullXmlTag $tag  Balise fille
    */
   function addChild(&$tag){
      $name=$tag->__name;
      if(isset($this->$name)){
         if(!is_array($this->$name)){
            $old=&$this->$name;
            unset($this->$name);

            $this->$name=array();
            // on n'utilise pas array_push car il nous faut une réference vers l'objet tag, pas une copie
            //array_push($this->$name, $old);
            // on ne peut pas faire $this->$name[]=... donc, on passe par une référence
            $t = & $this->$name;
            $t[] = &$old;
         }
         // on n'utilise pas array_push car il nous faut une réference vers l'objet tag, pas une copie
         //array_push($this->$name, $tag);
         // on ne peut pas faire $this->$name[]=... donc, on passe par une référence
         $t= & $this->$name;
         $t[] = &$tag;

      }else{
        $this->$name=&$tag;
      }

      /*
       if(isset($this->$name)){
         array_push($this->$name, $tag);
      }else{
         $this->$name= array();
         array_push($this->$name, $tag);
      }
      */
      $this->__childs[] = & $tag;
   }
}

/**
 * cette classe implémente un parseur XML, permettant de récuperer une arborescence
 * d'un fichier XML sous forme d'arbre d'objet krullXmlTag.
 * Elle comporte aussi d'autres fonctions utilitaires pour manipuler cet arbre.
 * @package   copix
 * @subpackage xmltools
 */
class krullSimpleXml extends krullSingleton
{

   var $_root=null;
   var $_currentTag=null;
   var $_parser=null;

   /**
    * le charset du contenu xml à lire
    */
   var $inputCharset = '';
   /**
    * le charset vers lequel il faut convertir le contenu lu, pour lors de la manipulation avec krullsimplexml
    */
   var $outputCharset = '';
   /**
   * Error code
   */
   var $_err_code   = null;
   /**
   * Error message
   */
   var $_err_string = null;
   /**
   * Line where the error occured
   */
   var $_err_line   = null;
   /**
   * COlumn where the error occured
   */
   var $_err_col    = null;

   var $_err_file ='';


   /**
   *
   * forceCase n'est pas en paramètre du constructeur, car à true, cela est
   * strictement non conforme à la spec XML !
   * n'est là que pour des raisons de compatibilité avec les versions précédentes.
   * @deprecated
   */
   var $forceCase   = false;


   /**
    *
    * @param string $inputCharset    le charset du contenu xml à lire
    * @param string $outputCharset   le charset vers lequel il faut convertir le contenu lu
    */
   function __construct($inputCharset='ISO-8859-1', $outputCharset='')
   {
		parent::__construct();

		$this->inputCharset=$inputCharset;
		$this->outputCharset = ($outputCharset !=''?$outputCharset:$inputCharset);
   }

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullSimpleXml');
    }

   /**
    * analyse un fichier xml
    * @param   string   $file chemin/nom du fichier à analyser
    * @return  krullXmlTag tag racine et ses fils
    */
	function & parseFile($file)
	{
        $fp = @fopen($file, "rb");
        $this->_err_file = $file;
        if (is_resource($fp)) {
            $this->_initParser();
            while ($data = fread($fp, 20)) {
               if ($this->_parse($data, feof($fp)) === false) {
                  fclose($fp);
                  $this->_free();
                  $return = false;
                  return $return;
               }
            }
            fclose($fp);
            $this->_free();
            return $this->_root;
        }else{
         return false;
        }
   }

   /**
    * analyse une chaine contenant un fichier xml
    * @param   string   $string  chaine contenant du xml valide
    * @return  krullXmlTag tag racine et ses fils
    */
   function & parse($string){
      $this->_initParser();
      if($this->_parse($string)){
        $ret = & $this->_root;
      }else{
        $ret = false;
      }
      $this->_free();
      return $ret;
   }

   /**
    * Génere une chaine de caractère à partir d'un XmlTag (inverse de parse), pour un document complet
    * @param   krullXmlTag $xmltag  tag qu'il faut transformer en chaine (y compris ses fils)
    * @param   boolean  $readable   indique si la sortie doit être lisible (true) ou compacte (false)
    * @param   boolean $sameInputCharset indique si il faut encoder avec le même charset que lors de la lecture du xml ou pas
    * @return  string
    */
   function toDocString(& $xmltag, $readable=false, $sameInputCharset = true){
      $str = '<?xml version="1.0" encoding="';
      if($sameInputCharset){
         $str.=$this->inputCharset;
      }else{
         $str.=$this->outputCharset;
      }
      $str.="\"?>\n".$this->toString($xmltag, $readable, $sameInputCharset);
      return $str;
   }

   /**
    * Génere une chaine de caractère à partir d'un XmlTag (inverse de parse)
    * @param   krullXmlTag $xmltag  tag qu'il faut transformer en chaine (y compris ses fils)
    * @param   boolean  $readable   indique si la sortie doit être lisible (true) ou compacte (false)
    * @param   boolean $sameInputCharset indique si il faut encoder avec le même charset que lors de la lecture du xml ou pas
    * @param   integer  $level   niveau d'indentation. utilisé en interne.
    * @return  string
    */
   function toString(& $xmltag, $readable=false, $sameInputCharset = true, $level=0){
      $fct='';
      if($sameInputCharset){
        if($this->inputCharset != $this->outputCharset){
          if($this->inputCharset == 'UTF-8')
              $fct='utf8_encode';
          else
              $fct='utf8_decode';
        }
      }
      $str='';
      if($readable){
        $str= str_repeat('  ',$level);
      }
      $str.= '<'.$xmltag->__name;
      foreach($xmltag->__attributes as $nom=>$valeur){
         if ($fct) $valeur = $fct($valeur);
         $str.= ' '.$nom.'="'.htmlspecialchars($valeur).'"';
      }
      if($xmltag->__content == '' && count($xmltag->__childs) == 0){
         $str.='/>';
         return $str;
      }else{
         $data = $xmltag->__content;
         if ($fct) $data = $fct($data);
         if($xmltag->__IsCdataContent && preg_match("/[<>&]+/",$data , $match)){
              $str.= '><![CDATA['. $data.']]>';
         }else{
           if(trim($data) == ''&& !$readable){
                $str.= '>';
           }else{
              $str.= '>'. htmlspecialchars($data);
           }
         }
         foreach($xmltag->__childs as $child){
            if($readable) $str.= "\r\n";
            $str.= $this->toString($child, $readable,$sameInputCharset, $level+1);
         }
         return $str. '</'.$xmltag->__name.'>';
      }
   }
   /**
    * analyse un contenu xml
    * @access private
    */
   function _parse(&$content, $eof=true){
      if (xml_parse($this->_parser, $content, $eof)) {
         return true;
      }else{
         // Error while parsing document
         $this->_registerError (xml_get_error_code($this->_parser), xml_error_string(xml_get_error_code($this->_parser)), xml_get_current_line_number($this->_parser), xml_get_current_column_number($this->_parser));
//         trigger_error("Erreur lecture fichier Xml :\ncode=$err_code\n$err_string\nLine=$err_line\nColumn=$err_col", E_USER_ERROR);
         return false;
      }
   }

   /**
   * Register an error
   * @param int $code the error code
   * @param string $string the error message
   * @param int $line the line
   * @param int $col the error column
   * @return void
   * @private
   */
   function _registerError ($code, $string, $line, $col){
         $this->_err_code   = $code;
         $this->_err_string = $string;
         $this->_err_line   = $line;
         $this->_err_col    = $col;
   }

   /**
   * Gets the error message
   * @return associative array where keys are code, string, line and col
   * @access public
   */
   function getError (){
       return array ('code'=>$this->_err_code, 'string'=>$this->_err_string, 'line'=>$this->_err_line, 'col'=>$this->_err_col);
   }

   /**
   * Raise the last error
   * @return void
   * @access public
   */
   function raiseError (){
		trigger_error('xmlRead : '.$this->_err_string.' à '.$this->_err_line.' colonne '.$this->_err_col, E_USER_ERROR);
   }

   /**
    * initialise le parser
    * @access private
    */
   function _initParser(){
      $this->_parser = xml_parser_create($this->inputCharset);
      xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, $this->forceCase);
      //xml_parser_set_option($this->_parser, XML_OPTION_SKIP_WHITE, true); // ne fonctionne pas sur toutes les configs :-/
      xml_parser_set_option($this->_parser, XML_OPTION_TARGET_ENCODING, $this->outputCharset);
      xml_set_object( $this->_parser, $this);
      xml_set_element_handler($this->_parser, "_startHandler", "_endHandler");
      xml_set_default_handler ($this->_parser, '_defaultHandler');
      xml_set_character_data_handler ( $this->_parser, '_dataHandler');
      $this->_root=null;
      $this->_currentTag=null;
      $this->_inCdataSection = false;
   }
   /**
    * libere les ressources du parser
    * @access private
    */
   function _free(){
      xml_parser_free($this->_parser);
      $this->_parser=null;
   }
   /**
    * fonction callback pour le parser
    * @access private
    */
   function _startHandler($parser,$name, $attributes=array()){
      $tag=new krullXmlTag($name, $attributes);
      if($this->_currentTag === null){
         // c'est la racine
         $this->_root= & $tag;
         $this->_currentTag= &$tag;
      }else{
         $tag->__parentTag= & $this->_currentTag;
         $this->_currentTag->addChild($tag);
         $this->_currentTag= & $tag;
      }
   }

   /**
    * fonction callback pour le parser
    * @access private
    */
   function _endHandler($parser, $name){
      if($this->_currentTag->__parentTag !== null){
         $this->_currentTag= & $this->_currentTag->__parentTag;
      }
   }

   /**
    * fonction callback pour le parser
    * @access private
    */
   function _defaultHandler($parser, $data){
     if($data == '<![CDATA[')
       $this->_currentTag->__IsCdataContent = true;

     /*  $this->_inCdataSection = true;
     if($data == ']]>')
       $this->_inCdataSection = false;
       */
   }

   /**
    * fonction callback pour le parser
    * on ne normalise pas le contenu, pour être le maximum compatible avec
    * simplexml, donc pas de remplacement de saut de ligne ou autre caractère blanc.
    * @access private
    */
   function _dataHandler($parser, $data){

      //if(!$this->_inCdataSection){
        // si on n'est pas dans une section cdata, on normalise la chaine
        // ie : on enleve tout les caractères blanc superflu
        //$data = $this->normalizeString($data); // finalement on fait pas pour être compatible avec simplexml
      //}
      $this->_currentTag->__content .= $data;
   }

   // cf http://www.w3.org/TR/2004/REC-xml-20040204/#sec-white-space
   function normalizeString($string){
       $data = preg_replace("/\015\012|\015|\012|\t/",' ',$string); // doit être remplacé par un "espace" et non par une chaine vide
       $data = trim(preg_replace("/ +/",' ',$data)); // pas de trim(), ce n'est pas pareil !
       return $data;
   }
}
?>