<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH . 'libraries/REST_Controller.php');

class Api extends REST_Controller {

    public function __construct() {
        parent::__construct();

        $this->API_ACCESS_KEY = "AAAALMx665o:APA91bGHg4uYe_uPcaIecTzutOUXbE3P4GF5xBtLOWuAfYi6MpUUeB-WL4YKXVn-ziQJrpot8q0uGS7KARKqrw1Ts9CBJeIunvhP0iuebR7oTMHdI3BW_nNWyysDSgADDibqCsIXdAT-";
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



        $headers = array(
            'Authorization: key=' . $this->API_ACCESS_KEY,
            'Content-Type: application/json'
        );



        return $this->useCurl($url, $headers, json_encode($data));
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

    function getUnseenMessage($channel_id = 0, $user_id = 0) {
        $this->db->select("count(id) as unseen");
        if ($channel_id) {
            $this->db->where("channel_id", $channel_id);
        }
        if ($user_id) {
            $this->db->where("receiver_id", $user_id);
        }
        $this->db->where("user_seen!=", "1");
        $query = $this->db->get("channel_message_personal");
        $messagedataall = $query->row_array();
        if ($messagedataall) {
            return $messagedataall["unseen"];
        } else {
            return 0;
        }
    }

    function getAllNotification_get($user_id = 0) {
        $this->db->select("message_body, cmp.channel_id, user_seen, paq.topic");
        $this->db->from('channel_message_personal cmp');
        $this->db->join('padai_ask_query paq', 'paq.id=cmp.channel_id', 'left');
        $this->db->where("user_seen!=", "1");
        $this->db->where("user_id", $user_id);
        $this->db->order_by("cmp.id desc");
        $query = $this->db->get();
        $messagedataall = $query->result_array();
        $this->response(array("notification_list" => $messagedataall, "notification_count" => "". count($messagedataall)));
    }

    function askQueryList_get($user_id) {
        $this->db->where("user_id", $user_id);
        $this->db->order_by("id desc");
        $query = $this->db->get("padai_ask_query");
        $querylist = $query->result_array();
        $querylistfinal = [];
        foreach ($querylist as $key => $value) {
            $channel_id = $value["id"];
            $value["unseen"] = "" . $this->getUnseenMessage($channel_id);
            $value["image"] = "https://app.padhaivadhai.com/padhaiVadhaiApp/original/" . $value["upload_file"];
            array_push($querylistfinal, $value);
        }

        if ($querylist) {
            $this->response(array("status" => "100", "query_list" => $querylistfinal));
        } else {
            $this->response(array("status" => "402", "query_list" => $querylistfinal, "message" => "No past quesries here, Start asking..."));
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
        $query = $this->db->get("channel_message_personal");
        $messagedataall = $query->result_array();

        $messagedata = [];
        $this->db->select("qry_date as m_date, qry_time as m_time, user_id as sender_id, '1' as receiver_id, topic, description, upload_file");
        $this->db->where("id", $channel_id);
        $query = $this->db->get("padai_ask_query");
        $querydata = $query->row_array();

        $message1 = $querydata;
        $message1["message_body"] = "Topic:\n " . $querydata["topic"] . " ..";
        $message1["image"] = "-";
        array_push($messagedata, $message1);

        $message2 = $querydata;
        $message2["message_body"] = $querydata["description"];
        $message2["image"] = "https://app.padhaivadhai.com/padhaiVadhaiApp/original/" . $querydata["upload_file"];


        array_push($messagedata, $message2);

        foreach ($messagedataall as $key => $value) {
            array_push($messagedata, $value);
        }
        $finalchat = [];
        foreach ($messagedata as $key => $value) {
            $value["m_time"] = $newDate = date("H:m:s", strtotime($value["m_time"]));
            array_push($finalchat, $value);
        }

        $this->db->set(array("user_seen" => "1"));
        $this->db->where("channel_id", $channel_id);
        $query = $this->db->update("channel_message_personal");



        $this->response($finalchat);
    }

    function queryChatInsert_post() {
        $postdata = $this->post();
        $postdata["m_date"] = date("Y-m-d");
        $postdata["m_time"] = date("h:i:s a");
        $postdata["admin_seen"] = "0";

        $this->db->insert("channel_message_personal", $postdata);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->response(array("status" => "100", "last_id" => $insert_id, "message" => "Your query has been submitted"));
        } else {
            $this->response(array("status" => "402", "message" => "Unable to submit query"));
        }
    }

    function fileupload_post() {

        $ext1 = explode('.', $_FILES['file']['name']);
        $ext = strtolower(end($ext1));
        $filename = $type . rand(1000, 10000);

        $actfilname = $_FILES['file']['name'];

        $filelocation = APPPATH . "../../../bookbnev/public_html/app.padhaivadhai.com/padhaiVadhaiApp/original/";
        move_uploaded_file($_FILES["file"]['tmp_name'], $filelocation . $actfilname);


        $this->response(array("status" => "200"));
    }

    function test_get() {
        echo $filelocation = APPPATH . "../../../bookbnev/public_html/app.padhaivadhai.com/padhaiVadhaiApp/original";
    }

    function setFCMToken_post() {
        $postdata = $this->post();
        $insertArray = array(
            "model" => "",
            "manufacturer" => "",
            "uuid" => "",
            "datetime" => date("Y-m-d H:m:s a"),
            "user_id" => $postdata["user_id"],
            "reg_id" => $postdata["token_id"],
        );
        $this->db->where("user_id", $postdata["user_id"]);
        $query = $this->db->get("gcm_registration");
        $querydata = $query->result_array();
        if ($querydata) {
            $this->db->set($insertArray)->where("user_id", $postdata["user_id"])->update("gcm_registration");
            $this->response(array("status" => "200", "last_id" => $querydata[0]["id"]));
        } else {
            $this->db->insert("gcm_registration", $insertArray);
            $insert_id = $this->db->insert_id();
        }
        $this->response(array("status" => "200", "last_id" => $insert_id));
    }

    function testNotification_get() {
        $tokenid = "eKfx0CHNTIqp0rgg8O-ykn:APA91bHdIew-e_pUidpVOZoCgW6Hn5bEidirQ6v20zJxbED2Td3-meKy015iA1BdTBV8cNqNs7jjFlJg1Qu8uR6lm2xcwT-ltiACBVg9XZ3de14rSaVVthzHdONDW8jl8ylmdvHvNrlJ";
        $data = [
            "to" => $tokenid,
            "notification" => [
                "body" => "This is message body 32322323 ",
                "title" => "32322323 this is message title",
                "icon" => "ic_launcher"
            ],
        ];
        echo $this->android($data, [$tokenid]);
    }

}

?>