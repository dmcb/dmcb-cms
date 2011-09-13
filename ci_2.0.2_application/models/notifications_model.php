<?php

class Notifications_model extends CI_Model {

    function Notifications_model()
    {
        parent::__construct();
		
		$this->load->library('email');
    }
	
	function add($adminid, $action, $actionon, $actiononid, $user, $scope, $scopeid, $content, $note, $alert_user = FALSE)
	{
		$scope_sql = "NULL";
		if ($scope != NULL)
		{
			$scope_sql = $this->db->escape($scope);
		}
		$scopeid_sql = "NULL";
		if ($scopeid != NULL)
		{
			$scopeid_sql = $this->db->escape($scopeid);
		}
		$content_sql = "NULL";
		if ($content != NULL)
		{
			$content_sql = $this->db->escape($content);
		}
	
		$this->db->query("INSERT into notifications (adminid, action, actionon, actiononid, parentid, scope, scopeid, content, note, date) VALUES (".$this->db->escape($adminid).",".$this->db->escape($action).",".$this->db->escape($actionon).",".$this->db->escape($actiononid).",".$this->db->escape($user['userid']).",".$scope_sql.",".$scopeid_sql.",".$content_sql.",".$this->db->escape($note).", NOW())");
		
		// If the notification is internally added and a message is not to be sent, don't send one to the end user
		if ($alert_user)
		{
			if ($actionon == "user")
			{
				return $this->notify_user($user, $action, $actionon, $scope, $scopeid, $content, $note);
			}
			else
			{
				return $this->notify_content($user, $action, $actionon, $content, $note);
			}
		}
	}
	
	function get($userid)
	{
		return $this->db->query("SELECT * FROM notifications WHERE parentid = ".$this->db->escape($userid)." AND action != 'edited' ORDER BY date DESC LIMIT 30");
	}
	
	function get_plus_minus($userid)
	{
		// Gets a numeric score based on goodthings - badthings since the last user status or role change
		// People with big minus scores should probably be dropped a status, likewise people with big positive scores should probably be upped a status
		$query = $this->db->query("SELECT date FROM notifications WHERE actionon = 'user' AND actiononid = ".$this->db->escape($userid)." AND scope IS NULL ORDER BY date DESC limit 1");
		if ($query->num_rows() == 0)
		{
			$badquery = $this->db->query("SELECT COUNT(*) FROM notifications WHERE (action = 'deleted' OR action = 'held back' OR action = 'unfeatured') AND parentid = ".$this->db->escape($userid));
			$goodquery = $this->db->query("SELECT COUNT(*) FROM notifications WHERE (action = 'approved' OR action = 'featured') AND parentid = ".$this->db->escape($userid));
		}
		else
		{
			$row = $query->row_array();
			$date = $row['date'];
			$badquery = $this->db->query("SELECT COUNT(*) FROM notifications WHERE (action = 'deleted' OR action = 'held back' OR action = 'unfeatured') AND date > ".$this->db->escape($date)." AND parentid = ".$this->db->escape($userid));
			$goodquery = $this->db->query("SELECT COUNT(*) FROM notifications WHERE (action = 'approved' OR action = 'featured') AND date > ".$this->db->escape($date)." AND parentid = ".$this->db->escape($userid));
		}
		$badrow = $badquery->row_array();
		$badthings = $badrow['COUNT(*)'];
		$goodrow = $goodquery->row_array();
		$goodthings = $goodrow['COUNT(*)'];
		
		return $goodthings - $badthings;
	}
	
	function notify_content($user, $action, $actionon, $content, $note)
	{
		$subject = "About your ".$actionon." on ".$this->config->item('dmcb_title');
		
		if ($action == "held back")
		{
			$message .= "Your ".$actionon." '".strip_tags($content)."', was ".$action." from public view. Please go to the following link to edit your ".$actionon." and resubmit it:\n".
			base_url()."profile/".$user['urlname']."/held".$actionon."s";
		}
		else if ($action == "featured")
		{
			$message .= "Your ".$actionon." '".$content."', was ".$action.". Congratulations, and thanks for contributing!";
		}
		else if ($action == "removed")
		{
			$message = "Your subscription was removed.";
		}
		else if ($action == "updated")
		{
			$message = "Your subscription has been updated to a ".$content.".";
		}
		else
		{
			$message = "Your ".$actionon." '".strip_tags($content)."', was ".$action.".";
		}
		
		$message .= "\nIf you feel this message is in error, please contact support at support@".$this->config->item('dmcb_server');
		if ($note != "")
		{
			$message .= "\n\nMessage from the ".$this->config->item('dmcb_title')." moderating team:\n'".$note."'";
		}

		return $this->send($user['email'], $subject, $message);
	}
	
	function notify_user($user, $action, $actionon, $scope, $scopeid, $content, $note)
	{
		$subject = "Your account at ".$this->config->item('dmcb_title')." has changed";
		
		if ($action == "set")
		{
			if ($scope != NULL)
			{
				$object = instantiate_library($scope, $scopeid);
				$scopedata = $object->$scope;
				$content .= " for the ".$scope." '".$scopedata['title']."' located at ".base_url().$scopedata['urlname'];
			}
			$message = "Your role has been set to ".$content.".";
		}
		else if ($action == "removed")
		{
			if ($scope != NULL)
			{
				$object = instantiate_library($scope, $scopeid);
				$scopedata = $object->$scope;
				$content .= " for the ".$scope." '".$scopedata['title']."' located at ".base_url().$scopedata['urlname'];
			}
			$message = "Your role has been removed ".$content.".";
		}
		else if ($action == "downgraded")
		{
			$message = "Your status has been downgraded to ".$content.".";
		}
		else if ($action == "upgraded")
		{
			$message = "Your status has been upgraded to ".$content.".";
		}
		else
		{
			$message = "Your account has been ".strtolower($action)." to ".$content.".";
		}
		
		$message .= "\nIf you feel this message is in error, please contact support at support@".$this->config->item('dmcb_server');
		if ($note != "")
		{
			$message .= "\n\nMessage from the ".$this->config->item('dmcb_title')." moderating team:\n'".$note."'";
		}

		return $this->send($user['email'], $subject, $message);
	}
	
	function send($email, $subject, $message, $attachments = array(), $source = "web")
	{
		$this->email->clear(TRUE);
	
		$this->email->to($email);
		$this->email->from($source."@".$this->config->item('dmcb_server'), $this->config->item('dmcb_friendly_server'));
		$this->email->subject($subject);
		$this->email->message($message);
		foreach ($attachments as $attachment)
		{
			if (file_exists($attachment))
			{
				$this->email->attach($attachment);
			}
		}

		return $this->email->send();
	}
	
	function send_to_server($email, $subject, $message, $attachments = array(), $destination = "web")
	{
		$this->email->clear(TRUE);
	
		$this->email->to($destination."@".$this->config->item('dmcb_server'));
		$this->email->from($email);
		$this->email->subject($subject);
		$this->email->message($message);
		foreach ($attachments as $attachment)
		{
			if (file_exists($attachment))
			{
				$this->email->attach($attachment);
			}
		}

		return $this->email->send();	
	}
}