<?php
require_once '../model/PollutionSensor.php';
require_once '../model/SensorsInRoom.php';
require_once '../model/UserRoomsLoad.php';
require_once '../model/SensorForGraph.php';

function deliver_response($format, $api_response, $isSaveQuery) {

    // Define HTTP responses
    $http_response_code = array(200 => 'OK', 400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found');

    // Set HTTP Response
    header('HTTP/1.1 ' . $api_response['status'] . ' ' . $http_response_code[$api_response['status']]);

    // Process different content types
    if (strcasecmp($format, 'json') == 0) {

        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');

        // Format data into a JSON response
        $json_response = json_encode($api_response);
        
        // Deliver formatted data
        echo $json_response;

    } elseif (strcasecmp($format, 'xml') == 0) {

        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');

        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<response>' . "\n" . "\t" . '<code>' . $api_response['code'] . '</code>' . "\n" . "\t" . '<data>' . $api_response['data'] . '</data>' . "\n" . '</response>';

        // Deliver formatted data
        echo $xml_response;

    } else {

        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');

        // Deliver formatted data
        echo $api_response['data'];

    }

    // End script process
    exit ;

}

// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;

// Define whether user authentication is required
$authentication_required = FALSE;

// Define API response codes and their related HTTP response
$api_response_code = array(0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'), 1 => array('HTTP Response' => 200, 'Message' => 'Success'), 2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'), 3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'), 4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'), 5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'), 6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format'));

// Set default HTTP response of 'ok'
$response['code'] = 0;
$response['status'] = 404;

// --- Step 2: Authorization

// Optionally require connections to be made via HTTPS
if ($HTTPS_required && $_SERVER['HTTPS'] != 'on') {
    $response['code'] = 2;
    $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
    $response['data'] = $api_response_code[$response['code']]['Message'];

    // Return Response to browser. This will exit the script.
    deliver_response($_GET['format'], $response);
}

// Optionally require user authentication
if ($authentication_required) {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $response['code'] = 3;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $response['data'] = $api_response_code[$response['code']]['Message'];

        // Return Response to browser
        deliver_response($_GET['format'], $response);

    }

    // Return an error response if user fails authentication. This is a very simplistic example
    // that should be modified for security in a production environment
    elseif ($_POST['username'] != 'foo' && $_POST['password'] != 'bar') {
        $response['code'] = 4;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $response['data'] = $api_response_code[$response['code']]['Message'];

        // Return Response to browser
        deliver_response($_GET['format'], $response);

    }

}

// --- Step 3: Process Request

// Switch based on incoming method

if (isset($_POST['method'])) {

}
else if (isset($_GET['method'])) {
    if (strcasecmp($_GET['method'], 'showSensorDataPoints') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $fetchSensorDetails = new PollutionSensor();
        $response['showPollutionSensorDetailsResponse'] = $fetchSensorDetails -> showingPollutionSensorDetails();
        deliver_response($_GET['format'], $response, false);
    }
    else if (strcasecmp($_GET['method'], 'showSensorDataPointsAsPerDateRange') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $fromDate = $_GET['fromDate'];
        $toDate = $_GET['toDate'];
        $fetchSensorDetails = new PollutionSensor();
        $response['showPollutionSensorDetailsResponse'] = $fetchSensorDetails -> showingPollutionSensorDetailsAsPerDateRange($fromDate, $toDate);
        deliver_response($_GET['format'], $response, false);
    }
	else if (strcasecmp($_GET['method'], 'roomWiseSensors') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $roomno = $_GET['roomno'];
        $email= $_GET['email'];
        $fetchSensorDetails = new SensorsInRoom();
        $response['showRoomWiseSensorsResponse'] = $fetchSensorDetails -> showingSensorDetailsAsPerRoom($roomno, $email);
        deliver_response($_GET['format'], $response, false);
    }
	else if (strcasecmp($_GET['method'], 'showSuggessions') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $roomno = $_GET['roomno'];
        $email= $_GET['email'];
        $fetchSuggessionDetails = new SensorsInRoom();
        $response['showSuggessionsResponse'] = $fetchSuggessionDetails -> showingSuggessionDetailsAsPerRoom($roomno, $email);
        deliver_response($_GET['format'], $response, false);
    }
	/*else if (strcasecmp($_GET['method'], 'sensorStatus') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $valueOfSensor = $_GET['valueOfSensor'];
        $nameOfSensor= $_GET['nameOfSensor'];
        $fetchSensorStatus = new SensorsInRoom();
        $response['showStatusResponse'] = $fetchSensorStatus -> showingSensorStatusAsPerRoom($valueOfSensor, $nameOfSensor);
        deliver_response($_GET['format'], $response, false);
    }*/
	if (strcasecmp($_GET['method'],'userRooms')==0) {
		$response['code']=1;
		$response['status']=$api_response_code[$response['code']]['HTTP Response'];
		$email= $_GET['email'];
		$fetchRooms=new UserRoomsLoad();
		$response['loadRooomsList']=$fetchRooms -> loadUserRooms($email);
		deliver_response($_GET['format'], $response,false);
	}
	else if (strcasecmp($_GET['method'], 'showGraphOfSensor') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
        $fromDate = $_GET['fromDate'];
        $toDate = $_GET['toDate'];
		$sensName = $_GET['nameOfSensor'];
		$email = $_GET['email'];
		$roomno = $_GET['roomno'];
        $fetchSensorDetails = new SensorForGraph();
        $response['showSensorDetailsResponse'] = $fetchSensorDetails -> showingSensorPointForGraph($fromDate, $toDate, $sensName, $email, $roomno);
        deliver_response($_GET['format'], $response, false);
    }
	else if (strcasecmp($_GET['method'], 'defaultSensorGraph') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
		$sensName = $_GET['nameOfSensor'];
		$email = $_GET['email'];
		$roomno = $_GET['roomno'];
        $fetchSensorDetails = new SensorForGraph();
        $response['showSensorDetailsResponse'] = $fetchSensorDetails -> showingSensorGraphDetails($sensName, $email, $roomno);
        deliver_response($_GET['format'], $response, false);
    }
	else if (strcasecmp($_GET['method'], 'pointRangeForYAxis') == 0) {
        $response['code'] = 1;
        $response['status'] = $api_response_code[$response['code']]['HTTP Response'];
		$sensName = $_GET['nameOfSensor'];
        $fetchSensorPointsDetails = new SensorForGraph();
        $response['showSensorPointsResponse'] = $fetchSensorPointsDetails -> showingPointRangeForYAxis($sensName);
        deliver_response($_GET['format'], $response, false);
    }
}
?>