import serial
import re
import time

PORT = "/dev/ttyACM0"
BAUDRATE = 9600

# Variables globales
temperature = None
humidity = None
last_temp_time = 0
last_hum_time = 0

# Regex corrigées pour ton format exact
temp_pattern = re.compile(r"Temp(erature|érature)[=\s]*([\d.]+)C?", re.IGNORECASE)
hum_pattern = re.compile(r"Hum(dity|idité|idity)[=\s]*([\d.]+)%?", re.IGNORECASE)

ser = serial.Serial(PORT, BAUDRATE, timeout=1)
print(f"Lecture sur {PORT}...")

def are_both_values_received():
    """Vérifie si les deux valeurs ont été reçues récemment"""
    return temperature is not None and humidity is not None

try:
    while True:
        line = ser.readline().decode("utf-8", errors="ignore").strip()
        if not line:
            continue

        print(f"Reçu : {line}")

        # Parse température
        temp_match = temp_pattern.search(line)
        if temp_match:
            temp_str = temp_match.group(2)
            temperature = round(float(temp_str), 1)
            last_temp_time = time.time()
            print(f"Temp mise à jour : {temperature} °C")

        # Parse humidité (regex plus flexible)
        hum_match = hum_pattern.search(line)
        if hum_match:
            hum_str = hum_match.group(2)
            humidity = round(float(hum_str), 1)
            last_hum_time = time.time()
            print(f"Humidite mise à jour : {humidity} %")

        # Affichage UNIQUEMENT si les deux sont reçues
        if are_both_values_received():
            print(f"Données actuelles - T:{temperature}°C H:{humidity}% (maj {time.time()-max(last_temp_time, last_hum_time):.1f}s)")

        time.sleep(0.1)  # Petit délai pour éviter spam

except KeyboardInterrupt:
    pass
finally:
    ser.close()
    if temperature is not None and humidity is not None:
        print(f"Données finales - T:{temperature}°C H:{humidity}%")