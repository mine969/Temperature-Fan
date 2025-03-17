#include "DHT.h"
#include <ESP8266WiFi.h>
WiFiServer server(80);
#define RELAY D0       

DHT dht(4, DHT11);

void setup() {
    Serial.begin(115200);
    dht.begin();
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

void loop() {
    float t = dht.readTemperature();  // Read Temperature

    if (isnan(t)) {  // Check if reading is valid
        Serial.println("Failed to read from DHT sensor!");
        
        return;
    }

    if (t >= 28) {  // Correct comparison
        Serial.println("Temperature High");
         digitalWrite(RELAY, LOW);
    } else {
        Serial.println("Temperature: LOW");
         digitalWrite(RELAY, HIGH);
    }

    delay(2000);  // Delay to prevent excessive readings
}
