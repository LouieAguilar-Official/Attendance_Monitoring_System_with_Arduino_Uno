#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <LiquidCrystal_I2C.h>
#include <Wire.h>
#include <Keypad.h>

// LCD display setup
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Software Serial setup for Fingerprint Sensor and ESP8266
SoftwareSerial fingerSerial(2, 3); // RX, TX for Fingerprint Sensor
SoftwareSerial espSerial(5, 6);     // RX, TX for ESP8266

Adafruit_Fingerprint finger(&fingerSerial);

// Keypad configuration
const byte ROWS = 4; // four rows
const byte COLS = 4; // four columns
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

const int pinCode = 123; // Correct PIN for unlocking

// Function declarations
void enrollFingerprint();
void setupFingerprintSensor();
uint8_t readNumber();
bool checkIfIDExists(uint8_t id);
bool getFingerprintEnroll(uint8_t id);
void displayMenu();
void handleMenuSelection();
void readPinFromKeypad();

void setup() {
    Serial.begin(9600);
    espSerial.begin(9600);
    lcd.init();
    lcd.backlight();

    lcd.setCursor(0, 0);
    lcd.print("Enter PIN:");
}

void loop() {
    // Check for keypad input (PIN) and process it
    readPinFromKeypad();
}

void readPinFromKeypad() {
    String input = "";
    while (true) {
        char key = keypad.getKey();
        if (key) { // Check if a key is pressed
            if (key == '#') { // Assuming # is the enter key
                break; // Break the loop to check the PIN
            } else if (key == '*') { // Clear input on *
                input = ""; // Reset the input
                lcd.clear();
                lcd.setCursor(0, 0);
                lcd.print("Enter PIN:");
                continue; // Continue to prompt for the PIN again
            } else {
                input += key; // Add key to input string
                lcd.setCursor(0, 1);
                lcd.print(input); // Display current input
            }
        }
    }

    int inputPIN = input.toInt(); // Convert input to integer for PIN check

    if (inputPIN == pinCode) {
        displayMenu(); // Go to the menu if PIN is correct
    } else {
        displayError("Incorrect PIN!", "Try again.", 2000);
    }
}

void displayMenu() {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Menu:");
    lcd.setCursor(0, 1);
    lcd.print("1.AD 2.UPD 3.DEL");

    handleMenuSelection(); // Call menu selection handler
}




void handleMenuSelection() {
    delay(500); // Small delay to allow time for input processing
    while (true) {
        char key = keypad.getKey();
        if (key) { // Check if a key is pressed
            if (key == '1') {
                enrollFingerprint();  // Call the enroll function
                break; // Exit the menu selection loop after calling the function
            } else if (key == '2') { // If * is pressed, return to menu
                lcd.print("not available");
                displayMenu(); // Redisplay the menu
                break; // Exit the menu selection loop
            } else if (key == '3') {
                deleteFingerprint();
                //enrollFingerprint();  // Call the enroll function
                break; // Exit the menu selection loop after calling the function
            } else if (key == '*') { // If * is pressed, return to menu
                displayMenu(); // Redisplay the menu
                break; // Exit the menu selection loop
            } else {
                displayError("Invalid choice!", "", 2000);
                displayMenu(); // Redisplay the menu
                break; // Exit the menu selection loop
            }
        }
    }
}

void setupFingerprintSensor() {
    fingerSerial.begin(57600); // Baud rate for the fingerprint sensor
    if (finger.verifyPassword()) {
        Serial.println("Fingerprint sensor found.");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Sensor Ready");
    } else {
        displayError("Sensor Error!", "", 0);
    }
}
void deleteFingerprint() {
    setupFingerprintSensor(); // Initialize the fingerprint sensor

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Enter ID to DEL:");

    uint8_t id = readNumber(); // Read the ID to delete

    if (!checkIfIDExists(id)) {
        displayError("ID not found!", "", 2000);
        displayMenu(); // Return to menu
        return; // Exit the function
    }

    // Attempt to delete the fingerprint
    if (finger.deleteModel(id) == FINGERPRINT_OK) {
        displayError("Deleted ID #", String(id).c_str(), 2000);
    } else {
        displayError("Delete failed!", "", 2000);
    }

    displayMenu(); // Return to menu after deletion
}

void enrollFingerprint() {
    setupFingerprintSensor(); // Initialize the fingerprint sensor

    uint8_t id = 0;
    while (true) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Please enter ID:");

        // Use keypad to read ID
        id = readNumber(); // Ask for the fingerprint ID

        if (id == 0) { // ID #0 not allowed
            displayError("Invalid ID!", "", 2000);
        } else if (checkIfIDExists(id)) {
            displayError("ID already used!", "", 2000);
        } else if (checkIfIDExists(id)) {
            displayError("ID already used!", "", 2000);
        } else {
            break; // ID is available, break out of the loop
        }
    }

    // Start the enrollment process and display the ID on the LCD
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Enrolling ID #");
    lcd.setCursor(0, 1);
    lcd.print(id);
    Serial.print("Enrolling ID #");
    Serial.println(id);

    // If enrollment fails, loop back to ask for a new ID
    while (!getFingerprintEnroll(id)) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Enroll failed!");
        lcd.setCursor(0, 1);
        lcd.print("Enter new ID:");

        // Re-prompt for a new ID
        id = 0; // Reset the ID
        while (true) {
            id = readNumber(); // Ask for a new fingerprint ID

            if (id == 0) { // ID #0 not allowed
                displayError("Invalid ID!", "", 2000);
            } else if (checkIfIDExists(id)) {
                displayError("ID already used!", "", 2000);
            } else {
                break; // ID is available, break out of the loop
            }
        }

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Enrolling ID #");
        lcd.setCursor(0, 1);
        lcd.print(id);
        Serial.print("Enrolling ID #");
        Serial.println(id);
    }

    // After successful enrollment, display success message
    displayError("Fingerprint Added", "", 2000);

    // Call displayMenu to go back to the menu
    displayMenu();
}

uint8_t readNumber() {
    uint8_t num = 0;
    String input = "";
    while (true) {
        char key = keypad.getKey();
        if (key) { // Check if a key is pressed
            if (key == '#') { // Confirm input
                if (input.length() > 0) {
                    num = input.toInt(); // Convert the input string to a number
                    break; // Exit the loop
                }
            } else if (key == '*') { // Clear input on *
                // input = ""; // Reset the input
                displayMenu();
                
            } else if (key == 'D') { // Clear input on *
                input = ""; // Reset the input
                lcd.clear();
                lcd.setCursor(0, 0);
                lcd.print("Please enter ID:");
                continue;
                
            } else {
                input += key; // Add key to input string
                lcd.setCursor(0, 1);
                lcd.print(input); // Display current input
            }
        }
    }
    return num;
}

bool checkIfIDExists(uint8_t id) {
    return (finger.loadModel(id) == FINGERPRINT_OK); // Check if ID exists
}

bool getFingerprintEnroll(uint8_t id) {
    int p = -1;
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Place finger...");

    Serial.print("Waiting for valid finger to enroll as #");
    Serial.println(id);
    while (p != FINGERPRINT_OK) {
        p = finger.getImage();
        handleFingerprintImageResponse(p);
        delay(1000); // Small delay before trying again
    }

    p = finger.image2Tz(1);
    if (p != FINGERPRINT_OK) {
        handleImageConversionError(p);
        return false; // Return to allow re-prompting for a new ID
    }

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Remove finger...");
    delay(2000);
    while (finger.getImage() != FINGERPRINT_NOFINGER);
    Serial.print("ID "); Serial.println(id);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Place same finger");

    while (finger.getImage() != FINGERPRINT_OK);

    p = finger.image2Tz(2);
    if (p != FINGERPRINT_OK) {
        handleImageConversionError(p);
        return false; // Return to allow re-prompting for a new ID
    }

    p = finger.createModel();
    if (p == FINGERPRINT_OK) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Prints matched!");
    } else {
        handleEnrollmentError(p);
        return false; // Return to allow re-prompting for a new ID
    }

    p = finger.storeModel(id);
    if (p == FINGERPRINT_OK) {
        Serial.println("Stored!");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Fingerprint Added");
    } else {
        handleStoreError(p);
        return false; // Return to allow re-prompting for a new ID
    }

    return true; // Return true to indicate success
}

void handleFingerprintImageResponse(int p) {
    switch (p) {
        case FINGERPRINT_OK:
            Serial.println("Image taken");
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Image taken");
            break;
        case FINGERPRINT_NOFINGER:
            Serial.println("No finger detected, try again");
            lcd.setCursor(0, 1);
            lcd.print("No finger detected");
            break;
        case FINGERPRINT_PACKETRECIEVEERR:
            Serial.println("Communication error");
            lcd.setCursor(0, 1);
            lcd.print("Comm error");
            break;
        case FINGERPRINT_IMAGEFAIL:
            Serial.println("Imaging error");
            lcd.setCursor(0, 1);
            lcd.print("Imaging error");
            break;
        default:
            Serial.println("Unknown error");
            lcd.setCursor(0, 1);
            lcd.print("Unknown error");
            break;
    }
}

void handleImageConversionError(int p) {
    Serial.println("Conversion error");
    lcd.setCursor(0, 1);
    lcd.print("Conversion error");
}

void handleEnrollmentError(int p) {
    Serial.print("Error creating model: ");
    Serial.println(p);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Error: Fingerprints Mismatch");
    delay(2000);
    lcd.clear(); // Clear the screen before returning to the ID prompt
}

void handleStoreError(int p) {
    Serial.print("Error storing model: ");
    Serial.println(p);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Store Error");
    delay(2000);
    lcd.clear(); // Clear the screen before returning to the ID prompt
}

void displayError(const char* msg1, const char* msg2, int delayTime) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(msg1);
    if (msg2[0] != '\0') { // Check if the second message is not empty
        lcd.setCursor(0, 1);
        lcd.print(msg2);
    }
    if (delayTime > 0) {
        delay(delayTime); // Delay to show the message
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Door Locked!");
        lcd.setCursor(0, 1);
        lcd.print("Enter PIN:");
    }
}