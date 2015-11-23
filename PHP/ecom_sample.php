<?php
/**
 * Author: Rajat Khanduja
 * Date: 11/8/15
 *
 */


# Authentication parameters
$AUTH_KEY = 'AUTH_KEY';
$AUTH_USER = 'AUTH_USER';
$AUTH_PASS = 'AUTH_PASS';
$AUTH_TYPE = 'ecom';

# Constants related to the API calls 
$BASE_URL = 'http://api.inlogg.com/v1';
$AUTHENTICATE_PATH = '/authenticateUser';
$CREATE_FORWARD_SHIPMENT_PATH = '/createForwardShipment';
$SHIPMENT_STATUS_PATH = '/shipmentStatus';
$RATE_CARD_PATH = '/getShipmentRate';

/**
 * @param $url string The resource endpoint to be queried
 * @param $parameters array GET parameters
 * @return string
 */
function create_get_url($url, $parameters) {
    $query_string = "";
    foreach($parameters as $key => $val){
        if (strlen($query_string) > 0) {
            $query_string .= '&';
        }
        $query_string .= $key . '=' . $val;
    }
    print("Constructed GET call : " . $url . '?'. $query_string . "\n");
    return $url . '?'. $query_string;
}

# Authenticate user and get token
print_r("Getting authentication token\n");
$authentication_params = array(
    'auth_key' => $AUTH_KEY,
    'auth_user' => $AUTH_USER,
    'auth_pass' => $AUTH_PASS,
    'auth_type' => $AUTH_TYPE
);
$response = http_get(create_get_url($BASE_URL . $AUTHENTICATE_PATH, $authentication_params), array(), $info);
$msg_body = http_parse_message($response)->body;
if ($info['response_code'] == 200) {
    $json_response = json_decode($msg_body);
    $token = $json_response[0]->token;
    $user_id = $json_response[0]->id;
    print("Authentication token : $token ; id : $user_id \n");
} else {
    if ($info['response_code'] == 401) {
        print("Authentication error\n");
    } else if ($info['response_code' == 412]) {
        print("Not all inputs are provided\n");
    } else {
        print($msg_body . '\n');
    }
    print("No token received from authentication. Exiting...\n");
    exit(1);
}

# Authenticate parameters in headers
$headers = array(
    'X-API-User-Token' => $token,
    'X-API-User-ID' => $user_id
);

# Create forward shipments
print("Creating forward shipment\n");
$shipment_data = array(
    array(
        "tracking_id" => "123456",
        "weight" => "100",
        "source_pincode" => "12356",
        "destination_pincode" => "23422",
        "buyer_name" => "Chinu",
        "buyer_phone" => "8971923193",
        "seller_name" => "Nokia",
        "seller_phone" => "1312313131",
        "src_address" => "1st street",
        "src_city" => "chennai",
        "src_state" => "TN",
        "dest_address" => "adsaa",
        "dest_city" => "Bangalore",
        "dest_state" => "Karnataka",
        "payment_mode" => "COD",
        "value" => "1110",
        "amount" => "120",
        "content_type" => "mobile phone",
        "qty" => "2",
        "vol_weight" => "",
        "length" => "10",
        "breadth" => "10",
        "height" => "10",
        "comments" => "TestForwardApi",
        "customer_pickup_sla" => "22/12/2015",
        "order_id" => "CHK123"
    )
);
$params = array('shipment_data' => json_encode($shipment_data));
$response = http_post_fields($BASE_URL . $CREATE_FORWARD_SHIPMENT_PATH, $params, null, array('headers' => $headers),  $info);
$msg_body = http_parse_message($response)->body;
switch($info['response_code']) {
    case 200:
        print_r(json_decode($msg_body));
        break;
    case 401:
        print("Unauthorized access\n");
        break;
    case 412:
        print("Mandatory inputs are missing\n");
        break;
    default:
        print($msg_body . "\n");
}

# Post shipment status
print("Updating shipment status");
$shipment_data = array(
    array(
        'tracking_id' => '123456',
        'shipment_status_text' => 'forward_confirmed'
    )
);
$params = array('shipment_data' => json_encode($shipment_data));
$response = http_post_fields($BASE_URL . $SHIPMENT_STATUS_PATH, $params, null, array('headers' => $headers), $info);
$msg_body = http_parse_message($response)->body;
switch($info['response_code']) {
    case 200:
        print($msg_body . "\n");
        break;
    case 400:
        print("Invalid status text or format\n");
        break;
    case 401:
        print("Unauthorized\n");
        break;
    case 404:
        print("No matching records\n");
        break;
    default:
        print($msg_body . "\n");
        break;
}



# Get Shipment Status
print("Get shipment status");
$request_data = array(
    array(
        'tracking_id' => '500123'
    )
);
$params = array('request_data' => json_encode($request_data));
$response = http_post_fields($BASE_URL . $SHIPMENT_STATUS_PATH, $params, null, array('headers' => $headers), $info);
$msg_body = http_parse_message($response)->body;
switch($info['response_code']) {
    case 200: //success
        print($msg_body . "\n");
        $json_response = json_decode($msg_body);
        $current_status = $json_response[0]->current_status;
        $previous_statuses = $json_response[0]->previous_statuses;
        break;
    case 400:
        print("Invalid format\n");
        break;
    case 412:
        print("Pre condition failed or mandatory input missing\n");
        break;
    case 404:
        print("Tracking_id or order_id is not found");
    default:
        print($msg_body . "\n");
        break;
}


# Get Shipment Rate - Rate card API
print("Get shipment rate");
$request_data = array(
    array(
        'dead_weight' => '500', //In grams
        'payment_mode' => 'PP',
        'src_pincode' => '600123',
        'dest_pincode' => '500123'
    )
);
$params = array('request_data' => json_encode($request_data));
$response = http_post_fields($BASE_URL . $RATE_CARD_PATH, $params, null, array('headers' => $headers), $info);
$msg_body = http_parse_message($response)->body;
switch($info['response_code']) {
    case 200: //success
        print($msg_body . "\n");
        $json_response = json_decode($msg_body);
        $rate = $json_response[0]->rate;
        break;
    case 400:
        print("Invalid format\n");
        break;
    case 412:
        print("Pre condition failed or mandatory input missing\n");
        break;
    default:
        print($msg_body . "\n");
        break;
}

