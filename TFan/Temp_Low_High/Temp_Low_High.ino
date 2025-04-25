#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

#define RELAY D0       
#define DHTPIN 4       
#define DHTTYPE DHT11  

DHT dht(DHTPIN, DHTTYPE);

const char* ssid = "RSU-IOT"; 
const char* password = "1otRSU@6F00d"; 
const char* serverName = "http://tempfan.atwebpages.com/index.php"; 

void setup() {
    Serial.begin(115200);
    dht.begin();
    pinMode(RELAY, OUTPUT);
    digitalWrite(RELAY, LOW);

    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
}

void loop() {
    float t = dht.readTemperature();
    float h = dht.readHumidity();

    if (isnan(t) || isnan(h)) {
        Serial.println("Failed to read from DHT sensor!");
        return;
    }

    int relayState = (t >= 28) ? 0 : 1;
    digitalWrite(RELAY, relayState);

    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;
        
        String url = serverName;
        url += "?temperature=" + String(t) + "&humidity=" + String(h) + "&relay=" + String(relayState);
        
        Serial.println("Sending data: " + url);
        
        http.begin(client, url);
        int httpCode = http.GET(); 

        if (httpCode > 0) { 
            Serial.println("Server Response: " + http.getString());
        } else {
            Serial.println("Error sending data");
        }
        
        http.end();
    }

    delay(6000); 
}