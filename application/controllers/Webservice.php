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
            //$this->load->library('RtcTokenBuilder');
            //require_once(APPPATH.'libraries/twilio-php-master/Twilio/autoload.php');

        }

        public function login()
        {
            header('Content-type:application/json');
            $number=trim($this->input->post('phone'));
            $is_date = trim($this->input->post('is_date'));
            // $code=trim($this->input->post('country_code'));
	

            if ($number == "" || $is_date == "") {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                if($is_date == '0'){
                    $userexist=$this->Webservice_model->check_phone($number);
                    if ($userexist) {
                        $randome = rand(1000,9999);	
                        $this->db->query("update user SET otp='$randome'where phone='".$number."'");
                        $details = array(
                            'phone'=> $number,
                            'otp'=> (string)$randome,
                        );
                        $data['status']='true';
                        $data['message']='OTP send to Your Mobile Number';
                        $data['details']=$details;
                    }
                    else {
                        $randome = rand(1000,9999);
                        // $mobile = $number;
                        $details = array(
                            'phone'=> $number,
                            'otp' => (string)$randome
                        );
                        $this->db->insert('user',$details);
                        $data['status']='true';
                        $data['message']='OTP send to Your Mobile Number';
                        $data['details']=$details;
                    }
                }
                else {
                    $userexist=$this->Webservice_model->checkphone($number);
                    if ($userexist) {
                        $randome = rand(1000,9999);	
                        $this->db->query("update partner SET otp='$randome'where phone='".$number."'");
                        $details = array(
                            'phone'=> $number,
                            'otp'=> (string)$randome,
                        );
                        $data['status']='true';
                        $data['message']='OTP send to Your Mobile Number';
                        $data['details']=$details;
                    }
                    else {
                        $randome = rand(1000,9999);
                        // $mobile = $number;
                        $details = array(
                            'phone'=> $number,
                            'otp' => (string)$randome
                        );
                        $this->db->insert('partner',$details);
                        $data['status']='true';
                        $data['message']='OTP send to Your Mobile Number';
                        $data['details']=$details;
                    }
                }
            }
            echo json_encode($data);
        }

        public function verifyOtp(){
            {
                header('Content-Type: application/json');
                $otp = (trim($this->input->post('otp')));
                $mobile = (trim($this->input->post('phone')));
                $is_date = trim($this->input->post('is_date'));

                if ($otp == "" || $mobile == "") 
                {
                    $data['status'] = "false"; 
                    $data['message'] = "Please entered all the required field";
                } 
                else 
                {
                    if($is_date == '0'){
                        $filter['phone'] = $mobile;
                        if ($getResult = $this->webservice_general_model->getData('user',$filter)) 
                        {
                            $filter['phone'] = $mobile;
                            $filter['otp'] = $otp;
                            if ($getResult = $this->webservice_general_model->getData('user',$filter)) 
                            {
                                $id = $this->db->select('user_id')->from('user')->where(['phone' => $mobile])->get()->row()->user_id;
                                $data['status'] = "true";
                                $data['message'] = "otp verified for date";
                                $data['userId'] = $id;
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
                    else {
                        $filter['phone'] = $mobile;
                        if ($getResult = $this->webservice_general_model->getData('partner',$filter)) 
                        {
                            $filter['phone'] = $mobile;
                            $filter['otp'] = $otp;
                            if ($getResult = $this->webservice_general_model->getData('partner',$filter)) 
                            {
                                $id = $this->db->select('user_id')->from('partner')->where(['phone' => $mobile])->get()->row()->user_id;
                                $data['status'] = "true";
                                $data['message'] = "otp verified for partner";
                                $data['userId'] = $id;
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
                }
        
                echo json_encode($data);
                }
        }

        public function signup(){
            header('Content-Type: application/json');
            date_default_timezone_set('Asia/Kolkata');
            $date = Date('y/m/d');
            $time = (date('H:i:s'));
            $firstname = (trim($this->input->post('firstname')));
            $lastname=(trim($this->input->post('lastname')));
            $dob=(trim($this->input->post('dob')));
            // $firebaseId = (trim($this->input->post('firebaseId')));
            // $device_token = (trim($this->input->post('device_token')));
            $id = (trim($this->input->post('user_id')));
            $is_date = trim($this->input->post('is_date'));
            if($firstname==""||$lastname==""||$id==""){
                $data['status']="false";
                $data['message']="please entered all the required field";
            }else
            {
                if ($is_date == '0') {
                    if ($_FILES) {
                        $image_name1 = "";
                        $image_name_thumb1 = "";
                        // Upload profile picture
                        $random = time();
                        $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . "/dating/uploads/";
                        $config['allowed_types'] = '*';
                        $config['file_name'] = $random;
                        $config['encrypt_name'] = TRUE;
                        $this->load->library('image_lib');
                        $this->image_lib->clear();
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        ini_set('upload_max_filesize', '64M');
                        ini_set('memory_limit', '-1');
                        if ($this->upload->do_upload('image')) {
                            $imageArray = $this->upload->data();
                            $image_name1 = $imageArray['raw_name'] . '' . $imageArray['file_ext']; // Job Attachment
                            $config1['image_library'] = 'gd2';
                            $config1['source_image'] = $_SERVER['DOCUMENT_ROOT'] . "/dating/uploads/" . $image_name1;
                            $config1['create_thumb'] = TRUE;
                            $config1['maintain_ratio'] = TRUE;
                            $config1['width']     = 300;
                            $config1['height']   = 377;
                            $this->load->library('image_lib', $config);
                            $this->image_lib->initialize($config1);
                            $this->image_lib->resize();
                            $this->image_lib->clear();
                            $image_name = base_url().'uploads/' .$imageArray['raw_name'] . $imageArray['file_ext'];
    
    
                            $set2['firstname']=$firstname;
                            $set2['lastname']=$lastname;
                            $set2['dob']=$dob;
                            $set2['time']=$time;
                            $set2['date']=$date;
                            // $set2['firebaseId']=$firebaseId;
                            // $set2['device_token']=$device_token;
                            $set2['image']=$image_name;
                            $filter2['user_id']=$id;
    
                            $this->webservice_general_model->update('user',$filter2,$set2);
                            $data['status']='true';
                            $data['message']='Addedd';
                            $data['data']=$set2;
                        }
                        else {
                            $set2['firstname']=$firstname;
                            $set2['lastname']=$lastname;
                            $set2['dob']=$dob;
                            $set2['time']=$time;
                            $set2['date']=$date;
                            // $set2['firebaseId']=$firebaseId;
                            // $set2['device_token']=$device_token;
                            // $set2['image']=$image_name;
                            $filter2['user_id']=$id;
    
                            $this->webservice_general_model->update('user',$filter2,$set2);
                            $data['status']='true';
                            $data['message']='Addedd';
                            $data['data']=$set2;
                        }
                    }
                    else {
                        $set2['firstname']=$firstname;
                            $set2['lastname']=$lastname;
                            $set2['dob']=$dob;
                            $set2['time']=$time;
                            $set2['date']=$date;
                            // $set2['firebaseId']=$firebaseId;
                            // $set2['device_token']=$device_token;
                            // $set2['image']=$image_name;
                            $filter2['user_id']=$id;
    
                            $this->webservice_general_model->update('user',$filter2,$set2);
                            $data['status']='true';
                            $data['message']='Addedd';
                            $data['data']=$set2;
                    }
                }
                    else {
                        if ($_FILES) {
                            $image_name1 = "";
                            $image_name_thumb1 = "";
                            // Upload profile picture
                            $random = time();
                            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . "/dating/uploads/";
                            $config['allowed_types'] = '*';
                            $config['file_name'] = $random;
                            $config['encrypt_name'] = TRUE;
                            $this->load->library('image_lib');
                            $this->image_lib->clear();
                            $this->load->library('upload', $config);
                            $this->upload->initialize($config);
                            ini_set('upload_max_filesize', '64M');
                            ini_set('memory_limit', '-1');
                            if ($this->upload->do_upload('image')) {
                                $imageArray = $this->upload->data();
                                $image_name1 = $imageArray['raw_name'] . '' . $imageArray['file_ext']; // Job Attachment
                                $config1['image_library'] = 'gd2';
                                $config1['source_image'] = $_SERVER['DOCUMENT_ROOT'] . "/dating/uploads/" . $image_name1;
                                $config1['create_thumb'] = TRUE;
                                $config1['maintain_ratio'] = TRUE;
                                $config1['width']     = 300;
                                $config1['height']   = 377;
                                $this->load->library('image_lib', $config);
                                $this->image_lib->initialize($config1);
                                $this->image_lib->resize();
                                $this->image_lib->clear();
                                $image_name = base_url().'uploads/' .$imageArray['raw_name'] . $imageArray['file_ext'];
        
        
                                $set2['firstname']=$firstname;
                                $set2['lastname']=$lastname;
                                $set2['dob']=$dob;
                                $set2['time']=$time;
                                $set2['date']=$date;
                                // $set2['firebaseId']=$firebaseId;
                                // $set2['device_token']=$device_token;
                                $set2['image']=$image_name;
                                $filter2['user_id']=$id;
        
                                $this->webservice_general_model->update('partner',$filter2,$set2);
                                $data['status']='true';
                                $data['message']='Addedd';
                                $data['data']=$set2;
                            }
                            else {
                                $set2['firstname']=$firstname;
                                $set2['lastname']=$lastname;
                                $set2['dob']=$dob;
                                $set2['time']=$time;
                                $set2['date']=$date;
                                // $set2['firebaseId']=$firebaseId;
                                // $set2['device_token']=$device_token;
                                // $set2['image']=$image_name;
                                $filter2['user_id']=$id;
        
                                $this->webservice_general_model->update('partner',$filter2,$set2);
                                $data['status']='true';
                                $data['message']='Addedd';
                                $data['data']=$set2;
                            }
                        }
                        else {
                            $set2['firstname']=$firstname;
                                $set2['lastname']=$lastname;
                                $set2['dob']=$dob;
                                $set2['time']=$time;
                                $set2['date']=$date;
                                // $set2['firebaseId']=$firebaseId;
                                // $set2['device_token']=$device_token;
                                // $set2['image']=$image_name;
                                $filter2['user_id']=$id;
        
                                $this->webservice_general_model->update('partner',$filter2,$set2);
                                $data['status']='true';
                                $data['message']='Addedd';
                                $data['data']=$set2;
                        }
                    }
                }            
                echo json_encode($data);
        }
    
        public function gender()
        {
            header('Content-type:application/json');
            $location=trim($this->input->post('location'));
            $gender=trim($this->input->post('gender'));
            $id = (trim($this->input->post('user_id')));
            if ($location == "" | $gender == ""| $id == "" ) {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                $set2['location']=$location;
                $set2['gender']=$gender;
                $set2['is_verify']='1';
                $filter2['user_id']=$id;
                $this->webservice_general_model->update('user',$filter2,$set2);
                $data['status']='true';    
                $data['message']='Data Added Successfully';

            }
            echo json_encode($data);
        }
	 
        public function getProfile(){
            header('Content-Type: application/json');
            $user_id=(trim($this->input->post('user_id')));
            $is_date = trim($this->input->post('is_date'));

            if($user_id==""){
                $data['status'] = "false"; 
                $data['message'] = "Please entered all the required field";
            }else{
                if ($is_date == '0') {
                    $this->db->select('user_id,firstname,lastname,gender,dob,image'); 
                    $this->db->from('user');
                    $this->db->where('user_id',$user_id);
                    $query = $this->db->get();
                    $result=$query->row();

                    if ($result){
                        $data['status'] = "true";
                        $data['message'] = "success";
                        $data['data'] = $result;
                    }else{
                        $data['status'] = "false";
                        $data['message'] = "User not exist";
                    }
                }
                else {
                    $this->db->select('user_id,firstname,lastname,gender,dob,image'); 
                    $this->db->from('partner');
                    $this->db->where('user_id',$user_id);
                    $query = $this->db->get();
                    $result=$query->row();

                    if ($result){
                        $data['status'] = "true";
                        $data['message'] = "success";
                        $data['data'] = $result;
                    }else{
                        $data['status'] = "false";
                        $data['message'] = "User not exist";
                    }
                }
                
            }
            echo json_encode($data);
        }

        public function isFor()
        {
            header('Content-type:application/json');
            $isfor=trim($this->input->post('isfor'));
            $gender=trim($this->input->post('gender'));
            $id = (trim($this->input->post('user_id')));
            if ($isfor == "" | $gender == ""| $id == "" ) {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                $set2['isfor']=$isfor;
                $set2['gender']=$gender;
                $filter2['user_id']=$id;
                $this->webservice_general_model->update('partner',$filter2,$set2);
                $data['status']='true';    
                $data['message']='Data Added Successfully';

            }
            echo json_encode($data);
        }

        public function religion()
        {
            header('Content-type:application/json');
            $religion=trim($this->input->post('religion'));
            $community=trim($this->input->post('community'));
            $familytype=trim($this->input->post('familytype'));
            $id = (trim($this->input->post('user_id')));
            if ($religion == "" | $community == ""| $id == "" | $familytype == "") {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                $set2['religion']=$religion;
                $set2['community']=$community;
                $set2['familytype']=$familytype;
                $filter2['user_id']=$id;
                $this->webservice_general_model->update('partner',$filter2,$set2);
                $data['status']='true';    
                $data['message']='Data Added Successfully';

            }
            echo json_encode($data);
        }

        public function profile()
        {
            header('Content-type:application/json');
            $state=trim($this->input->post('state'));
            $city=trim($this->input->post('city'));
            $marritalstatus=trim($this->input->post('marritalstatus'));
            $diet=trim($this->input->post('diet'));
            $height=trim($this->input->post('height'));
            $subcommunity=trim($this->input->post('subcommunity'));
            $qualification=trim($this->input->post('qualification'));
            $job=trim($this->input->post('job'));
            $income=trim($this->input->post('income'));
            $drink=trim($this->input->post('drink'));
            $smoke=trim($this->input->post('smoke'));
            $id = (trim($this->input->post('user_id')));

            if ($state == "" | $city == ""| $marritalstatus == "" | $diet == "" | $height == "" |$subcommunity == "" |$qualification == "" |$job == "" |$income == "" | $drink == "" | $smoke == ""  ) {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                $set2['state']=$state;
                $set2['city']=$city;
                $set2['marritalstatus']=$marritalstatus;
                $set2['diet']=$diet;
                $set2['height']=$height;
                $set2['subcommunity']=$subcommunity;
                $set2['qualification']=$qualification;
                $set2['job']=$job;
                $set2['income']=$income;
                $set2['drink']=$drink;
                $set2['smoke']=$smoke;
                $filter2['user_id']=$id;
                $this->webservice_general_model->update('partner',$filter2,$set2);
                $data['status']='true';    
                $data['message']='Data Added Successfully';

            }
            echo json_encode($data);
        }

        public function parentsDetails()
        {
            header('Content-type:application/json');
            $fathername=trim($this->input->post('fathername'));
            $mothername=trim($this->input->post('mothername'));
            $fatherphone=trim($this->input->post('fatherphone'));
            $fatheroccupation=trim($this->input->post('fatheroccupation'));
            $motheroccupation=trim($this->input->post('motheroccupation'));
            $isverify = "1";
           
            $id = (trim($this->input->post('user_id')));

            if ($fathername == "" | $mothername == ""| $fatherphone == "" | $fatheroccupation == "" | $motheroccupation == "" | $id == ""  ) {
                $data['status']='false';    
                $data['message']='Please enter all the requried details';
            }
            else {
                $set2['fathername']=$fathername;
                $set2['mothername']=$mothername;
                $set2['fatherphone']=$fatherphone;
                $set2['fatheroccupation']=$fatheroccupation;
                $set2['motheroccupation']=$motheroccupation;
                $set2['is_verify']=$isverify;
                
                $filter2['user_id']=$id;
                $this->webservice_general_model->update('partner',$filter2,$set2);
                $data['status']='true';    
                $data['message']='Data Added Successfully';

            }
            echo json_encode($data);
        }

        public function editPartnerProfile()
        {
            header('Content-Type: application/json');
            $user_id = trim($this->input->post('user_id'));

            $firstname = trim($this->input->post('firstname'));
            $lastname=trim($this->input->post('lastname'));
            $phone = trim($this->input->post('phone'));
            $familytype = trim($this->input->post('familytype'));
            $qualification = trim($this->input->post('qualification'));
            $job = trim($this->input->post('job'));
            $income = trim($this->input->post('income'));
            $isfor = trim($this->input->post('isfor'));
            if($user_id == "" ){
                $data['status'] = "false";
                $data['message'] = "Pls Enter all required field";
            }
            else{
                $filter['user_id'] = $user_id;
                if($this->webservice_general_model->getData('user',$filter)){
                    if($_FILES){
                        $image_name1 = "";
                        $image_name_thumb1 = "";
                        // Upload profile picture
                        $random = time();
                        $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/";
                        $config['allowed_types'] = '*';
                        $config['file_name'] = $random ;
                        $config['encrypt_name'] = TRUE;
                        $this->load->library('image_lib');
                        $this->image_lib->clear();
                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        ini_set('upload_max_filesize', '64M');
                        ini_set('memory_limit', '-1');
                        if($this->upload->do_upload('image')){
                            $imageArray = $this->upload->data();         
                            $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                            $config1['image_library'] = 'gd2';
                            $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/".$image_name1;
                            $config1['create_thumb'] = TRUE;
                            $config1['maintain_ratio'] = TRUE;
                            $config1['width']     = 300;
                            $config1['height']   = 377;
                            $this->load->library('image_lib', $config);
                            $this->image_lib->initialize($config1);
                            $this->image_lib->resize();
                            $this->image_lib->clear();
                            $image_name =base_url(). 'uploads/' .$imageArray['raw_name'] . $imageArray['file_ext'];;
                            
                            $filter1['user_id'] = $user_id;
                            $set1['image'] = base_url()."/uploads/".$image_name;
                            $this->webservice_general_model->update('user', $filter1, $set1);
                            
                            $filteruserInfo['user_id'] = $user_id;
                            $setUserDetail['firstname'] = $firstname;
                            $setUserDetail['lastname']=$lastname;
                            $setUserDetail['phone'] = $phone;
                            $setUserDetail['familytype'] = $familytype;
                            $setUserDetail['qualification'] = $qualification;
                            $setUserDetail['job'] = $job;
                            $setUserDetail['income'] = $income;
                            $setUserDetail['isfor'] = $isfor;
                            $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);

                            $user = $this->db->select("user_id,firstname,lastname,phone,familytype,qualification,job,income,isfor")->where(['user_id' => $user_id])->get("partner")->row(); 
                            $data['status']="true";
                            $data['message'] = "Profile has been updated";
                            $data['data']=$user;
                        }
                        else {
                            $filteruserInfo['user_id'] = $user_id;
                            $setUserDetail['firstname'] = $firstname;
                            $setUserDetail['lastname']=$lastname;
                            $setUserDetail['phone'] = $phone;
                            $setUserDetail['familytype'] = $familytype;
                            $setUserDetail['qualification'] = $qualification;
                            $setUserDetail['job'] = $job;
                            $setUserDetail['income'] = $income;
                            $setUserDetail['isfor'] = $isfor;
                            $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);
                        }     
                    }
                    else{
                        $filteruserInfo['user_id'] = $user_id;
                        $setUserDetail['firstname'] = $firstname;
                        $setUserDetail['lastname']=$lastname;
                        $setUserDetail['phone'] = $phone;
                        $setUserDetail['familytype'] = $familytype;
                        $setUserDetail['qualification'] = $qualification;
                        $setUserDetail['job'] = $job;
                        $setUserDetail['income'] = $income;
                        $setUserDetail['isfor'] = $isfor;
                        $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);

                        $data['status'] = "true";
                        $data['message'] = 'profile Updated Successfully';
                    }
                }
                else{
                    $data['status'] = "false";
                    $data['message'] = 'user id Not Found';
                }
            }
        }

        public function editProfile(){
            header('Content-Type: application/json');
            $user_id = trim($this->input->post('user_id'));
            $gender = trim($this->input->post('gender'));
            $dob = trim($this->input->post('dob'));
            $firstname = trim($this->input->post('firstname'));
            $lastname=trim($this->input->post('lastname'));
            $is_date = trim($this->input->post('is_date'));
            if($user_id==""){
                $data['status'] = "false";
                $data['message'] = "Pls Enter  all required field";
            }else{
                if ($is_date == '0') {
                    $filter['user_id'] = $user_id;
                    if($this->webservice_general_model->getData('user',$filter)){
                        if($_FILES){
                            $image_name1 = "";
                            $image_name_thumb1 = "";
                            // Upload profile picture
                            $random = time();
                            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/";
                            $config['allowed_types'] = '*';
                            $config['file_name'] = $random ;
                            $config['encrypt_name'] = TRUE;
                            $this->load->library('image_lib');
                            $this->image_lib->clear();
                            $this->load->library('upload', $config);
                            $this->upload->initialize($config);
                            ini_set('upload_max_filesize', '64M');
                            ini_set('memory_limit', '-1');
                            if($this->upload->do_upload('image')){
                                $imageArray = $this->upload->data();         
                                $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                                $config1['image_library'] = 'gd2';
                                $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/".$image_name1;
                                $config1['create_thumb'] = TRUE;
                                $config1['maintain_ratio'] = TRUE;
                                $config1['width']     = 300;
                                $config1['height']   = 377;
                                $this->load->library('image_lib', $config);
                                $this->image_lib->initialize($config1);
                                $this->image_lib->resize();
                                $this->image_lib->clear();
                                $image_name =base_url(). 'uploads/' .$imageArray['raw_name'] . $imageArray['file_ext'];;
                                $filter1['user_id'] = $user_id;
                                $set1['image'] = base_url()."/uploads/".$image_name;
                                $this->webservice_general_model->update('user', $filter1, $set1);
                                $filteruserInfo['user_id'] = $user_id;
                                $setUserDetail['gender'] = $gender;
                                $setUserDetail['dob'] = $dob;
                                $setUserDetail['firstname'] = $firstname;
                                $setUserDetail['lastname']=$lastname;
                                $this->webservice_general_model->update('user', $filteruserInfo, $setUserDetail);
                                $user = $this->db->select("user_id,firstname,lastname,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("user")->row(); 
                                $data['status']="true";
                                $data['message'] = "Profile has been updated";
                                $data['data']=$user;
                            }
                            else {
                                $filteruserInfo['user_id'] = $user_id;
                                $setUserDetail['gender'] = $gender;
                                $setUserDetail['dob'] = $dob;
                                $setUserDetail['firstname'] = $firstname;
                                $setUserDetail['lastname']=$lastname;
                                $this->webservice_general_model->update('user', $filteruserInfo, $setUserDetail);
                            }     
                        }
                        else{
                            $filteruserInfo['user_id'] = $user_id;
                            $setUserDetail['gender'] = $gender;
                            $setUserDetail['dob'] = $dob;
                            $setUserDetail['firstname'] = $firstname;
                            $setUserDetail['lastname']=$lastname;
                            $this->webservice_general_model->update('user', $filteruserInfo, $setUserDetail);
                            $data['status'] = "true";
                            $data['message'] = 'profile Updated Successfully';

                        }
                    }
                    else{
                        $data['status'] = "false";
                        $data['message'] = 'user id Not Found';
                    }
                }
                else {
                    $filter['user_id'] = $user_id;
                    if($this->webservice_general_model->getData('partner',$filter)){
                        if($_FILES){
                            $image_name1 = "";
                            $image_name_thumb1 = "";
                            // Upload profile picture
                            $random = time();
                            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/";
                            $config['allowed_types'] = '*';
                            $config['file_name'] = $random ;
                            $config['encrypt_name'] = TRUE;
                            $this->load->library('image_lib');
                            $this->image_lib->clear();
                            $this->load->library('upload', $config);
                            $this->upload->initialize($config);
                            ini_set('upload_max_filesize', '64M');
                            ini_set('memory_limit', '-1');
                            if($this->upload->do_upload('image')){
                                $imageArray = $this->upload->data();         
                                $image_name1 = $imageArray['raw_name'].''.$imageArray['file_ext']; // Job Attachment
                                $config1['image_library'] = 'gd2';
                                $config1['source_image'] = $_SERVER['DOCUMENT_ROOT']."/dating/uploads/".$image_name1;
                                $config1['create_thumb'] = TRUE;
                                $config1['maintain_ratio'] = TRUE;
                                $config1['width']     = 300;
                                $config1['height']   = 377;
                                $this->load->library('image_lib', $config);
                                $this->image_lib->initialize($config1);
                                $this->image_lib->resize();
                                $this->image_lib->clear();
                                $image_name =base_url(). 'uploads/' .$imageArray['raw_name'] . $imageArray['file_ext'];;
                                $filter1['user_id'] = $user_id;
                                $set1['image'] = base_url()."/uploads/".$image_name;
                                $this->webservice_general_model->update('partner', $filter1, $set1);

                                
                                $filteruserInfo['user_id'] = $user_id;
                                $setUserDetail['firstname'] = $firstname;
                                $setUserDetail['lastname']=$lastname;
                                $setUserDetail['gender'] = $gender;
                                $setUserDetail['dob'] = $dob;
                                $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);
                                $user = $this->db->select("user_id,firstname,lastname,email,gender,dob,image,firebaseId,is_active")->where(['user_id' => $user_id])->get("partner")->row(); 
                                $data['status']="true";
                                $data['message'] = "Profile has been updated";
                                $data['data']=$user;
                            }
                            else {
                                $filteruserInfo['user_id'] = $user_id;
                                $setUserDetail['gender'] = $gender;
                                $setUserDetail['dob'] = $dob;
                                $setUserDetail['firstname'] = $firstname;
                                $setUserDetail['lastname']=$lastname;
                                $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);
                            }     
                        }
                        else{
                            $filteruserInfo['user_id'] = $user_id;
                            $setUserDetail['gender'] = $gender;
                            $setUserDetail['dob'] = $dob;
                            $setUserDetail['firstname'] = $firstname;
                            $setUserDetail['lastname']=$lastname;
                            $this->webservice_general_model->update('partner', $filteruserInfo, $setUserDetail);
                            $data['status'] = "true";
                            $data['message'] = 'profile Updated Successfully';

                        }
                    }
                    else{
                        $data['status'] = "false";
                        $data['message'] = 'user id Not Found';
                    }
                }
            }
            echo str_replace('\/', '/', json_encode($data));
        }

        public function like()
        {
            header('Content-type:application/json');
            $user_id = trim($this->input->post('user_id'));
            $result['data'] = $this->db->select('image,user_id')->from('partner')->get()->result_array();            
            print_r($result['data']);
        }


        

    }
?>
