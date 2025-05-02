#include <U8g2lib.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>

// U8g2 OLED setup - Using I2C with default ESP32-C3 pins (SDA=8, SCL=9)
U8G2_SSD1306_128X64_NONAME_F_HW_I2C u8g2(U8G2_R0, /* reset=*/ U8X8_PIN_NONE);

const char* ssid = "";
const char* password = "";
const char* server = "";

unsigned long lastUpdate = 0;
const unsigned long interval = 5000; // 5 seconds
bool showBills = true; // Toggle between bills and equipment stats

void setup() {
  Serial.begin(115200);
  
  // Initialize OLED
  Wire.begin(8, 9); // SDA, SCL for ESP32-C3
  u8g2.begin();
  u8g2.setFont(u8g2_font_helvB08_tr);
  u8g2.setFontRefHeightExtendedText();
  u8g2.setDrawColor(1);
  u8g2.setFontPosTop();
  displayMessage("Connecting to WiFi...");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  displayMessage("WiFi connected!");
  Serial.println("\nConnected to WiFi");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (millis() - lastUpdate > interval) {
    lastUpdate = millis();
    
    if (showBills) {
      fetchAndDisplayBills();
    } else {
      fetchAndDisplayEquipmentStats();
    }
    
    showBills = !showBills; // Toggle for next cycle
  }
}

void displayMessage(String message) {
  u8g2.clearBuffer();
  
  int yPos = 10;
  int newlineIndex = 0;
  do {
    newlineIndex = message.indexOf('\n');
    String line = (newlineIndex != -1) ? message.substring(0, newlineIndex) : message;
    u8g2.drawStr(0, yPos, line.c_str());
    yPos += 12;
    if (newlineIndex != -1) {
      message = message.substring(newlineIndex + 1);
    }
  } while (newlineIndex != -1);
  
  u8g2.sendBuffer();
}

void fetchAndDisplayBills() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(server) + "/api/bills_unpaid";
    http.begin(url);
    
    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      DynamicJsonDocument doc(1024);
      DeserializationError error = deserializeJson(doc, payload);

      if (!error) {
        u8g2.clearBuffer();
        u8g2.drawStr(0, 0, "Unpaid Bills:");
        
        int yPos = 12;
        int billCount = doc.size();
        int maxBillsToShow = 5;
        
        for (int i = 0; i < billCount && i < maxBillsToShow; i++) {
          JsonObject bill = doc[i];
          String dueDate = bill["dueDate"].as<String>();
          String line = "#" + String(bill["billid"].as<int>()) + 
                       ": $" + String(bill["amount"].as<float>(), 2) + 
                       " " + dueDate.substring(0, 10);
          
          if (line.length() > 21) {
            line = line.substring(0, 21);
          }
          
          u8g2.drawStr(0, yPos, line.c_str());
          yPos += 12;
        }
        u8g2.sendBuffer();
      } else {
        displayMessage("JSON Error\n" + String(error.c_str()));
      }
    } else {
      displayMessage("HTTP Error\nCode: " + String(httpCode));
    }
    http.end();
  } else {
    displayMessage("WiFi Disconnected\nReconnecting...");
    WiFi.reconnect();
  }
}

void fetchAndDisplayEquipmentStats() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(server) + "/api/statistics/equipment";
    http.begin(url);
    
    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      DynamicJsonDocument doc(2048); // Larger buffer for stats
      DeserializationError error = deserializeJson(doc, payload);

      if (!error) {
        u8g2.clearBuffer();
        u8g2.drawStr(0, 0, "Equipment Stats:");
        
        // Display category stats (simplified for OLED)
        JsonArray categoryStats = doc["categoryStats"];
        int yPos = 12;
        u8g2.drawStr(0, yPos, "Categories:");
        yPos += 12;
        
        for (int i = 0; i < categoryStats.size() && i < 3; i++) {
          String line = String(categoryStats[i]["name"].as<String>().substring(0, 8)) + 
                       ": " + String(categoryStats[i]["count"].as<int>());
          u8g2.drawStr(0, yPos, line.c_str());
          yPos += 12;
        }
        
        // Display state stats if space allows
        if (yPos < 64) {
          JsonArray stateStats = doc["stateStats"];
          u8g2.drawStr(64, 12, "States:");
          
          int stateYPos = 24;
          for (int i = 0; i < stateStats.size() && stateYPos < 64; i++) {
            String line = String(stateStats[i]["name"].as<String>().substring(0, 8)) + 
                         ": " + String(stateStats[i]["count"].as<int>());
            u8g2.drawStr(64, stateYPos, line.c_str());
            stateYPos += 12;
          }
        }
        
        u8g2.sendBuffer();
      } else {
        displayMessage("JSON Error\n" + String(error.c_str()));
      }
    } else {
      displayMessage("HTTP Error\nCode: " + String(httpCode));
    }
    http.end();
  } else {
    displayMessage("WiFi Disconnected\nReconnecting...");
    WiFi.reconnect();
  }
}