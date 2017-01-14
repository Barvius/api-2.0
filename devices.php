<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

//include './class/gpio.php';
include './class/system.php';
include './class/db.php';
include './config.php';
$DB = New DB();
//$Gpio = New Gpio();
//$System = New System();


//$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
if ($DB->CheckTooken()) {
  //основной иф авторизации
  if (isset($_GET['system'])) {
    switch ($_GET['system']) {
      case 'functionality':
        $DB->GetFunctionality();
        break;

      default:
        # code...
        break;
    }
  };
// тут ооп
  if (isset($_GET['gpio']) and $DB->CheckFunctionality('gpio')) {
    switch ($_GET['gpio']) {
      case 'set_atr':
        if (isset($_GET['port'])) {
          if($DB->GetUserLevel == 0){
            $DB->GpioSetAtribute($_GET['port'],$_POST['name'],$_POST['functionality']);
          } else {
            System::JsonInput(403);
          }
        }
        break;
      case 'list':
        $DB->GpioList();
        break;
      case 'st':
        if (isset($_GET['port'])) {
          System::GpioRead($_GET['port']);
        }
        break;
      case 'on':
        if (isset($_GET['port'])) {
          $DB->GpioLogWrite($_GET['port'], 'on');
        }
        break;
      case 'off':
        if (isset($_GET['port'])) {
          $DB->GpioLogWrite($_GET['port'], 'off');
        }
        break;
      case 'log':
        if (isset($_GET['port'])) {
          $DB->GpioLogRead($_GET['port']);
        }
        break;
      default:
        System::JsonInput(404);
        break;
    };
  };

  if (isset($_GET['sensor']) and $DB->CheckFunctionality('sensors')) {
    switch ($_GET['sensor']) {
      case 'set_atr':
        if (isset($_GET['id'])) {
          if($DB->GetUserLevel == 0){
            $DB->SensorSetAtribute($_GET['id'],$_POST['name'],$_POST['prefix'],$_POST['enable']);
          } else {
            System::JsonInput(403);
          }
        } else {
          System::JsonInput(400);
        }
        break;
      case 'list':
        $DB->SensorList();
        break;
      default:
        System::JsonInput(404);
        break;
    };
  };

  if (isset($_GET['get']) and isset($_GET['period']) and check_functionality('sensors')) {
    if (check_dev()) {
      switch ($_GET['period']) {
        case 'all':
          $mysql_results = $pdo->query("SELECT {$_GET['get']} ,date FROM `stats` order by `id`");
          while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array( ($rr['date']+10800)*1000, $rr[$_GET['get']]);
          }
          json_input(200, array('id' => $_GET['get'], 'count'=>$mysql_results->rowCount(), 'data' => $data));
          break;
        case '1m':
          $mysql_results = $pdo->query("SELECT {$_GET['get']} ,date FROM `stats` order by `id` desc limit 8640");
          while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array( ($rr['date']+10800)*1000, $rr[$_GET['get']]);
          }
          json_input(200, array('id' => $_GET['get'], 'data' => $data));
          break;
        case '1w':
          $mysql_results = $pdo->query("SELECT {$_GET['get']} ,date FROM `stats` order by `id` desc limit 2016");
          while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array( ($rr['date']+10800)*1000, $rr[$_GET['get']]);
          }
          json_input(200, array('id' => $_GET['get'], 'data' => $data));
          break;
        case '1d':
          $mysql_results = $pdo->query("SELECT {$_GET['get']} ,date FROM `stats` order by `id` desc limit 288");
          while($rr = $mysql_results->fetch(PDO::FETCH_ASSOC)) {
            $data[] = array( ($rr['date']+10800)*1000, $rr[$_GET['get']]);
          }
          json_input(200, array('id' => $_GET['get'], 'data' => $data));
          break;
        case 'now':
          $mysql_results = $pdo->query("SELECT {$_GET['get']} FROM `stats` WHERE id=(SELECT MAX(id) FROM stats)");
          $rr = $mysql_results->fetch(PDO::FETCH_ASSOC);
          json_input(200, array('id' => $_GET['get'], 'data' => $rr[$_GET['get']]));
          break;
        default:
          System::JsonInput(404);
          break;
      }
    }
  };
  //основной иф авторизации
  }

?>
