<?php
/**
 * @package		dmcb-cms
 * @author		Derek McBurney
 * @copyright	Copyright (c) 2011, Derek McBurney, derek@dmcbdesign.com
 *              This code may not be used commercially without the expressed
 *              written consent of Derek McBurney. Non-commercial use requires
 *              attribution.
 * @link		http://dmcbdesign.com
 */
class Files_model extends CI_Model {

    function Files_model()
    {
        parent::__construct();
    }

	function add($userid, $filename, $extension, $isimage, $attachedto, $attachedid, $filetypeid)
	{
		if (!$attachedid)
		{
			$attachedid = NULL;
		}
		if (!$filetypeid)
		{
			$filetypeid = NULL;
		}
		$this->db->query("INSERT into files (userid, filename, extension, isimage, attachedto, attachedid, filetypeid, date, datemodified) VALUES (".$this->db->escape($userid).", ".$this->db->escape($filename).", ".$this->db->escape($extension).", ".$this->db->escape($isimage).", ".$this->db->escape($attachedto).", ".$this->db->escape($attachedid).", ".$this->db->escape($filetypeid).", NOW(), NOW())");
		return $this->db->insert_id();
	}

	function check_stockimage($fileid)
	{
		$query = $this->db->query("SELECT fileid as total FROM files_stockimages WHERE fileid = ".$this->db->escape($fileid));
		if ($query->num_rows() == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	function delete($fileid)
	{
		$this->db->query("DELETE FROM files WHERE fileid = ".$this->db->escape($fileid));
		$this->db->query("DELETE FROM files_stockimages WHERE fileid = ".$this->db->escape($fileid));
	}

	function get($fileid)
	{
		$query = $this->db->query("SELECT * FROM files WHERE fileid = ".$this->db->escape($fileid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array();
		}
	}

	function get_by_details($details)
	{
		$attachedid = "";
		if (isset($details[3]))
		{
			$attachedid = " AND attachedid = ".$this->db->escape($details[3]);
		}
		$query = $this->db->query("SELECT fileid FROM files WHERE filename = ".$this->db->escape($details[0])." AND extension = ".$this->db->escape($details[1])." AND attachedto = ".$this->db->escape($details[2]).$attachedid);
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			$row = $query->row_array();
			return $row['fileid'];
		}
	}

	function get_attached($attachedto, $attachedid = NULL, $filetypeid = NULL)
	{
		$attachedid_sql = "";
		if ($attachedid != NULL)
		{
			$attachedid_sql = " AND attachedid = ".$this->db->escape($attachedid);
		}

		$filetypeid_sql = "";
		if ($filetypeid != NULL)
		{
			$filetypeid_sql = " AND filetypeid = ".$this->db->escape($filetypeid);
		}

		return $this->db->query("SELECT fileid FROM files WHERE attachedto = ".$this->db->escape($attachedto).$attachedid_sql.$filetypeid_sql." ORDER BY filetypeid, extension, filename ASC");
	}
	function get_attached_listed($attachedto, $attachedid = NULL)
	{
		$attachedid_sql = "";
		if ($attachedid != NULL)
		{
			$attachedid_sql = " AND attachedid = ".$this->db->escape($attachedid);
		}
		return $this->db->query("SELECT fileid FROM files WHERE listed = '1' AND attachedto = ".$this->db->escape($attachedto).$attachedid_sql." ORDER BY filetypeid, extension, filename ASC");
	}

	function get_attached_images($attachedto, $attachedid = NULL)
	{
		$attachedid_sql = "";
		if ($attachedid != NULL)
		{
			$attachedid_sql = " AND attachedid = ".$this->db->escape($attachedid);
		}
		return $this->db->query("SELECT fileid FROM files WHERE isimage = '1' AND attachedto = ".$this->db->escape($attachedto).$attachedid_sql." ORDER BY filetypeid, extension, filename ASC");
	}

	function get_stockimages()
	{
		return $this->db->query("SELECT fileid FROM files_stockimages");
	}

	function remove_stockimage($fileid)
	{
		$this->db->query("DELETE FROM files_stockimages WHERE fileid = ".$this->db->escape($fileid));
	}

	function rename_folder($attachedto, $attachedname, $newattachedname)
	{
		if (file_exists("files/".$attachedto."/".$attachedname) && $attachedname != NULL)
		{
			rename("files/".$attachedto."/".$attachedname, "files/".$attachedto."/".$newattachedname);
		}
		if (file_exists("files_managed/".$attachedto."/".$attachedname) && $attachedname != NULL)
		{
			rename("files_managed/".$attachedto."/".$attachedname, "files_managed/".$attachedto."/".$newattachedname);
		}
	}

	function set_stockimage($fileid)
	{
		$this->db->query("INSERT into files_stockimages (fileid) VALUES (".$this->db->escape($fileid).")");
	}

	function update($fileid, $file)
	{
		$this->db->query("UPDATE files SET userid = ".$this->db->escape($file['userid']).",
			filename = ".$this->db->escape($file['filename']).",
			extension = ".$this->db->escape($file['extension']).",
			isimage = ".$this->db->escape($file['isimage']).",
			listed = ".$this->db->escape($file['listed']).",
			downloadcount = ".$this->db->escape($file['downloadcount']).",
			attachedto = ".$this->db->escape($file['attachedto']).",
			attachedid = ".$this->db->escape($file['attachedid']).",
			filetypeid = ".$this->db->escape($file['filetypeid']).",
			date = ".$this->db->escape($file['date']).",
			datemodified = ".$this->db->escape($file['datemodified'])."
			WHERE fileid=".$this->db->escape($fileid));
	}
}