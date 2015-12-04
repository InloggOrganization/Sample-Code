using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Net.Http;
using System.Net.Http.Headers;
using Newtonsoft.Json.Linq;

namespace InLoggSampleCode
{
    public class SingleShipmentData
    {
        public string tracking_id { get; set; }
        public string Channel_id { get; set; }
        public string size { get; set; }
        public string qty { get; set; }
        public string weight { get; set; }
        public string vol_weight { get; set; }
        public string length { get; set; }
        public string breadth { get; set; }
        public string height { get; set; }
        public string source_pin { get; set; }
        public string destination_pin { get; set; }
        public string buyer_name { get; set; }
        public string buyer_phone { get; set; }
        public string seller_name { get; set; }
        public string seller_phone { get; set; }
        public string src_address { get; set; }
        public string src_locality { get; set; }
        public string src_city { get; set; }
        public string src_state { get; set; }
        public string dest_address { get; set; }
        public string dest_locality { get; set; }
        public string dest_city { get; set; }
        public string dest_state { get; set; }
        public string pickup_address { get; set; }
        public string pickup_locality { get; set; }
        public string pickup_city { get; set; }
        public string pickup_state { get; set; }
        public string return_name { get; set; }
        public string return_phone { get; set; }
        public string return_pin { get; set; }
        public string return_address { get; set; }
        public string return_locality { get; set; }
        public string return_city { get; set; }
        public string return_state { get; set; }
        public string customer_sla { get; set; }
        public string logistics_sla { get; set; }
        public string paymentmode { get; set; }
        public string value { get; set; }
        public string amount { get; set; }
        public string content_type { get; set; }
        public string handling { get; set; }
        public string direction { get; set; }
        public string type { get; set; }
        public string service_type { get; set; }
        public string comments { get; set; }
        public string order_id { get; set; }
    }
    
    public class GetShipmentsResponse
    {
        public SingleShipmentData[] shipment_data ;
    }
    
    public class ShipmentStatusBean
    {
        public string tracking_id { get; set; }
        public string shipment_status_text { get; set; }
    }
    
    public class PostShipmentStatusMessage
    {
        public ShipmentStatusBean[] shipment_data;
    }
    
    public class Program
    {
        private static string BASE_URL = "http://api.inlogg.com/v1"; 
            
        public static void Main(string[] args)
        {
            RunAsync().Wait();            
        }
        
        public static async Task RunAsync()
        {
            using (var client = new HttpClient())
            {  
                // TODO: Change parameters here. 
                HttpResponseMessage response = await client.GetAsync(BASE_URL + "/authenticateUser?auth_key=key&auth_user=user&auth_pass=pass&auth_type=vendor");
                
                var token = "";
                var id = "";
                
                if (response.IsSuccessStatusCode)
                {
                    Console.WriteLine("Successfully authenticated");
                    var content = await response.Content.ReadAsStringAsync();
                    JArray array = Newtonsoft.Json.JsonConvert.DeserializeObject<JArray>(content);
                    token = array[0]["token"].ToString();
                    id = array[0]["id"].ToString();
                    Console.WriteLine("Authentication successful. Token: " + token + ", id : " + id);
                }
                else 
                {
                    Console.WriteLine("Failed authentication. Should exit");
                }                
                
                // Get shipments
                HttpRequestMessage requestMessage = new HttpRequestMessage(HttpMethod.Get, BASE_URL + "/getShipments");
                requestMessage.Headers.Add("X-API-User-Token", token);
                requestMessage.Headers.Add("X-API-User-ID", id);
                HttpResponseMessage responseMessage = await client.SendAsync(requestMessage);
                
                if (response.IsSuccessStatusCode)
                {
                    var content = await responseMessage.Content.ReadAsStringAsync();
                    GetShipmentsResponse getShipmentsResponse = Newtonsoft.Json.JsonConvert.DeserializeObject<GetShipmentsResponse>(content);
                    
                    if (getShipmentsResponse.shipment_data.Length > 0)
                    {
                        Console.WriteLine("Received following tracking_ids");
                        Console.WriteLine("-------------");              
                        for (int i = 0; i < getShipmentsResponse.shipment_data.Length; i++)
                        {
                            Console.WriteLine(getShipmentsResponse.shipment_data[i].tracking_id);
                        }
                        Console.WriteLine("-------------");
                    }
                }    
                else
                {
                    var content = await responseMessage.Content.ReadAsStringAsync();
                    Console.WriteLine("Unable to get shipments");
                    Console.WriteLine(content);
                }
                
                // Update Shipment status
                requestMessage = new HttpRequestMessage(HttpMethod.Post, BASE_URL + "/shipmentStatus");
                requestMessage.Headers.Add("X-API-User-Token", token);
                requestMessage.Headers.Add("X-API-User-ID", id);
                
                var shipmentDataArray = new List<ShipmentStatusBean>();
                shipmentDataArray.Add(new ShipmentStatusBean(){tracking_id="GR11", shipment_status_text="forward_confirmed"});
                var postParameters = new Dictionary<string, string>
                    {
                        { "shipment_data", Newtonsoft.Json.JsonConvert.SerializeObject(shipmentDataArray)},                               
                    };
                requestMessage.Content = new FormUrlEncodedContent(postParameters);
                responseMessage = await client.SendAsync(requestMessage);
                
                if(responseMessage.IsSuccessStatusCode)
                {
                    Console.WriteLine("Updated status");
                }
                else 
                {
                    Console.WriteLine(await responseMessage.Content.ReadAsStringAsync());
                }                                
            }
        }
    }
}
