<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*								krullCache.class.php
*  --------------------------------------------------------------------------
*
*	   Site Web :		
*	   Fait par :		
*	   Commenc� le :	
*	   Modifi� le :		
*
*  --------------------------------------------------------------------------
*	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier
*	selon les termes de la Licence Publique G�n�rale GNU publi�e par la Free
*	Software Foundation (version 2). Reportez-vous � la Licence Publique
*	G�n�rale GNU pour plus de d�tails. Vous devez avoir re�u une copie de
*	la Licence Publique G�n�rale GNU en m�me temps que ce programme ; si ce
*	n'est pas le cas, �crivez � la Free Software Foundation, Inc., 59 Temple
*	Place, Suite 330, Boston, MA 02111-1307, �tats-Unis.
*  --------------------------------------------------------------------------
*
*******************************************************************************/

/*
	PRINCIPE

Source code : http://www.siteduzero.com/tuto-3-12707-1-systeme-de-cache-de-dr-night.html#ss_part_1

	-> Requete avec le systeme de cache <-

// on essaye de r�cup�rer les donn�es dans le cache
if ( !$donnees = get_cache('messages_sujet_42') )
{
        // le cache n'existe pas, r�cup�ration des messages dans la base de donn�es
        $resultat = mysql_query('SELECT * FROM messages WHERE sujet = 42');

        // stockage du r�sultat dans la variable $donnees
        $donnees = array();
        while ( $donnees[] = mysql_fetch_array($resultat) );

        // mise en cache de $donnees
        create_cache('messages_sujet_42', $donnees);
}

// lecture des messages
foreach ( $donnees as $ligne )
{
        //
        // on affiche les messages
        //
}


	-> Mis � jour du cache <-

// ajout d'un message
mysql_query('INSERT INTO messages(texte, sujet) VALUES (\'blablablablabla\', 42)');

// suppression du cache
destroy_cache('messages_sujet_42');
 
*/

class krullCache extends krullSingleton
{
	//
	var $_cacheDir = './cache/';


	function __construct()
	{
		parent::__construct();
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullCache');
    }

	/*

	*/
	function create($name, $content)
	{
        // utilisation de serialize() pour transformer $content en chaine de caract�res
        $content = serialize($content);

        // �chappement les caract�res sp�ciaux pour pouvoir mettre le tout entre quotes dans le futur fichier
        $content = str_replace(array('\\', '\'', "\0"), array('\\\\', '\\\'', '\\0'), $content);

        // cr�ation du code php � stocker dans le fichier
        $content = '<?php' . "\n\n" . '$cache = unserialize(\'' .  $content . '\');' . "\n\n" . '?>';
       
        // �criture du code dans le fichier
        $fichier = fopen('./cache/donnees_' . $name . '.php', 'w');
        $resultat = fwrite($fichier, $content);
        fclose($fichier);

        // renvoie true si l'�criture du fichier a r�ussi
        return $resultat;
	}

	/*

	*/
	function get($name)
	{
        // v�rifie que le fichier de cache existe
        if ( is_file('./cache/donnees_' . $name . '.php') )
        {
                // le fichier existe, on l'ex�cute puis on retourne le contenu de $cache
                include('./cache/donnees_' . $name . '.php');
                return $cache;
        }
        else
        {
                // le fichier de cache n'existe pas, on retourne false
                return false;
        }		
	}

	/*
		
	*/
	function destroy($name)
	{
        return @unlink('./cache/donnees_' . $name . '.php');
	}

}

?>