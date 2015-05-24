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

/*
	PRINCIPE

Source code : http://www.siteduzero.com/tuto-3-12707-1-systeme-de-cache-de-dr-night.html#ss_part_1

	-> Requete avec le systeme de cache <-

// on essaye de récupérer les données dans le cache
if ( !$donnees = get_cache('messages_sujet_42') )
{
        // le cache n'existe pas, récupération des messages dans la base de données
        $resultat = mysql_query('SELECT * FROM messages WHERE sujet = 42');

        // stockage du résultat dans la variable $donnees
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


	-> Mis à jour du cache <-

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
        // utilisation de serialize() pour transformer $content en chaine de caractères
        $content = serialize($content);

        // échappement les caractères spéciaux pour pouvoir mettre le tout entre quotes dans le futur fichier
        $content = str_replace(array('\\', '\'', "\0"), array('\\\\', '\\\'', '\\0'), $content);

        // création du code php à stocker dans le fichier
        $content = '<?php' . "\n\n" . '$cache = unserialize(\'' .  $content . '\');' . "\n\n" . '?>';
       
        // écriture du code dans le fichier
        $fichier = fopen('./cache/donnees_' . $name . '.php', 'w');
        $resultat = fwrite($fichier, $content);
        fclose($fichier);

        // renvoie true si l'écriture du fichier a réussi
        return $resultat;
	}

	/*

	*/
	function get($name)
	{
        // vérifie que le fichier de cache existe
        if ( is_file('./cache/donnees_' . $name . '.php') )
        {
                // le fichier existe, on l'exécute puis on retourne le contenu de $cache
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