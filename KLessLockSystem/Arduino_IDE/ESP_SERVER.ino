#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

const char* ssid = "klesslock";  // WiFi network name
const char* password = "quantumcoders2024";  // WiFi password
const char* serverName = "http://192.168.1.100/post-data.php"; // Server URL

WiFiClient client;

void setup() {
  Serial.begin(9600);
  pinMode(LED_BUILTIN, OUTPUT);  // Ensure LED pin is set as an output
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  if (Serial.available() > 0) {
    String received = Serial.readStringUntil('\n');
    Serial.println("Received: " + received);  // Output received data

    // Blink the LED to indicate data reception
    digitalWrite(LED_BUILTIN, LOW);  // Turn the LED on
    delay(100);                       // Wait for 100 milliseconds
    digitalWrite(LED_BUILTIN, HIGH);   // Turn the LED off

    if(WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      http.begin(client, serverName);  // Use the WiFiClient instance
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");

      String httpRequestData = "number=" + received; 
      int httpResponseCode = http.POST(httpRequestData);

      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      
      http.end();
    } else {
      Serial.println("WiFi Disconnected");
    }
  }
}