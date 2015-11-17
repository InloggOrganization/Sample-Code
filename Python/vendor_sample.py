#!/usr/bin/python
#
# Author : Rajat Khanduja
#
# A sample flow for logistics vendor with InLogg APIs


import requests
import json
import sys
import urllib

# Authentication parameters [TODO: Update these before executing script]
AUTH_KEY = 'YOUR_AUTH_KEY'
AUTH_USER = 'YOUR_USER'
AUTH_PASS = 'YOUR_PASSWORD'
AUTH_TYPE = 'vendor'

# Constants related to the API calls
BASE_URL = 'http://api.inlogg.com/v1'
AUTHENTICATE_PATH = '/authenticateUser'
GET_SHIPMENTS_PATH = '/getShipments'
SHIPMENT_STATUS_PATH = '/shipmentStatus'
SHIPMENT_RATE_PATH = '/getShipmentRate'

# Authenticate user and get token
print("Getting access token by authenticating ....")
authentication_params = {
    'auth_key' : AUTH_KEY,
    'auth_user' : AUTH_USER,
    'auth_pass' : AUTH_PASS,
    'auth_type' : AUTH_TYPE
}
response = requests.get(BASE_URL + AUTHENTICATE_PATH, authentication_params)
if response.ok:
    json_response = json.loads(response.text)
    token = json_response[0]['token']
    user_id = json_response[0]['id']
    print("Authentication successful. token : " + token + " user_id : " + user_id)
else:
    if response.status_code == 401:
        print("Authentication error")
    elif response.status_code == 412:
        print("Not all inputs provided")
    else: 
        print(response.text)
    print("Exiting because no token received for further requests")
    sys.exit(1)

# Authentication parameters required in header
headers = {
    'X-API-User-Token': token,
    'X-API-User-ID': user_id
}

# Fetching shipments
print("Fetching shipments...")
response = requests.get(BASE_URL + GET_SHIPMENTS_PATH, headers=headers)
if response.ok:
    print(response.text)
    shipment_data = json.loads(response.text)[0]['shipment_data']
    print(shipment_data)
else:
    print(response.text)
    if response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 404:
        print("No shipment data found")
    else:
        print(response.text)


# Getting shipment status
print("Getting shipment status")
shipment_data = [
        {
            "tracking_id":"GR11098787"
        }]
parameters = {
        'shipment_data': json.dumps(shipment_data) 
        }
print(parameters)
response = requests.get(BASE_URL + SHIPMENT_STATUS_PATH, parameters, headers=headers)
if response.ok:
    statuses = json.loads(response.text)
    print(statuses)
else:
    print(response.text)
    if response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 404:
        print("No matching records found")
    else:
        print(response.text)

# Post shipment status
print("Posting shipment status")
shipment_data = [
        {
            "tracking_id": "GR11098787",
            "shipment_status_text": "forward_confirmed"
        }]
parameters = {
        'shipment_data': shipment_data
}
response = requests.post(BASE_URL + SHIPMENT_STATUS_PATH, parameters, headers=headers)
if response.ok:
    print("Successfully updated")
else:
    print(response.text)
    if response.status_code == 404:
        print("No matching record found")
    elif response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 412:
        print("Insufficient parameters passed")
    elif response.status_code == 400:
        print("Incorrect format or status not matching")
    else:
        print("Failure")

# Accessing the Rate card
print("Accessing rate card")
request_data = [
            {
                'dead_weight': '500',   # dead weight in grams
                'payment_mode': 'COD',  # 'COD' or 'PP'
                'src_pincode': '560068',
                'dest_pincode': '560037'
            }] 
parameters = {
        'request_data': json.dumps(request_data) 
        }
response = requests.get(BASE_URL + SHIPMENT_RATE_PATH, parameters, headers=headers)
if response.ok:
    rates = json.loads(response.text)
    print(rates)
else:
    print(response.text)
    if response.status_code == 401:
        print("Authentication failure")
    elif response.status_code == 412:
        print("Insufficient parameters passed")
    else:
        print("Failure")

