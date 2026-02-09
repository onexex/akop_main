import serial
import time
import json
import re
from datetime import datetime, timedelta

SERIAL_PORT = 'COM6' 
BAUD_RATE = 115200

def get_inbox():
    try:
        ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=5)
        time.sleep(1)

        # 1. Initialize and Select SIM Storage
        ser.write(b'AT+CPMS="SM","SM","SM"\r\n')
        time.sleep(0.5)
        ser.write(b'AT+CMGF=1\r\n') 
        time.sleep(0.5)

        # 2. Read all messages
        ser.write(b'AT+CMGL="ALL"\r\n')
        time.sleep(2)
        response = ser.read_all().decode(errors='ignore')

        lines = response.split('\n')
        raw_parts = []
        
        # Parse everything into a list first
        for i in range(len(lines)):
            if "+CMGL:" in lines[i]:
                try:
                    meta = lines[i].split(',')
                    sender = meta[2].replace('"', '').strip()
                    
                    # Create a real datetime object for comparison
                    date_raw = meta[4].replace('"', '').strip() # YY/MM/DD
                    time_raw = meta[5].replace('"', '').split('+')[0].strip() # HH:MM:SS
                    dt_obj = datetime.strptime(f"20{date_raw} {time_raw}", "%Y/%m/%d %H:%M:%S")
                    
                    content = lines[i+1].strip()
                    raw_parts.append({'sender': sender, 'dt': dt_obj, 'msg': content})
                except:
                    continue

        # 3. CONCATENATION LOGIC (The "Time-Window" fix)
        # Sort by sender, then by time
        raw_parts.sort(key=lambda x: (x['sender'], x['dt']))
        
        merged = []
        if raw_parts:
            current = raw_parts[0]
            
            for next_part in raw_parts[1:]:
                # Calculate time difference
                time_diff = (next_part['dt'] - current['dt']).total_seconds()
                
                # If same sender AND arrived within 15 seconds of the previous part
                if next_part['sender'] == current['sender'] and time_diff <= 15:
                    current['msg'] += " " + next_part['msg'] # Concatenate
                    # Update 'dt' to the latest part's time for the next comparison
                    current['dt'] = next_part['dt']
                else:
                    merged.append(current)
                    current = next_part
            merged.append(current)

        # 4. Final Output Formatting
        final_output = []
        for m in merged:
            final_output.append({
                'sender': m['sender'],
                'time': m['dt'].strftime('%Y-%m-%d %H:%M:%S'),
                'message': m['msg']
            })

        # 5. AGGRESSIVE DELETE (Clean the SIM after success)
        if final_output:
            ser.reset_input_buffer()
            ser.write(b'AT+CMGD=1,4\r\n') 
            time.sleep(1)

        ser.close()
        return json.dumps(final_output)

    except Exception as e:
        return json.dumps([{"error": str(e)}])

if __name__ == "__main__":
    print(get_inbox())