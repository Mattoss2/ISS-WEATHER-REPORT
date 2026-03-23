import serial
import re

PORT = "/dev/ttyACM0"   # à adapter
BAUDRATE = 9600

# Regex pour ton format réel
temp_pattern = re.compile(r"Temp[ée]rature[=\s]+([\d.]+)°?C")
hum_pattern = re.compile(r"Humidit[éey]+[=\s]+([\d.]+)")

ser = serial.Serial(PORT, BAUDRATE, timeout=1)
print(f"Lecture sur {PORT}...")

try:
    while True:
        line = ser.readline().decode("utf-8", errors="ignore").strip()
        if not line:
            continue

        print("Reçu brut :", line)

        # Parse température
        temp_match = temp_pattern.search(line)
        if temp_match:
            temperature = float(temp_match.group(1))
            print(f"✅ Température = {temperature} °C")

        # Parse humidité
        hum_match = hum_pattern.search(line)
        if hum_match:
            humidity = float(hum_match.group(1))
            print(f"✅ Humidité = {humidity} %")

except KeyboardInterrupt:
    pass
finally:
    ser.close()