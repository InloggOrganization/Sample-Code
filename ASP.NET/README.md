# Accessing InLogg using ASP.NET

`Project.cs` contains the sample code, parts of which are discussed below.

## Authenticating user and getting token

This would generally be the first step in the flow of APIs. Upon successful 
authentication, a `token` and `id` is received, which needs to be used in all
future calls (unless the token expires).

```csharp
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
```
The token received in the above example is used in all future calls and passed as header parameters.


## Getting Shipments

To get the shipments assigned to you (identified by the `X-API-User-Token` and `X-API-User-ID` in the header), you'd need to access the `/getShipments` method. 

(Please refer to `Program.cs` for details of `GetShipmentsResponse`)

```csharp
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
 
```

This calls takes an optional GET parameter `shipments_from` (string) that makes the API list only shipments after a particular time. The expected format for the parameter is `YYYY-MM-DD HH:MM:SS` (eg, `2015-09-28 10:13:12`)
