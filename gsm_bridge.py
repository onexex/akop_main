import serial
import sys
import time

# Use the COM port you found in Step 1 (e.g., 'COM3')
SERIAL_PORT = 'COM3' 
BAUD_RATE = 115200 # Most SIM800C USB modules use 115200

def send_sms(phone_number, message):
    try:
        # Initialize Serial Connection
        ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=5)
        time.sleep(1) # Let the connection settle

        # 1. Test Communication
        ser.write(b'AT\r\n')
        time.sleep(0.5)
        
        # 2. Set SMS to Text Mode
        ser.write(b'AT+CMGF=1\r\n')
        time.sleep(0.5)

        # 3. Set Recipient Number
        ser.write(f'AT+CMGS="{phone_number}"\r\n'.encode())
        time.sleep(0.5)

        # 4. Send Message Content + Ctrl+Z (Hex 1A)
        ser.write(f'{message}\x1a'.encode())
        time.sleep(3) # Wait for network to process

        response = ser.read_all().decode()
        ser.close()
        
        if "OK" in response:
            print(f"SUCCESS: {response}")
        else:
            print(f"FAILED: {response}")

    except Exception as e:
        print(f"ERROR: {str(e)}")

if __name__ == "__main__":
    if len(sys.argv) > 2:
        send_sms(sys.argv[1], sys.argv[2])
    else:
        print("Usage: python gsm_bridge.py [number] [message]")