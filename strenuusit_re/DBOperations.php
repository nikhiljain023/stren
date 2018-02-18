<?php

class DBOperations{

	 private $host = 'localhost';
	 private $user = 'root';
	 private $db = 'strenuus_toll';
	 private $pass = 'root';
	 private $conn;

public function __construct() {

	$this -> conn = new PDO("mysql:host=".$this -> host.";dbname=".$this -> db, $this -> user, $this -> pass);

}


 public function insertData($name,$email,$password){

 	$unique_id = uniqid('', true);
    $hash = $this->getHash($password);
    $encrypted_password = $hash["encrypted"];
	$salt = $hash["salt"];

 	$sql = 'INSERT INTO users SET unique_id =:unique_id,name =:name,
    email =:email,encrypted_password =:encrypted_password,salt =:salt,created_at = NOW()';

 	$query = $this ->conn ->prepare($sql);
 	$query->execute(array('unique_id' => $unique_id, ':name' => $name, ':email' => $email,
     ':encrypted_password' => $encrypted_password, ':salt' => $salt));

    if ($query) {
        
        return true;

    } else {

        return false;

    }
 }


 public function checkLogin($email, $password) {

    $sql = 'SELECT * FROM operator WHERE operator_id = :email and password=:password and roll_id=1';
    $query = $this -> conn -> prepare($sql);
    $query -> execute(array(':email' => $email, ':password' => $password));
    $data = $query -> fetchObject();
    

    if ($data){
        $user["name"] = $data -> first_name;
        $user["email"] = $data -> operator_id;
        $user["contact"] = $data -> contact;
        return $user;

    } else {

        return false;
    }

 }



public function dayReport($email){
  $sql="SELECT
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2)) AND (transaction.`shift_id`='1')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT1_AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2)) AND (transaction.`shift_id`='2')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT2_AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2)) AND (transaction.`shift_id`='3')) THEN transaction.`transaction_amount` END)) ,0)AS `SHIFT3_AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (3,4)) AND (transaction.`shift_id`='1')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT1_MANUALAMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (3,4)) AND (transaction.`shift_id`='2')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT2_MANUALAMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (3,4)) AND (transaction.`shift_id`='3')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT3_MANUALAMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (5)) AND (transaction.`shift_id`='1')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT1_TICKETCANCEL`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (5)) AND (transaction.`shift_id`='2')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT2_TICKETCANCEL`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (5)) AND (transaction.`shift_id`='3')) THEN transaction.`transaction_amount` END)),0) AS `SHIFT3_TICKETCANCEL`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4)) AND (transaction.`shift_id`='1')) THEN transaction.`transaction_amount` END)),0) AS `TOTAL_SHIFT1AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4)) AND (transaction.`shift_id`='2')) THEN transaction.`transaction_amount` END)),0) AS `TOTAL_SHIFT2AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4)) AND (transaction.`shift_id`='3')) THEN transaction.`transaction_amount` END)),0) AS `TOTAL_SHIFT3AMOUNT`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4))) THEN transaction.`transaction_amount` END)),0) AS `TOTAL_AMOUNT`,

(SELECT IFNULL(SUM(short_excess.`short_amount`),0) FROM short_excess WHERE `shift`='1' AND DATE=:email)AS SHIFT1_SHORT,
(SELECT IFNULL(SUM(short_excess.`short_amount`),0) FROM short_excess WHERE `shift`='2' AND DATE=:email)AS SHIFT2_SHORT,
(SELECT IFNULL(SUM(short_excess.`short_amount`),0) FROM short_excess WHERE `shift`='3' AND DATE=:email)AS SHIFT3_SHORT,
(SELECT IFNULL(SUM(short_excess.`excess_amount`),0)+IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='1' AND DATE=:email)AS SHIFT1_ACCESS_AMOUNT,
(SELECT IFNULL(SUM(short_excess.`excess_amount`),0)+IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='2' AND DATE=:email)AS SHIFT2_ACCESS_AMOUNT,
(SELECT IFNULL(SUM(short_excess.`excess_amount`),0)+IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='3' AND DATE=:email)AS SHIFT3_ACCESS_AMOUNT,
(SELECT IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='1' AND DATE=:email)AS SHIFT1_DEBIT_AMOUNT,
(SELECT IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='2' AND DATE=:email)AS SHIFT2_DEBIT_AMOUNT,
(SELECT IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`='3' AND DATE=:email)AS SHIFT3_DEBIT_AMOUNT,
(SELECT IFNULL(SUM(monthly_pass.`amount`),0) FROM monthly_pass WHERE monthly_pass.`start_date`=:email)AS SHIFT1_PASS_AMOUNT
FROM transaction
WHERE transaction.`transaction_date`=:email";

    $query = $this -> conn -> prepare($sql);
    $query -> execute(array(':email' => $email));
    $data = $query -> fetchObject();

    if ($data) {
        $user["amount1"] = $data -> SHIFT1_AMOUNT;
        $user["amount2"] = $data -> SHIFT2_AMOUNT;
        $user["amount3"] = $data -> SHIFT3_AMOUNT;
        $user["manual1"] = $data -> SHIFT1_MANUALAMOUNT;
        $user["manual2"] = $data -> SHIFT2_MANUALAMOUNT;
        $user["manual3"] = $data -> SHIFT3_MANUALAMOUNT;
        $user["ticket1"] = $data -> SHIFT1_TICKETCANCEL;
        $user["ticket2"] = $data -> SHIFT2_TICKETCANCEL;
        $user["ticket3"] = $data -> SHIFT3_TICKETCANCEL;
        $user["samount1"] = $data -> TOTAL_SHIFT1AMOUNT;
        $user["samount2"] = $data -> TOTAL_SHIFT2AMOUNT;
        $user["samount3"] = $data -> TOTAL_SHIFT3AMOUNT;
        $user["total"] = $data ->TOTAL_AMOUNT ;
        $user["short1"] = $data -> SHIFT1_SHORT;
        $user["short2"] = $data -> SHIFT2_SHORT;
        $user["short3"] = $data -> SHIFT3_SHORT;
        $user["excess1"] = $data ->SHIFT1_ACCESS_AMOUNT ;
        $user["excess2"] = $data ->SHIFT2_ACCESS_AMOUNT ;
        $user["excess3"] = $data -> SHIFT3_ACCESS_AMOUNT;
        $user["pass1"] = $data -> SHIFT1_PASS_AMOUNT;

        return $user;

    } else {

        return false;

    }

 }


public function shiftReport($email, $password){
  $sql='SELECT
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2))) THEN transaction.`transaction_amount` END)),0) AS `systemamount`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (3,4))) THEN transaction.`transaction_amount` END)),0) AS `manualamount`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (5))) THEN transaction.`transaction_amount` END)),0) AS `ticketcancel`,
IFNULL(short_excess.`short_amount`,0) AS `col_short`,
IFNULL(short_excess.`excess_amount`,0)+IFNULL(short_excess.`debit_amount`,0) AS `col_acess`,
IFNULL(cashup.`total`,0) AS declarecash,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4))) THEN transaction.`transaction_amount` END)),0)+IFNULL(short_excess.`excess_amount`,0)+IFNULL(short_excess.`debit_amount`,0) AS `totalamount`
FROM transaction
LEFT JOIN cashup ON cashup.`date`=:email AND cashup.`shift`=:password
LEFT JOIN short_excess ON short_excess.`date`=:email AND short_excess.`shift`=:password
WHERE transaction.`transaction_date`=:email AND transaction.`shift_id`=:password';

    $query = $this -> conn -> prepare($sql);
    $query -> execute(array(':email' => $email, ':password' => $password));
    $data = $query -> fetchObject();

    if ($data) {
        $user["ssystemamount"] = $data -> systemamount;
        $user["smanual"] = $data -> manualamount;
        $user["sticketcancel"] = $data -> ticketcancel;
        $user["sshort"] = $data -> col_short;
        $user["sexcess"] = $data -> col_acess;
        $user["sdeclarecash"] = $data -> declarecash;
        $user["stotalamount"] = $data -> totalamount;

        return $user;

    } else {

        return false;

    }

 }


public function boothReport($name,$email, $password){
  $sql='SELECT toll_master.`companyname`,toll_master.`tollname`,
COUNT((CASE WHEN ((transaction.status IN (1,2))) THEN transaction.`transaction_id` END)) AS `count_system`,
SUM((CASE WHEN ((transaction.status IN (3,4))) THEN transaction.`manual_count` END)) AS `count_manual`,
COUNT((CASE WHEN ((transaction.status IN (5))) THEN transaction.`transaction_id` END)) AS `count_cancel`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2))) THEN transaction.`transaction_amount` END)),0) AS `amount_system`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (3,4))) THEN transaction.`transaction_amount` END)),0) AS `amount_manual`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (5))) THEN transaction.`transaction_amount` END)),0) AS `amount_cancel`,
IFNULL(SUM((CASE WHEN ((transaction.status IN (1,2,3,4))) THEN transaction.`transaction_amount` END)),0) AS `total_amount`,
(SELECT IFNULL(SUM(short_excess.`short_amount`),0) FROM short_excess WHERE `shift`=:email AND DATE=:name AND booth_no=:password)AS ShortAmount,
(SELECT IFNULL(SUM(short_excess.`excess_amount`),0) FROM short_excess WHERE `shift`=:email AND DATE=:name AND booth_no=:password)  AS AcessAmount,
(SELECT IFNULL(SUM(short_excess.`debit_amount`),0) FROM short_excess WHERE `shift`=:email AND DATE=:name AND booth_no=:password)AS DebitAmount
FROM toll_master
LEFT JOIN transaction ON transaction.transaction_date=:name AND transaction.shift_id=:email  AND transaction.booth_no=:password';

    $query = $this -> conn -> prepare($sql);
    $query -> execute(array(':name' => $name,':email' => $email, ':password' => $password));
    $data = $query -> fetchObject();

    if ($data) {
        $user["ssystemamount"] = $data -> amount_system;
        $user["smanual"] = $data -> amount_manual;
        $user["sticketcancel"] = $data -> amount_cancel;
        $user["sshort"] = $data -> ShortAmount;
        $user["sexcess"] = $data -> AcessAmount;
        $user["sdeclarecash"] = $data -> DebitAmount;
        $user["stotalamount"] = $data -> total_amount;

        return $user;

    } else {

        return false;

    }

 }




 public function changePassword($email, $password){


    $hash = $this -> getHash($password);
    $encrypted_password = $hash["encrypted"];
    $salt = $hash["salt"];

    $sql = 'UPDATE operator SET password = :password WHERE operator_id = :email';
    $query = $this -> conn -> prepare($sql);
    $query -> execute(array(':email' => $email, ':password' => $password));

    if ($query) {
        
        return true;

    } else {

        return false;

    }

 }

 public function checkUserExist($email){

    $sql = 'SELECT COUNT(*) from operator WHERE operator_id =:email';
    $query = $this -> conn -> prepare($sql);
    $query -> execute(array('email' => $email));

    if($query){

        $row_count = $query -> fetchColumn();

        if ($row_count == 0){

            return false;

        } else {

            return true;

        }
    } else {

        return false;
    }
 }

 public function getHash($password) {

     $salt = sha1(rand());
     $salt = substr($salt, 0, 10);
     $encrypted = password_hash($password.$salt, PASSWORD_DEFAULT);
     $hash = array("salt" => $salt, "encrypted" => $encrypted);

     return $hash;

}



public function verifyHash($password, $hash) {

    return password_verify ($password, $hash);
}
}




