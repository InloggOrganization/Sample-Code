# Accessing InLogg using PHP

`ecom_sample.py` contains a sample execution flow through different APIs provided 
by InLogg to E_Commerce.

Following are some of the important steps explained along with relevant code

## Authenticating user and getting token


This would generally be the first step in the flow of APIs. Upon successful 
authentication, a `token` and `id` is received, which needs to be used in all
future calls (unless the token expires).

```php
# Authentication parameters
$AUTH_KEY = 'AUTH_KEY';
$AUTH_USER = 'AUTH_USER';
$AUTH_PASS = 'AUTH_PASS';
$AUTH_TYPE = 'ecom';

response = http_get(create_get_url('http://api.inlogg.com/v1/authenticateUser', $authentication_params), array(), $info);
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
```

The token received in the above example is used in all future calls and passed as header parameters.

```php
# Authentication parameters required in header
$headers = array(
    'X-API-User-Token' => $token,
    'X-API-User-ID' => $user_id
);
```

## Creating Shipments

To create forward-shipment, you need to access `/createForwardShipment` endpoint. 
An example of shipment creation is given below. 

```php
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
```

## Updating Shipment Status

`POST /shipmentStatus` updates the status of the shipments provided through the `shipment_data` parameter. The parameter is expected to be an array of JSON objects, each of which contain the field `tracking_id` or `order_id` along with the `shipment_status_text`. See sample code below.

```php
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
```
