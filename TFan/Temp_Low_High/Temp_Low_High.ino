#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "DHT.h"

// ==== Pin Definitions ====
#define RELAY D0             // Relay connected to GPIO16 (D0)
#define DHTPIN 4             // DHT11 data pin (GPIO4)
#define DHTTYPE DHT11

// ==== DHT Sensor Setup ====
DHT dht(DHTPIN, DHTTYPE);

// ==== WiFi & Server Settings ====
const char* ssid = "Mine_2G";
const char* password = "882023123";
const char* serverName = "http://tempfan.atwebpages.com/index.php";
const char* manualControlURL = "http://tempfan.atwebpages.com/manual.txt";

// ==== Mode Control Variables ====
bool manualMode = false;
int manualRelayState = 1;  // 0 = ON, 1 = OFF
unsigned long lastLogTime = 0;
const unsigned long logInterval = 10 * 60 * 1000;  // Log every 10 minutes
unsigned long lastCheckTime = 0;
const unsigned long checkInterval = 2000;          // Check every 2 seconds

void setup() {
  Serial.begin(115200);
  dht.begin();

  pinMode(RELAY, OUTPUT);
  digitalWrite(RELAY, 1);  // Default relay OFF

  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi Connected");
}

void loop() {
  unsigned long now = millis();

  if (now - lastCheckTime >= checkInterval) {
    lastCheckTime = now;

    float temp = dht.readTemperature();
    float hum = dht.readHumidity();

    if (isnan(temp) || isnan(hum)) {
      Serial.println("❌ Failed to read from DHT sensor!");
      return;
    }

    if (WiFi.status() == WL_CONNECTED) {
      WiFiClient client;
      HTTPClient http;

      // === Check Manual Mode from Server ===
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
          manualMode = false;    // AUTO mode
        }
      }
      http.end();

      // === Control Fan Based on Mode ===
      int relayState = manualMode ? manualRelayState : (temp >= 28 ? 0 : 1);
      digitalWrite(RELAY, relayState);

      // === Display Status ===
      Serial.println("---- Current Status ----");
      Serial.print("Mode        : ");
      Serial.println(manualMode ? "MANUAL" : "AUTO");

      Serial.print("Temperature : ");
      Serial.print(temp);
      Serial.println(" °C");

      Serial.print("Humidity    : ");
      Serial.print(hum);
      Serial.println(" %");

      Serial.print("Fan Status  : ");
      Serial.println(relayState == 0 ? "ON" : "OFF");

      // === Prepare Log URL ===
      String logURL = serverName;
      logURL += "?temperature=" + String(temp);
      logURL += "&humidity=" + String(hum);
      logURL += "&relay=" + String(relayState);
      Serial.println("Log URL     : " + logURL);

      // === Send Log if Needed ===
      bool shouldLog = false;

      if (!manualMode && now - lastLogTime >= logInterval) {
        shouldLog = true;
        lastLogTime = now;
      }

      if (manualMode) {
        shouldLog = true;
        lastLogTime = now; // Prevent auto log just after manual
      }

      if (shouldLog) {
        http.begin(client, logURL);
        int logCode = http.GET();
        if (logCode > 0) {
          Serial.println("✅ Log sent to server");  
        } else {
          Serial.println("❌ Log failed to send");
        }
        http.end();
      }

      Serial.println("------------------------");
    }
  }
}
