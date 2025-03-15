#include "DHT.h"


DHT dht(4, DHT11);

void setup() {
    Serial.begin(115200);
    dht.begin();
}

void loop() {
    float t = dht.readTemperature();  // Read Temperature

    if (isnan(t)) {  // Check if reading is valid
        Serial.println("Failed to read from DHT sensor!");
        return;
    }

    if (t >= 28) {  // Correct comparison
        Serial.println("Temperature High");
    } else {
        Serial.println("Temperature: LOW");
    }

    delay(2000);  // Delay to prevent excessive readings
}
