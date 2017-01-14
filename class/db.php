<?php
/**
 * db
 */
 class DB {
   private $dbServer;
   private $dbUser;
   private $dbPassword;
   private $dbName;
   private $timezone;

   public $pdo;

   public function __construct(){
     $this->dbServer = dbServer;
     $this->dbUser = dbUser;
     $this->dbPassword = dbPassword;
     $this->dbName = dbName;
     $this->pdo = new PDO("mysql:host=$this->dbServer;dbname=$this->dbName", $this->dbUser, $this->dbPassword);
   }

   public function __destruct(){
     $this->pdo = null;
   }

   public function CheckTooken(){
     if (isset($_GET['tooken'])) {
       $mysql_results = $this->pdo->prepare("SELECT * FROM users WHERE tooken=:tooken");
       $mysql_results->bindParam(':tooken', $_GET['tooken']);
       $mysql_results->execute();
       if($mysql_results->fetch(PDO::FETCH_ASSOC)){
         return 1;
       } else {
         System::JsonInput(403);
       }
     } else {
       System::JsonInput(404);
     }
   }

   public function GetUserLevel(){
     $mysql_results = $this->pdo->prepare("SELECT * FROM users WHERE tooken=:tooken");
     $mysql_results->bindParam(':tooken', $_GET['tooken']);
     $mysql_results->execute();
     $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
     return $rr['user_level'];
   }

   public function GetUserName(){
     $mysql_results = $this->pdo->prepare("SELECT * FROM users WHERE tooken=:tooken");
     $mysql_results->bindParam(':tooken', $_GET['tooken']);
     $mysql_results->execute();
     $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
     return $rr['name'];
   }

   public function CheckDevice() {
     $mysql_results = $this->pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'stats' AND table_schema = 'mh' AND column_name LIKE '%\_%'");
     while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
       $sens_list[] = $rr['COLUMN_NAME'];
     }
     if (in_array($_GET['get'], $sens_list)) {
       return 1;
     }
   }

   public function CheckFunctionality($value){
     $mysql_results = $this->pdo->query("SELECT * FROM sys_functionality WHERE id = 1");
     $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
     return $rr[$value];
   }

   public function GetFunctionality(){
     $mysql_results = $this->pdo->query("SELECT * FROM sys_functionality WHERE id = 1");
     $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
     System::JsonInput(200, $rr);
   }

   public function GpioLogWrite($port,$action){
     $mysql_results = $this->pdo->prepare("INSERT INTO `gpio_logs`(`gpio_num`, `time`, `ip`, `user`, `action`) VALUES (:gpio_num, :time, :ip, :user, :action)");
     $mysql_results->bindParam(':gpio_num', $port);
     $mysql_results->bindParam(':time', date_timestamp_get(date_create()));
     $mysql_results->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
     $mysql_results->bindParam(':user',  $this->GetUserName());
     $mysql_results->bindParam(':action',  $action);
     $mysql_results->execute();
     System::JsonInput(200);
   }

   public function GpioLogRead($port){
     $mysql_results = $this->pdo->prepare("SELECT * FROM `gpio_logs` where `gpio_num`=:gpio_num limit 15");
     $mysql_results->bindParam(':gpio_num', $port);
     $mysql_results->execute();
     while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
       $data[] = array($rr['time'],$rr['ip'],$rr['user'],$rr['action']);
     }
     System::JsonInput(200,$data);
   }

   public function GpioSetAtribute($port,$name,$functionality){
     $mysql_results = $this->pdo->prepare("UPDATE `gpio_aliases` SET `name`=:name, `functionality`=:functionality WHERE port=:port");
     $mysql_results->bindParam(':port', $port);
     $mysql_results->bindParam(':name', $name);
     $mysql_results->bindParam(':functionality', $functionality);
     $mysql_results->execute();
     System::JsonInput(200);
   }

   public function GpioList(){
     $mysql_results = $this->pdo->query("SELECT * FROM `gpio_aliases`");
     while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
       $data[] = $rr['port'];
       $name[$rr['port']] = array($rr['name'],$rr['functionality']);
     }
     System::JsonInput(200,array('id' => $data, 'atr'=> $name));
   }

   public function SensorSetAtribute($id,$name,$prefix,$enable){
     $mysql_results = $this->pdo->prepare("SELECT `id` FROM `name_aliases` WHERE `sensor_id`=:sensor_id");
     $mysql_results->bindParam(':sensor_id', $id);
     $mysql_results->execute();
     $id = $mysql_results->fetch(PDO::FETCH_ASSOC);
     if(!$id) {
       $mysql_results = $this->pdo->prepare("INSERT INTO `name_aliases`(`sensor_id`, `name`, `prefix`, `enable`) VALUES (:sensor_id, :name, :prefix, :enable)");
       $mysql_results->bindParam(':sensor_id', $id);
       $mysql_results->bindParam(':name', $name);
       $mysql_results->bindParam(':prefix', $prefix);
       $mysql_results->bindParam(':enable', $enable);
       $mysql_results->execute();
       System::JsonInput(200);
     } else {
       $mysql_results = $this->pdo->prepare("UPDATE `name_aliases` SET `sensor_id`=:sensor_id,`name`=:name, `prefix`=:prefix, `enable`=:enable WHERE id=:id");
       $mysql_results->bindParam(':id', $id['id']);
       $mysql_results->bindParam(':sensor_id', $id);
       $mysql_results->bindParam(':name', $name);
       $mysql_results->bindParam(':prefix', $prefix);
       $mysql_results->bindParam(':enable', $enable);
       $mysql_results->execute();
       System::JsonInput(200);
     }
   }

   public function SensorList(){
     $mysql_results = $this->pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'stats' AND table_schema = 'mh' AND column_name LIKE '%\_%'");
     while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
       $data[] = $rr['COLUMN_NAME'];
     }
     $mysql_results = $this->pdo->query("SELECT * FROM `name_aliases`");
     while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
       $name[$rr['sensor_id']] = array($rr['name'],$rr['prefix'],$rr['enable']);
     }
      System::JsonInput(200,array('id' => $data, 'atr'=> $name));
   }
 }

 ?>
