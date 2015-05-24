<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*							krullTemplate.class.php
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
 * Template class. By Nathan Codding of the phpBB group.
 * The interface was originally inspired by PHPLib templates,
 * and the template file formats are quite similar.
 *
 */

class krullTemplate extends krullSingleton
{
	var $classname = "krullTemplate";

	// variable that holds all the data we'll be substituting into
	// the compiled templates.
	// ...
	// This will end up being a multi-dimensional array like this:
	// $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
	// if it's a root-level variable, it'll be like this:
	// $this->_tpldata[.][0][varname] == value
	var $_tpldata = array();

	// Hash of filenames for each template handle.
	var $files = array();

	// Root template directory.
	var $root = "";

	// this will hash handle names to the compiled code for that handle.
	var $compiled_code = array();

	// This will hold the uncompiled code for that handle.
	var $uncompiled_code = array();

	/**
	 * Constructor. Simply sets the root dir.
	 *
	 */
	function __construct($root = ".")
	{
		parent::__construct();
		$this->setRootDir($root);
	}

	/*
		Function: get_instance
		
		Returns:
		
		object - a reference to the one and only instance of the krullTemplate.
	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullTemplate');
    }

	/**
	 * Destroys this template object. Should be called when you're done with it, in order
	 * to clear out the template data so you can load/parse a new template set.
	 */
	function destroy()
	{
		$this->_tpldata = array();
	}

	/**
	 * Sets the template root directory for this Template object.
	 */
	function setRootDir($dir)
	{
		if (!is_dir($dir))
		{
			return false;
		}

		$this->root = $dir;
		return true;
	}

	/**
	 * Sets the template filenames for handles. $filename_array
	 * should be a hash of handle => filename pairs.
	 */
	function setFilenames($filename_array)
	{
		if (!is_array($filename_array))
		{
			return false;
		}

		reset($filename_array);
		while(list($handle, $filename) = each($filename_array))
		{
			$this->files[$handle] = $this->makeFilename($filename);
		}

		return true;
	}


	/**
	 * Load the file for the handle, compile the file,
	 * and run the compiled code. This will print out
	 * the results of executing the template.
	 */
	function parseAndPrint($handle)
	{
		if (!$this->loadFile($handle))
		{
			trigger_error('parseAndPrint : Couldn\'t load template file for handle '.$handle, E_USER_ERROR);
			return false;
		}

		// actually compile the template now.
		if (!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle]))
		{
			// Actually compile the code now.
			$this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle]);
		}

		// Run the compiled code.
		eval($this->compiled_code[$handle]);
		return true;
	}

	/**
	 * Load the file for the handle, compile the file,
	 * and 
	 */
	function parseAndBuff($handle)
	{
		$lo_out = "";

		if (!$this->loadFile($handle))
		{
			trigger_error('parseAndPrint : Couldn\'t load template file for handle '.$handle, E_USER_ERROR);
			return false;
		}

		// actually compile the template now.
		if (!isset($this->compiled_code[$handle]) || empty($this->compiled_code[$handle]))
		{
			// Actually compile the code now.
			$this->compiled_code[$handle] = $this->compile($this->uncompiled_code[$handle]);
		}

		// Get the generated string
		ob_start();
		eval($this->compiled_code[$handle]);
		$lo_out = ob_get_contents();
		ob_end_clean();

		return $lo_out;
	}

	/**
	 * Inserts the uncompiled code for $handle as the
	 * value of $varname in the root-level. This can be used
	 * to effectively include a template in the middle of another
	 * template.
	 * Note that all desired assignments to the variables in $handle should be done
	 * BEFORE calling this function.
	 */
	function assignVarFromHandle($varname, $handle)
	{
		if (!$this->loadFile($handle))
		{
			trigger_error('assignVarFromHandle : Couldn\'t load template file for handle '.$handle, E_USER_ERROR);
			return false;
		}

		// Compile it, with the "no echo statements" option on.
		$_str = "";
		$code = $this->compile($this->uncompiled_code[$handle], true, '_str');

		// evaluate the variable assignment.
		eval($code);
		// assign the value of the generated variable to the given varname.
		$this->assignVar($varname, $_str);

		return true;
	}

	/**
	 * Block-level variable assignment. Adds a new block iteration with the given
	 * variable assignments. Note that this should only be called once per block
	 * iteration.
	 */
	function assignBlockVars($blockname, $vararray)
	{
		if (strstr($blockname, '.'))
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;
			$str = '$this->_tpldata';
			for ($i = 0; $i < $blockcount; $i++)
			{
				$str .= '[\'' . $blocks[$i] . '.\']';
				eval('$lastiteration = sizeof(' . $str . ') - 1;');
				$str .= '[' . $lastiteration . ']';
			}
			// Now we add the block that we're actually assigning to.
			// We're adding a new iteration to this block with the given
			// variable assignments.
			$str .= '[\'' . $blocks[$blockcount] . '.\'][] = $vararray;';

			// Now we evaluate this assignment we've built up.
			eval($str);
		}
		else
		{
			// Top-level block.
			// Add a new iteration to this block with the variable assignments
			// we were given.
			$this->_tpldata[$blockname . '.'][] = $vararray;
		}

		return true;
	}

	/**
	 * Root-level variable assignment. Adds to current assignments, overriding
	 * any existing variable assignment with the same name.
	 */
	function assignVars($vararray)
	{
		reset ($vararray);
		while (list($key, $val) = each($vararray))
		{
			$this->_tpldata['.'][0][$key] = $val;
		}

		return true;
	}

	/**
	 * Root-level variable assignment. Adds to current assignments, overriding
	 * any existing variable assignment with the same name.
	 */
	function assignVar($varname, $varval)
	{
		$this->_tpldata['.'][0][$varname] = $varval;

		return true;
	}


	/**
	 * Generates a full path+filename for the given filename, which can either
	 * be an absolute name, or a name relative to the rootdir for this Template
	 * object.
	 */
	function makeFilename($filename)
	{
		// Check if it's an absolute or relative path.
		/*if (substr($filename, 0, 1) != '/')
		{
       		$filename = ($rp_filename = phpbb_realpath($this->root . '/' . $filename)) ? $rp_filename : $filename;
		}
		*/
		if (!file_exists($filename))
		{
			trigger_error('makeFilename : file '.$filename.' does not exist.', E_USER_ERROR);
			return false;
		}

		$filename = $this->root . '/' . $filename;

		return $filename;
	}


	/**
	 * If not already done, load the file for the given handle and populate
	 * the uncompiled_code[] hash with its code. Do not compile.
	 */
	function loadFile($handle)
	{
		// If the file for this handle is already loaded and compiled, do nothing.
		if (isset($this->uncompiled_code[$handle]) && !empty($this->uncompiled_code[$handle]))
		{
			return true;
		}

		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error('loadFile: No file specified for handle '.$handle, E_USER_ERROR);
			return false;
		}

		$filename = $this->files[$handle];

		$str = implode("", @file($filename));
		if (empty($str))
		{
			trigger_error('loadFile: File '.$filename.' for handle <b>'.$handle.' is empty', E_USER_ERROR);
			return false;
		}

		$this->uncompiled_code[$handle] = $str;

		return true;
	}



	/**
	 * Compiles the given string of code, and returns
	 * the result in a string.
	 * If "do_not_echo" is true, the returned code will not be directly
	 * executable, but can be used as part of a variable assignment
	 * for use in assign_code_from_handle().
	 */
	function compile($code, $do_not_echo = false, $retvar = '')
	{
		// replace \ with \\ and then ' with \'.
		$code = str_replace('\\', '\\\\', $code);
		$code = str_replace('\'', '\\\'', $code);

		// change template varrefs into PHP varrefs

		// This one will handle varrefs WITH namespaces
		$varrefs = array();
		preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $code, $varrefs);
		$varcount = sizeof($varrefs[1]);
		for ($i = 0; $i < $varcount; $i++)
		{
			$namespace = $varrefs[1][$i];
			$varname = $varrefs[3][$i];
			$new = $this->generateBlockVarRef($namespace, $varname);

			$code = str_replace($varrefs[0][$i], $new, $code);
		}

		// This will handle the remaining root-level varrefs
		$code = preg_replace('#\{([a-z0-9\-_]*?)\}#is', '\' . ( ( isset($this->_tpldata[\'.\'][0][\'\1\']) ) ? $this->_tpldata[\'.\'][0][\'\1\'] : \'\' ) . \'', $code);

		// Break it up into lines.
		$code_lines = explode("\n", $code);

		$block_nesting_level = 0;
		$block_names = array();
		$block_names[0] = ".";

		// Second: prepend echo ', append ' . "\n"; to each line.
		$line_count = sizeof($code_lines);
		for ($i = 0; $i < $line_count; $i++)
		{
			$code_lines[$i] = chop($code_lines[$i]);
			if (preg_match('#<!-- BEGIN (.*?) -->#', $code_lines[$i], $m))
			{
				$n[0] = $m[0];
				$n[1] = $m[1];

				// Added: dougk_ff7-Keeps templates from bombing if begin is on the same line as end.. I think. :)
				if ( preg_match('#<!-- END (.*?) -->#', $code_lines[$i], $n) )
				{
					$block_nesting_level++;
					$block_names[$block_nesting_level] = $m[1];
					if ($block_nesting_level < 2)
					{
						// Block is not nested.
						$code_lines[$i] = '$_' . $n[1] . '_count = ( isset($this->_tpldata[\'' . $n[1] . '.\']) ) ?  sizeof($this->_tpldata[\'' . $n[1] . '.\']) : 0;';
						$code_lines[$i] .= "\n" . 'for ($_' . $n[1] . '_i = 0; $_' . $n[1] . '_i < $_' . $n[1] . '_count; $_' . $n[1] . '_i++)';
						$code_lines[$i] .= "\n" . '{';
					}
					else
					{
						// This block is nested.

						// Generate a namespace string for this block.
						$namespace = implode('.', $block_names);
						// strip leading period from root level..
						$namespace = substr($namespace, 2);
						// Get a reference to the data array for this block that depends on the
						// current indices of all parent blocks.
						$varref = $this->generateBlockDataRef($namespace, false);
						// Create the for loop code to iterate over this block.
						$code_lines[$i] = '$_' . $n[1] . '_count = ( isset(' . $varref . ') ) ? sizeof(' . $varref . ') : 0;';
						$code_lines[$i] .= "\n" . 'for ($_' . $n[1] . '_i = 0; $_' . $n[1] . '_i < $_' . $n[1] . '_count; $_' . $n[1] . '_i++)';
						$code_lines[$i] .= "\n" . '{';
					}

					// We have the end of a block.
					unset($block_names[$block_nesting_level]);
					$block_nesting_level--;
					$code_lines[$i] .= '} // END ' . $n[1];
					$m[0] = $n[0];
					$m[1] = $n[1];
				}
				else
				{
					// We have the start of a block.
					$block_nesting_level++;
					$block_names[$block_nesting_level] = $m[1];
					if ($block_nesting_level < 2)
					{
						// Block is not nested.
						$code_lines[$i] = '$_' . $m[1] . '_count = ( isset($this->_tpldata[\'' . $m[1] . '.\']) ) ? sizeof($this->_tpldata[\'' . $m[1] . '.\']) : 0;';
						$code_lines[$i] .= "\n" . 'for ($_' . $m[1] . '_i = 0; $_' . $m[1] . '_i < $_' . $m[1] . '_count; $_' . $m[1] . '_i++)';
						$code_lines[$i] .= "\n" . '{';
					}
					else
					{
						// This block is nested.

						// Generate a namespace string for this block.
						$namespace = implode('.', $block_names);
						// strip leading period from root level..
						$namespace = substr($namespace, 2);
						// Get a reference to the data array for this block that depends on the
						// current indices of all parent blocks.
						$varref = $this->generateBlockDataRef($namespace, false);
						// Create the for loop code to iterate over this block.
						$code_lines[$i] = '$_' . $m[1] . '_count = ( isset(' . $varref . ') ) ? sizeof(' . $varref . ') : 0;';
						$code_lines[$i] .= "\n" . 'for ($_' . $m[1] . '_i = 0; $_' . $m[1] . '_i < $_' . $m[1] . '_count; $_' . $m[1] . '_i++)';
						$code_lines[$i] .= "\n" . '{';
					}
				}
			}
			else if (preg_match('#<!-- END (.*?) -->#', $code_lines[$i], $m))
			{
				// We have the end of a block.
				unset($block_names[$block_nesting_level]);
				$block_nesting_level--;
				$code_lines[$i] = '} // END ' . $m[1];
			}
			else
			{
				// We have an ordinary line of code.
				if (!$do_not_echo)
				{
					$code_lines[$i] = 'echo \'' . $code_lines[$i] . '\' . "\\n";';
				}
				else
				{
					$code_lines[$i] = '$' . $retvar . '.= \'' . $code_lines[$i] . '\' . "\\n";'; 
				}
			}
		}

		// Bring it back into a single string of lines of code.
		$code = implode("\n", $code_lines);
		return $code	;

	}


	/**
	 * Generates a reference to the given variable inside the given (possibly nested)
	 * block namespace. This is a string of the form:
	 * ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	 * It's ready to be inserted into an "echo" line in one of the templates.
	 * NOTE: expects a trailing "." on the namespace.
	 */
	function generateBlockVarRef($namespace, $varname)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, strlen($namespace) - 1);

		// Get a reference to the data block for this namespace.
		$varref = $this->generateBlockDataRef($namespace, true);
		// Prepend the necessary code to stick this in an echo line.

		// Append the variable reference.
		$varref .= '[\'' . $varname . '\']';

		$varref = '\' . ( ( isset(' . $varref . ') ) ? ' . $varref . ' : \'\' ) . \'';

		return $varref;

	}


	/**
	 * Generates a reference to the array of data values for the given
	 * (possibly nested) block namespace. This is a string of the form:
	 * $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	 *
	 * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	 * NOTE: does not expect a trailing "." on the blockname.
	 */
	function generateBlockDataRef($blockname, $include_last_iterator)
	{
		// Get an array of the blocks involved.
		$blocks = explode(".", $blockname);
		$blockcount = sizeof($blocks) - 1;
		$varref = '$this->_tpldata';
		// Build up the string with everything but the last child.
		for ($i = 0; $i < $blockcount; $i++)
		{
			$varref .= '[\'' . $blocks[$i] . '.\'][$_' . $blocks[$i] . '_i]';
		}
		// Add the block reference for the last child.
		$varref .= '[\'' . $blocks[$blockcount] . '.\']';
		// Add the iterator for the last child if requried.
		if ($include_last_iterator)
		{
			$varref .= '[$_' . $blocks[$blockcount] . '_i]';
		}

		return $varref;
	}

}

?>