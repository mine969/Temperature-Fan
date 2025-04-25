#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

#define RELAY D1        // Use D1 instead of D0
#define DHTPIN 4        // D2 on NodeMCU
#define DHTTYPE DHT11   

DHT dht(DHTPIN, DHTTYPE);

const char* ssid = "Mine_2G"; 
const char* password = "882023123"; 
const char* serverName = "http://tempfan.atwebpages.com/index.php"; 
const char* manualControlURL = "http://tempfan.atwebpages.com/manual.txt";

bool manualMode = false;
int manualRelayState = 1; // 0 = ON, 1 = OFF

void setup() {
    Serial.begin(115200);
    dht.begin();
    pinMode(RELAY, OUTPUT);
    digitalWrite(RELAY, HIGH);  // OFF by default

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

    Serial.println("Temp: " + String(t) + " | Hum: " + String(h));

    if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;

        // Step 1: Check manual control file
        http.begin(client, manualControlURL);
        int code = http.GET();
        if (code == 200) {
            String control = http.getString();
            control.trim(); // remove \n, space
            Serial.println("Manual Control Value: [" + control + "]");

            if (control == "on") {
                manualMode = true;
                manualRelayState = 0;
            } else if (control == "off") {
                manualMode = true;
                manualRelayState = 1;
            } else {
                manualMode = false;
            }
        } else {
            Serial.println("Error reading manual.txt");
        }
        http.end();

        // Step 2: Decide relay state
        int relayState;
        if (manualMode) {
            relayState = manualRelayState;
            Serial.println("Using Manual Mode. Relay = " + String(relayState));
        } else {
            relayState = (t >= 28) ? 0 : 1;  // LOW = ON
            Serial.println("Using Auto Mode. Relay = " + String(relayState));
        }

        digitalWrite(RELAY, relayState);

        // Step 3: Log data to server
        String url = serverName;
        url += "?temperature=" + String(t) + "&humidity=" + String(h) + "&relay=" + String(relayState);
        http.begin(client, url);
        int httpCode = http.GET();
        if (httpCode > 0) {
            Serial.println("Logged: " + http.getString());
        } else {
            Serial.println("Error sending log");
        }
        http.end();
    }

    delay(5000);
}
