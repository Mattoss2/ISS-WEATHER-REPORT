import serial
import re
import time

PORT = "/dev/ttyACM0"
BAUDRATE = 9600

# Variables globales pour stocker les dernières valeurs valides
temperature = 0.0
humidity = 0.0
last_update = 0

# Regex comme avant
temp_pattern = re.compile(r"Temp[ée]rature[=\s]+([\d.]+)°?C")
hum_pattern = re.compile(r"Humidit[éey]+[=\s]+([\d.]+)")

ser = serial.Serial(PORT, BAUDRATE, timeout=1)

def update_sensor_data():
    """Lit une ligne et met à jour les variables globales"""
    global temperature, humidity, last_update
    
    line = ser.readline().decode("utf-8", errors="ignore").strip()
    if not line:
        return False

    print("Reçu :", line)

    # Mise à jour température
    temp_match = temp_pattern.search(line)
    if temp_match:
        temperature = round(float(temp_match.group(1)), 1)
        print(f"Temp mise à jour : {temperature} °C")

    # Mise à jour humidité
    hum_match = hum_pattern.search(line)
    if hum_match:
        humidity = round(float(hum_match.group(1)), 1)
        print(f"Hum mise à jour : {humidity} %")

    last_update = time.time()
    return True

def get_sensor_data():
    """Retourne les dernières valeurs connues"""
    return {
        "temperature": round(temperature, 1),
        "humidity": round(humidity, 1),
        "last_update": last_update
    }

# Boucle principale
try:
    while True:
        update_sensor_data()
        
        # Exemple d'utilisation des variables
        print(f"Données actuelles - T:{temperature}°C H:{humidity}% (maj {time.time()-last_update:.1f}s)")
        
        time.sleep(1)  # Rafraîchissement toutes les secondes

except KeyboardInterrupt:
    pass
finally:
    ser.close()
    print(f"Données finales - T:{temperature}°C H:{humidity}%")