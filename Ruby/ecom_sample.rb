# Sample code for using InLogg Ecommerce APIs
# Please change the authentication parameters before executing

require 'rest-client'
require 'open-uri'

AUTH_KEY = 'insert_your_auth_key'
AUTH_USER = 'insert_your_auth_user'
AUTH_PASS = 'insert_your_auth_password'

# This is required because RestClient assumes that headers are case-insensitive
class CaseSensitiveString < String
    def downcase
        self
    end
    def capitalize
        self
    end
end

class InLoggClient
    # Wrapper class to work with InLogg APIs
     
    BASE_URL = 'http://api.inlogg.com'
    VERSION = 'v1'
    AUTHENTICATE_PATH = '/authenticateUser'
    SHIPMENT_RATE_PATH = '/getShipmentRate'
    CREATE_FORWARD_SHIPMENT_PATH = '/createForwardShipment'
    SHIPMENT_STATUS_PATH = '/shipmentStatus'
    SHIPMENT_STATUS_CHANGES_PATH = '/shipmentStatusChanges'

    public 
    def initialize(authKey, authUser, authPass, authType)
        @authKey = authKey
        @authUser = authUser
        @authPass = authPass
        @authType = authType        
    end

    def authenticateUser
        url = "#{BASE_URL}/#{VERSION}#{AUTHENTICATE_PATH}"
        params_string = "auth_key=#{@authKey}&auth_user=#{@authUser}&auth_pass=#{@authPass}&auth_type=#{@authType}"
        response = RestClient.get "#{url}?#{params_string}", {:accept => 'application/json'}
        
        returnedMessage = JSON.parse(response)
        @token = returnedMessage[0]["token"]
        @userId = returnedMessage[0]["id"]
        @headers = { 
            CaseSensitiveString.new('X-API-User-Token') => @token,
            CaseSensitiveString.new('X-API-User-ID') => @userId,
            'Accept' => "application/json"
        }
        puts "Authentication successful. token : #{@token}, user_id : #{@userId}"
    end

    def createShipment(shipmentData)
        url = "#{BASE_URL}/#{VERSION}#{CREATE_FORWARD_SHIPMENT_PATH}"
        params = {
            "shipment_data" => shipmentData.to_json
        }
        begin
            response = RestClient.post url, params, @headers
            returnedMessage = JSON.parse(response)
            return returnedMessage[0]['tracking_id']
        rescue Exception => e
            puts "Couldn't create shipment"
            puts e.response.body
            return Nil
        end
    end

    def getShipmentRate(requestData)
        url = "#{BASE_URL}/#{VERSION}#{SHIPMENT_RATE_PATH}"
        queryString = "request_data=#{URI::encode(requestData.to_json)}"
        response = RestClient.get "#{url}?#{queryString}", @headers
        return JSON.parse(response)
    end

    def getShipmentStatus(shipmentData)
        url = "#{BASE_URL}/#{VERSION}#{SHIPMENT_STATUS_PATH}"
        queryString = "shipment_data=#{URI::encode(shipmentData.to_json)}"
        response = RestClient.get "#{url}?#{queryString}", @headers
        return JSON.parse(response)
    end
    
    # Method to get changes to shipment status between startTime and endTime (both inclusive)
    # Time has to be given as epoch time
    def getShipmentStatusChanges(startTime, endTime)
        url = "#{BASE_URL}/#{VERSION}#{SHIPMENT_STATUS_CHANGES_PATH}"
        queryString = "start_time=#{startTime}&end_time=#{endTime}"
        response = RestClient.get "#{url}?#{queryString}", @headers
        return JSON.parse(response)
    end
end


# Sample code using the InLoggClient class
client = InLoggClient.new(AUTH_KEY, AUTH_USER, AUTH_PASS, 'ecom')
client.authenticateUser
shipmentRateRequestData = [{
    :dead_weight => "500",
    :payment_mode => "PP",
    :src_pincode => "560068",
    :dest_pincode => "560010"
}]
rates = client.getShipmentRate(shipmentRateRequestData)
trackingId = client.createShipment([{
                "tracking_id" => "123499",
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
}])

status = client.getShipmentStatus([{
    "tracking_id" => "123499"
}])
startTime = Time.new(2016, 1, 10, 0, 0, 0, "+05:30") # Date : 11/01/2016 00:00:00 IST
endTime   = Time.new(2016, 1, 12, 0, 0, 0, "+05:30") # Date : 11/01/2016 00:00:00 IST
changes = statusChanges = client.getShipmentStatusChanges(startTime.to_i, endTime.to_i)
if changes['response_code'] = 'true'
    puts changes['status_changes']
end
