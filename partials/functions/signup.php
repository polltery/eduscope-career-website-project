<?php 

/*  Author: Balraj
    This is script is responsible for backend validation and inserting data from signup forms
    How it does validation?
    1. CHECK IF POST Variables exists
    2. CHECK IF terms and validations are checked
    3. CHECK IF email is valid
    4. CHECK IF email and username exists in DB
    5. CHECK IF username is valid
    6. CHECK IF password length is correct
    7. CHECK IF password matches the confirmation password
    
    After all these validations are done, the users data goes in the database
*/

include_once 'config.php';

// Check if POST variables exists
if( array_key_exists('signup_email',$_POST) 
    && array_key_exists('signup_username',$_POST)
    && array_key_exists('signup_password',$_POST)
    && array_key_exists('signup_password_confirm',$_POST)
    && array_key_exisits('signup_terms_checkbox',$_POST)
){
    
    // Assign post variables to variables
    $email = $_POST['signup_email'];
    $username = $_POST['signup_username'];
    $password = $_POST['signup_password'];
    $password_c = $_POST['signup_password_confirm'];

    // Check terms and validations
    if($_POST['signup_terms_checkbox'] === 'on'){
        
        // Validate E-Mail
        if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
            
            $response = "Invalid email address";

        }else{
            
            // SQL for selecting our user
            $sql = "SELECT * FROM userDB WHERE user_email = ?";
            
            // Prepare statement
            $stmt = $mysqli->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("s",$email);
            
            // Execute statement
            $stmt->execute();
            
            // Get the results
            $result = $stmt->get_result();
            
            // SQL for selecting our user
            $sql = "SELECT * FROM userDB WHERE user_username = ?";
            
            // Prepare statement
            $stmt = $mysqli->prepare($sql);
            
            // Bind parameters
            $stmt->bind_param("s",$username);
            
            // Execute statement
            $stmt->execute();
            
            // Get the results
            $result_user_valid = $stmt->get_result();
            
            // Check number of results we got, If there is no such user, then we should get 0
            if($result->num_rows === 0 && $result_user_valid->num_rows === 0){
                
                // Check other inputs, starting with username
                if(!preg_match("/^([a-zA-Z0-9_]){4,50}/", $username, $match)){
                
                    $response = "Username Doesn't Match the criteria: Alphanumeric and minimum 4 characters";
                    
                }else{
                    
                    // Check password
                    if(strlen($password) < 8){
                        
                        $response = "Password must be atleast 8 words long"; 
                        
                    }else{
                        
                        // Match password
                        if($password != $password_c){
                            
                            $response = "Passwords don't match, please try again";
                            
                        }else{
                            
                            // Log the user details in the db
                            // Hash the password
                            $password_hash = password_hash($password, PASSWORD_BCRYPT);

                            // The query
                            $sql = 'INSERT INTO userDB (user_email, user_username, user_password) VALUES (?,?,?)';

                            // Prepare the statement to sanetize the sql data
                            $stmt = $mysqli->prepare($sql);

                            // bind the parameters
                            $stmt->bind_param('sss', $email, $username, $password_hash);

                            // Execute statement
                            $stmt->execute();
                                                
                            $response = "success";
                            
                        } // end Match Password
                        
                    } // end Check Password
                    
                } // end Check username
                
                
            }else{
                
                // If Unsuccessful
                if($result->num_rows === 0){
                    $response = "Username is already taken";
                }else{
                    $response = "Email Already Exists, Please Login";
                }
            
            }
            
        }
        
    }else{
        $response = "Please check terms and conditions";
    }

    // Close the connection
    $mysqli->close();

}else{
    
    $response = "Field(s) missing, please check your inputs";
    
}

// Print the response
echo $response;

?>