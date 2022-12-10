<?php

if( !defined("IN_PAGE") )
{
	header("location: https://www.google.ru/");
	die();
}

class CUsers
{
	public $db = null;

	function __construct()
	{
		$this->db = CEngineDB::$EngineDB;
	}

	public function AddUser($name,$hwid,$date)
	{
		$data = array(
			"name" => $name,
			"hwid" => $hwid,
			"date" => $date
			);

		if ( $this->db->insert("users", $data) )
			return true;

		echo 'user add failed: ' . $this->db->getLastError();

		return false;
	}

	public function FindByName($name)
	{
		$this->db->where("name", $name);

		$users = $this->db->get("users");

		if ( $this->db->count > 0 )
		{
			return true;
		}

		return false;
	}
	
	public function FindByHwid($hwid)
	{
		$this->db->where("hwid", $hwid);

		$users = $this->db->get("users");

		if ( $this->db->count > 0 )
		{
			return true;
		}

		return false;
	}
	
	public function GetUserDay($hwid)
	{
		$user = $this->GetUserByHwid($hwid);
		
		if ( array_key_exists('id',$user) )
		{
			$user_expire_date = $user['date'];
			
			$date1 = new DateTime( $user_expire_date );
			$date2 = new DateTime( date("d.m.Y") );
			$interval = $date2->diff($date1);
			
			$day_count = (int)$interval->format('%R%a');
			return $day_count;
		}

		return 0;
	}

	public function GetUserById($id)
	{
		$this->db->where("id", $id);

		$user = $this->db->getOne("users");

		if ( $this->db->count == 1 )
		{
			return $user;
		}

		return false;
	}
	
	public function GetUserByName($name)
	{
		$this->db->where("name", $name);

		$user = $this->db->getOne("users");

		if ( $this->db->count == 1 )
		{
			return $user;
		}

		return false;
	}
	
	public function GetUserByHwid($hwid)
	{
		$this->db->where("hwid", $hwid);

		$user = $this->db->getOne("users");

		if ( $this->db->count == 1 )
		{
			return $user;
		}

		return false;
	}

	public function UpdateUserById($id,$data)
	{
		$this->db->where("id", $id);

		if ( $this->db->update('users', $data) )
			return true;
		else
			echo 'user update failed: ' . $this->db->getLastError();

		return false;
	}

	public function DeleteUserById($id)
	{
		$this->db->where("id", $id);

		if ( $this->db->delete('users') )
			return true;
		else
			echo 'user delete failed: ' . $this->db->getLastError();

		return false;
	}
}
