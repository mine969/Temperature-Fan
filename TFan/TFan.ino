#include <ESP8266WiFi.h>
#define RELAY D0       				
WiFiServer server(80);
void setup()
{
  Serial.begin(115200);
  pinMode(RELAY  , OUTPUT);
  digitalWrite(RELAY  , LOW);
  WiFi.begin("RSU-IOT", "1otRSU@6F00d");
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);     Serial.print(".");
  }
  Serial.println("WiFi connected");
  server.begin();  // Starts the Server
  Serial.println("Server started");
  Serial.print("IP Address of network: "); // will IP address on Serial Monitor
  Serial.println(WiFi.localIP());
  Serial.print("Copy and paste the following URL: https://"); // Will print IP address in URL format
  Serial.print(WiFi.localIP());
  Serial.println("/");
}
void loop()
{
  WiFiClient client = server.available();
  if (!client)  {
    return;
  }
  Serial.println("Waiting for new client");
  while(!client.available()) {
    delay(1);
  }
  String request = client.readStringUntil('\r');
  Serial.println(request);
  client.flush();
  int value = LOW;
  if(request.indexOf("/RELAY=ON") != -1)  {
    digitalWrite(RELAY, HIGH); // Turn RELAY ON
    value = HIGH;
  }
  if(request.indexOf("/RELAY=OFF") != -1)  {
    digitalWrite(RELAY, LOW); // Turn RELAY OFF
    value = LOW;
  }
//*------------------HTML Page Code---------------------*//
  client.println("<!DOCTYPE HTML>");
  client.println("<html>");
  client.print(" CONTROL RELAY: ");
  if(value == HIGH)       
client.print("ON");
  else      
client.print("OFF");
  client.println("<br><br>");
  client.println("<a href=\"/RELAY=ON\"\"><button>ON</button></a>");
  client.println("<a href=\"/RELAY=OFF\"\"><button>OFF</button></a><br />");
  client.println("</html>");
  delay(1);
} // Loop End
