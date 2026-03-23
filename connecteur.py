import serial
import re

PORT = "/dev/ttyACM0"   
BAUDRATE = 9600         
pattern = re.compile(r"Humidité:\s*([\d.]+)%\s*Température:\s*([\d.]+)")

def main():
    ser = serial.Serial(PORT, BAUDRATE, timeout=1)

    print(f"Lecture sur {PORT} à {BAUDRATE} bauds...")

    try:
        while True:
            
            line = ser.readline().decode("utf-8", errors="ignore").strip()
            if not line:
                continue

            print("Reçu brut :", line)

            
            match = pattern.search(line)
            if match:
                humidity_str, temp_str = match.groups()
                try:
                    humidity = float(humidity_str)
                    temperature = float(temp_str)
                    print(f"Humidité = {humidity} % | Température = {temperature} °C")
                except ValueError:
                    print("Impossible de convertir en float :", humidity_str, temp_str)
            else:
                print("Ligne non reconnue, format différent ?")

    except KeyboardInterrupt:
        print("Arrêt demandé (Ctrl+C).")
    finally:
        ser.close()
        print("Port série fermé.")

if __name__ == "__main__":
    main()