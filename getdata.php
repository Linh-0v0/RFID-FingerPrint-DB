<?php  
//Connect to database
require 'connectDB.php';
date_default_timezone_set('Asia/Damascus');
$d = date("Y-m-d");
$t = date("H:i:sa");

if (isset($_GET['card_uid']) && isset($_GET['device_token'])) {
    
    $card_uid = $_GET['card_uid'];
    $device_uid = $_GET['device_token'];

    $sql = "SELECT * FROM devices WHERE device_uid=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error_Select_device";
        exit();
    }
    else{
        mysqli_stmt_bind_param($result, "s", $device_uid);
        mysqli_stmt_execute($result);

        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)){
            $device_mode = $row['device_mode'];
            $device_dep = $row['device_dep'];
            if ($device_mode == 1) {
                $sql = "SELECT * FROM users WHERE card_uid=?";
                $result = mysqli_stmt_init($conn);

                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_card";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "s", $card_uid);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    if ($row = mysqli_fetch_assoc($resultl)){
                        //*****************************************************
                        //An existed Card has been detected for Login or Logout
                        if ($row['add_card'] == 1){
                            if ($row['device_uid'] == $device_uid || $row['device_uid'] == 0){
                                $Uname = $row['username'];
                                $Number = $row['s_id'];
                                $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND card_out=0";
                                $result = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($result, $sql)) {
                                    echo "SQL_Error_Select_logs";
                                    exit();
                                }
                                else{
                                    mysqli_stmt_bind_param($result, "ss", $card_uid, $d);
                                    mysqli_stmt_execute($result);
                                    $resultl = mysqli_stmt_get_result($result);
                                    //*****************************************************
                                    //Login
                                    if (!$row = mysqli_fetch_assoc($resultl)){

                                        $sql = "INSERT INTO users_logs (username, s_id, card_uid, device_uid, device_dep, checkindate, timein, timeout) VALUES (? ,?, ?, ?, ?, ?, ?, ?)";
                                        $result = mysqli_stmt_init($conn);
                                        if (!mysqli_stmt_prepare($result, $sql)) {
                                            echo "SQL_Error_Select_login1";
                                            exit();
                                        }
                                        else{
                                            $timeout = "00:00:00";
                                            mysqli_stmt_bind_param($result, "sdssssss", $Uname, $Number, $card_uid, $device_uid, $device_dep, $d, $t, $timeout);
                                            mysqli_stmt_execute($result);

                                            echo "login".$Uname;
                                            exit();
                                        }
                                    }
                                    //*****************************************************
                                    //Logout
                                    else{
                                        $sql="UPDATE users_logs SET timeout=?, card_out=1 WHERE card_uid=? AND checkindate=? AND card_out=0";
                                        $result = mysqli_stmt_init($conn);
                                        if (!mysqli_stmt_prepare($result, $sql)) {
                                            echo "SQL_Error_insert_logout1";
                                            exit();
                                        }
                                        else{
                                            mysqli_stmt_bind_param($result, "sss", $t, $card_uid, $d);
                                            mysqli_stmt_execute($result);

                                            echo "logout".$Uname;
                                            exit();
                                        }
                                    }
                                }
                            }
                            else {
                                echo "Not Allowed!";
                                exit();
                            }
                        }
                        else if ($row['add_card'] == 0){
                            echo "Not registerd!";
                            exit();
                        }
                    }
                    else{
                        echo "Not found!";
                        exit();
                    }
                }
            }
            else if ($device_mode == 0) {
                //New Card has been added
                $sql = "SELECT * FROM users WHERE card_uid=?";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_card";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "s", $card_uid);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    //The Card is available
                    if ($row = mysqli_fetch_assoc($resultl)){
                        $sql = "SELECT card_select FROM users WHERE card_select=1";
                        $result = mysqli_stmt_init($conn);

                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_Select";
                            exit();
                        }
                        else{
                            mysqli_stmt_execute($result);
                            $resultl = mysqli_stmt_get_result($result);
                            
                            if ($row = mysqli_fetch_assoc($resultl)) {
                                $sql="UPDATE users SET card_select=0";
                                $result = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($result, $sql)) {
                                    echo "SQL_Error_insert";
                                    exit();
                                }

                                else{
                                    mysqli_stmt_execute($result);

                                    $sql="UPDATE users SET card_select=1 WHERE card_uid=?";
                                    $result = mysqli_stmt_init($conn);
                                    if (!mysqli_stmt_prepare($result, $sql)) {
                                        echo "SQL_Error_insert_An_available_card";
                                        exit();
                                    }
                                    else{
                                        mysqli_stmt_bind_param($result, "s", $card_uid);
                                        mysqli_stmt_execute($result);

                                        echo "available";
                                        exit();
                                    }
                                }
                            }
                            else{
                                $sql="UPDATE users SET card_select=1 WHERE card_uid=?";
                                $result = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($result, $sql)) {
                                    echo "SQL_Error_insert_An_available_card";
                                    exit();
                                }
                                else{
                                    mysqli_stmt_bind_param($result, "s", $card_uid);
                                    mysqli_stmt_execute($result);

                                    echo "available";
                                    exit();
                                }
                            }
                        }
                    }
                    //The Card is new
                    else{
                        $sql="UPDATE users SET card_select=0";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert";
                            exit();
                        }
                        else{
                            mysqli_stmt_execute($result);
                            $sql = "INSERT INTO users (card_uid, card_select, device_uid, device_dep, user_date) VALUES (?, 1, ?, ?, CURDATE())";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error_Select_add";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "sss", $card_uid, $device_uid, $device_dep );
                                mysqli_stmt_execute($result);

                                echo "succesful";
                                exit();
                            }
                        }
                    }
                }    
            }
        }
        else{
            echo "Invalid Device!";
            exit();
        }
    }          
}
// *******************************************************
if (isset($_GET['FingerID']) && isset($_GET['device_token'])) {
    
    $fingerID = $_POST['FingerID'];
    $device_uid = $_GET['device_token'];

    $sql = "SELECT * FROM users WHERE device_uid=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error_Select_device";
        exit();
    }
    else{
        mysqli_stmt_bind_param($result, "s", $device_uid);
        // mysqli_stmt_bind_param($result, "s", $fingerID);
        mysqli_stmt_execute($result);

        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)){
            $device_mode = $row['device_mode'];
            $device_dep = $row['device_dep'];
            // if ($row['username'] != "Name"){
            //     $Uname = $row['username'];
            //     $Number = $row['s_id'];
            if ($device_mode == 1) {
                $sql = "SELECT * FROM users WHERE fingerprint_id=?";
                $result = mysqli_stmt_init($conn);

                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_fingerprint";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "s", $fingerID);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    if ($row = mysqli_fetch_assoc($resultl)){
                    //******************************************** */
                    //An existed Fingerprint has been detected for Login or Logout
                        if ($row['add_fingerprint'] == 1){
                            if ($row['device_uid'] == $device_uid || $row['device_uid'] == 0){
                                $Uname = $row['username'];
                                $Number = $row['s_id'];
                                $sql = "SELECT * FROM users_logs WHERE fingerprint_id=? AND checkindate=? AND fingerprint_out=0";
                                $result = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($result, $sql)) {
                                    echo "SQL_Error_Select_logs";
                                    exit();
                                }
                                else{
                                    mysqli_stmt_bind_param($result, "ss", $fingerprint_id, $d);
                                    mysqli_stmt_execute($result);
                                    $resultl = mysqli_stmt_get_result($result);
                    //*****************************************************
                    //Login
                    if (!$row = mysqli_fetch_assoc($resultl)){

                        $sql = "INSERT INTO users_logs (username, s_id, fingerprint_id, device_uid, device_dep, checkindate, timein, timeout) VALUES (? ,?, ?, ?, ?, ?, ?, ?)";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_Select_login1";
                            exit();
                        }
                        else{
                            $timeout = "00:00:00";
                            mysqli_stmt_bind_param($result, "sdssssss", $Uname, $Number, $fingerprint_id, $device_uid, $device_dep, $d, $t, $timeout);
                            mysqli_stmt_execute($result);

                            echo "login".$Uname;
                            exit();
                        }
                    }
                    //*****************************************************
                    //Logout
                    else{
                        $sql="UPDATE users_logs SET timeout=?, fingerprint_out=1 WHERE checkindate=? AND fingerprint_id=? AND fingerprint_out=0";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert_logout1";
                            exit();
                        }
                        else{
                            mysqli_stmt_bind_param($result, "sss", $t, $fingerprint_id, $d);
                            mysqli_stmt_execute($result);

                            echo "logout".$Uname;
                            exit();
                        }
                    }
                }
            }
            else {
                echo "Not Allowed!";
                exit();
            }
        }
        else if ($row['add_card'] == 0){
            echo "Not registerd!";
            exit();
        }
    }
    else{
        echo "Not found!";
        exit();
    }
}
}
            //*****************************************************
            //An available Fingerprint has been detected
            else if ($device_mode == 0){
                // $sql = "SELECT fingerprint_select FROM users WHERE fingerprint_select=1";
                $sql = "SELECT * FROM users WHERE fingerprint_id=?";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_fingerprint";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "s", $fingerprint_id);
                    mysqli_stmt_execute($result);
                    $resultl = mysqli_stmt_get_result($result);
                    //The Fingerprint is available
                    if ($row = mysqli_fetch_assoc($resultl)){
                        $sql = "SELECT fingerprint_select FROM users WHERE fingerprint_select=1";
                        $result = mysqli_stmt_init($conn);
                        
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_Select";
                            exit();
                        } else {
                            mysqli_stmt_execute($result);
                            $resultl = mysqli_stmt_get_result($result);

                    if ($row = mysqli_fetch_assoc($resultl)) {
                        $sql="UPDATE users SET fingerprint_select=0";
                        $result = mysqli_stmt_init($conn);

                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert";
                            exit();
                        }
                        else{
                            mysqli_stmt_execute($result);

                            $sql="UPDATE users SET fingerprint_select=1 WHERE fingerprint_id=?";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error_insert_An_available_fingerprint";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "s", $fingerprint_id);
                                mysqli_stmt_execute($result);

                                echo "available";
                                exit();
                            }
                        }
                    }
                    else{
                        $sql="UPDATE users SET fingerprint_select=1 WHERE fingerprint_id=?";
                        $result = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($result, $sql)) {
                            echo "SQL_Error_insert_An_available_fingerprint_id";
                            exit();
                        }
                        else{
                            mysqli_stmt_bind_param($result, "s", $finger_sel, $fingerprint_id);
                            mysqli_stmt_execute($result);

                            echo "available";
                            exit();
                        }
                    }
                }
            }
        
        //*****************************************************
        //New Fingerprint has been added
        else{
            $sql="UPDATE users SET fingerprint_select=0";
            $result = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($result, $sql)) {
                echo "SQL_Error_insert";
                exit();
            }
            else{
                mysqli_stmt_execute($result);
                $sql = "INSERT INTO users (fingerprint_id, fingerprint_select, device_uid, device_dep,user_date) VALUES (?, 1, ?, ?, CURDATE())";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_Select_add";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($result, "sss", $fingerprint_id, $device_uid, $device_dep);
                    mysqli_stmt_execute($result);

                    echo "succesful1";
                    exit();
                }
            }
        }
        
    }
}
} 
        else {
            echo "Invalid Device!";
            exit();
        }
    }
}
if (isset($_POST['Get_Fingerid'])) {
    
    if ($_POST['Get_Fingerid'] == "get_id") {
        $sql= "SELECT fingerprint_id FROM users WHERE add_fingerid=1";
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            echo "SQL_Error_Select";
            exit();
        }
        else{
            mysqli_stmt_execute($result);
            $resultl = mysqli_stmt_get_result($result);
            if ($row = mysqli_fetch_assoc($resultl)) {
                echo "add-id".$row['fingerprint_id'];
                exit();
            }
            else{
                echo "Nothing";
                exit();
            }
        }
    }
    else{
        exit();
    }
}
if (!empty($_POST['confirm_id'])) {

    $fingerid = $_POST['confirm_id'];

    $sql="UPDATE users SET fingerprint_select=0 WHERE fingerprint_select=1";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error_Select";
        exit();
    }
    else{
        mysqli_stmt_execute($result);
        
        $sql="UPDATE users SET add_fingerid=0, fingerprint_select=1 WHERE fingerprint_id=?";
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            echo "SQL_Error_Select";
            exit();
        }
        else{
            mysqli_stmt_bind_param($result, "s", $fingerid);
            mysqli_stmt_execute($result);
            echo "Fingerprint has been added!";
            exit();
        }
    }  
}
if (isset($_POST['DeleteID'])) {

    if ($_POST['DeleteID'] == "check") {
        $sql = "SELECT fingerprint_id FROM users WHERE del_fingerid=1";
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            echo "SQL_Error_Select";
            exit();
        }
        else{
            mysqli_stmt_execute($result);
            $resultl = mysqli_stmt_get_result($result);
            if ($row = mysqli_fetch_assoc($resultl)) {
                
                echo "del-id".$row['fingerprint_id'];

                $sql = "DELETE FROM users WHERE del_fingerid=1";
                $result = mysqli_stmt_init($conn);
                if (!mysqli_stmt_prepare($result, $sql)) {
                    echo "SQL_Error_delete";
                    exit();
                }
                else{
                    mysqli_stmt_execute($result);
                    exit();
                }
            }
            else{
                echo "nothing";
                exit();
            }
        }
    }
    else{
        exit();
    }
}
mysqli_stmt_close($result);
mysqli_close($conn);
?>