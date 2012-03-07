<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * dmcb file library
 *
 * Initalizes a file and runs checks and operations on that file
 *
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class File_lib {

	public  $file     = array();
	public  $new_file = array();
	private $path;
	private $rootpath;
	private $folder;
	private $rootfolder;
	private $managed;

	/**
	 * Constructor
	 *
	 * Grab CI
	 *
	 * @access	public
	 */
	function File_lib($params = NULL)
	{
		$this->CI =& get_instance();
		$this->CI->load->model('files_model');
		if (isset($params['id']))
		{
			$this->new_file = $this->CI->files_model->get($params['id']);
			$this->file = $this->new_file;
			$this->_initialize_paths();
			$this->_initialize_info();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize info
	 *
	 * Set the date modified and file size properties
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_info()
	{
		$this->CI->load->helper('file');
		$this->file['filesize'] = filesize($this->path);
		$this->file['filemodified'] = filemtime($this->path);
		$this->file['mimetype'] = get_mime_by_extension($this->path);
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize paths
	 *
	 * Set all the file path information
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize_paths()
	{
		$this->rootfolder = $this->file['attachedto'].'/';
		if ($this->new_file['attachedto'] == "page")
		{
			$object = instantiate_library('page', $this->new_file['attachedid']);
			// Convert nested file path of page to a flat folder structure internally
			$this->rootfolder .= str_replace('/', '+', $object->page['urlname']).'/';
		}
		else if ($this->new_file['attachedto'] == "post")
		{
			$object = instantiate_library('post', $this->new_file['attachedid']);
			// Convert nested file path of post to a flat folder structure internally
			$this->rootfolder .= str_replace('/', '+', $object->post['urlname']).'/';
		}
		else if ($this->new_file['attachedto'] == "user")
		{
			$object = instantiate_library('user', $this->new_file['attachedid']);
			$this->rootfolder .= $object->user['urlname'].'/';
		}
		$this->file['fullfilename'] = $this->new_file['filename'].'.'.$this->new_file['extension'];
		$this->rootpath = $this->rootfolder.$this->file['fullfilename'];
		// Ensure external URL given for the file is not a flat file path
		$this->file['urlpath'] = 'file/'.str_replace('+', '/', $this->rootpath);
		$this->managed;

		if (file_exists('files/'.$this->rootpath))
		{
			$this->path = 'files/'.$this->rootpath;
			$this->folder = 'files/'.$this->rootfolder;
			$this->managed = FALSE;
		}
		else
		{
			$this->path = 'files_managed/'.$this->rootpath;
			$this->folder = 'files_managed/'.$this->rootfolder;
			$this->managed = TRUE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Build search metadata
	 *
	 * Build text data file from non text file to use against searching
	 *
	 * @access	private
	 * @return	void
	 */
	function _build_search_metadata()
	{
		if ($this->file['extension'] == "pdf") //add more file type support in the future (i.e. office documents)
		{
			shell_exec('bin/pdftotext '.$this->path.' '.$this->path.'.searchmetadata');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Clear cache
	 *
	 * Clear cached image resizes, search meta data, and possibly the whole folder if empty
	 *
	 * @access	private
	 * @return	void
	 */
	function _clear_cache()
	{
		if ($handle = opendir($this->folder))
		{
			while (false !== ($dirfile = readdir($handle)))
			{
				//if there is a file that has additional naming after filename.fileextension, delete it
				if (isset($this->file['fullfilename']) && strpos($dirfile, $this->file['fullfilename'].'.') === 0)
				{
					unlink($this->folder.$dirfile);
				}
			}
			closedir($handle);
		}
		//check if folder is empty, and if so, delete it
		$empty = TRUE;
		if ($handle = opendir($this->folder))
		{
			while (false !== ($dirfile = readdir($handle)))
			{
				if ($dirfile != "." && $dirfile != ".." )
				{
					$empty = FALSE;
				}
			}
			closedir($handle);
		}
		if ($empty)
		{
			rmdir($this->folder);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Delete a file and clears out references
	 *
	 * @access	public
	 * @return	void
	 */
	function delete()
	{
		if (file_exists($this->path))
		{
			unlink($this->path);
			$this->_clear_cache();
		}
		$this->CI->files_model->delete($this->file['fileid']);
	}

	// --------------------------------------------------------------------

	/**
	 * Manage
	 *
	 * Moves a file to it's respective managed or unmanaged location
	 *
	 * @access	public
	 * @return	void
	 */
	function manage()
	{
		$this->_initialize_paths();
		$should_be_managed = $this->_manage_check();
		if ($should_be_managed != $this->managed)
		{
			$newpath = 'files/'.$this->rootpath;
			$newfolder = 'files/'.$this->rootfolder;
			if ($should_be_managed)
			{
				$newpath = 'files_managed/'.$this->rootpath;
				$newfolder = 'files_managed/'.$this->rootfolder;
			}
			if (!file_exists($newfolder))
			{
				mkdir($newfolder);
			}
			rename($this->path, $newpath);
			$this->_clear_cache();
			$this->_initialize_paths();
			$this->_build_search_metadata();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Manage check
	 *
	 * Determine if a file needs to be managed
	 *
	 * @access	private
	 * @return	void
	 */
	function _manage_check()
	{
		$attachedid = $this->new_file['attachedid'];
		//if ($this->new_file['listed']) Why should a listed file be protected by default? Commented out
		//{
		//	return TRUE;
		//}
		if ($this->new_file['attachedto'] == "post")
		{
			$object = instantiate_library('post', $attachedid);
			if (isset($object->new_post['postid']))
			{
				if ($object->new_post['published'] == 0 || $object->new_post['featured'] == -1 || $object->new_post['needsubscription'] == 1)
				{
					return TRUE;
				}
				$attachedid = $object->new_post['pageid']; // If it's attached to a post, we also need to check it's parent's properties
			}
		}
		if ($this->new_file['attachedto'] == "post" || $this->new_file['attachedto'] == "page") // Check page, or if it was a post, check post's parent
		{
			$object = instantiate_library('page', $attachedid);
			if (isset($object->new_page['pageid']))
			{
				if ($object->new_page['published'] == 0 || $object->new_page['needsubscription'] == 1 || sizeof($object->new_page['protection']))
				{
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Overwrite
	 *
	 * Given a temporary file path, overwrite the existing file with that temporary file and remove it
	 *
	 * @access	public
	 * @param   string  temp file
	 * @return	void
	 */
	function overwrite($tempfilepath)
	{
		if (file_exists($tempfilepath))
		{
			copy($tempfilepath, $this->path);
			if ($this->path != $tempfilepath)
			{
				unlink($tempfilepath);
			}
			$this->new_file['datemodified'] = date('YmdHis');
			$this->save();
			$this->_clear_cache();
			$this->_build_search_metadata();
			$this->_initialize_info();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Save
	 *
	 * Save file properties
	 *
	 * @access	public
	 * @return	int    new fileid from file creation
	 */
	function save()
	{
		// Check if the file wasn't initialized from an existing one
		if (!isset($this->file['fileid'])) // If it wasn't, create a new file
		{
			if (!isset($this->new_file['listed']))
			{
				$this->new_file['listed'] = 0;
			}
			if (!isset($this->new_file['downloadcount']))
			{
				$this->new_file['downloadcount'] = 0;
			}
			$this->new_file['fileid'] = $this->CI->files_model->add($this->new_file['userid'], $this->new_file['filename'], $this->new_file['extension'], $this->new_file['isimage'], $this->new_file['attachedto'], $this->new_file['attachedid'], $this->new_file['filetypeid']);
			$this->file = $this->new_file;
			// Create search meta data for first time
			$this->_initialize_paths();
			$this->_build_search_metadata();
			// All new files upload to managed directory, sort out if it needs to be there
			$this->manage();
			return $this->file['fileid'];
		}
		else // If it was, update the existing file
		{
			// If a file is being renamed, copy it over, and update any references to it in pages and posts
			if (($this->new_file['filename'] != $this->file['filename']) || ($this->new_file['extension'] != $this->file['extension']))
			{
				if (file_exists($this->path))
				{
					$this->suggest(); // Make sure we are renaming to the file to something that doesn't exist
					copy($this->path, $this->folder.$this->new_file['filename'].".".$this->new_file['extension']);
					if ($this->path != $this->folder.$this->new_file['filename'].".".$this->new_file['extension'])
					{
						if ($this->new_file['attachedto'] == "page")
						{
							$object = instantiate_library('page', $this->new_file['attachedid']);
							$object->new_page['content'] = str_replace(
																	"/file/page/".$object->page['urlname']."/".$this->file['filename'],
																	"/file/page/".$object->page['urlname']."/".$this->new_file['filename'],
																	$object->page['content']);
							$object->save();
						}
						else if ($this->new_file['attachedto'] == "post")
						{
							$object = instantiate_library('post', $this->new_file['attachedid']);
							$object->new_post['content'] = str_replace(
																	"/file/post/".$object->post['urlname']."/".$this->file['filename'],
																	"/file/post/".$object->post['urlname']."/".$this->new_file['filename'],
																	$object->post['content']);
							$object->new_post['css'] = str_replace(
																	"/file/post/".$object->post['urlname']."/".$this->file['filename'],
																	"/file/post/".$object->post['urlname']."/".$this->new_file['filename'],
																	$object->post['css']);
							$object->new_post['javascript'] = str_replace(
																	"/file/post/".$object->post['urlname']."/".$this->file['filename'],
																	"/file/post/".$object->post['urlname']."/".$this->new_file['filename'],
																	$object->post['javascript']);
							$object->save();
						}
						unlink($this->path);
						$this->_clear_cache();
						$this->_initialize_paths();
						$this->_build_search_metadata();
					}
				}
			}
			if ($this->new_file['listed'] != $this->file['listed'])
			{
				$this->manage();
			}
			$this->CI->files_model->update($this->file['fileid'], $this->new_file);
			$this->file = $this->new_file;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Suggest
	 *
	 * Check to see if the propsed file name and extension already exists and if it does, suggest a new name
	 *
	 * @access	public
	 */
	function suggest()
	{
		// Get and clean up name
		$proposed_filename = to_urlname($this->new_file['filename']);
		$proposed_extension = to_urlname($this->new_file['extension']);

		$root_filename = $proposed_filename;
		$i=0;
		if (preg_match("/^(\w+)(\d+)$/", $proposed_filename, $matches)) // If name already has a numeric ending, use it to increment
		{
			$root_filename = $matches[1];
			$i = $matches[2];
		}

		// Making sure that the new uploaded file gets a unique name by checking filenames of other files attached to the same place
		// This way if there's a file in /files and you are uploading to /files_managed, there won't be a name collision
		$object = instantiate_library('file', array($proposed_filename, $proposed_extension, $this->new_file['attachedto'], $this->new_file['attachedid']), 'details');

		// If this isn't a new file, make sure we allow the name if it's the name of the file we are editing
		if (isset($this->file['fileid']))
		{
			while (isset($object->file['fileid']) && $object->file['fileid'] != $this->file['fileid'])
			{
				$i++;
				$proposed_filename = $root_filename.$i;
				$object = instantiate_library('file', array($proposed_filename, $proposed_extension, $this->new_file['attachedto'], $this->new_file['attachedid']), 'details');
			}
		}
		else
		{
			while (isset($object->file['fileid']))
			{
				$i++;
				$proposed_filename = $root_filename.$i;
				$object = instantiate_library('file', array($proposed_filename, $proposed_extension, $this->new_file['attachedto'], $this->new_file['attachedid']), 'details');
			}

		}
		$this->new_file['filename'] = $proposed_filename;
		$this->new_file['extension'] = $proposed_extension;
	}
}