#include "DHT.h"
#define RELAY D0       

DHT dht(4, DHT11);

void setup() {
    Serial.begin(115200);
    dht.begin();
    pinMode(RELAY  , OUTPUT);
    digitalWrite(RELAY  , LOW);

}

void loop() {
    float t = dht.readTemperature();  // Read Temperature

    if (isnan(t)) {  // Check if reading is valid
        Serial.println("Failed to read from DHT sensor!");
        
        return;
    }

    if (t >= 28) {  // Correct comparison
        Serial.println("Temperature High");
         digitalWrite(RELAY, LOW)
    } else {
        Serial.println("Temperature: LOW");
         digitalWrite(RELAY, HIGH)
    }

    delay(2000);  // Delay to prevent excessive readings
}
