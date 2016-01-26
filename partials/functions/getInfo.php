<?php

// Start the session
session_start();

/*
    getInfo is a common service script for all requests that replies with a JSON query based on the arguments provided

    Initlaize response array to be sent as JSON, each response will have ['status'] attribute to notify the frontend about any errors
    The response['status'] of type int attribute will mean the following
    0 : user not logged in
    1 : user logged in, but no profile details found, first time login
    2 : user logged in, profile details found
*/

$response = array();

if(isset($_SESSION['loggedin_user']) == false || checkType($_GET['type']) == false){
    
    $response['status'] = 0;
    send_response($response);

}else{
    
    require_once 'config.php';
    
    // Get the type of database that needs to be accessed, eg. userDB, userDetailDB.. etc. 
    $type = $_GET['type'];
    $user_id = $_SESSION['loggedin_user'];
    
    // Variables
    $sql;       // The SQL Query
    $result;    // Result for the SQL query
    
    // Set response
    $response['status'] = 1;
    
    // UserDB contains sensitive information and requires precaution
    if($type == 'user'){
        $sql = 'SELECT user_username, user_email FROM userDB WHERE user_id = '.$user_id;
    }else{
        $sql = 'SELECT * FROM '.$type.'DB WHERE '.$type.'_fk_user_id = '.$user_id;
    }
    
    // Get the results
    $result = $mysqli->query($sql);
    $response[$type] = $result->fetch_assoc();
    send_response($response);

}

/*  This function takes a response and simply echos it for the app.js to read
    $data : Array - array $response from the php file 
*/
function send_response($data){
    header('Content-type: application/json');
    echo json_encode($data);
}

function checkType($type){
    if(preg_match("/^(userDetail|user)$/", $type, $match)){
        return true;
    }else{
        return false;
    }
}