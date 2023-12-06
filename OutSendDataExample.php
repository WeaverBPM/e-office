<?php
/**
 * Send data to external system demo
 *
 * Outsend data to url address example： http://127.0.0.1:8010/eoffice/server/ext/data_out/data_outsend.php
 *
 * @author Weaver International
 *
 * @Date 06/12/2023
 */

// $_REQUEST Workflow outsend data，the data structure example in POST
/*
{
    // Database table id
    "id": "2",
    // Workflow id, unicode
    "run_id": "2313",
    // Format： DATA_(number)  mean the form control data, DATA_(Number) means control id
    "DATA_1": "Sysadmin",
    "DATA_2": "IT Department",
    "DATA_3": "Meeting title",
    "DATA_4": "",
    "DATA_5": "",
    "DATA_6": "2018-03-15",
    "DATA_7": "00:00",
    "DATA_8": "2018-03-16",
    "DATA_9": "00:00",
    // Control type: user selector, value is user id concat string
    "DATA_10": "WV00000009,WV00000002,WV00000001",
    // DATA_10 Control corresponding user name string, match with user id
    "DATA_10_TEXT": "Mike Tan,Sheily Wong,John David",
    "DATA_14": "Approval",
    // Countersign control, each element is a countersign content
    "DATA_15": [
        {
            // countersign_id
            "countersign_id": "384",
            // Content
            "countersign_content": "<p>Approve</p>",
            // countersign_user_id
            "countersign_user_id": "admin",
            // countersign_user complete information, user_id and username
            "countersign_user": {
                "user_id": "admin",
                "user_name": "System admin"
            },
            // countersign_time
            "countersign_time": "2018-03-15 14:49:33",
            // Workflow id
            "run_id": "2313",
            // Step id
            "process_id": "1",
            // Node id
            "flow_process": "709",
            // countersign_control_id
            "countersign_control_id": "DATA_15"
        }
    ],
    //Workflow name
    "run_name": "cesi-copy-Meeting application(system intetration)(2018-03-15 14:49:33:System admin)",
    // workflow id
    "flow_id": "3131",
    // Form id
    "form_id": "2008",
    // workflow node name
    "process_name": "Meeting application",
    // Current user information
    "userInfo": {
        // user id
        "user_id": "admin",
        // username
        "user_name": "System admin",
        // Account
        "user_accounts": "admin",
        // Department
        "dept_name": "IT Department",
        // Department id
        "dept_id": "25",
        // Role name, if multiple role, split by comma
        "role_name": "[OA Admin,Accounting]",
        // Role id，if multiple role, split by comma
        "role_id": "[1,37]"
    }
}
*/

/**
 * Default method，import laravel related environment, the path is relevant path, adjust base on actual case
 * __DIR__ value example： D:\e-office11\www\eoffice\server\ext\data_out
 * Complete path： D:\e-office11\www\eoffice\server\bootstrap\app.php
 */
require __DIR__ . '/../../bootstrap/app.php';
// Default, import database
use Illuminate\Support\Facades\DB;

// Via $_REQUEST method to get relevant data
$data = (isset($_REQUEST) && !empty($_REQUEST)) ? $_REQUEST : [];
// log file path： D:\e-office11\www\eoffice\server\storage\logs\
$logDir = base_path('/storage/') . 'logs/';
if(empty($data)) {
    // If there is not data, record error message in D:\e-office11\www\eoffice\server\storage\logs\licenseDownQuery_log.txt 
    file_put_contents($logDir."licenseDownQuery_log.txt","Data error, outsend failed",FILE_APPEND);
    exit();
}
// Get outsend data example
// workflow id
$run_id    = isset($data["run_id"]) ? $data["run_id"] : "";
// Workflow current node
$flow_prcs = isset($data["node_id"]) ? $data["node_id"] : "";
// Workflow step id
$prcs_id   = isset($data["process_id"]) ? $data["process_id"] : "";

// Concat parameter base on business requirements
// license identification code, relevant control id DATA_100, other control is the same
$serverCode = isset($data["DATA_100"]) ? $data["DATA_100"] : "";
$serverCode = trim($serverCode);
// Get customer id
$customerId = isset($data["DATA_101"]) ? $data["DATA_101"] : "";
// Based on customer id，get customer information in customer table
$customerInfo = DB::select("select * from customer where customer_id = '".$customerId."'");
$customerInfo = json_decode(json_encode($customerInfo),true);
if(count($customerInfo) && !empty($customerInfo[0])) {
    $customerInfo = $customerInfo[0];
}
// Customer name
$customerName = isset($customerInfo["customer_name"]) ? $customerInfo["customer_name"] : "";
if($customerName == "") {
    // Exception, record error message
    file_put_contents($logDir."licenseDownQuery_log.txt","Customer name is black，outsend faild；workflow id：".$run_id."。",FILE_APPEND);
    exit();
}
// Server name with customer name
$serverName = $customerName;
// license file end date
$expireDate = isset($data["DATA_102"]) ? $data["DATA_102"] : "";
if(!$expireDate || $expireDate == "")
    $expireDate = "2115-05-25";
// Server address
$serverIp = isset($data["DATA_103"]) ? $data["DATA_103"] : "";
// port
$serverPort = isset($data["DATA_104"]) ? $data["DATA_104"] : "";
// Server key word eoffice
$serverKey = "Weaver eoffice";
// license Mobile concurrent uer license
$maxUserOnlineCount = "";
// license user license
$maxUserCount = isset($data["DATA_105"]) ? $data["DATA_105"] : "";
$maxUserCount = trim($maxUserCount);
if(!$maxUserCount || $maxUserCount == "")
    $maxUserCount = "0";
// other parameter
$isuseplate = 0;
// Mobile relevant product
$pluginType = "1";
// Whether control concurrent
$concurrentFlag = "0";
// Client type--iphone,ipad,android,webclient
$clients = "iphone,ipad,android,webclient";
// Module, default value
$modules = "1,2,3,4,5,6,7,8,9,10,11,13";
// generate business logical url
$licenseUrl = "http://127.0.0.1:1000/server/createLicense.do?serverCode=".$serverCode."&serverName=".implode(",",unpack('c*', $serverName))."&customerName=".implode(",",unpack('c*', $customerName))."&expireDate=".$expireDate."&serverIp=".implode(",",unpack('c*', $serverIp))."&serverPort=".implode(",",unpack('c*', $serverPort))."&serverKey=".implode(",",unpack('c*', $serverKey))."&maxUserOnlineCount=".$maxUserOnlineCount."&maxUserCount=".$maxUserCount."&isuseplate=".$isuseplate."&pluginType=".$pluginType."&concurrentFlag=".$concurrentFlag."&clients=".$clients."&modules=".$modules."";
// Write url in log
file_put_contents($logDir."licenseDownQuery_log.txt",$licenseUrl."@*@",FILE_APPEND);
// get data from url, can change to http request
$licenseReturnString = file_get_contents($licenseUrl);
// Return result to log
file_put_contents($logDir."licenseDownQuery_log.txt",$licenseReturnString."@*@",FILE_APPEND);
// Check return result
$licenseReturnArray = explode(";", $licenseReturnString);
if(count($licenseReturnArray) > 0 && $licenseReturnArray[0] == "true") {
    // get licenseId
    $licenseId = $licenseReturnArray[1];
    // Generate feedback
    $feedbackInfo = [
        "content"      => '<a href="http://127.0.0.1/server/getLicense.do?id='.$licenseId.'" style="font-family: Arial; line-height: 12px; white-space: normal;" target="_blank">Succeefully, please click to download license</a>',
        "flow_process" => $flow_prcs,
        "process_id"   => $prcs_id,
        "run_id"       => $run_id,
        "user_id"      => $user_id,
    ];
    // Logs
    file_put_contents($logDir."licenseDownQuery_log.txt",json_encode($feedbackInfo)."\r\n",FILE_APPEND);
    // Call service : FlowService ，inser feedback data
    app('App\EofficeApp\Flow\Services\FlowService')->createFlowFeedbackService($feedbackInfo);
}

?>
