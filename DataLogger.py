#!/usr/bin/python3

import sqlite3
import serial
from datetime import datetime

def kelvin_to_fahrenheit(kelvin):
    """
    Convert temperature from Kelvin to Fahrenheit.

    Args:
    kelvin (float): Temperature in Kelvin.

    Returns:
    float: Temperature converted to Fahrenheit.
    """
    fahrenheit = (kelvin - 273.15) * 9/5 + 32
    return fahrenheit

# Connect to database and open serial connection to STM Board
db = sqlite3.connect('/var/www/html/temperature.db')
cursor = db.cursor()
ser = serial.Serial('/dev/ttyACM0', 115200)

# Write character to STM Board and read the returned message, temperature in Kelvin
ser.write(b'h');
line = ser.readline();
line = line.decode();
# Cut ending characters off so only numbers remain
linesp = line.split('\r');
# Convert to float to prep for temp conversion
line = float(linesp[0]);
# Convert to Farenheit
line = kelvin_to_fahrenheit(line)
# Get datetime and place in ISO Format to only use a single table row
timestamp = datetime.now()
timestamp = timestamp.isoformat()

# Add data to database
cursor.execute("INSERT INTO tempData VALUES (?, ?)", (line, timestamp))

# Clean up
db.commit()
db.close()
