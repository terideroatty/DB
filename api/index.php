<?php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->post('/login','login'); /* User login */
$app->post('/signup','signup'); /* User Signup  */
$app->get('/getFeed','getFeed'); /* User Feeds  */
$app->get('/getDetail','getDetail');
$app->post('/feed','feed'); /* User Feeds  */
$app->post('/feedUpdate','feedUpdate'); /* User Feeds  */
$app->post('/feedDelete','feedDelete'); /* User Feeds  */
$app->post('/getImages', 'getImages');
$app->post('/profileUpdate','profileUpdate');
$app->post('/getNS','getNS');
$app->post('/randdata','randdata');
$app->post('/checkdata','checkdata');
$app->run();

/************************* USER LOGIN *************************************/
/* ### User login ### */
function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    
    try {
        
        $db = getDB();
        $userData ='';
        $sql = "SELECT user_id, name, email, username, type, address, surname FROM users WHERE (username=:username or email=:username) and password=:password ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username, PDO::PARAM_STR);
        $password=hash('sha256',$data->password);
        $stmt->bindParam("password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $mainCount=$stmt->rowCount();
        $userData = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!empty($userData))
        {
            $user_id=$userData->user_id;
            $userData->token = apiToken($user_id);
        }
        
        $db = null;
         if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Bad request wrong username and password"}}';
            }

           
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


/* ### User registration ### */


function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;
    $username=$data->username;
    $name=$data->name;
    $surname=$data->surname;
    $address=$data->address;    
	$type=$data->type;
    
    $password=$data->password;
    
    try {
        
        
        $emain_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
        
        
        
        if (strlen(trim($username))>0 && strlen(trim($password))>0 && strlen(trim($email))>0 && $emain_check>0  && $password_check>0)
        {
            $db = getDB();
            $userData = '';
            $sql = "SELECT * FROM users WHERE username=:username or email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("username", $username,PDO::PARAM_STR);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                
                /*Inserting user values*/
                $sql1="INSERT INTO users(username,password,email,name,surname,address,type)VALUES(:username,:password,:email,:name,:surname,:address, :type)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("username", $username,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->bindParam("name", $name,PDO::PARAM_STR);
                $stmt1->bindParam("surname", $surname,PDO::PARAM_STR);
                $stmt1->bindParam("address", $address,PDO::PARAM_STR);
				$stmt1->bindParam("type", $type,PDO::PARAM_STR);
                
                $stmt1->execute();
                
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
            echo '{"userData": ' . json_encode($userData) . '}';
                           
           }
       }
       catch(PDOException $e) {
           echo '{"error":{"text":'. $e->getMessage() .'}}';
       }
}



function signup2() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;
    $name=$data->name;
    $username=$data->username;
    $password=$data->password;
	$surname=$data->surname;
	$age=$data->age;
	$gender=$data->gender;
	$mailing_address=$data->mailing_address;
	$scancer=$data->scancer;
    
    try {
        
        $username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $password);
        
        echo $email_check.'<br/>'.$email;
        
        if (strlen(trim($username))>0 && strlen(trim($password))>0 && strlen(trim($email))>0 && $email_check>0 && $username_check>0 && $password_check>0)
        {
            echo 'here';
            $db = getDB();
            $userData = '';
            $sql = "SELECT user_id FROM member WHERE username=:username or email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("username", $username,PDO::PARAM_STR);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                
                /*Inserting user values*/
                $sql1="INSERT INTO member(username,password,email,name,surname,age,gender,mailing_address,scancer)VALUES(:username,:password,:email,:name,:surname,
				:age,:gender,:mailing_address,:scancer)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("username", $username,PDO::PARAM_STR);
                $password=hash('sha256',$data->password);
                $stmt1->bindParam("password", $password,PDO::PARAM_STR);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->bindParam("name", $name,PDO::PARAM_STR);
				$stmt1->bindParam("surname", $surname,PDO::PARAM_STR);
				$stmt1->bindParam("age", $age,PDO::PARAM_STR);
				$stmt1->bindParam("gender", $gender,PDO::PARAM_STR);
				$stmt1->bindParam("mailing_address", $mailing_address,PDO::PARAM_STR);
				$stmt1->bindParam("scancer", $scancer,PDO::PARAM_STR);
                $stmt1->execute();
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
         

            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid data"}}';
            }

           
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}




/* ### internal Username Details ### */
function internalUserDetails($input) {
    
    try {
        $db = getDB();
        $sql = "SELECT user_id, name, email, username, surname, address, type FROM users WHERE username=:input or email=:input";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $usernameDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $usernameDetails->token = apiToken($usernameDetails->user_id);
        $db = null;
        return $usernameDetails;
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}
function getDetail(){
  
   
    try {
         
            $detailData = '';
            $db = getDB();
          
                $sql = "SELECT * FROM detail  ORDER BY id DESC LIMIT 15";
                $stmt = $db->prepare($sql);
          
            $stmt->execute();
            $detailData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($detailData)
            echo '{"feedData": ' . json_encode($detailData) . '}';
            else
            echo '{"feedData": ""}';
        
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}
function getFeed(){
  
   
    try {
         
        if(1){
            $feedData = '';
            $db = getDB();
          
                $sql = "SELECT * FROM feed  ORDER BY feed_id DESC LIMIT 15";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam("lastCreated", $lastCreated, PDO::PARAM_STR);
          
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function getNS(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $type=$data->type;
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $modata = '';
            $db = getDB();
            $sql = "SELECT * FROM feed WHERE ftype=:type";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("type", $type, PDO::PARAM_STR);
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
            
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function getCourse(){

}
function checkdata(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id = $data->user_id;
    $type = $data->type;
    $token=$data->token;
    $systemToken=apiToken($user_id);
     try{
        if($systemToken == $token){
           $db = getDB();
           $testData = '';
           $testData2 = '';
           $testData3 = '';
        
           $sql = "SELECT * FROM mcourse WHERE uid = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_STR);
            $stmt->execute();
            $testData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $uptimenow = "UPDATE `timeupdate` SET `datetime`=CURRENT_DATE";
			$stmt3 = $db->prepare($uptimenow);
            $stmt3->execute();
            
            $datetoday = "SELECT datetime FROM timeupdate";
			$stmt2 = $db->prepare($datetoday);
			$stmt2->bindParam("datetime", $datetime, PDO::PARAM_INT);
			$stmt2->execute();
            $datetodayNew = $stmt2->fetchAll(PDO::FETCH_OBJ);
			
  		    $timestamp = "SELECT datetime FROM time";
            $stmt4 = $db->prepare($timestamp);
            $stmt4->execute();
            $dateDB = $stmt4->fetchAll(PDO::FETCH_OBJ);
          
           if($testData){

                if($datetodayNew != $dateDB){
                    $datarandnew = '';
                    $datarandnew2 = '';
                    $datarandnew3 = '';    
                    
                    //update Data
                    $sql3 = "UPDATE mcourse AS m 
                    INNER JOIN product AS p ON m.uid = :user_id
                    SET m.mfk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND()%2 LIMIT 1)";
                    $stmt7 = $db->prepare($sql3);
                    $stmt7->bindParam("user_id", $user_id, PDO::PARAM_INT);
                    $stmt7->execute();
                 
                    $sql4 = "UPDATE mcourse AS m 
                    INNER JOIN product AS p ON m.uid = :user_id
                    SET m.nfk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND()*1000 LIMIT 1)";
                    $stmt8 = $db->prepare($sql4);
                    $stmt8->bindParam("user_id", $user_id, PDO::PARAM_INT);
                    $stmt8->execute();

                    $sql5 = "UPDATE mcourse AS m 
                    INNER JOIN product AS p ON m.uid = :user_id
                    SET m.efk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND()%2 LIMIT 1)";
                    $stmt9 = $db->prepare($sql5);
                    $stmt9->bindParam("user_id", $user_id, PDO::PARAM_INT);
                    $stmt9->execute();

                    //Select Data
                    $datarand = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT mfk FROM mcourse WHERE uid = :user_id)";
                    $smm = $db->prepare($datarand);
                    $smm->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $smm->execute();
                    $datarandnew = $smm->fetchAll(PDO::FETCH_OBJ);
    
                    $datarand2 = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT nfk FROM mcourse WHERE uid = :user_id)";
                    $smm2 = $db->prepare($datarand2);
                    $smm2->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $smm2->execute();
                    $datarandnew2 = $smm2->fetchAll(PDO::FETCH_OBJ);

                    $datarand3 = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT efk FROM mcourse WHERE uid = :user_id)";
                    $smm3 = $db->prepare($datarand3);
                    $smm3->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $smm3->execute();
                    $datarandnew3 = $smm3->fetchAll(PDO::FETCH_OBJ);

                    $timestampnew = "UPDATE `time` SET `datetime`=CURRENT_DATE";
				    $stmt5 = $db->prepare($timestampnew);
				    $stmt5->execute();

                    echo json_encode(array($datarandnew, $datarandnew2,$datarandnew3));
                }else{
                    $sql1 = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT mfk FROM mcourse WHERE uid = :user_id)";
                    $stmt1 = $db->prepare($sql1);
                    $stmt1->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $stmt1->execute();
                    $testData2 = $stmt1->fetchAll(PDO::FETCH_OBJ);
    
                    $sql2 = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT nfk FROM mcourse WHERE uid = :user_id)";
                    $stmt6 = $db->prepare($sql2);
                    $stmt6->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $stmt6->execute();
                    $testData3 = $stmt6->fetchAll(PDO::FETCH_OBJ);

                    $new = "SELECT product.pro_name FROM product 
                    INNER JOIN mcourse ON product.pro_id = (SELECT efk FROM mcourse WHERE uid = :user_id)";
                    $scc = $db->prepare($new);
                    $scc->bindParam("user_id", $user_id, PDO::PARAM_STR);
                    $scc->execute();
                    $testData3 = $scc->fetchAll(PDO::FETCH_OBJ);


                    echo json_encode(array($testData2, $testData3,$testData3));
                }
           }else{
            $dataset = '';
            $dataset2 = '';
            $dataset3 = '';

            $insertdata = "UPDATE mcourse AS m 
            INNER JOIN product AS p ON m.uid = :user_id
            SET m.mfk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND() LIMIT 1)";
            $stt = $db->prepare($insertdata);
            $stt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stt->execute();
         
            $insertdata2 = "UPDATE mcourse AS m 
            INNER JOIN product AS p ON m.uid = :user_id
            SET m.nfk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND() LIMIT 1)";
            $stt2 = $db->prepare($insertdata2);
            $stt2->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stt2->execute();

            $insertdata3 = "UPDATE mcourse AS m 
            INNER JOIN product AS p ON m.uid = :user_id
            SET m.efk = (SELECT pro_id FROM product WHERE pt_id = 1 ORDER BY RAND() LIMIT 1)";
            $stt3 = $db->prepare($insertdata3);
            $stt3->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stt3->execute();

            $show = "SELECT product.pro_name FROM product 
            INNER JOIN mcourse ON product.pro_id = (SELECT mfk FROM mcourse WHERE uid = :user_id)";
            $sdd = $db->prepare($show);
            $sdd->bindParam("user_id", $user_id, PDO::PARAM_STR);
            $sdd->execute();
            $dataset = $sdd->fetchAll(PDO::FETCH_OBJ);

            $show2 = "SELECT product.pro_name FROM product 
            INNER JOIN mcourse ON product.pro_id = (SELECT nfk FROM mcourse WHERE uid = :user_id)";
            $sdd2 = $db->prepare($show2);
            $sdd2->bindParam("user_id", $user_id, PDO::PARAM_STR);
            $sdd2->execute();
            $dataset2 = $sdd2->fetchAll(PDO::FETCH_OBJ);

            $show3 = "SELECT product.pro_name FROM product 
            INNER JOIN mcourse ON product.pro_id = (SELECT efk FROM mcourse WHERE uid = :user_id)";
            $sdd3 = $db->prepare($show3);
            $sdd3->bindParam("user_id", $user_id, PDO::PARAM_STR);
            $sdd3->execute();
            $dataset3 = $sdd3->fetchAll(PDO::FETCH_OBJ);
           }
           /*if($testData)
            echo '{"OatData": ' . json_encode($testData) . '}';
            else
            echo '{"OatData": ""}';*/
        }else{
            echo '{"error":{"text":"No access"}}';
        }
        
     }catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
function randdata($user_id,$token){
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $systemToken=apiToken($user_id);
	 try {
        if($systemToken == $token){
            $CourseData = '';
			$dateDB = '';
			$morningdata = '';
			$noondata = '';
			$eveningdata = '';
            $datetodayNew = '';
            $modata = '';
            $nodata = '';
            $evdata = '';
            $morningja = '';
            $noonja = '';
            $eveningja = '';
            $db = getDB();
			$db1 = getDB();
      
			$uptimenow = "UPDATE `timeupdate` SET `datetime`=CURRENT_DATE";
			$stmt3 = $db->prepare($uptimenow);
			$stmt3->execute();
		
			
			$datetoday = "SELECT datetime FROM timeupdate";
			$stmt2 = $db->prepare($datetoday);
			$stmt2->bindParam("datetime", $datetime, PDO::PARAM_INT);
			$stmt2->execute();
            $datetodayNew = $stmt2->fetchAll(PDO::FETCH_OBJ);
			
  		    $timestamp = "SELECT datetime FROM time";
            $stmt1 = $db->prepare($timestamp);
            $stmt1->execute();
            $dateDB = $stmt1->fetchAll(PDO::FETCH_OBJ);
			
			if ($datetodayNew != $dateDB){
				
				$morning = "UPDATE users AS u 
                INNER JOIN course AS c ON u.user_id = :user_id
                SET u.c_idfk = (SELECT c_id FROM course WHERE ctype = 1 ORDER BY RAND() LIMIT 1)";
                $stmtm = $db->prepare($morning);
                $stmtm->bindParam("user_id", $user_id, PDO::PARAM_INT);
				$stmtm->execute();
				$noon = "UPDATE users AS u 
                INNER JOIN course AS c ON u.user_id = :user_id
                SET u.c_idfk2 = (SELECT c_id FROM course WHERE ctype = 2 ORDER BY RAND() LIMIT 1)";
                $stmtn = $db->prepare($noon);
                $stmtn->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmtn->execute();
				$evening = "UPDATE users AS u 
                INNER JOIN course AS c ON u.user_id = :user_id
                SET u.c_idfk3 = (SELECT c_id FROM course WHERE ctype = 3 ORDER BY RAND() LIMIT 1)";
                $stmte = $db->prepare($evening);
                $stmte->bindParam("user_id", $user_id, PDO::PARAM_INT);
				$stmte->execute();
                
                 //morning
                $sql1 = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk WHERE users.user_id = :user_id";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt1->execute();
                $morningja = $stmt1->fetch(PDO::FETCH_OBJ);
                //noon
                $sql2 = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk2 WHERE users.user_id = :user_id";
                $stmt2 = $db->prepare($sql2);
                $stmt2->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt2->execute();
                $noonja = $stmt2->fetch(PDO::FETCH_OBJ);
                //evening
                $sql3 = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk3 WHERE users.user_id = :user_id";
                $stmt3 = $db->prepare($sql3);
                $stmt3->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt3->execute();
                $eveningja = $stmt3->fetch(PDO::FETCH_OBJ);
            
			    $sql1 = "UPDATE `time` SET `datetime`=CURRENT_DATE";
				$stmt4 = $db->prepare($sql1);
				$stmt4->execute();
                echo ('time change');
            
			}else{
                 //morning
                $datauser = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk WHERE users.user_id = :user_id";
                $data1 = $db->prepare($datauser);
                $data1->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $data1->execute();
                $modata = $data1->fetch(PDO::FETCH_OBJ);
                //noon
                $datauser2 = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk2 WHERE users.user_id = :user_id";
                $data2 = $db->prepare($datauser2);
                $data2->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $data2->execute();
                $nodata = $data2->fetch(PDO::FETCH_OBJ);
                //evening
                $datauser3 = "SELECT course.name,users.username,course.c_id FROM course INNER JOIN users ON course.c_id = users.c_idfk3 WHERE users.user_id = :user_id";
                $data3 = $db->prepare($datauser3);
                $data3->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $data3->execute();
                $evdata = $data3->fetch(PDO::FETCH_OBJ);
                //prepare userdata and execute
                echo ('not change');
                $db = null;
			}
            if($morningja)
            echo '{"morningja": ' . json_encode($morningja) . '}';
            else
            echo '{"morningja": ""}';
            if($noonja)
            echo '{"noonja": ' . json_encode($noonja) . '}';
            else
            echo '{"noonja": ""}';
            if($eveningja)
            echo '{"eveningja": ' . json_encode($eveningja) . '}';
            else
            echo '{"eveningja": ""}';
            if($modata)
            echo '{"modata": ' . json_encode($modata) . '}';
            else
            echo '{"modata": ""}';
            if($nodata)
            echo '{"nodata": ' . json_encode($nodata) . '}';
            else
            echo '{"nodata": ""}';
            if($evdata)
            echo '{"evdata": ' . json_encode($evdata) . '}';
            else
            echo '{"evdata": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
        
    } catch(PDOException $e) {
        echo '{"error random":{"text":'. $e->getMessage() .'}}';
    }
}
function feed(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $lastCreated = $data->lastCreated;
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            if($lastCreated){
                $sql = "SELECT * FROM feed WHERE user_id_fk=:user_id AND created < :lastCreated ORDER BY feed_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
                $stmt->bindParam("lastCreated", $lastCreated, PDO::PARAM_STR);
            }
            else{
                $sql = "SELECT * FROM feed WHERE user_id_fk=:user_id ORDER BY feed_id DESC LIMIT 5";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $feedData = $stmt->fetchAll(PDO::FETCH_OBJ);
           
            $db = null;

            if($feedData)
            echo '{"feedData": ' . json_encode($feedData) . '}';
            else
            echo '{"feedData": ""}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}
function profileUpdate(){
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $email=$data->email;
    $username=$data->username;
    $name=$data->name;
    $surname=$data->surname;
    $address=$data->address;
    
   
    
    $systemToken=apiToken($user_id);

    try {
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            $sql = "UPDATE users SET email = :email, username = :username, name = :name,
                    surname = :surname , address = :address   WHERE user_id = :user_id"; 
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email, PDO::PARAM_STR);
            $stmt->bindParam("username", $username, PDO::PARAM_STR);
            $stmt->bindParam("name", $name, PDO::PARAM_STR);
            $stmt->bindParam("surname", $surname, PDO::PARAM_STR);
            $stmt->bindParam("address", $address, PDO::PARAM_STR);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->execute();

            
            $db = null;
            echo '{"feedData": ' . json_encode($feedData) . '}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}

function feedUpdate(){

    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $feed=$data->feed;
    
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
         
            
            $feedData = '';
            $db = getDB();
            $sql = "INSERT INTO feed ( feed, created, user_id_fk) VALUES (:feed,:created,:user_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("feed", $feed, PDO::PARAM_STR);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $created = time();
            $stmt->bindParam("created", $created, PDO::PARAM_INT);
            $stmt->execute();
            


            $sql1 = "SELECT * FROM feed WHERE user_id_fk=:user_id ORDER BY feed_id DESC LIMIT 1";
            $stmt1 = $db->prepare($sql1);
            $stmt1->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt1->execute();
            $feedData = $stmt1->fetch(PDO::FETCH_OBJ);


            $db = null;
            echo '{"feedData": ' . json_encode($feedData) . '}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

}



function feedDelete(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $feed_id=$data->feed_id;
    
    $systemToken=apiToken($user_id);
   
    try {
         
        if($systemToken == $token){
            $feedData = '';
            $db = getDB();
            $sql = "Delete FROM feed WHERE user_id_fk=:user_id AND feed_id=:feed_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam("feed_id", $feed_id, PDO::PARAM_INT);
            $stmt->execute();
            
           
            $db = null;
            echo '{"success":{"text":"Feed deleted"}}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
       
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }   
    
}
$app->post('/userImage','userImage'); /* User Details */
function userImage(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    $imageB64=$data->imageB64;
    $systemToken=apiToken($user_id);
    try {
        if(1){
            $db = getDB();
            $sql = "INSERT INTO imagesData(b64,user_id_fk) VALUES(:b64,:user_id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam("b64", $imageB64, PDO::PARAM_STR);
            $stmt->execute();
            $db = null;
            echo '{"success":{"status":"uploaded"}}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

$app->post('/getImages', 'getImages');
function getImages(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $user_id=$data->user_id;
    $token=$data->token;
    
    $systemToken=apiToken($user_id);
    try {
        if(1){
            $db = getDB();
            $sql = "SELECT b64 FROM imagesData";
            $stmt = $db->prepare($sql);
           
            $stmt->execute();
            $imageData = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"imageData": ' . json_encode($imageData) . '}';
        } else{
            echo '{"error":{"text":"No access"}}';
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
?>
