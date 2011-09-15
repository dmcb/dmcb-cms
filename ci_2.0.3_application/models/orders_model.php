<?php

class Orders_model extends CI_Model {

    function Orders_model()
    {
        parent::__construct();
    }
	
	function add($orderid, $userid, $firstname, $lastname, $address, $city, $province, $postalcode, $country, $phone, $comment)
	{
		$time = date('Y-m-d H:i:s');
		$this->db->query("INSERT into orders (orderid, userid, firstname, lastname, address, city, province, postalcode, country, phone, comment, date) VALUES (".$this->db->escape($orderid).",".$this->db->escape($userid).",".$this->db->escape($firstname).",".$this->db->escape($lastname).",".$this->db->escape($address).",".$this->db->escape($city).",".$this->db->escape($province).",".$this->db->escape($postalcode).",".$this->db->escape($country).",".$this->db->escape($phone).",".$this->db->escape($comment).",".$this->db->escape($time).")");
	}
	
	function add_item($orderid, $description, $quantity, $price, $tax)
	{
		$this->db->query("INSERT into orders_items (orderid, description, quantity, price, tax) VALUES (".$this->db->escape($orderid).",".$this->db->escape($description).",".$this->db->escape($quantity).",".$this->db->escape($price).",".$this->db->escape($tax).")");
	}
	
	function delete($orderid)
	{
		$this->db->query("DELETE orders, orders_items FROM orders, orders_items WHERE orders.orderid = orders_items.orderid AND orders.orderid=".$this->db->escape($orderid));
	}
	
	function delete_open($userid)
	{
		$this->db->query("DELETE orders, orders_items FROM orders, orders_items WHERE orders.completed='0' AND orders.orderid = orders_items.orderid AND orders.userid=".$this->db->escape($userid));
	}
	
	function get($orderid)
	{
		$query = $this->db->query("SELECT orders.*, SUM((price + (price * tax)) * quantity) AS total FROM orders, orders_items WHERE orders.orderid = orders_items.orderid AND orders.orderid = ".$this->db->escape($orderid));
		if ($query->num_rows() == 0)
		{
			return NULL;
		}
		else
		{
			return $query->row_array(); 
		}
	}
	
	function get_items($orderid)
	{
		return $this->db->query("SELECT *, SUM((price + (price * tax)) * quantity) AS total FROM orders_items WHERE orderid=".$this->db->escape($orderid)." GROUP BY description");
	}

	function set_completed($orderid, $paypal_transaction)
	{
		$this->db->query("UPDATE orders SET completed='1', paypal_transaction = ".$this->db->escape($paypal_transaction)." WHERE orderid = ".$this->db->escape($orderid));
	}
	
	function set_reviewed($orderid)
	{
		$this->db->query("UPDATE orders SET reviewed='1' WHERE orderid = ".$this->db->escape($orderid));
	}
}