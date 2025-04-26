#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

#define RELAY D0           // Relay connected to GPIO16 (D0)
#define DHTPIN 4           // DHT11 data pin (GPIO4)
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

// WiFi & Server
const char* ssid = "Mine_2G";
const char* password = "882023123";
const char* serverName = "http://tempfan.atwebpages.com/index.php";
const char* manualControlURL = "http://tempfan.atwebpages.com/manual.txt";

// Mode Control
bool manualMode = false;
int manualRelayState = 1; // 0 = ON, 1 = OFF
unsigned long lastLogTime = 0;
const unsigned long logInterval = 10 * 60 * 1000;  // 10 min in milliseconds
unsigned long lastCheckTime = 0;
const unsigned long checkInterval = 2000;          // 5 seconds

void setup() {
  Serial.begin(115200);
  dht.begin();
  pinMode(RELAY, OUTPUT);
  digitalWrite(RELAY, 1);  // Default OFF

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
}

void loop() {
  unsigned long now = millis();

  if (now - lastCheckTime >= checkInterval) {
    lastCheckTime = now;

    float temp = dht.readTemperature();
    float hum = dht.readHumidity();

    if (isnan(temp) || isnan(hum)) {
      Serial.println("Failed to read DHT!");
      return;
    }

    if (WiFi.status() == WL_CONNECTED) {
      WiFiClient client;
      HTTPClient http;

      // Check manual mode status
      http.begin(client, manualControlURL);
      int code = http.GET();
      if (code == 200) {
        String status = http.getString();
        status.trim();

        if (status == "on") {
          manualMode = true;
          manualRelayState = 0;  // ON
        } else if (status == "off") {
          manualMode = true;
          manualRelayState = 1;  // OFF
        } else {
          manualMode = false; // auto
        }
      }
      http.end();

      // Control fan
      int relayState = manualMode ? manualRelayState : (temp >= 28 ? 0 : 1); // 0 = ON, 1 = OFF
      digitalWrite(RELAY, relayState);

      Serial.print("Mode: ");
      Serial.print(manualMode ? "MANUAL" : "AUTO");
      Serial.print(" | Fan: ");
      Serial.println(relayState == 0 ? "ON" : "OFF");

      // Log every 10 mins
      if (now - lastLogTime >= logInterval) {
        lastLogTime = now;
        String logURL = serverName + String("?temperature=") + temp + "&humidity=" + hum + "&relay=" + relayState;
        http.begin(client, logURL);
        int logCode = http.GET();
        if (logCode > 0) {
          Serial.println("Data logged");
        } else {
          Serial.println("Failed to log data");
        }
        http.end();
      }
    }
  }
}
