🌡️ Temperature-Fan

Temperature-Fan is an IoT-based project that controls a fan based on temperature readings. It allows for remote monitoring and logging of the fan’s power status, making it ideal for applications like server rooms, greenhouses, or any environment where temperature regulation is crucial.

🚀 Features
	•	Automatic Fan Control: Turns the fan on or off based on temperature thresholds.
	•	IoT Integration: Control and monitor the fan remotely through a web interface.
	•	Logging: Automatically logs power on/off events for tracking and analysis.
	•	Web Dashboard: User-friendly interface to view temperature readings and fan status. ￼

🛠️ Technologies Used
	•	PHP: Backend scripting for server-side operations.
	•	C++: Microcontroller programming for reading temperature sensors and controlling the fan.
	•	CSS: Styling the web interface for better user experience
🔧 Setup Instructions
	1.	Hardware Setup:
	•	Connect a temperature sensor (e.g., DHT11) to your microcontroller.
	•	Connect the fan to a relay module controlled by the microcontroller.
	2.	Microcontroller Programming:
	•	Upload the C++ code from TFan/Temp_Low_High/ to your microcontroller.
	•	Ensure the code reads temperature data and controls the fan accordingly. ￼ ￼
	3.	Web Server Setup:
	•	Place index.php, style.css, and log.txt on your web server.
	•	Ensure the server has PHP support enabled. ￼
	4.	Accessing the Dashboard:
	•	Navigate to http://tempfan.atwebpages.com/index.php to view the dashboard.
	•	Monitor temperature readings and fan status in real-time.

📈 Usage
	•	The fan turns on when the temperature exceeds the high threshold and turns off when it drops below the low threshold.
	•	All power on/off events are logged in log.txt with timestamps.
	•	Use the web dashboard to monitor current temperature and fan status. ￼

🤝 Contributing

Contributions are welcome! Feel free to fork the repository and submit pull requests.

📄 License

This project is licensed under the MIT License
