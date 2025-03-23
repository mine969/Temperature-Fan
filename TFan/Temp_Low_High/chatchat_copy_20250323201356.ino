#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

#define RELAY D0       
#define DHTPIN 4       
#define DHTTYPE DHT11  

DHT dht(DHTPIN, DHTTYPE);
WiFiClient client;
const char* ssid = "RSU-IOT"; // Your WiFi SSID
const char* password = "1otRSU@6F00d"; // Your WiFi Password
const char* serverName = "http://htetlin.atwebpages.com/chatchat.php"; // Your server URL

void setup() {
    Serial.begin(115200);
    dht.begin();
    pinMode(RELAY, OUTPUT);
    digitalWrite(RELAY, LOW);

    // Connect to WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
}

void loop() {
    float t = dht.readTemperature();
    int relayState; // To store the relay state

    if (isnan(t)) {
        Serial.println("Failed to read from DHT sensor!");
        return;
    }

    Serial.print("Temperature: ");
    Serial.println(t);

    // Control relay based on temperature
    if (t >= 28) {
        Serial.println("Temperature High - Turning Relay OFF");
        digitalWrite(RELAY, LOW);
        relayState = 0; // Relay OFF
    } else {
        Serial.println("Temperature LOW - Turning Relay ON");
        digitalWrite(RELAY, HIGH);
        relayState = 1; // Relay ON
    }

    // Send data to MySQL via PHP
    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;
        
        String url = serverName;
        url += "?temperature=";
        url += String(t);
        url += "&relay=";
        url += String(relayState);
        
        Serial.print("Requesting URL: ");
        Serial.println(url);
        
        http.begin(client, url);
        int httpCode = http.GET(); // Make GET request
g
        if (httpCode > 0) { 
            String payload = http.getString();
            Serial.println("Server Response: " + payload);
        } else {
            Serial.println("Error sending data");
        }
        
        http.end();
    } else {
        Serial.println("WiFi Disconnected");
    }

    delay(60000); // Wait 60 seconds before next reading
}
