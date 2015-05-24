<?php
/*******************************************************************************
*  ==========================================================================
*                                    Xiawe
*  ==========================================================================
*
*                              	class_archive.php
*  --------------------------------------------------------------------------
*
*      Site Web :       http://www.xiawe.org
*      Fait par :       Dravick
*      Commencé le :    10 mai 2005
*      Modifié le :     3 février 2006
*
*  --------------------------------------------------------------------------
*   Ce programme est libre, vous pouvez le redistribuer et/ou le modifier
*   selon les termes de la Licence Publique Générale GNU publiée par la Free
*   Software Foundation (version 2). Reportez-vous à la Licence Publique
*   Générale GNU pour plus de détails. Vous devez avoir reçu une copie de
*   la Licence Publique Générale GNU en même temps que ce programme ; si ce
*   n'est pas le cas, écrivez à la Free Software Foundation, Inc., 59 Temple
*   Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*  --------------------------------------------------------------------------
*	Tar archive class mostly inspired from Josh Barger <joshb@npt.com>,
*	but adapted by Dravick to the class template of Devin Doucette 
*	<darksnoopy@shaw.ca>.
*  --------------------------------------------------------------------------

*
*******************************************************************************/
 

class archive
{
	var $error = array();
	var $fatal_error = array();
	function archive($name)
	{
		if($this->fatal_error) return FALSE;
		$this->options = array (
			'basedir' => '.',
			'name' => $name,
			'inmemory' => 0,
			'overwrite' => 0,
			'recurse' => 1,
			'level' => 3,
			'method' => 1,
			'type' => "",
		);
		$this->files = array ();
		$this->dirs = array();
		$this->nb_dir = 0;
		$this->nb_files = 0;
	}

	function set_options($options)
	{
		foreach ($options as $key => $value)
			$this->options[$key] = $value;
		if (!empty ($this->options['basedir']))
		{
			$this->options['basedir'] = str_replace("\\", "/", $this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/+/", "/", $this->options['basedir']);
			$this->options['basedir'] = preg_replace("/\/$/", "", $this->options['basedir']);
		}
		if (!empty ($this->options['name']))
		{
			$this->options['name'] = str_replace("\\", "/", $this->options['name']);
			$this->options['name'] = preg_replace("/\/+/", "/", $this->options['name']);
		}
	}

	function create_archive()
	{
		if($this->fatal_error) return FALSE;
		if ($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			$ext = ($this->options['type'] == "gzip" || $this->options['type'] == "bzip") ? ".tmp" : "";
			
			if ($this->options['overwrite'] == 0 && file_exists($this->options['name'] . $ext))
			{
				$this->error[] = "File {$this->options['name']} already exists.";
				chdir($pwd);
				return 0;
			}
			else if ($this->archive = @fopen($this->options['name'] . $ext, "wb+"))
			{
				chdir($pwd);
			}
			else
			{
				$this->error[] = "Could not open {$this->options['name']} for writing.";
				chdir($pwd);
				return 0;
			}
		}
		else
		{
			$this->archive = "";
		}

		switch ($this->options['type'])
		{
			case "zip":
				if (!$this->create_zip())
				{
					$this->error[] = "Could not create zip file.";
					return 0;
				}
				break;
			case "bzip":
				if (!$this->create_tar())
				{
					$this->error[] = "Could not create tar file.";
					return 0;
				}
				if (!$this->create_bzip())
				{
					$this->error[] = "Could not create bzip2 file.";
					return 0;
				}
				break;
			case "gzip":
				if (!$this->create_tar())
				{
					$this->error[] = "Could not create tar file.";
					return 0;
				}
				if (!$this->create_gzip())
				{
					$this->error[] = "Could not create gzip file.";
					return 0;
				}
				break;
			case "tar":
				if (!$this->create_tar())
				{
					$this->error[] = "Could not create tar file.";
					return 0;
				}
		}

		if ($this->options['inmemory'] == 0)
		{
			fclose($this->archive);
			if ($this->options['type'] == "gzip" || $this->options['type'] == "bzip")
			{
				unlink($this->options['basedir'] . "/" . $this->options['name'] . ".tmp");
			}
		}
	}

	function add_data($data)
	{
		if ($this->options['inmemory'] == 0)
			fwrite($this->archive, $data);
		else
			$this->archive .= $data;
	}

	function add_files($list)
	{
		if($this->fatal_error) return FALSE;
		if (!is_array($list)) $list = array($list);

		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach ($list as $current)
		{
			if($current == '.' || $current == '..') continue;
			$current = str_replace("\\", "/", $current);
			$current = preg_replace("/\/+/", "/", $current);
			$current = preg_replace("/\/$/", "", $current);
			
			$this->element('add', $current);
		}

		chdir($pwd);
	}
	
	function remove_files($list)
	{
		if (!is_array($list)) $list = array($list);
		
		$pwd = getcwd();
		chdir($this->options['basedir']);
		
		foreach ($list as $current) $this->element('rm', $current);
		
		chdir($pwd);
	}
	
	// $action peut être 'add' ou 'rm'
	function element($action, $name)
	{
		$func1 = 'priv_' . $action . '_dir';
		$func2 = 'priv_' . $action . '_file';
		if(is_dir($name))
		{
			$this->$func1($name);
			if (!empty($this->options['recurse'])) $this->priv_recurse_dir($action, $name);
		}
		elseif(is_file($name))
		{
			$this->$func2($name);
		}
	}
	
	// $action peut être 'add' ou 'rm'
	function priv_recurse_dir($action, $dirname)
	{
		$dir = @opendir($dirname);
		while (($file = @readdir($dir)) !== FALSE)
		{
			$fullname = $dirname . "/" . $file;
			if ($file == "." || $file == "..") continue;
			$this->element($action, $fullname);
		}
		closedir($dir);
	}
	
	function priv_add_file($name)
	{
		if(!is_file($name))
		{
			$this->error[] = "File {$current['name']} doesn't exist.";
			return FALSE;
		}
		
		$info = stat($name);

		if(!$fp = fopen($name,"rb"))
		{
			$this->error[] = "Could not open file {$current['name']} for reading. It was not added.";
			return FALSE;
		}
		
		$file_contents = @fread($fp, filesize($name));
		fclose($fp);
		
		$this->nb_files++;
		$file	= &$this->files[$name];
		$file['name']	= $name;
		$file['mode']	= $info['mode'];
		$file['uid']	= $info['uid'];
		$file['gid']	= $info['gid'];
		$file['size']	= $info['size'];
		$file['time']	= $info['mtime'];
		// non-défini :S
		$file['checksum']	= $checksum;
		$file['user_name'] = (($tmp = @posix_getpwuid($info['uid'])) ? $tmp['name']: '');
		$file['group_name'] = (($tmp = @posix_getgrgid($info['gid'])) ? $tmp['name']: '');
		$file['file']	= $file_contents;
	}
	
	function priv_rm_file($name)
	{
		if(!isset($this->files[$name])) return;
		$this->nb_files--;
		unset($this->files[$name]);
	}
	
	function priv_add_dir($name)
	{
		if(!is_dir($name))
		{
			$this->error[] = "Directory {$current['name']} doesn't exist.";
			return FALSE;
		}
		elseif($name == '.' || $name == '..')
		{
			return;
		}
		
		$info = stat($name);

		$this->nb_dir++;
		$dir	= &$this->dirs[$name];
		$dir['name'] = $name;
		$dir['mode'] = $info['mode'];
		$dir['time'] = $info['time'];
		$dir['uid']	 = $info['uid'];
		$dir['gid']	 = $info['gid'];
		// Non-défini :S
		$dir['checksum']	= $checksum;
	}
	
	function priv_rm_dir($name)
	{
		if(!isset($this->dirs[$name])) return;
		$this->nb_dirs--;
		unset($this->dirs[$name]);
		if (!empty($this->options['recurse'])) $this->priv_recurse_dir('rm', $name);
	}

	function download_file()
	{
		if ($this->options['inmemory'] == 0)
		{
			$this->error[] = "Can only use download_file() if archive is in memory. Redirect to file otherwise, it is faster.";
			return;
		}
		switch ($this->options['type'])
		{
		case "zip":
			header("Content-Type: application/zip");
			break;
		case "bzip":
			header("Content-Type: application/x-bzip2");
			break;
		case "gzip":
			header("Content-Type: application/x-gzip");
			break;
		case "tar":
			header("Content-Type: application/x-tar");
		}
		$header = "Content-Disposition: attachment; filename=\"";
		$header .= strstr($this->options['name'], "/") ? substr($this->options['name'], strrpos($this->options['name'], "/") + 1) : $this->options['name'];
		$header .= "\"";
		header($header);
		header("Content-Length: " . strlen($this->archive));
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: no-cache, must-revalidate, max-age=60");
		header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
		print($this->archive);
	}
}

class tar_file extends archive
{
	function tar_file($name)
	{
		if($this->fatal_error) return FALSE;
		$this->archive($name);
		$this->options['type'] = "tar";
	}

	function create_tar()
	{		
		$pwd = getcwd();
		chdir($this->options['basedir']);
		
		if($this->nb_dir > 0)
		{
			foreach($this->dirs as $information)
			{
				$header = '';
				// Generate tar header for this directory
				// Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
				$header .= str_pad($information["name"], 100, chr(0));
				$header .= str_pad(decoct($information["mode"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["uid"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["gid"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct(0), 11, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["time"]), 11, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_repeat(" ", 8);
				$header .= "5";
				$header .= str_repeat(chr(0), 100);
				$header .= str_pad("ustar", 6, chr(32));
				$header .= chr(32) . chr(0);
				$header .= str_pad("", 32, chr(0));
				$header .= str_pad("", 32, chr(0));
				$header .= str_repeat(chr(0), 8);
				$header .= str_repeat(chr(0), 8);
				$header .= str_repeat(chr(0), 155);
				$header .= str_repeat(chr(0), 12);
				
				// Compute header checksum
				$checksum = str_pad(decoct($this->make_checksum($header)), 6, "0", STR_PAD_LEFT);
				for($i = 0; $i < 6; $i++)
				{
					$header{148 + $i} = $checksum{$i};
				}
				$header{154} = chr(0);
				$header{155} = chr(32);

				// Add new tar formatted data to tar file contents
				$this->add_data($header);
			}
		}
		
		if($this->nb_files > 0)
		{
			foreach($this->files as $information)
			{
				$header = '';
				// Generate the TAR header for this file
				// Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
				$header .= str_pad($information["name"], 100, chr(0));
				$header .= str_pad(decoct($information["mode"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["uid"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["gid"]), 7, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["size"]), 11, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_pad(decoct($information["time"]), 11, "0", STR_PAD_LEFT) . chr(0);
				$header .= str_repeat(" ", 8);
				$header .= "0";
				$header .= str_repeat(chr(0), 100);
				$header .= str_pad("ustar", 6, chr(32));
				$header .= chr(32) . chr(0);
				$header .= str_pad($information["user_name"], 32, chr(0));
				$header .= str_pad($information["group_name"], 32, chr(0));
				$header .= str_repeat(chr(0), 8);
				$header .= str_repeat(chr(0), 8);
				$header .= str_repeat(chr(0), 155);
				$header .= str_repeat(chr(0), 12);

				// Compute header checksum
				$checksum = str_pad(decoct($this->make_checksum($header)), 6, "0", STR_PAD_LEFT);
				for($i = 0; $i < 6; $i++)
				{
					$header{148 + $i} = $checksum{$i};
				}
				$header{154} = chr(0);
				$header{155} = chr(32);

				// Pad file contents to byte count divisible by 512
				$file_contents = str_pad($information["file"], (ceil($information["size"] / 512) * 512), chr(0));

				// Add new tar formatted data to tar file contents
				$this->add_data($header . $file_contents);
			}
		}
		
		// Add 512 bytes of NULLs to designate EOF
		$this->add_data(str_repeat(chr(0), 512));
		
		chdir($pwd);
		return TRUE;
	}
	
	function extract_files()
	{
		if($this->fatal_error) return FALSE;
		$pwd = getcwd();
		chdir($this->options['basedir']);
		
		if ($this->options['inmemory'] == 1)
		{
			$this->files = array ();
		}
			
		if (!$fp =  $this->open_archive())
		{
			$this->error[] = "Could not open file {$this->options['name']}";
			return FALSE;
		}
		
		$tar_file = '';
		while($tmp = fread($fp, filesize($this->options['name']))) $tar_file .= $tmp;
		fclose($fp);
		
		// Read Files from archive
		$tar_length = strlen($tar_file);
		$main_offset = 0;
		
		while($main_offset < $tar_length)
		{
			// If we read a block of 512 nulls, we are at the end of the archive
			if(substr($tar_file, $main_offset, 512) == str_repeat(chr(0), 512)) break;

			// Parse file name
			$file_name = $this->getstr(substr($tar_file, $main_offset, 100));

			// Parse the file mode
			$file_mode = substr($tar_file, $main_offset + 100, 8);

			// Parse the file user ID
			$file_uid = octdec(substr($tar_file, $main_offset + 108, 8));

			// Parse the file group ID
			$file_gid = octdec(substr($tar_file, $main_offset + 116, 8));

			// Parse the file size
			$file_size = octdec(substr($tar_file, $main_offset + 124, 12));

			// Parse the file update time - unix timestamp format
			$file_time = octdec(substr($tar_file, $main_offset + 136, 12));

			// Parse Checksum
			$file_chksum = octdec(substr($tar_file, $main_offset + 148, 6));

			// Parse user name
			$file_uname = $this->getstr(substr($tar_file, $main_offset + 265, 32));

			// Parse Group name
			$file_gname = $this->getstr(substr($tar_file, $main_offset + 297, 32));

			// Make sure our file is valid
			if($this->make_checksum(substr($tar_file, $main_offset, 512)) != $file_chksum)
			{
				$this->error[] = "Could not extract from {$this->options['name']}, it is corrupt.";
				return FALSE;
			}

			// Parse File Contents
			$file_contents = substr($tar_file, $main_offset + 512, $file_size);

			if($file_size > 0)
			{
				// Increment number of files
				$this->nb_files++;

				// Create us a new file in our array
				$file = &$this->files[$file_name];

				// Asign Values
				$file['name'] = $file_name;
				$file['mode'] = $file_mode;
				$file['size'] = $file_size;
				$file['time'] = $file_time;
				$file['uid'] = $file_uid;
				$file['gid'] = $file_gid;
				$file['user_name'] = $file_uname;
				$file['group_name'] = $file_gname;
				$file['checksum'] = $file_chksum;
				$file['file'] = $file_contents;

			}
			else
			{
				// Increment number of directories
				$this->nb_dirs++;

				// Create a new directory in our array
				$dir = &$this->dirs[$file_name];

				// Assign values
				$dir['name'] = $file_name;
				$dir['mode'] = $file_mode;
				$dir['time'] = $file_time;
				$dir['uid'] = $file_uid;
				$dir['gid'] = $file_gid;
				$dir['user_name'] = $file_uname;
				$dir['group_name'] = $file_gname;
				$dir['checksum'] = $file_chksum;
			}

			// Move our offset the number of blocks we have processed
			$main_offset += 512 + (ceil($file_size / 512) * 512);
		}
		
		unset($file, $dir);
		
		if ($this->options['inmemory'] == 0)
		{
			foreach($this->dirs as $name => $dir)
			{
				if(!is_dir($name))
				{
					mkdir($name, octdec(substr($dir["mode"], 4)));
				}
			}
			
			foreach($this->files as $file)
			{
				if($this->options['overwrite'] == 0 && is_file($file['name']))
				{
					$this->error[] = "{$file['name']} already exists.";
				}
				elseif ($new = @fopen($file['name'], "wb"))
				{
					fwrite($new, $file['file']);
					fclose($new);
					@chmod($file['name'], octdec(substr($file["mode"], 4)));
					@chown($file['name'], $file['uid']);
					@chgrp($file['name'], $file['gid']);
					@touch($file['name'], $file['time']);
				}
				else
				{
					$this->error[] = "Could not open {$file['name']} for writing.";
				}
			}
		}
		
		chdir($pwd);
		return TRUE;
	}

	function open_archive()
	{
		return @fopen($this->options['name'], "rb");
	}
	
	// Computes the unsigned Checksum of a file's header
	// to try to ensure valid file
	// PRIVATE ACCESS FUNCTION
	function make_checksum($bytestring)
	{
		for($i = 0; $i < 512; $i++) $unsigned_chksum += ord($bytestring{$i});
		for($i = 0; $i < 8; $i++) $unsigned_chksum -= ord($bytestring{148 + $i});
		$unsigned_chksum += ord(" ") * 8;

		return $unsigned_chksum;
	}
	
	// Converts a NULL padded string to a non-NULL padded string
	// PRIVATE ACCESS FUNCTION
	function getstr($string)
	{
		return substr($string, 0, strpos($string, chr(0)));
	}
}

class gzip_file extends tar_file
{
	function gzip_file($name)
	{
		if($this->fatal_error) return FALSE;
		if(!function_exists('gzopen'))
		{
			$this->error[] = "Cannot load bzip extension";
			$this->fatal_error = TRUE;
			return FALSE;
		}
		$this->tar_file($name);
		$this->options['type'] = "gzip";
	}

	function create_gzip()
	{
		if ($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			if ($fp = gzopen($this->options['name'], "wb{$this->options['level']}"))
			{
				fseek($this->archive, 0);
				while ($temp = fread($this->archive, 1048576))
					gzwrite($fp, $temp);
				gzclose($fp);
				chdir($pwd);
			}
			else
			{
				$this->error[] = "Could not open {$this->options['name']} for writing.";
				chdir($pwd);
				return 0;
			}
		}
		else
			$this->archive = gzencode($this->archive, $this->options['level']);

		return 1;
	}

	function open_archive()
	{
		return @gzopen($this->options['name'], "rb");
	}
}

class bzip_file extends tar_file
{
	function bzip_file($name)
	{
		if($this->fatal_error) return FALSE;
		if(!function_exists('bzopen'))
		{
			$this->error[] = "Cannot load bzip extension";
			$this->fatal_error = TRUE;
			return FALSE;
		}
		$this->tar_file($name);
		$this->options['type'] = "bzip";
	}

	function create_bzip()
	{
		if ($this->options['inmemory'] == 0)
		{
			$pwd = getcwd();
			chdir($this->options['basedir']);
			if ($fp = bzopen($this->options['name'], "wb"))
			{
				fseek($this->archive, 0);
				while ($temp = fread($this->archive, 1048576))
					bzwrite($fp, $temp);
				bzclose($fp);
				chdir($pwd);
			}
			else
			{
				$this->error[] = "Could not open {$this->options['name']} for writing.";
				chdir($pwd);
				return 0;
			}
		}
		else
			$this->archive = bzcompress($this->archive, $this->options['level']);

		return 1;
	}

	function open_archive()
	{
		return @bzopen($this->options['name'], "rb");
	}
}

class zip_file extends archive
{
	function zip_file($name)
	{
		$this->archive($name);
		$this->options['type'] = "zip";
	}

	function create_zip()
	{
		$files = 0;
		$offset = 0;
		$central = "";

		$pwd = getcwd();
		chdir($this->options['basedir']);

		foreach ($this->files as $current)
		{
			if ($current['name'] == $this->options['name'])
				continue;

			$timedate = explode(" ", date("Y n j G i s", $current['stat'][9]));
			$timedate = ($timedate[0] - 1980 << 25) | ($timedate[1] << 21) | ($timedate[2] << 16) |
				($timedate[3] << 11) | ($timedate[4] << 5) | ($timedate[5]);

			$block = pack("VvvvV", 0x04034b50, 0x000A, 0x0000, (isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate);

			if ($current['stat'][7] == 0 && $current['type'] == 5)
			{
				$block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['name2']) + 1, 0x0000);
				$block .= $current['name2'] . "/";
				$this->add_data($block);
				$central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
					(isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
					0x00000000, 0x00000000, 0x00000000, strlen($current['name2']) + 1, 0x0000, 0x0000, 0x0000, 0x0000, $current['type'] == 5 ? 0x00000010 : 0x00000000, $offset);
				$central .= $current['name2'] . "/";
				$files++;
				$offset += (31 + strlen($current['name2']));
			}
			else if ($current['stat'][7] == 0)
			{
				$block .= pack("VVVvv", 0x00000000, 0x00000000, 0x00000000, strlen($current['name2']), 0x0000);
				$block .= $current['name2'];
				$this->add_data($block);
				$central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
					(isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
					0x00000000, 0x00000000, 0x00000000, strlen($current['name2']), 0x0000, 0x0000, 0x0000, 0x0000, $current['type'] == 5 ? 0x00000010 : 0x00000000, $offset);
				$central .= $current['name2'];
				$files++;
				$offset += (30 + strlen($current['name2']));
			}
			else if ($fp = @fopen($current['name'], "rb"))
			{
				$temp = fread($fp, $current['stat'][7]);
				fclose($fp);
				$crc32 = crc32($temp);
				if (!isset($current['method']) && $this->options['method'] == 1)
				{
					$temp = gzcompress($temp, $this->options['level']);
					$size = strlen($temp) - 6;
					$temp = substr($temp, 2, $size);
				}
				else
					$size = strlen($temp);
				$block .= pack("VVVvv", $crc32, $size, $current['stat'][7], strlen($current['name2']), 0x0000);
				$block .= $current['name2'];
				$this->add_data($block);
				$this->add_data($temp);
				unset ($temp);
				$central .= pack("VvvvvVVVVvvvvvVV", 0x02014b50, 0x0014, $this->options['method'] == 0 ? 0x0000 : 0x000A, 0x0000,
					(isset($current['method']) || $this->options['method'] == 0) ? 0x0000 : 0x0008, $timedate,
					$crc32, $size, $current['stat'][7], strlen($current['name2']), 0x0000, 0x0000, 0x0000, 0x0000, 0x00000000, $offset);
				$central .= $current['name2'];
				$files++;
				$offset += (30 + strlen($current['name2']) + $size);
			}
			else
				$this->error[] = "Could not open file {$current['name']} for reading. It was not added.";
		}

		$this->add_data($central);

		$this->add_data(pack("VvvvvVVv", 0x06054b50, 0x0000, 0x0000, $files, $files, strlen($central), $offset,
			!empty ($this->options['comment']) ? strlen($this->options['comment']) : 0x0000));

		if (!empty ($this->options['comment']))
			$this->add_data($this->options['comment']);

		chdir($pwd);

		return 1;
	}
} ?>