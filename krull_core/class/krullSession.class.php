<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*							krullSession.class.php
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

*/
class krullSession extends krullSingleton
{

	/*

	*/
	function __construct()
	{
		parent::__construct();

		session_start();
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullSession');
    }

	/*

	*/
	function launch($id)
	{
		$addrs[] = $_SERVER['REMOTE_ADDR'];
		$chosen = -1;
		$ip = '';
		$ip_sep = '';
		$ip_enc = '';
		$agent_enc = '';
		$sid = '';
		$ok = true;

		// Make sure we take a valid ID
		if(!preg_match("/^([0-9]+)$/",$id))
		{
			trigger_error('launch : need a valid ID', E_USER_ERROR);
			return false;
		}

		// Taking user IP
		ksort($addrs);

        foreach ($addrs as $k => $v)
        {
            if (isset($v))
            {
                $chosen = $v;
                break;
            }
        }

		// Make sure we take a valid IP address
		$ip = preg_replace( "/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4",$chosen );

		// Encode IP
		$ip_sep = explode('.', $ip);
		$ip_enc = sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);

		// Encode user agent
		$agent_enc = sha1($_SERVER['HTTP_USER_AGENT']);

		// Get sesssion id
		$sid = session_id();

		// Generate and register the key
		$_SESSION['key'] = $id."-".$sid."-".$ip_enc."-".$agent_enc;

		return $ok;
	}

	/*


	*/
	function pageStart()
	{
		$addrs[] = $_SERVER['REMOTE_ADDR'];
		$id = '';
		$ip = '';
		$ip_enc = '';
		$ip_sep = '';
		$tab = array();
		$lo_sess_id = '';
		$lo_enc_agent = '';
		$key_enc = '';

		// Taking user IP
		ksort($addrs);

        foreach ($addrs as $k => $v)
        {
            if (isset($v))
            {
                $chosen = $v;
                break;
            }
        }

		// Make sure we take a valid IP address
		$ip = preg_replace( "/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4",$chosen );

		// Encode IP
		$ip_sep = explode('.', $ip);
		$ip_enc = sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);

		// Get sesssion id
		$sid = session_id();

		// Encode user agent
		$agent_enc = sha1($_SERVER['HTTP_USER_AGENT']);

		if($this->isOpened())
		{
			$key_enc = $_SESSION['key'];

			$tab = explode("-",$key_enc);

			// Authentification		
			if(strcmp($tab[1]."-".$tab[2]."-".$tab[3], $sid."-".$ip_enc."-".$agent_enc) != 0)
			{
				$this->stop();
				trigger_error('pageStart : invalid session key', E_USER_ERROR);
				return false;
			}

			$id = $lo_tab[0];

			if(empty($id))
			{
				$this->stop();
				trigger_error('pageStart : unknown user', E_USER_ERROR);
				return false;
			}

			// Remise à jour de la clef
			session_regenerate_id();
			$sid = session_id();
			unset($_SESSION['key']);
			$_SESSION['key'] = $id."-".$sid."-".$tab[2]."-".$tab[3];
		}

		return $id;
	}

	/*
		Méthode qui permet de déterminer si une session est lancée et est valide.

		@return true si OK, false sinon
	*/
	function isOpened()
	{
		return isset($_SESSION['key']) && !empty($_SESSION['key']) && $this->isLegalKey($_SESSION['key']);
	}

	/*
		Méthode qui vérifie l'intégrité d'une clef d'authentification
	*/
	function isLegalKey($key)
	{
		if(!isset($key) || empty($key))
		{
			trigger_error('isLegalKey : empty key', E_USER_WARNING);
			return false;
		}

		return preg_match("/^([0-9]+)-([A-Za-z0-9]{32})-([A-Za-z0-9]+)-([A-Za-z0-9]{40})$/",$key);
	}

	/*

	*/
	function stop()
	{
		if($this->isOpened())
		{
			$_SESSION = array();
		}
	}
}

?>