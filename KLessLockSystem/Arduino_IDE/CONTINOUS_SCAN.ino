
#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <Keypad.h>
#include <LiquidCrystal_I2C.h>


int relayPin = 13; // Using pin 0 (RX)

// LCD display setup
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Define the size of the keypad (4 rows and 4 columns)
const byte ROWS = 4;
const byte COLS = 4;

// Define the keymap of your keypad (Adjust this based on your keypad's layout)
char keys[ROWS][COLS] = {
  {'1', '2', '3'},
  {'4', '5', '6'},
  {'7', '8', '9'},
  {'*', '0', '#'}
};

// Connect the rows and columns of the keypad to the Arduino
byte rowPins[ROWS] = {4, 7, 8, 9}; // Pins connected to the rows (adjust as needed)
byte colPins[COLS] = {10, 11, 12}; // Pins connected to the columns (adjust as needed)

// Create a keypad object
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);
// Define pin connections for fingerprint sensor (adjust these for your setup)
#define RXP 2
#define TXP 3
#define UNLOCK_PIN 123 // Define the correct PIN for unlocking
#define LOCK_PIN 321   // Define the PIN for locking

String inputPin = "";             // To store the PIN input

SoftwareSerial espSerial(5, 6); // RX, TX for ESP8266
SoftwareSerial fingerSerial(RXP, TXP); // RX, TX for Fingerprint sensor

Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);

bool pinVerified = false; // Variable to check if the PIN was correctly entered
bool unlockPhaseComplete = false; // To track if the "Scan to Unlock" phase is done
bool scanPromptDisplayed = false; // To ensure "Scan Fingerprint!" is displayed only once
bool fingerPresent = false; // Track whether a finger was previously detected
bool fingerNotRecognizedDisplayed = false; // Track whether "Fingerprint not recognized" was displayed

// Function prototypes (declare before usage in setup and loop)
void scanFingerprintLoop();
uint8_t getFingerprintID();
void requestPin();
void checkLockPIN();
void lockSystem();
void scanToUnlock();


void setup() {
  // Initialize serial communication with PC and ESP8266
  Serial.begin(9600);
  espSerial.begin(9600);

  lcd.init();             // Initialize the LCD
  lcd.backlight();        // Turn on the LCD backlight (if supported)
  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, HIGH ); 

  // Initialize fingerprint sensor
  finger.begin(57600);
  if (finger.verifyPassword()) {
    Serial.println("Fingerprint sensor found!");
  } else {
    Serial.println("Fingerprint sensor not found, check connections.");
    while (1); // Halt the program if the sensor is not found
  }

  // Get the number of fingerprints stored and the sensor's maximum capacity
  finger.getTemplateCount();

  if (finger.templateCount == 0) {
    Serial.println("Sensor doesn't contain any fingerprint data. Please run the 'enroll' example.");
  } else {
    Serial.print("Sensor contains "); 
    Serial.print(finger.templateCount); 
    Serial.println(" templates");
  }

  Serial.print("Maximum fingerprint storage capacity: ");
  Serial.println(finger.capacity);

  // Prompt the user for the PIN
  requestPin();
}

void loop() {
  // If the PIN has been verified but the unlock phase is not complete, run the initial "Scan to Unlock"
  if (pinVerified && !unlockPhaseComplete) {
    scanToUnlock(); // Only allow fingerprint ID 1 or 2
  } else if (unlockPhaseComplete) {
    scanFingerprintLoop(); // Allow fingerprint IDs up to the capacity
  }

  checkLockPIN(); // Check for PIN input at the end of loop
}


void requestPin() {
  bool pinCorrect = false;
  inputPin = ""; // Clear previous input

  // Print the message once before entering the input loop
  Serial.println("Enter PIN to proceed:");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Enter PIN:");

  while (!pinCorrect) {
    char key = keypad.getKey();    // Read the keypress from the keypad

    if (key) {  // If a key is pressed
      if (key == '#') { // Use '#' to submit the input
        break;
      } else if (key == '*') {  // Use '*' to clear the input
        inputPin = "";
        lcd.setCursor(0, 1);  // Move to second line for showing input
        lcd.print("                "); // Clear the second line
        lcd.setCursor(0, 1); // Reset cursor for new input
        Serial.println("\nInput cleared. Enter PIN again:");
        
      } else {
        inputPin += key;        // Add the key to the input PIN
        Serial.print("*");      // Print '*' for each digit entered
        lcd.setCursor(inputPin.length() - 1, 1);  // Set the cursor based on input length
        lcd.print("*");         // Display '*' for each entered character
      }
    }

    if (inputPin.length() == 3) {  // Check if the entered PIN has 3 digits
      if (inputPin == String(UNLOCK_PIN)) {
        digitalWrite(relayPin, HIGH);
        Serial.println("\nCorrect PIN!");  // Display correct PIN message
        pinVerified = true;                // Set pinVerified to true
        pinCorrect = true;                 // Exit the loop
        Serial.println("Scan to Unlock");  // Prompt to scan the fingerprint

        lcd.clear();                       // Clear the LCD after correct PIN
        lcd.setCursor(0, 0);
        lcd.print("Correct PIN!");
        delay(1000);                       // Wait for 1 second before proceeding
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Scan to Unlock");
        // digitalWrite(relayPin, LOW);
      } else {
        Serial.println("\nIncorrect PIN! Try again.");  // Error message for incorrect PIN
        lcd.clear();  // Clear the LCD and display error message
        lcd.setCursor(0, 0);
        lcd.print("Incorrect PIN!");
        delay(1000);  // Hold the message for 1 second
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Enter PIN:");
        inputPin = ""; // Clear the input and try again
      }
    }
  }
}
void scanToUnlock() {
  if (!fingerPresent) {
    uint8_t fingerprintID = getFingerprintID();

    if (fingerprintID > 0) {
      if (fingerprintID == 1 || fingerprintID == 2) {
        String fingerprintIDStr = String(fingerprintID);
        espSerial.println(fingerprintIDStr);
        Serial.println("Welcome Admin ID: " + fingerprintIDStr);
        unlockPhaseComplete = true;
        scanPromptDisplayed = false;
        fingerPresent = false;

digitalWrite(relayPin, LOW);
       
        
        // Display welcome message
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Welcome Admin!");
        lcd.setCursor(0, 1); // Move to the second line
        lcd.print("ID: " + fingerprintIDStr); // Print the ID on the next line

        //  digitalWrite(relayPin, LOW);
        
        // Hold the message for 1 second
        delay(1000);
        lcd.clear(); // Clear the display after the message
        
        // Call scanFingerprintLoop after the unlock phase is complete
        scanFingerprintLoop();
      } else {
        Serial.println("Not Authorized.");
        fingerPresent = false;
        
        // Display "Not Authorized" on the LCD
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Not Authorized");
        
        // Hold the message for 1 second
        delay(500);
        lcd.clear();
        
        // Display "Scan to Unlock" prompt
        lcd.setCursor(0, 0);
        lcd.print("Scan to Unlock");
      }

      // Wait until no finger is detected
      while (finger.getImage() != FINGERPRINT_NOFINGER) {
        delay(1000);
      }

      // Delay before scanning again
      delay(1000); 
    }
  }
}



void scanFingerprintLoop() {
  // Always show "Please scan your finger" if no finger is present
  if (!fingerPresent && !scanPromptDisplayed) {
    
    Serial.println("Scan Fing user!");
    scanPromptDisplayed = true; // Mark that the prompt has been displayed
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Please scan");
    lcd.setCursor(0, 1);
    lcd.print("your finger");

    
    delay(1000);
    digitalWrite(relayPin, HIGH);
  }

  // Finger is being scanned
  if (!fingerPresent) {
    uint8_t fingerprintID = getFingerprintID();

    // Only if a valid fingerprint is detected (greater than 0 and within capacity)
    if (fingerprintID > 0 && fingerprintID <= finger.capacity) {
      String fingerprintIDStr = String(fingerprintID);
      espSerial.println(fingerprintIDStr);
      Serial.println("Welcome ID: " + fingerprintIDStr);

      if (fingerprintID == 3) {
        Serial.println("Fingerprint matched with ID 3. Terminating and resetting...");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("System Resetting...");
        delay(2000); // Hold the message for 2 seconds
        resetSystem(); // Call the reset function
        return; // Exit the function after resetting
      }

      fingerPresent = true;  // Finger has been recognized
      fingerNotRecognizedDisplayed = false; // Reset "Not Recognized" flag
      scanPromptDisplayed = false; // Reset the scan prompt display

      digitalWrite(relayPin, LOW);

      // Display "Welcome User" message
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Welcome User!");
      lcd.setCursor(0, 1); // Move to the second line
      lcd.print("ID: " + fingerprintIDStr); // Print the ID on the next line

      // Wait for the finger to be removed before continuing
      while (finger.getImage() != FINGERPRINT_NOFINGER) {
        delay(800);
      }

      // After finger is removed, reset the state
      fingerPresent = false;
      delay(1000); // Add delay of 1.5 seconds before scanning again

      // Return to "Please scan your finger" prompt
      lcd.setCursor(0, 0);
      lcd.print("Please scan");
      lcd.setCursor(0, 1);
      lcd.print("your finger");
      // digitalWrite(relayPin, LOW);
    } 
    // Only display "Not Recognized" if fingerprintID is explicitly -1 (no match)
    else if (fingerprintID == -1 && !fingerNotRecognizedDisplayed) {
      Serial.println("Not Authorized.");
        fingerNotRecognizedDisplayed = false;
        
        // Display "Not Authorized" on the LCD
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Not Authorized");
        
        // Hold the message for 1 second
        delay(500);
        lcd.clear();

      // Return to "Please scan your finger" prompt
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Please scan");
      lcd.setCursor(0, 1);
      lcd.print("your finger");

      // digitalWrite(relayPin, LOW);
    }
  } else {
    // Reset the "Not Recognized" flag when a new finger is scanned
    fingerNotRecognizedDisplayed = false;
  }

  checkLockPIN();
 
  delay(200); // Small delay to avoid rapid looping
}



void checkLockPIN() {
  static String enteredSequence = ""; // Store the entered sequence

  char key = keypad.getKey(); // Read the key from the keypad

  // If a key is pressed, append it to the sequence
  if (key) {
    enteredSequence += key; // Add the pressed key to the sequence

    // If the entered sequence matches the lock PIN
    if (enteredSequence == String(LOCK_PIN)) {
      Serial.println("Correct PIN! Locking the system..."); // Show "Correct PIN!" message
      delay(1000); // Wait for 1 second
      lockSystem(); // Call the locking function directly after 1 second
      enteredSequence = ""; // Clear the sequence after locking
    }
    // Reset the sequence if it exceeds the length of LOCK_PIN
    else if (enteredSequence.length() > String(LOCK_PIN).length()) {
      enteredSequence = ""; // Clear the sequence
    }
  }
}
void lockSystem() {
  // Example code to lock the system
  Serial.println("System Locked!");
  
  // You might want to implement additional locking behavior here
  // For example, turning off a relay, disabling further access, etc.

  // For now, we'll simply wait and display a message
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("System Locked!");
 
  delay(2000); // Hold the message for 2 seconds
  
  // Optionally, you could reset the system or go back to the main loop
  // requestPin(); // Uncomment this if you want to request the PIN again after locking
}




uint8_t getFingerprintID() {
  uint8_t p = finger.getImage();

  if (p == FINGERPRINT_NOFINGER) {
    return 0; // No finger detected, return 0
  }

  if (p == FINGERPRINT_OK) {
    Serial.println("Fingerprint image capture");
  } else {
    return p;
  }

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) {
    Serial.print("Error"); 
    return 0;
  }

  p = finger.fingerFastSearch();
  if (p == FINGERPRINT_OK) {
    Serial.println("Fingerprint matched.");
    Serial.print("ID: "); Serial.print(finger.fingerID); // Display the fingerprint ID
    Serial.print(", Confidence: "); Serial.println(finger.confidence); // Display match confidence
    return finger.fingerID; // Return the fingerprint ID
  } else {
    Serial.println("No match found.");
    return 0; // No match found
  }
}


void resetSystem() {
  // Reset any necessary variables and states
  pinVerified = false;
  unlockPhaseComplete = false;
  fingerPresent = false;

  // Optionally, you can reinitialize or reset other components here
 digitalWrite(relayPin, HIGH );
  // Go back to requesting the PIN to start fresh
  requestPin();
}
