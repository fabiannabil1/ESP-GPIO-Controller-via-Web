#include <WiFi.h>
#include <HTTPClient.h>
#include <Arduino_JSON.h>

#define MAX_GPIO 40  // Misalnya, 40 GPIO yang akan diproses

const char* url = "10.132.0.213/Contactor%20Indikator";
const char* ssid = "UNEJ-ACCESS";
const char* password = "your_pass";

// Gunakan objek String untuk konkatenasi URL
String serverName = "http://" + String(url) + "/esp-outputs-action.php?action=outputs_state&board=1";

// Update interval time set to 5 seconds
const long interval = 5000;
unsigned long previousMillis = 0;

String outputsState;

// Contoh fungsi untuk mengecek apakah suatu pin dikonfigurasikan sebagai input.
// Perlu disesuaikan dengan kebutuhan Anda.
bool pinIsInput(int gpio) {
  // Misal: kita asumsikan pin 0-19 sebagai output, sisanya input.
  return (gpio >= 20);
}

void setup() {
  Serial.begin(115200);
  
  WiFi.begin(ssid);
  Serial.println("Connecting");
  while (WiFi.status() != WL_CONNECTED) { 
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  unsigned long currentMillis = millis();
  
  // Bagian Output
  if (currentMillis - previousMillis >= interval) {
    if (WiFi.status() == WL_CONNECTED) {
      outputsState = httpGETRequest(serverName.c_str());
      JSONVar myObject = JSON.parse(outputsState);
      
      for (int i = 0; i < myObject.keys().length(); i++) {
        String key = (const char*) myObject.keys()[i];
        JSONVar value = myObject[key];
        String gpioType = (const char*) value["type"];
        int pin = key.toInt();
        
        if (gpioType == "output") {
          pinMode(pin, OUTPUT);
          digitalWrite(pin, atoi((const char*) value["state"]));
        } 
        else if (gpioType == "input") {
          pinMode(pin, INPUT_PULLUP);
        }
      }
      previousMillis = currentMillis;
    }
  }
  
  // Bagian Input
  static bool firstRun = true;
  static int lastInputState[MAX_GPIO];
  if (firstRun) {
    for (int i = 0; i < MAX_GPIO; i++) {
      lastInputState[i] = -1;
    }
    firstRun = false;
  }
  
  for (int gpio = 0; gpio < MAX_GPIO; gpio++) {
    if (pinIsInput(gpio)) { // Cek apakah GPIO dikonfigurasikan sebagai input
      int currentState = digitalRead(gpio);
      if (lastInputState[gpio] != currentState) {
        sendInputState(gpio, currentState);
        lastInputState[gpio] = currentState;
      }
    }
  }
}

void sendInputState(int gpio, int state) {
  if (WiFi.status() == WL_CONNECTED) {
    String serverPath = "http://" + String(url) + "/esp-inputs-action.php?action=input_update&gpio=" +
                        String(gpio) + "&state=" + String(state) + "&board=1";
    Serial.println(serverPath);
                        
    httpGETRequest(serverPath.c_str());
  }
}

String httpGETRequest(const char* serverURL) {
  WiFiClient client;
  HTTPClient http;
    
  http.begin(client, serverURL);
  
  int httpResponseCode = http.GET();
  String payload = "{}"; 
  
  if (httpResponseCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
    payload = http.getString();
  } 
  else {
    Serial.print("Error code: ");
    Serial.println(httpResponseCode);
  }
  http.end();
  
  return payload;
}

void httpPOSTRequest(const char* serverPath) {
  WiFiClient client;
  HTTPClient http;
  
  http.begin(client, serverPath);
  int httpResponseCode = http.POST(""); // Mengirim POST request dengan body kosong
  if (httpResponseCode > 0) {
    Serial.print("POST Response code: ");
    Serial.println(httpResponseCode);
  } 
  else {
    Serial.print("Error on sending POST: ");
    Serial.println(httpResponseCode);
  }
  http.end();
}
