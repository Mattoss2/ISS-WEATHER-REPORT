#!/usr/bin/env python3
"""
DHT11 Arduino → Table 'donnees_capteurs' MariaDB
Champs : date_mesure, temperature, humidite
Ubuntu 24.04 Apache phpMyAdmin
"""

import serial
import re
import pymysql
import time

# ========== CONFIG MARIA DB (Bitwarden) ==========
DB_CONFIG = {
    'host': 'localhost',
    'user': 'makio',
    'password': 'anto123',
    'database': 'meteo',
    'charset': 'utf8mb4',
    'autocommit': True
}

# Arduino
PORT = "/dev/ttyACM0"
BAUDRATE = 9600
SAVE_INTERVAL = 15 * 60  # 15 minutes

temperature = None
humidity = None
last_save_time = 0

# Regex Arduino DHT11
DHT_PATTERN = re.compile(r"Temperature:\s*(\d+)\s*°C\s*Humidity:\s*(\d+)\s*%")

def test_connection():
    """Test MariaDB"""
    try:
        conn = pymysql.connect(**DB_CONFIG)
        conn.close()
        print("Connexion meteo OK")
        return True
    except Exception as e:
        print(f"Connexion KO : {e}")
        return False

def save_to_db(temp, hum):
    """INSERT dans donnees_capteurs (id_immeuble=NULL)"""
    try:
        conn = pymysql.connect(**DB_CONFIG)
        with conn.cursor() as cursor:
            # id_immeuble=NULL, date_mesure=CURRENT_TIMESTAMP auto
            cursor.execute(
                "INSERT INTO donnees_capteurs (temperature, humidite) VALUES (%s, %s)",
                (temp, hum)
            )
        conn.close()
        print(f"INSERT T:{temp}°C H:{hum}% → phpMyAdmin")
        return True
    except Exception as e:
        print(f"INSERT ERROR : {e}")
        return False

def latest_record():
    """Dernière ligne DB (debug)"""
    try:
        conn = pymysql.connect(**DB_CONFIG)
        with conn.cursor() as cursor:
            cursor.execute("SELECT id_donnees_capteurs, temperature, humidite, date_mesure FROM donnes_capteurs ORDER BY id_donnees_capteurs DESC LIMIT 1")
            row = cursor.fetchone()
            if row:
                print(f"DERNIÈRE : #{row[0]} T:{row[1]}°C H:{row[2]}% {row[3]}")
        conn.close()
    except:
        pass

# ========== MAIN ==========
print("DHT11 Arduino → donnees_capteurs MariaDB")

if not test_connection():
    exit(1)

latest_record()

try:
    ser = serial.Serial(PORT, BAUDRATE, timeout=1)
    print(f"Arduino {PORT} OK")
    
    while True:
        line = ser.readline().decode("utf-8", errors="ignore").strip()
        if line:
            print(f"Arduino: {line}")
            
            match = DHT_PATTERN.search(line)
            if match:
                temp = round(float(match.group(1)), 1)
                hum = round(float(match.group(2)), 1)
                
                print(f"MESURE T:{temp}°C H:{hum}%")
                
                # Sauvegarde 15min
                if time.time() - last_save_time >= SAVE_INTERVAL:
                    if save_to_db(temp, hum):
                        latest_record()
                        last_save_time = time.time()
            
            time.sleep(2)  # Cycle DHT11
            
except KeyboardInterrupt:
    print("\nArrêt propre")
except Exception as e:
    print(f"Erreur : {e}")
finally:
    if 'ser' in locals():
        ser.close()
        print("Arduino déconnecté")