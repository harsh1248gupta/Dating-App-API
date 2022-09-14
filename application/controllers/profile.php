<?php
error_reporting(0);

defined('BASEPATH') OR exit('No direct script access allowed');
class Webservice extends CI_Controller
{

public function __construct() 
{

parent::__construct();
// Load form validation library
$this->load->library('form_validation');
// Load database
$this->load->model('webservice_general_model');
$this->load->model('Webservice_model');
$this->load->library('email');
$this->load->library('RtcTokenBuilder');
require_once(APPPATH.'libraries/twilio-php-master/Twilio/autoload.php');

}
	
	public function getApi()
{
		header('Content-Type: application/json');
$user_id = trim($this->input->post('test'));
	echo json_encode($user_id);	
	}


public function signUp()
{
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
$date = Date('y/m/d');
$time = (date('H:i:s'));
$full_name = (trim($this->input->post('full_name')));
$email = (trim($this->input->post('email')));
$mobile = (trim($this->input->post('mobile')));
$password = md5(trim($this->input->post('password')));
$firebaseId = (trim($this->input->post('firebaseId')));
$device_token = (trim($this->input->post('device_token')));


if ($full_name == "" || $password == "" || $device_token == "" || $email == "" || $mobile == "")
    {
    $data['status'] = "false";
    $data['message'] = "Please entered all the required field";
    } 
else 
    {
        $this->db->select('*'); 
        $this->db->from('users');
        $this->db->where('email', $email);
        $this->db->where('is_verify', 1);
        $query = $this->db->get();
        if ($query->num_rows() == 0) 
            {
            $this->db->select('*'); 
            $this->db->from('users');
            $this->db->where('mobile', $mobile);
            $this->db->where('is_verify', 1);
            $query2 = $this->db->get();
            if ($query2->num_rows() == 0) 
            {
            $this->db->select('*'); 
            $this->db->from('users');
            $this->db->where('mobile', $mobile);
            $query2 = $this->db->get();
            if ($query2->num_rows() == 0) 
            {
            $randome = rand(1000,9999);
            $sid = 'AC62d7b0afaa5b1f66e2fa964b5fc9d969';
            $token = '6bce8e62568a56c4b47c979c3355809f';
            try
            {
             $client = new Twilio\Rest\Client($sid, $token);
             $client->messages->create(
                 $mobile,
                array(
                    "from" => "+61485871211",
                    'body' => "Welcome to Walkie Talkie Your OTP is $randome."
                )
            );
            
            $insert_data = array(
            'full_name'=>$full_name,
            'email'=>$email,
            'password'=>$password,
            'time'=>$time,
            'date'=>$date,
            'firebaseId'=>$firebaseId,
            'mobile'=>$mobile,
            'device_token'=>$device_token,
            'otp' => ($randome),
            );
            $this->db->insert('users',$insert_data);
            // $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['email' => $email])->get("users")->row();
           
            $data['status'] = "true";
            $data['message'] = "Otp successfully send to your contact number";
            $data['otp'] = $randome;
            }
            catch(Exception $e)
            {
            $data['status'] = "false";
            $data['message'] = "Please enter valid mobile number"; 
            } 
            }
            else{
                $randome = rand(1000,9999);
                $sid = 'AC62d7b0afaa5b1f66e2fa964b5fc9d969';
                $token = '6bce8e62568a56c4b47c979c3355809f';
                try
                {
                 $client = new Twilio\Rest\Client($sid, $token);
                 $client->messages->create(
                     $mobile,
                    array(
                        "from" => "+61485871211",
                        'body' => "Welcome to Walkie Talkie Your OTP is $randome."
                    )
                );
                }
                catch(Exception $e)
                {
                $data['status'] = "false";
                $data['message'] = "Please enter valid mobile number"; 
                }
                
                $user = $this->db->select("user_id")->where(['mobile' => $mobile])->get("users")->row()->user_id;
                $filter2['user_id'] = $user;
                $set2['is_verify'] = "1";
                $this->webservice_general_model->update('users', $filter2, $set2);
                
                $data['status'] = "true";
                $data['message'] = "Otp successfully send to your contact number";  
                $data['otp'] = $randome;
                } 
            }
            else
                {                
                $data['status'] = "false";
                $data['message'] = "Mobile number already exist";
                }
            }
        else
            {
            $data['status'] = "false";
            $data['message'] = "email  is already exist";
            }
    }
echo json_encode($data);
}
	
	public function testApi() 
{
		header('Content-Type: application/json');
$otp = (trim($this->input->post('otp')));
		echo json_encode($otp);
	}

public function verifyOtpByMobile() 
{
header('Content-Type: application/json');
$otp = (trim($this->input->post('otp')));
$mobile = (trim($this->input->post('mobile')));

if ($otp == "" || $mobile == "") 
{
$data['status'] = "false"; 
$data['message'] = "Please entered all the required field";
} 
else 
{
$filter['mobile'] = $mobile;
if ($getResult = $this->webservice_general_model->getData('users',$filter)) 
{
$filter['mobile'] = $mobile;
$filter['otp'] = $otp;
if ($getResult = $this->webservice_general_model->getData('users',$filter)) 
{
$user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['mobile' => $mobile])->get("users")->row();
$filter2['user_id'] = $user->user_id;
$set2['is_verify'] = "1";
$this->webservice_general_model->update('users', $filter2, $set2);
$data['status'] = "true";
$data['message'] = "otp verified";
$data['data'] = $user;
} 
else 
{
$data['status'] = "false";
$data['message'] = "Please enter valid otp";
}
}
else 
{
$data['status'] = "false";
$data['message'] = "Please enter valid mobile number";
}

}

echo json_encode($data);
}



public function signin() 
{
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
$date = Date('y/m/d');
$time = (date('H:i:s'));
$mobile = (trim($this->input->post('mobile')));
$password = md5(trim($this->input->post('password')));
$device_token = (trim($this->input->post('device_token')));

if ($mobile == "" || $password == "" || $device_token == "") 
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    } 
else 
    {
            $filter1['mobile'] = $mobile;
            $filter1['password'] = $password;
            if ($this->webservice_general_model->getData('users',$filter1))
            {
            $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active,location_on as location_status")->where(['mobile' => $mobile])->get("users")->row();
            $filter4['user_id'] = $user->user_id;
            $filter4['is_verify'] = "1";
            $statusdone=$this->webservice_general_model->getData('users', $filter4);
            if($statusdone)
            {
            $filter2['user_id'] = $user->user_id;
            $set2['device_token'] = $device_token;
            $changepasswordstatus=$this->webservice_general_model->update('users', $filter2, $set2);
            
            $data['status'] = "true";
            $data['message'] = "Login successfully";
            $data['data'] = $user;
            }
            else
            {
            $data['status'] = "false";
            $data['message'] = "user not verified";
            }
            }
            else
            {
            $data['status'] = "false";
            $data['message'] = "Credentials not matched";
            }
}
echo json_encode($data);
}

public function ForgotPassword()
{
header('Content-Type: application/json');
$this->load->config('email');
$this->load->library('email');

$from = $this->config->item('smtp_user');
$email = $this->input->post('email');
if($email == "")
    {
    $data['status'] = "false";
    $data['message'] = "Please enter the required field";
    }
else
    {
    $this->db->select('email');
    $this->db->from("users");
    $this->db->where('email',$email);
    $query=$this->db->get();
    $result=$query->row();
    if($result)
        {
        $randome = rand(1000,9999);
        $subject="New Password";
        $this->load->config('email');
        $this->load->library('email');
        
        $from = $this->config->item('smtp_user');
        $email = $this->input->post('email');
        
        $this->email->set_newline("\r\n");
        $this->email->from($from);
        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message('Your New OTP Is  ' .$randome.  '  Please do not share it with anybody');
        
        if ($this->email->send()) 
            {
            $newotp = array(
            'otp' => ($randome),
            );
            $this->db->where('email', $email);
            $this->db->update('users',$newotp);
            $data['status'] = "true";
            $data['message'] = "OTP Is Sent to Your mail";
            $data['otp'] = $randome;
            } 
        else 
            {
            show_error($this->email->print_debugger());
            } 
        }
    else
        {
        $data['status'] = "false";
        $data['message'] = "This email is not registered";
        }
    }
echo json_encode($data);
}

public function getProfile()
{
header('Content-Type: application/json');
$user_id = (trim($this->input->post('user_id')));
if($user_id == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    }
else
    {
    $this->db->select('user_id,full_name,email,gender,dob,image,firebaseId,is_active'); 
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    $result=$query->row();
    if ($result) 
        {
        $data['status'] = "true";
        $data['message'] = "success";
        $data['data'] = $result;
        
        }
    else
        {
        $data['status'] = "false";                    
        $data['message'] = "User not exist";
        }
    }
echo json_encode($data);
}

public function editProfile()
{
header('Content-Type: application/json');
$user_id = trim($this->input->post('user_id'));
$gender = trim($this->input->post('gender'));
$dob = trim($this->input->post('dob'));
$full_name = trim($this->input->post('full_name'));

if ($user_id == "") 
    {
    $data['status'] = "false";
    $data['message'] = "Pls Enter  all required field";
    }
else 
    {
    $filter['user_id'] = $user_id;
    if ($this->webservice_general_model->getData('users',$filter))
        {
        $filteruserInfo['user_id'] = $user_id;
        $setUserDetail['gender'] = $gender;
        $setUserDetail['dob'] = $dob;
        $setUserDetail['full_name'] = $full_name;
        $this->webservice_general_model->update('users', $filteruserInfo, $setUserDetail);
        
        if($_FILES)
            { 
            $image_name1 = "";
            $image_name_thumb1 = "";
            // Upload profile picture
            $random = time();
            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/profile/";
            $config['allowed_types'] = '*';
            $config['file_name'] = $random ;
            $config['encrypt_name'] = TRUE;
            $this->load->library('image_lib');
            $this->image_lib->clear();
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            
            ini_set('upload_max_filesize', '64M');
            ini_set('memory_limit', '-1');
            
            if($this->upload->do_upload('image'))
                {
                $imageArray = $this->upload->data();
                
                $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                
                $config1['image_library'] = 'gd2';
                $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/profile/".$image_name;
                $config1['create_thumb'] = TRUE;
                $config1['maintain_ratio'] = TRUE;
                $config1['width']     = 300;
                $config1['height']   = 377;
                
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config1);
                $this->image_lib->resize();
                $this->image_lib->clear();
                
                $image_name =$imageArray['raw_name'].$imageArray['file_ext'];
                
                $filter1['user_id'] = $user_id;
                $set1['image'] = base_url()."/uploads/profile/".$image_name;
                $this->webservice_general_model->update('users', $filter1, $set1);
                
                $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("users")->row(); 
                $data['status']="true";
                $data['message'] = "Profile has been updated";
                $data['data']=$user;
                
                }
            if($this->upload->do_upload('image2'))
                {
                $imageArray = $this->upload->data();
                
                $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                
                $config1['image_library'] = 'gd2';
                $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/profile/".$image_name;
                $config1['create_thumb'] = TRUE;
                $config1['maintain_ratio'] = TRUE;
                $config1['width']     = 300;
                $config1['height']   = 377;
                
                $this->load->library('image_lib', $config);
                $this->image_lib->initialize($config1);
                $this->image_lib->resize();
                $this->image_lib->clear();
                
                $image_name1 =$imageArray['raw_name'].$imageArray['file_ext'];
                
                $filter2['user_id'] = $user_id;
                $set2['image2'] = base_url()."/uploads/profile/".$image_name1;
                $this->webservice_general_model->update('users', $filter2, $set2);
                
                $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("users")->row(); 
                $data['status']="true";
                $data['message'] = "Profile has been updated";
                $data['data']=$user;
                
                }
                else
                {
                $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("users")->row(); 
                $data['status']="true";
                $data['message'] = "Profile has been updated";
                $data['data']=$user;
                }
            }
        else
            {
            $user = $this->db->select("user_id,full_name,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("users")->row(); 
            $data['status']="true";
            $data['message'] = "Profile has been updated";
            $data['data']=$user;
            // $data['image']=$user->image;
            } 
        
        }
    else
        {
        $data['status'] = "false";
        $data['message'] = 'user id Not Found';
        }
    }
echo str_replace('\/', '/', json_encode($data));
}

public function ChangePassword()
{
header('Content-Type: application/json');
$user_id = (trim($this->input->post('user_id')));
$current_password = md5(trim($this->input->post('current_password')));
$new_password = md5(trim($this->input->post('new_password')));
if($user_id == "" || $current_password == "" || $new_password == "" )
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    }
else
    {
    $this->db->select('user_id,password'); 
    $this->db->from(' users');
    $this->db->where('user_id',$user_id);
    $this->db->where('password',$current_password);
    $query = $this->db->get();
    if ($query->num_rows() == 0) 
        {
        $data['status'] = "false"; 
        $data['message'] = 'Please Enter Correct Current Password';
        
        }
    else
        {
        $filter['user_id'] = $user_id;
        $set['password'] = $new_password;
        $changepasswordstatus=$this->webservice_general_model->update('users', $filter, $set);
        if($changepasswordstatus)
            {
            $data['status'] = "true"; 
            $data['message'] = 'Password Change Successfully';
            }
        else
            {
            $data['status'] = "false"; 
            $data['message'] = 'Something Went Wrong';
            }
        }
    }
echo json_encode($data);
}

public function setNewPassword() 
{
header('Content-Type: application/json');
$otp = (trim($this->input->post('otp')));
$email = (trim($this->input->post('email')));
$password = md5(trim($this->input->post('password')));

if ($otp == "" || $email == "" || $password=="" ) 
{
$data['status'] = "false"; 
$data['message'] = "Please entered all the required field";
} 
else 
{
$filter['email'] = $email;
$filter['otp'] = $otp;
if ($getResult = $this->webservice_general_model->getData('users',$filter)) 
{
$filter3['email'] = $email;
$set3['password'] = $password;
$this->webservice_general_model->update('users', $filter3, $set3);
$data['status'] = "true";
$data['user_id'] = $getResult->user_id;
$data['message'] = "Your password reset Successfull try to Login with new password";
} 
else 
{
$data['status'] = "false";
$data['message'] = "Please enter valid otp";
}
}

echo json_encode($data);
}

public function verifyOtp() 
{
header('Content-Type: application/json');
$otp = (trim($this->input->post('otp')));
$email = (trim($this->input->post('email')));

if ($otp == "" || $email == "") 
{
$data['status'] = "false"; 
$data['message'] = "Please entered all the required field";
} 
else 
{
$filter['email'] = $email;
$filter['otp'] = $otp;
if ($getResult = $this->webservice_general_model->getData('users',$filter)) 
{
$data['status'] = "true";
$data['message'] = "otp verified";
} 
else 
{
$data['status'] = "false";
$data['message'] = "Please enter valid otp";
}
}

echo json_encode($data);
}

public function getHome()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    if($user_id=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $this->db->select('users.user_id,users.image,users.full_name,users.mobile,users.email,users.is_active');
            $this->db->from('recent');
            $this->db->join("users",'users.user_id=recent.member_id');
            $this->db->where('recent.user_id',$user_id);
            $this->db->order_by('recent.timestamp','desc');
            $this->db->limit(10);
            $query=$this->db->get();
            $member_list=$query->result();
            if($member_list)
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data["data"]=$member_list;
            }
            else
            {
                $data['status']="false";
                $data['message']="No data found";
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function createGroup()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $group_name=(trim($this->input->post('group_name')));
    $description=(trim($this->input->post('description')));
    $member_id=(trim($this->input->post('member_id')));
    if($user_id==""|| $group_name=="" || $description=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        $result=$query->num_rows();
        if($result == "1")
        {

            if($_FILES)
            { 
                $image_name1 = "";
                $image_name_thumb1 = "";
                // Upload profile picture
                $random = time();
                $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/profile/";
                $config['allowed_types'] = '*';
                $config['file_name'] = $random ;
                $config['encrypt_name'] = true;
                $this->load->library('image_lib');
                $this->image_lib->clear();
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                
                ini_set('upload_max_filesize', '64M');
                ini_set('memory_limit', '-1');
                
                if($this->upload->do_upload('image'))
                {
                    $imageArray = $this->upload->data();
                    
                    $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                    
                    $config1['image_library'] = 'gd2';
                    $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/profile/".$image_name;
                    $config1['create_thumb'] = true;
                    $config1['maintain_ratio'] = true;
                    $config1['width']     = 300;
                    $config1['height']   = 377;
                    
                    $this->load->library('image_lib', $config);
                    $this->image_lib->initialize($config1);
                    $this->image_lib->resize();
                    $this->image_lib->clear();
                    $image_name =$imageArray['raw_name'].$imageArray['file_ext'];
                    
                    $insert_data=array(
                        'user_id'=>$user_id,
                        'group_name'=>$group_name,
                        'description'=>$description,
                        'image'=>base_url()."/uploads/profile/".$image_name,
                         );
                        $this->db->insert('group',$insert_data);
                        $group_id=$this->db->insert_id();
                        $memberArray = explode(',', $member_id);
                        foreach ($memberArray as $row)             
                        {
                              $item['member_id'] = $row;
                              $item['group_id'] = $group_id;
                              $item['user_id'] = $user_id;

                            $this->db->insert('group_members', $item);
                        
                        }
                        $data['status']= "true";
                        $data['message']="group created succesfully"; 
                    $groupData['group_id']=$group_id; 
                    $groupData['group_name']=$group_name;
                    $groupData['image']=base_url()."/uploads/profile/".$image_name;
                    $data['data']=$groupData;
                } 
                else
                {
                    $insert_data=array(
                    'user_id'=>$user_id,
                    'group_name'=>$group_name,
                    'description'=>$description,
                        );
                    $this->db->insert('group',$insert_data);
                    $group_id=$this->db->insert_id();
                        $memberArray = explode(',', $member_id);
                        foreach ($memberArray as $row)             
                        {
                              $item['member_id'] = $row;
                              $item['group_id'] = $group_id;
                              $item['user_id'] = $user_id;

                            $this->db->insert('group_members', $item);
                        
                        }
                    $data['status']= "true";
                    $data['message']="group created succesfully";
                    $groupData['group_id']=$group_id; 
                    $groupData['group_name']=$group_name;
                    $groupData['image']="";
                    $data['data']=$groupData;
                }
            }
          else
            {
                $insert_data=array(
                'user_id'=>$user_id,
                'group_name'=>$group_name,
                'description'=>$description,
                  );
                $this->db->insert('group',$insert_data);
                $group_id=$this->db->insert_id();
                        $memberArray = explode(',', $member_id);
                        foreach ($memberArray as $row)             
                        {
                              $item['member_id'] = $row;
                              $item['group_id'] = $group_id;
                              $item['user_id'] = $user_id;

                            $this->db->insert('group_members', $item);
                        
                        }
                $data['status']= "true";
                $data['message']="group created succesfully"; 
                    $groupData['group_id']=$group_id; 
                    $groupData['group_name']=$group_name;
                    $groupData['image']="";
                    $data['data']=$groupData;
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function groupList()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    
    if($user_id == "")
    {
        $data["status"]="false";
        $data["Message"]="Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
      if($query->num_rows()=="1")
       {
            $this->db->select("id as group_id,group_name,image,description");
            $this->db->from("group");
            $this->db->where('user_id',$user_id);
            $a=$this->db->get();
            $group_list=$a->result();
            
            $data['status']= "true";
            $data['message']="success"; 
            $data["data"]=$group_list;
       }
        else
        {
        $data["status"]="false";
        $data["Message"]=" user not exist";
        }
        
    }
        
    echo json_encode($data);
}

public function addMemberToGroup()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
$group_id=(trim($this->input->post('group_id')));
if($user_id==""|| $group_id==""||$member_id=="")
{
    $data['status']="false";
    $data['message']= "Please enter required details";
}
else
{
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows() == "1")
    {
    $this->db->select('*');
    $this->db->from('group');
    $this->db->where('id',$group_id);
    $query1=$this->db->get();
    if($query1->num_rows() == "1")
        {
        $filter['group_id'] = $group_id;
        $success=$this->webservice_general_model->delete('group_members',$filter);
        if($success)
            {
            $memberArray = explode(',', $member_id);
            foreach ($memberArray as $row)             
            {
            $item['member_id'] = $row;
            $item['group_id'] = $group_id;
            $item['user_id'] = $user_id;
            $this->db->insert('group_members', $item);
            
            }
            $data['status']= "true";
            $data['message']="member added succesfully"; 
            }
        else
            {
            $data['status']="false";
            $data['message']="something went wrong";
            }
        }
    else
        {
        $data['status']="false";
        $data['message']="group not found";
        }
    }
    else
    {
    $data['status']="false";
    $data['message']="please enter valid user_id";
    }
}
    echo json_encode($data);
}

public function groupMemberList()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $group_id=(trim($this->input->post('group_id')));
    if($user_id==""||$group_id=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $this->db->distinct();
            $this->db->select('users.image,users.full_name,users.mobile,group_members.member_id,users.email,users.user_id,users.is_active');
            $this->db->from('group_members');
            $this->db->join("users",'users.user_id=group_members.member_id');
            $this->db->where('group_members.group_id',$group_id);
            $query=$this->db->get();
            $member_list=$query->result();
            foreach ($member_list as $key => $row) 
            {
            $member_list[$key]->status=$this->getfriendstatus($member_list[$key]->user_id,$user_id);
            }
            if($member_list)
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data["data"]=$member_list;
            }
            else
            {
                $data['status']="false";
                $data['message']="something wrong";
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function memberDetails()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $member_id=(trim($this->input->post('member_id')));
    if($user_id == "" || $member_id == "")
        {
        $data['status'] = "false"; 
        $data['message'] = "Please entered all the required field";
        }
    else
        {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $this->db->select('user_id as member_id,mobile,full_name,email,image,is_active'); 
            $this->db->from('users');
            $this->db->where('user_id',$member_id);
            $query = $this->db->get();
            $result=$query->row();
            if ($result) 
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data['data'] = $result;
                
            }
            else
            {
            $data['status'] = "false";                    
            $data['message'] = "please enter valid user id";
            }
        }
         else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
            
        }
    echo json_encode($data);
}

public function addMemberToContactList()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $member_id=(trim($this->input->post('member_id')));
    if($user_id=="" ||$member_id=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $userrequestexist = $this->db->select("*")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("notification")->num_rows();
            $userrequestexist2 = $this->db->select("*")->where(['member_id' => $user_id])->where(['user_id' => $member_id])->get("notification")->num_rows();
            if($userrequestexist)
            {
            $data['status']= "true";
            $data['message']="contect added succesfully";   
            }
            else{
            $insert_data=array
            (
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            );
            $this->db->insert('notification',$insert_data);
            $data['status']= "true";
            $data['message']="contect added succesfully"; 
            } 
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}
                                                                                                                                                                                                                                                                                                                                                                
public function contact()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $offset = (trim($this->input->post('offset')));
    $newoffset=$offset+10;
    if($user_id=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $this->db->select('users.user_id,users.image,users.full_name,users.mobile,users.email,users.is_active');
            $this->db->from('friend');
            $this->db->join('users','friend.member_id=users.user_id');
            $this->db->where('friend.user_id',$user_id);
            $this->db->where('friend.status','1');
            $this->db->limit($newoffset);
            $query=$this->db->get();
            $member_list=$query->result();
            if($member_list)
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data1['offset'] = strval($newoffset); 
                $data1["data"]=$member_list;
                $data["data"]=$data1;
            }
            else
            {

                $data['status']="false";
                $data['message']="No record found";
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function acceptrequest()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
$status=(trim($this->input->post('status')));
if($user_id==""||$status==""||$member_id=="")
    {
    $data["status"]="false";
    $data["Message"]="Please enter required details";
    }
else
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows()=="1")
        {
        $id = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("notification")->row()->id;
        $id1 = $this->db->select("id")->where(['member_id' => $user_id])->where(['user_id' => $member_id])->get("notification")->row()->id;
        if($status == "1")
            {
            $insert_data=array
            (
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            'status'=>"1",
            );
            $success=$this->db->insert('friend',$insert_data);
            if($success)
            {
            $filter['id'] = $id;
            $this->webservice_general_model->delete('notification',$filter);
            if($id1){
            $filter['id'] = $id1;
            $this->webservice_general_model->delete('notification',$filter);}
            $data['status']= "true";
            $data['message']="request acepted succesful "; 
            }
            else
            {
                $data['status']="false";
                $data['message']="No record found"; 
            }
            }
        else if($status == "2")
            {
                $filter['id'] = $id;
                $success=$this->webservice_general_model->delete('notification',$filter);
                if($id1){
                $filter['id'] = $id1;
                $success=$this->webservice_general_model->delete('notification',$filter);
                    
                }
                $data['status']= "true";
                $data['message']="request rejected"; 
            }
        else
            {
            $data['status']= "false";
            $data['message']="request not acepted "; 
            }
        }
    else
        {
        $data["status"]="false";
        $data["Message"]="Please enter valid user_id";
        }
    }
echo json_encode($data);   
}

public function notificationList()
{
   
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    if($user_id=="") 
    {
        $data["status"]="false";
        $data["Message"]="Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows()=="1")
        {
            $this->db->select('users.image,users.full_name,notification.status,users.is_active,users.email,notification.user_id');
            $this->db->from('notification');
            $this->db->join("users",'users.user_id=notification.member_id');
            $this->db->where("notification.member_id",$user_id);
            $query=$this->db->get();
            $member_list=$query->result();
            if($member_list)
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data["data"]=$member_list;
            }
            else
            {
                $data['status']="false";
                $data['message']="No record found";
            }
        }
        else
        {
            $data['status'] = "false";
            $data['message'] = "Invalid User";
        }
    }
    echo json_encode($data);   
}

public function searchApi()
{
    header('Content-Type: application/json');
    date_default_timezone_set('Asia/Kolkata');
    $user_id = (trim($this->input->post('user_id')));
    $key = (trim($this->input->post('key')));
    $offset = (trim($this->input->post('offset')));
    $newoffset=$offset+10;
    if($user_id == "")
    {
        $data['status'] = "false"; 
        $data['message'] = "Please entered all the required field";
    }
    else
    {
        $this->db->select('*'); 
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query = $this->db->get();
        $result=$query->row();
        if($result)
        {
            $this->db->select('user_id,image,full_name,mobile,email,is_active');
            $this->db->from("users");
            $this->db->like('full_name',$key);
            $this->db->where('user_id !=',$user_id);
            $this->db->limit($newoffset);
            $query=$this->db->get();
            $member_list=$query->result();
            foreach ($member_list as $key => $row) 
            {
            $member_list[$key]->status=$this->getfriendstatus($member_list[$key]->user_id,$user_id);
            }
            if ($member_list) 
            {
                $data['status'] = "true";
                $data['message'] = "Success";
                $data1['offset'] = strval($newoffset); 
                $data1["data"]=$member_list;
                $data["data"]=$data1;
    
            }
            else
            {
            $data['status'] = "false";
            $data['message'] = "Data Not Available";
            $data['data'] = [];
            }
        }
        else
        {
        $data['status'] = "false";
        $data['message'] = "Invalid User"; 
        }
        
    }
    echo json_encode($data);
}

public function setFriendStatus()
{
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kolkata');
$user_id = (trim($this->input->post('user_id')));
$member_id = (trim($this->input->post('member_id')));
$status = (trim($this->input->post('status')));
if($user_id == "" || $status =="" || $member_id == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    }
else
    {
    $this->db->select('*'); 
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    $result=$query->row();
    if($result)
        {
        
        $contact_id = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("friend")->row()->id;
        if($status == "0")
            {
            if($contact_id)
                {
                $filter2['id'] = $contact_id;
                $success=$this->webservice_general_model->delete('friend', $filter2);
                
                $filter2['user_id'] = $member_id;
                $filter2['member_id'] = $user_id;
                $success1=$this->webservice_general_model->delete('friend', $filter2);
                if($success1) 
                    {
                    $data['status'] = "true";
                    $data['message'] = "Success";
                    }
                else
                    {
                    $data['status'] = "false";
                    $data['message'] = "Something went wrong";
                    }
                }
                else
                {
                $data['status'] = "false";
                $data['message'] = "Something went wrong";  
                }
            }
        elseif($status == "2")
            {
    $this->db->select('device_token'); 
    $this->db->from('users');
    $this->db->where('user_id', $member_id);
    $query = $this->db->get();
    $resultm=$query->row()->device_token;
    /*echo $resultm;

    die();*/
    if ($resultm) 
    {
      $device_token = $resultm;

      /*echo $device_token;

      die();  */        
    }
    else
    {
      $data['status'] = "false";
      $data['message'] = "this user has ho device token";
    }
    #API access key from Google API's Console
    if(!defined( 'API_ACCESS_KEY'))
    {
      define( 'API_ACCESS_KEY', 'AAAAtkEI9SM:APA91bFDKld-MTiR1WmSs4PY5UUtm1j3K5SsKu7XIK6ZCyrPi9LUP9gvXFSVjqNLzbpDPRcOxg5GuB9mX0UPr10xQqmicGULxtXriLZ7cUJKlWHhQvTKB6fzXR3AhmutlhkiidEPBFJO' );
    }

    #prep the bundle
    $userDeatils = $this->db->select("*")->where(['user_id' => $user_id])->get("users")->row();

    $msg = array

    (

    'body' => $userDeatils->full_name.' '.'send your friend request',

    'title' => 'Friend request recieved',

    'icon' => 'myicon',/*Default Icon*/

    );

    //print_r($msg);

    $fields = array

    (

    'to' => $device_token,

    'notification' => $msg,

    'data' => $msg,

    'priority' => 'high'

    );

    $headers = array

    (

    'Authorization: key=' . API_ACCESS_KEY,

    'Content-Type: application/json'

    );

    #Send Reponse To FireBase Server

    $ch = curl_init();

    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );

    curl_setopt( $ch,CURLOPT_POST, true );

    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );

    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );

    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );

    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

    $result = curl_exec($ch );

    curl_close( $ch );



    
            $insert_data=array(
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            );
            $success=$this->db->insert('notification',$insert_data);
            if($success) 
                {
                $data['status'] = "true";
                $data['message'] = "request send successfully";
                // return true;
                }
            else
                {
                $data['status'] = "false";
                $data['message'] = "Something went wrong";
                }
            }
            elseif($status == "5")
            {
            $contact_id_exist = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("notification")->row()->id;
            if($contact_id_exist)
                {
                $filter2['id'] = $contact_id_exist;
                $success=$this->webservice_general_model->delete('notification', $filter2);
                if($success) 
                    {
                    $data['status'] = "true";
                    $data['message'] = "Success";
                    }
                else
                    {
                    $data['status'] = "false";
                    $data['message'] = "Something went wrong";
                    }
                }
                else
                {
                $data['status'] = "false";
                $data['message'] = "Something went wrong";  
                }
            }
             elseif($status == "6")
            {
                $contact_id_exist = $this->db->select("id")->where(['member_id' => $user_id])->where(['user_id' => $member_id])->get("notification")->row()->id;
                $filter2['id'] = $contact_id_exist;
                $success=$this->webservice_general_model->delete('notification', $filter2);
                if($success) 
                    {
                    $data['status'] = "true";
                    $data['message'] = "Success";
                    }
                else
                    {
                    $data['status'] = "false";
                    $data['message'] = "Something went wrong";
                    }
            }
        else
            {
            $insert_data=array(
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            );
            $success=$this->db->insert('friend',$insert_data);
            $insert_data=array(
            'member_id'=>$user_id,
            'user_id'=>$member_id,
            );
            $success=$this->db->insert('friend',$insert_data);
            if($success)
                {
                $details = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("notification")->row()->id;
                $details1 = $this->db->select("id")->where(['member_id' => $user_id])->where(['user_id' => $member_id])->get("notification")->row()->id;
                $filter2['id'] = $details;
                $this->webservice_general_model->delete('notification', $filter2);  
                
                $filter3['id'] = $details1;
                $this->webservice_general_model->delete('notification', $filter3); 
                
                $data['status'] = "true";
                $data['message'] = "success";
                }
            else
                {
                $data['status'] = "false";
                $data['message'] = "Something went wrong";  
                }
            }
        }
    else
        {
        $data['status'] = "false";
        $data['message'] = "Invalid User"; 
        }
    }
echo json_encode($data);
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

public function getfriendstatus132($id,$user_id)
{
$data = array();
$multiClause = array('member_id' => $id, 'user_id' => $user_id, 'is_accept' => 2);
$multiClause1 = array('user_id' => $id, 'member_id' => $user_id, 'is_accept' => 3);

    $this->db->select('is_accept');
    $this->db->from('contact_list');
    $this->db->where($multiClause);
    $query = $this->db->get();
    if($query->num_rows() == "1")
        {
        return '2';
        }
        else
        {
    $this->db->where($multiClause1);
    $query = $this->db->get();
    if($query->num_rows() == "1")
        {
        return '3';
        }
    else
        {
        $query->row()->is_accept;
    }
    }
}

public function addRating()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $review=(trim($this->input->post('rating'))); 
    if($user_id==""||$review=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        $result=$query->num_rows();
        if($result == "1")
        {
            $insert_data=array(
            'user_id'=>$user_id,
            'review'=>$review,
        );
        $this->db->insert('review',$insert_data);
        $data['status']= "true";
        $data['message']="rating added succesful";
        }
        else
        {
        $data['status']="false";
        $data['message']="Please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function getfrinedList()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    if($user_id=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        if($query->num_rows() == "1")
        {
            $this->db->select('users.user_id,users.image,users.full_name,users.mobile,users.email,users.is_active');
            $this->db->from('friend');
            $this->db->join('users','friend.member_id=users.user_id');
            $this->db->where('friend.user_id',$user_id);
            $this->db->where('friend.status','1');
            $query=$this->db->get();
            $member_list=$query->result();
            foreach ($member_list as $key => $row) 
            {
            $member_list[$key]->status=$this->getfriendstatus($member_list[$key]->user_id,$user_id);
            }
            if($member_list)
            {
                $data['status'] = "true";
                $data['message'] = "success";
                $data["data"]=$member_list;
            }
            else
            {
                $data['status']="false";
                $data['message']="No record found";
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function addToBlock()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
$status=(trim($this->input->post('status')));
if($user_id==""||$status==""||$member_id=="")
    {
    $data["status"]="false";
    $data["Message"]="Please enter required details";
    }
else
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows()=="1")
        {
        if($status == "4")
            {
            $userrequestexist = $this->db->select("*")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("friend")->num_rows();
            if($userrequestexist > 0)
            {
            $filteruserInfo['user_id'] = $user_id;
            $filteruserInfo['member_id'] = $member_id;
            $setUserDetail['status'] =$status;
            $success=$this->webservice_general_model->update('friend',$filteruserInfo, $setUserDetail);
            
            $data['status']= "true";
            $data['message']="Blocked successfully"; 
            }
            else
            {
            $insert_data=array
            (
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            'status'=>"4",
            );
            
            $this->db->insert('friend',$insert_data);
            
            $data['status']= "true";
            $data['message']="Blocked successfully"; 
            }
            }
        else
            {
            $data['status']= "false";
            $data['message']="request not Process "; 
            }
        }
    else
        {
        $data["status"]="false";
        $data["Message"]="Please enter valid user_id";
        }
    }
echo json_encode($data);   
}

public function removeFromBlock()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
$status=(trim($this->input->post('status')));
if($user_id==""||$status==""||$member_id=="")
    {
    $data["status"]="false";
    $data["Message"]="Please enter required details";
    }
else
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows()=="1")
        {
        if($status == "0")
            {
            $userrequestexist = $this->db->select("*")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("friend")->num_rows();
            $userrequestId = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("friend")->row()->id;
            if($userrequestexist > 0)
            {
            $filter['id'] = $userrequestId;
            $success=$this->webservice_general_model->delete('friend',$filter);
            
            $data['status']= "true";
            $data['message']="Unblocked successfully"; 
            }
            else
            {
            $data['status']= "false";
            $data['message']="something went wrong";  
            }
            }
        else
            {
            $data['status']= "false";
            $data['message']="request not Process"; 
            }
        }
    else
        {
        $data["status"]="false";
        $data["Message"]="Please enter valid user_id";
        }
    }
echo json_encode($data);   
}

public function blockList()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    
    if($user_id == "")
    {
        $data["status"]="false";
        $data["Message"]="Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
      if($query->num_rows()=="1")
       {
            $this->db->select('users.user_id,users.image,users.full_name,users.mobile,users.email,users.is_active');
            $this->db->from('friend');
            $this->db->join('users','friend.member_id=users.user_id');
            $this->db->where('friend.user_id',$user_id);
            $this->db->where('friend.status','4');
            $a=$this->db->get();
            $block_list=$a->result();
            if($block_list){
            $data['status']= "true";
            $data['message']="success"; 
            $data["data"]=$block_list;
            }
            else
            {
            $data['status']= "false";
            $data['message']="No record found"; 
            }
       }
        else
        {
        $data["status"]="false";
        $data["Message"]=" user not exist";
        }
        
    }
        
    echo json_encode($data);
}

public function removeGroup() 
{
header('Content-Type: application/json');
$user_id = (trim($this->input->post('user_id')));
$group_id = (trim($this->input->post('group_id')));

if ($user_id == "" || $group_id == "") 
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    } 
else 
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
  if($query->num_rows()=="1")
  {
    $filter['id'] = $group_id;
    $success=$this->webservice_general_model->delete('group', $filter);
    if($success)
    {
    $data['status'] = "true";
    $data['message'] = "group successfully removed";
    }
    else
    {
    $data['status'] = "false";
    $data['message'] = "Something went wrong";   
    }
    }
    else
    {
    $data['status'] = "false";
    $data['message'] = "Something went wrong";
    }
}
echo json_encode($data);
}

public function updateGroup()
{
    header('Content-Type: application/json');
    $user_id=(trim($this->input->post('user_id')));
    $group_name=(trim($this->input->post('group_name')));
    $description=(trim($this->input->post('description')));
    $group_id=(trim($this->input->post('group_id')));
    if($user_id==""|| $group_name=="" || $description=="")
    {
        $data['status']="false";
        $data['message']= "Please enter required details";
    }
    else
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('user_id',$user_id);
        $query=$this->db->get();
        $result=$query->num_rows();
        if($result == "1")
        {

            if($_FILES)
            { 
                $image_name1 = "";
                $image_name_thumb1 = "";
                // Upload profile picture
                $random = time();
                $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/group/";
                $config['allowed_types'] = '*';
                $config['file_name'] = $random ;
                $config['encrypt_name'] = true;
                $this->load->library('image_lib');
                $this->image_lib->clear();
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                
                ini_set('upload_max_filesize', '64M');
                ini_set('memory_limit', '-1');
                
                if($this->upload->do_upload('image'))
                {
                    $imageArray = $this->upload->data();
                    
                    $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                    
                    $config1['image_library'] = 'gd2';
                    $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/WalkieTalkieApp/uploads/group/".$image_name;
                    $config1['create_thumb'] = true;
                    $config1['maintain_ratio'] = true;
                    $config1['width']     = 300;
                    $config1['height']   = 377;
                    
                    $this->load->library('image_lib', $config);
                    $this->image_lib->initialize($config1);
                    $this->image_lib->resize();
                    $this->image_lib->clear();
                    $image_name =$imageArray['raw_name'].$imageArray['file_ext'];
                        $filter2['id'] = $group_id;
                        $set2['group_name'] = $group_name;
                        $set2['description'] = $description;
                        $set2['image'] = base_url()."/uploads/group/".$image_name;
                        $this->webservice_general_model->update('group', $filter2, $set2);

                        $data['status']= "true";
                        $data['message']="group update succesfully"; 

                } 
                else
                {
                        $filter2['id'] = $group_id;
                        $set2['group_name'] = $group_name;
                        $set2['description'] = $description;
                        $this->webservice_general_model->update('group', $filter2, $set2);

                        $data['status']= "true";
                        $data['message']="group update succesfully"; 
                }
            }
          else
            {
                        $filter2['id'] = $group_id;
                        $set2['group_name'] = $group_name;
                        $set2['description'] = $description;
                        $this->webservice_general_model->update('group', $filter2, $set2);

                        $data['status']= "true";
                        $data['message']="group update succesfully";
            }
        }
        else
        {
        $data['status']="false";
        $data['message']="please enter valid user_id";
        }
    }
    echo json_encode($data);
}

public function removeMemberFromGroup()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
$group_id=(trim($this->input->post('group_id')));
if($user_id==""|| $group_id==""||$member_id=="")
{
    $data['status']="false";
    $data['message']= "Please enter required details";
}
else
{
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows() == "1")
    {
       $this->db->select('*');
        $this->db->from('group');
        $this->db->where('id',$group_id);
        $query1=$this->db->get();
        if($query1->num_rows() == "1")
        { 
            $filter2['group_id'] =$group_id;
            $filter2['member_id'] = $member_id;
            $this->webservice_general_model->delete('group_members', $filter2);
            
            $data['status']= "true";
            $data['message']="member removed succesfully"; 
        }
        else
        {
            $data['status']="false";
            $data['message']="group not found";
        }
    }
    else
    {
    $data['status']="false";
    $data['message']="please enter valid user_id";
    }
}
    echo json_encode($data);
}

public function Locationstatus()
{
header('Content-Type: application/json');
$user_id = (trim($this->input->post('user_id')));
$status = (trim($this->input->post('status')));
if($user_id == "" || $status == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    }
else
    {
    $this->db->select('*'); 
    $this->db->from(' users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    if ($query->num_rows() == 0) 
        {
        $data['status'] = "false"; 
        $data['message'] = 'Please Enter Correct UserDeatils';
        
        }
    else
        {
        $filter['user_id'] = $user_id;
        $set['location_on'] = $status;
        $status=$this->webservice_general_model->update('users', $filter, $set);
        if($status)
            {
            $data['status'] = "true"; 
            $data['message'] = 'Success';
            }
        else
            {
            $data['status'] = "false"; 
            $data['message'] = 'Something Went Wrong';
            }
        }
    }
echo json_encode($data);
}

public function nearByUsers()
{

header('Content-Type: application/json');
$user_id= (trim($this->input->post('user_id')));
$lat= (trim($this->input->post('lat')));
$long= (trim($this->input->post('long')));
$radius ="10";
if($user_id == ""|| $lat =="" || $long == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "please entered all the required field";
    }
else
    {
    $this->db->select('*'); 
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    if($query->num_rows()==1)
        {
            header('Access-Control-Allow-Origin: *');
            $location = $this->Webservice_model->getnearbyData($radius,$lat,$long,$user_id);
            if ($location) 
                {

                $data['status'] = "true";
                $data['message'] = "success";
                $data['data'] = $location;
                }
            else
                {
                $data['status'] = "false";                    
                $data['message'] = "No record found";
                } 
        }
    else

        {

        $data['status'] = "false";                    

        $data['message'] = "please enter valid user id";

        }

    }

echo json_encode($data);

}

public function updateUserLocation()
{
header('Content-Type: application/json');
$user_id= (trim($this->input->post('user_id')));
$lat= (trim($this->input->post('lat')));
$long= (trim($this->input->post('long')));
if($user_id == ""|| $lat =="" || $long == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "please entered all the required field";
    }
else
    {
    $this->db->select('*'); 
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    if($query->num_rows()==1)
        {
            $filter2['user_id'] = $user_id;
            $set2['lat'] = $lat;
            $set2['long'] = $long;
            $changestatus=$this->webservice_general_model->update('users', $filter2, $set2);
            if ($changestatus) 
                {

                $data['status'] = "true";
                $data['message'] = "success";
                }
            else
                {
                $data['status'] = "false";                    
                $data['message'] = "Something went wrong";
                } 
        }
    else

        {

        $data['status'] = "false";                    

        $data['message'] = "please enter valid user id";

        }

    }

echo json_encode($data);

}

public function onlineStatus()
{
header('Content-Type: application/json');
$user_id = (trim($this->input->post('user_id')));
$status = (trim($this->input->post('status')));
if($user_id == "" || $status == "")
    {
    $data['status'] = "false"; 
    $data['message'] = "Please entered all the required field";
    }
else
    {
    $this->db->select('user_id,password'); 
    $this->db->from(' users');
    $this->db->where('user_id',$user_id);
    $query = $this->db->get();
    if ($query->num_rows() == 0) 
        {
        $data['status'] = "false"; 
        $data['message'] = 'Please enter valid user details';
        
        }
    else
        {
        $filter['user_id'] = $user_id;
        $set['is_active '] = $status;
        $success=$this->webservice_general_model->update('users', $filter, $set);
        if($success)
            {
            $data['status'] = "true"; 
            $data['message'] = 'status change Successfully';
            }
        else
            {
            $data['status'] = "false"; 
            $data['message'] = 'Something Went Wrong';
            }
        }
    }
echo json_encode($data);
}

public function addToRecentList()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
if($user_id==""||$member_id=="")
    {
    $data["status"]="false";
    $data["Message"]="Please enter required details";
    }
else
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows()=="1")
        {
            $userrequestexist = $this->db->select("*")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("recent")->num_rows();
            $userrequestexist2 = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("recent")->row()->id;
            if($userrequestexist > 0)
            {
            $filter2['id'] = $userrequestexist2;
            $set2['timestamp'] = time();
            $changepasswordstatus=$this->webservice_general_model->update('recent', $filter2, $set2);
            $data['status']= "true";
            $data['message']="add to recent successfully";   
            }
            else
            {
            $insert_data=array
            (
            'user_id'=>$user_id,
            'member_id'=>$member_id,
            'timestamp' => time(),
            );
            
            $this->db->insert('recent',$insert_data);
            
            $insert_data1=array
            (
            'member_id'=>$user_id,
            'user_id'=>$member_id,
            'timestamp' => time(),
            );
            
            $this->db->insert('recent',$insert_data1);
            
            $data['status']= "true";
            $data['message']="add to recent successfully";   
            }
        }
    else
        {
        $data["status"]="false";
        $data["Message"]="Please enter valid user_id";
        }
    }
echo json_encode($data);   
}

public function removeFromRecentList()
{
header('Content-Type: application/json');
$user_id=(trim($this->input->post('user_id')));
$member_id=(trim($this->input->post('member_id')));
if($user_id==""||$member_id=="")
    {
    $data["status"]="false";
    $data["Message"]="Please enter required details";
    }
else
    {
    $this->db->select('*');
    $this->db->from('users');
    $this->db->where('user_id',$user_id);
    $query=$this->db->get();
    if($query->num_rows()=="1")
        {
            $idexist = $this->db->select("*")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("recent")->num_rows();
            $id = $this->db->select("id")->where(['user_id' => $user_id])->where(['member_id' => $member_id])->get("recent")->row()->id;
            if($idexist > 0)
            {
            $filter['id'] = $id;
            $success=$this->webservice_general_model->delete('recent',$filter);
            
            $data['status']= "true";
            $data['message']="Remove from recent successfully"; 
            }
            else
            {
            $data['status']= "false";
            $data['message']="something went wrong";  
            }
        }
    else
        {
        $data["status"]="false";
        $data["Message"]="Please enter valid user_id";
        }
    }
echo json_encode($data);   
}

// public function getAgoraToken()
// {
//     header('Content-Type: application/json');
//     $user_id=(trim($this->input->post('user_id')));
//     $member_id = (trim($this->input->post('member_id')));
//     $timestamp=time();
//     if($user_id =="" || $member_id == "")
//     {
//         $data['status']="false";
//         $data['message']= "Please enter required details";
//     }
//     else
//     {
//         $this->db->select('*');
//         $this->db->from('users');
//         $this->db->where('user_id',$user_id);
//         $query=$this->db->get();
//         if($query->num_rows() == "1")
//         {
//             $this->db->select('group_token,timestamp,channel_id');
//             $this->db->from('tokens');
//             $this->db->where('user_id',$user_id);
//             $this->db->where('member_id',$member_id);
//             $query=$this->db->get();
//             $tokens=$query->row();
//             if($tokens)
//             {
//                 $currenttimestamp=time();
//                 if($currenttimestamp < $tokens2->timestamp){
//                 $data['status'] = "true";
//                 $data['message'] = "success";
//                 $data["token"]=$tokens->group_token;
//                 }
//                 else
//                 {
//                 $data['status'] = "false";
//                 $data['message'] = "token expired";  
//                 }
//             }
//             else
//             {
//             $this->db->select('group_token,timestamp,channel_id');
//             $this->db->from('tokens');
//             $this->db->where('member_id',$user_id);
//             $this->db->where('user_id',$member_id);
//             $query=$this->db->get();
//             $tokens2=$query->row();
//             if($tokens2)
//             {
//                 $currenttimestamp=time();
//                 if($currenttimestamp < $tokens2->timestamp){
//                 $data['status'] = "true";
//                 $data['message'] = "success";
//                 $data["token"]=$tokens->group_token;
//                 }
//                 else
//                 {
//                 $data['status'] = "false";
//                 $data['message'] = "token expired";  
//                 }
//             }
//             else{
//                 $data['status'] = "false";
//                 $data['message'] = "No token available";  
//             }
//             }
//         }
//         else
//         {
//         $data['status']="false";
//         $data['message']="please enter valid user_id";
//         }
//     }
//     echo json_encode($data);
// }


public function getAgoraToken()
{ 
    date_default_timezone_set('Asia/Kolkata');
    $notification_date = Date('y/m/d');
    $notification_time = (date('H:i:s'));
    $member_id = (trim($this->input->post('member_id')));
    $caller_user_id = (trim($this->input->post('user_id')));
    $userDeatils=$this->db->select('full_name')->from('users')->where('user_id', $caller_user_id)->get()->row()->full_name;
    $user_image=$this->db->select('image')->from('users')->where('user_id', $caller_user_id)->get()->row()->image;

    if ($member_id == "" || $caller_user_id == "" )
    {
        $data['status'] = "false";
        $data['message'] = "Please entered user id or channel id";
    }
    else
    {
    $this->db->select('*'); 
    $this->db->from(' users');
    $this->db->where('user_id',$caller_user_id);
    $query = $this->db->get();
    if ($query->num_rows() == 0) 
    {
        $data['status'] = "false"; 
        $data['message'] = 'Please Enter Correct UserDeatils';
        
        }
    else
    {
        $currenttimestamp=time();
        $this->db->select('id,group_token,timestamp,channel_id');
        $this->db->from('tokens');
        $this->db->where('user_id',$caller_user_id);
        $this->db->where('member_id',$member_id);
        $query=$this->db->get();
        $tokens=$query->row();
        if($tokens)
        {
            $currenttimestamp=time();
            if($currenttimestamp < $tokens->timestamp)
            {
                $token=$tokens->group_token;
                $channelName=$tokens->channel_id;  
                $group =$tokens->id;
                }
            else
            {
            $randome = rand(100000,999999);
            $appID = "4cb4d5f093b643c8968f6c7d68481e9e";
            $appCertificate = "e9d817eef131484fbc017ce007757e3f";
            $channelName =  "1".time();
            $uid = 0;
            $role = RtcTokenBuilder::RoleAttendee;
            $expireTimeInSeconds = 86400;
            $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
            $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
            
            $filter1['id'] = $tokens->id;
            $set1['group_token'] = $token;
            $set1['member_id'] = $member_id;
            $set1['user_id'] = $caller_user_id;
            $set1['timestamp'] = $expireTimeInSeconds + time();
            $set1['channel_id'] = $channelName;
            $this->webservice_general_model->update('tokens', $filter1, $set1);
            $group =$tokens->id;
            }
        }
        else
        {
        $this->db->select('id,group_token,timestamp,channel_id');
        $this->db->from('tokens');
        $this->db->where('member_id',$caller_user_id);
        $this->db->where('user_id',$member_id);
        $query=$this->db->get();
        $tokens2=$query->row();
        if($tokens2)
            {
            if($currenttimestamp < $tokens2->timestamp)
                {
                $token=$tokens2->group_token;
                $channelName=$tokens2->channel_id;
                $group =$tokens2->id;
                }
            else
                {
                $randome = rand(100000,999999);
                $appID = "4cb4d5f093b643c8968f6c7d68481e9e";
                $appCertificate = "e9d817eef131484fbc017ce007757e3f";
                $channelName = "1".time();
                $uid = 0;
                $role = RtcTokenBuilder::RoleAttendee;
                $expireTimeInSeconds = 86400;
                $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
                $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
                $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
                
                $filter1['id'] = $tokens2->id;
                $set1['group_token'] = $token;
                $set1['member_id'] = $member_id;
                $set1['user_id'] = $caller_user_id;
                $set1['timestamp'] = $expireTimeInSeconds + time();
                $set1['channel_id'] = $channelName;
                $this->webservice_general_model->update('tokens', $filter1, $set1);
                $group =$tokens2->id;
                }
            }
        else
            {
            $randome = rand(100000,999999);
            $appID = "4cb4d5f093b643c8968f6c7d68481e9e";
            $appCertificate = "e9d817eef131484fbc017ce007757e3f";
            $channelName =  "1".time();
            $uid = 0;
            $role = RtcTokenBuilder::RoleAttendee;
            $expireTimeInSeconds = 86400;
            $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
            $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);
            
            $insert_data=array(
            'user_id'=>$caller_user_id,
            'member_id'=>$member_id,
            'group_token'=>$token,
            'channel_id'=>$channelName,
            'timestamp'=>$expireTimeInSeconds + time(),
            );
            $this->db->insert('tokens',$insert_data);
            $group =$this->db->insert_id();
            }
        }
        
        $fullname = $this->db->select("full_name")->where(['user_id' => $member_id])->get("users")->row()->full_name; 
        $this->db->select('device_token'); 
        $this->db->from('users');
        $this->db->where('user_id', $member_id);
        $query = $this->db->get();
        $resultm=$query->row()->device_token;
        if ($resultm) 
        {
            
            $fullname = $this->db->select("full_name")->where(['user_id' => $member_id])->get("users")->row()->full_name; 
            $this->db->select('device_token'); 
            $this->db->from('users');
            $this->db->where('user_id', $member_id);
            $this->db->where('is_active', 1);//by kunal to cheack weater user is online or not
            $query = $this->db->get();
            $resultas=$query->row()->device_token;
            if ($resultas) 
            {
                $data['status'] = "true";
                $data['message'] = "notification send Successfully";
                $data1['token'] = $token;
                $data1['group'] = strval($group);
                $data1['channel'] = $channelName;
                $data['data'] = $data1;
            }
            else
            {
                $fullname = $this->db->select("full_name")->where(['user_id' => $member_id])->get("users")->row()->full_name; 
                $this->db->select('device_token'); 
                $this->db->from('users');
                $this->db->where('user_id', $member_id);
                $query = $this->db->get();
                $resultase=$query->row()->device_token;
                if ($resultase) 
                {
                    $device_token = $resultase;
                    if(!defined( 'API_ACCESS_KEY'))
                    {
                        define( 'API_ACCESS_KEY', 'AAAAtkEI9SM:APA91bFDKld-MTiR1WmSs4PY5UUtm1j3K5SsKu7XIK6ZCyrPi9LUP9gvXFSVjqNLzbpDPRcOxg5GuB9mX0UPr10xQqmicGULxtXriLZ7cUJKlWHhQvTKB6fzXR3AhmutlhkiidEPBFJO' );
                    }
                    #prep the bundle
                    $msg = array
                    (
                    'body' => $userDeatils.' '."want's to talk you",
                    'title' => "Walkie-talkie",
                    'icon' => 'myicon',/*Default Icon*/
                    // 'channel_id' => $channelName,
                    // 'token' => $token,
                    // 'calling_type' => "audio",
                    // 'user_id' => $user_id,
                    // 'fullname' => $fullname,
                    // 'callerName' => $userfullname,
                    // 'click_action' =>  'FLUTTER_NOTIFICATION_CLICK',
                    // 'image' =>$user_image,
                    );
                    // print_r($msg);
                    $fields = array
                    (
                    'to' => $device_token,
                    'notification' => $msg,
                    'data' => $msg,
                    'priority' => 'high'
                    );
                    $headers = array
                    (
                    'Authorization: key=' . API_ACCESS_KEY,
                    'Content-Type: application/json'
                    );
                    #Send Reponse To FireBase Server
                    $ch = curl_init();
                    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                    curl_setopt( $ch,CURLOPT_POST, true );
                    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                    $result = curl_exec($ch);            
                    curl_close( $result);
                    
                    // $data['status'] = "true";
                    // $data['message'] = "notification send Successfully";
                    
                    
                    $data['status'] = "false";
                    $data['message'] = "User is offline, Notification sent successfully";
                }
                else
                {
                    $data['status'] = "false";
                    $data['message'] = "Something Went Wrong";
                }
                
                
                
            }
        }
        else
        {
            $data['status'] = "false";
            $data['message'] = "this user has no device token";
        }
    #API access key from Google API's Console
    
    // return true;
    }
}
    echo json_encode($data);
}  


 protected function sendSMS($data) {
          // Your Account SID and Auth Token from twilio.com/console
            $sid = 'AC62d7b0afaa5b1f66e2fa964b5fc9d969';
            $token = '6bce8e62568a56c4b47c979c3355809f';
            $client = new Client($sid, $token);
 
            // Use the client to do fun stuff like send text messages!
             return $client->messages->create(
                // the number you'd like to send the message to
                $data['phone'],
                array(
                    // A Twilio phone number you purchased at twilio.com/console
                    "from" => "+14094986023",
                    // the body of the text message you'd like to send
                    'body' => $data['otp']
                )
            );
    }

public function PrivacyPolicy()
{
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$this->load->view('PrivacyPolicy');
}

}?>