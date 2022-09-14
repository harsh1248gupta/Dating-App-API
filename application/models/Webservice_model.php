<?php
error_reporting(0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Webservice_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }


    public function check_phone($number)
        {
            $this->db->select('*');
                $this->db->from('user');
                $this->db->where('phone',$number);
                $query=$this->db->get();
                if ($query->num_rows() == '1') {
                    return True;
                }else {
                    return false;
                }
        }

        public function checkphone($number)
        {
            $this->db->select('*');
                $this->db->from('partner');
                $this->db->where('phone',$number);
                $query=$this->db->get();
                if ($query->num_rows() == '1') {
                    return True;
                }else {
                    return false;
                }
        }
    
  public  function getnearbyData($radius,$lat,$long,$user_id) 

{  
    $this->db->distinct();
   	$this->db->select('user_id,image,image2,full_name,mobile,email,is_active,lat,long,ROUND(((acos(sin(('.$lat.'*pi()/180)) *
    sin((`lat`*pi()/180))+cos(('.$lat.'*pi()/180)) *
    cos((`lat`*pi()/180)) * cos((('.$long.'-
    `long`)*pi()/180))))*180/pi())*60*1.1515*1.609344,2) 
    as distance');
 	$this->db->from('users');
    $this->db->having('distance < '.$radius.'');
    $this->db->where('user_id!=',$user_id);
   	$query = $this->db->get();
   	$result = $query->result();
    foreach ($result as $key => $row) 
    {
    $result[$key]->status=$this->getfriendstatus($result[$key]->user_id,$user_id);
    }
    return $result;
} 

public function getfriendstatus($id,$user_id)
{
$this->db->select('*');
$this->db->from('friend');
$this->db->where("member_id",$id);
$this->db->where("user_id",$user_id);
$this->db->where("status",'1');
$query = $this->db->get();
if($query->num_rows() == "1")
    {
    return '1';
    }
else
    {
    $this->db->select('*');
    $this->db->from('friend');
    $this->db->where("member_id",$id);
    $this->db->where("user_id",$user_id);
    $this->db->where("status",'4');
    $query1 = $this->db->get();
    if($query1->num_rows() == "1")
        {
        return '4';
        }
    else
        {
        $this->db->select('*');
        $this->db->from('notification');
        $this->db->where("member_id",$id);
        $this->db->where("user_id",$user_id);
        $query2 = $this->db->get();
        if($query2->num_rows() == "1")
            {
            return '2';
            }
        else
            {
            $this->db->select('*');
            $this->db->from('notification');
            $this->db->where("member_id",$user_id);
            $this->db->where("user_id",$id);
            $query2 = $this->db->get();
            if($query2->num_rows() == "1")
                {
                return '3';
                }
            else
                {
                $this->db->select('*');
                $this->db->from('friend');
                $this->db->where("member_id",$id);
                $this->db->where("user_id",$user_id);
                $this->db->where("status",'1');
                $query1 = $this->db->get();
                if($query2->num_rows() == "1")
                    {
                    return '1';
                    }
                else
                    {
                $this->db->select('*');
                $this->db->from('friend');
                $this->db->where("user_id",$id);
                $this->db->where("member_id",$user_id);
                $this->db->where("status",'4');
                $query1 = $this->db->get();
                if($query2->num_rows() == "1")
                    {
                    return '4';
                    }
                else
                    {
                    return '0';
                    }
                    }
                }
            
            }
        }
    }
}

}?>
