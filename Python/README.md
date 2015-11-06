# Accessing InLogg using Python

`vendor_sample.py` contains a sample execution flow through different APIs provided 
by InLogg to logistics vendors.

Following are some of the important steps explained along with relevant code

## Authenticating user and getting token


This would generally be the first step in the flow of APIs. Upon successful 
authentication, a `token` and `id` is received, which needs to be used in all
future calls (unless the token expires).

```python
import requests 
import json

authentication_params = {
    'auth_key' : '<auth_key>',
    'auth_user' : '<auth_user>',
    'auth_pass' : '<auth_pass>',
    'auth_type' : 'vendor'
}
response = requests.get('http://api.inlogg.com/v1/authenticateUser', authentication_params)
if response.ok:
    # Authentication successful
    json_response = json.loads(response.text)
    token = json_response[0]['token']
    user_id = json_response[0]['id']
else:
    if response.status_code == 401:
        print("Authentication error")
    elif response.status_code == 412:
        print("Not all inputs provided")
    else: 
        print(response.text)

```

The token received in the above example is used in all future calls and passed as header parameters.

```python
# Authentication parameters required in header
headers = {
    'X-API-User-Token': token,
    'X-API-User-ID': user_id
}
```

## Getting Shipments

To get the shipments assigned to you (identified by the `X-API-User-Token` and `X-API-User-ID` in the header), you'd need to access the `/getShipments` method. 

```python
response = requests.get('http://api.inlogg.com/v1/getShipments', headers=headers)
if response.ok:
    shipment_data = json.loads(response.text)[0]['shipment_data']
else:
    if response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 404:
        print("No shipment data found")
    else:
        print(response.text)
```

This calls takes an optional GET parameter `shipments_from` (string) that makes the API list only shipments after a particular time. The expected format for the parameter is `YYYY-MM-DD HH:MM:SS` (eg, `2015-09-28 10:13:12`)

## Getting Shipment Status

`GET /shipmentStatus` returns the status of the shipments provided through the `shipment_data` parameter. The parameter is expected to be an array of JSON objects, each of which contain the field `tracking_id` or `order_id`.

```python
shipment_data = [
        {
            "tracking_id":"GR11098787"
        }]
parameters = {
        'shipment_data': json.dumps(shipment_data) 
        }

response = requests.get('http://api.inlogg.com/v1/shipmentStatus', parameters, headers=headers)
if response.ok:
    statuses = json.loads(response.text)
else:
    if response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 404:
        print("No matching records found")
    else:
        print(response.text)
```