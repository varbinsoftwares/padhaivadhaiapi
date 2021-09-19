<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();


        $this->load->library('session');
        $this->checklogin = $this->session->userdata('logged_in');
        $this->user_id = $this->session->userdata('logged_in')['login_id'];
    }

    public function index() {
        $this->load->view('welcome_message');
    }

    private function useCurl($url, $headers, $fields = null) {
        // Open connection
        $ch = curl_init();
        if ($url) {
            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }

            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }

            // Close connection
            curl_close($ch);

            return $result;
        }
    }

    public function android($data, $reg_id_array) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $insertArray = array(
            'title' => $data['title'],
            'message' => $data['message'],
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->insert("notification", $insertArray);

        $message = array(
            'title' => $data['title'],
            'message' => $data['message'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . $this->API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => $reg_id_array,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

    public function androidAdmin($data, $reg_id_array) {
        $url = 'https://fcm.googleapis.com/fcm/send';

        $insertArray = array(
            'title' => $data['title'],
            'message' => $data['message'],
            "datetime" => date("Y-m-d H:i:s a")
        );
        $this->db->insert("notification", $insertArray);

        $message = array(
            'title' => $data['title'],
            'message' => $data['message'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1
        );

        $headers = array(
            'Authorization: key=' . "AIzaSyBlRI5PaIZ6FJPwOdy0-hc8bTiLF5Lm0FQ",
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => $reg_id_array,
            'data' => $message,
        );

        return $this->useCurl($url, $headers, json_encode($fields));
    }

    public function iOS($data, $devicetoken) {
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        // ck.pem is your certificate file
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
        // Open a connection to the APNS server
        $fp = stream_socket_client(
                'ssl://gateway.sandbox.push.apple.com:2195', $err,
                $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        // Create the payload body
        $body['aps'] = array(
            'alert' => array(
                'title' => $data['mtitle'],
                'body' => $data['mdesc'],
            ),
            'sound' => 'default'
        );
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        // Close the connection to the server
        fclose($fp);
        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
    }

    function registration_post() {
        $postdata = $this->post();
        $email = $postdata["username"];
        $mobile_no = $postdata["mobile_no"];
        $this->db->where("email", $email);
        $this->db->or_where("mobile_no", $mobile_no);
        $query = $this->db->get('app_users');
        $userdata = $query->row_array();
        if ($userdata) {
            $this->response(array("status" => "401", "message" => "Email or mobile no. already registered"));
        } else {
            $this->db->insert("app_users", $postdata);
            $insert_id = $this->db->insert_id();
            $postdata["id"] = $insert_id;
            if ($insert_id) {
                $this->response(array("status" => "100", "userdata" => $postdata, "message" => "Your account has been created."));
            } else {
                $this->response(array("status" => "402", "message" => "Unable to create account please try again"));
            }
        }
    }

    function login_post() {
        $postdata = $this->post();
        $username = $postdata["username"];
        $password = $postdata["password"];
        $this->db->where("password", $password);
        $this->db->where("email", $username);
        $this->db->or_where("mobile_no", $username);
        $query = $this->db->get('app_users');
        $userdata = $query->row_array();
        if ($userdata) {
            if ($userdata["password"] == $password) {
                $this->response(array("status" => "100", "userdata" => $userdata, "message" => "You have logged in successfully"));
            } else {
                $this->response(array("status" => "401", "message" => "You have entered incorrect Password"));
            }
        } else {
            $this->response(array("status" => "401", "message" => "Email or mobile no. not registered"));
        }
    }

    function askQuery_post() {
        $postdata = $this->post();
        $postdata["qry_date"] = date("Y-m-d");
        $postdata["qry_time"] = date("h:i:s a");
        $this->db->insert("padai_ask_query", $postdata);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->response(array("status" => "100", "last_id" => $insert_id, "message" => "Your query has been submitted"));
        } else {
            $this->response(array("status" => "402", "message" => "Unable to submit query"));
        }
    }

    function askQueryList_get($user_id) {
        $this->db->where("user_id", $user_id);
        $this->db->order_by("id desc");
        $query = $this->db->get("padai_ask_query");
        $querylist = $query->result_array();
        if ($querylist) {
            $this->response(array("status" => "100", "query_list" => $querylist));
        } else {
            $this->response(array("status" => "402", "query_list" => $querylist, "message" => "No past quesries here, Start asking..."));
        }
    }

    function queryChat_get($channel_id, $user_id) {
        $sql = "select 
                   au2.username as username2, au2.name as name2, au2.email as email2, au2.profile_image as profile_image2,
                   au.username, au.name, au.email, au.profile_image, ch.message_body, ch.message_file, ch.m_date,
                              ch.m_time, ch.sender_id, ch.receiver_id from channel_message_personal as ch 
                              left join app_users as au on au.id = ch.sender_id
                              left join app_users as au2 on au2.id = ch.receiver_id
                              where ((ch.receiver_id = '$user_id' and ch.sender_id = '1') or (ch.receiver_id = '1' and ch.sender_id = '$user_id')) and channel_id = '$channel_id' order by ch.id";
        $query = $this->db->query($sql);
        $messagedata = $query->result_array();
        $this->response($messagedata);
    }

    function queryChat2_get($channel_id, $user_id) {
        $this->db->select("m_date, m_time, sender_id, receiver_id, message_body, '-' as image");
        $this->db->where("channel_id", $channel_id);
//        $this->db->where("sender_id", $user_id)->or_where("receiver_id", $user_id);
//        $this->db->order_by("id desc");
        $query = $this->db->get("channel_message_personal");
        $messagedataall = $query->result_array();

        $messagedata = [];
        $this->db->select("qry_date as m_date, qry_time as m_time, user_id as sender_id, '1' as receiver_id, topic, description, upload_file");
        $this->db->where("id", $channel_id);
        $query = $this->db->get("padai_ask_query");
        $querydata = $query->row_array();

        $message1 = $querydata;
        $message1["message_body"] = "Topic:\n ". $querydata["topic"]. " ..";
         $message1["image"]  = "-";
         array_push($messagedata, $message1);
         
        $message2 = $querydata;
        $message2["message_body"] = $querydata["description"];
        $message2["image"] = "https://app.padhaivadhai.com/padhaiVadhaiApp/original/" .$querydata["upload_file"];
        array_push($messagedata, $message2);
        
        foreach ($messagedataall as $key => $value) {
             array_push($messagedata, $value);
        }
        
        $this->response($messagedata);
    }

    function fileupload_post() {

        $ext1 = explode('.', $_FILES['file']['name']);
        $ext = strtolower(end($ext1));
        $filename = $type . rand(1000, 10000);

        $actfilname = $_FILES['file']['name'];

        move_uploaded_file($_FILES["file"]['tmp_name'], 'assets/profile_image/' . $actfilname);


        $this->response(array("status" => "200"));
    }

}

?>