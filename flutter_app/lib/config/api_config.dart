class ApiConfig {
  // Usa el emulador con 10.0.2.2.
  // Usa la IP de tu laptop con 192.168.1.202 para un dispositivo físico.
  static const bool useEmulator = false;

  static const String emulatorBaseUrl = 'http://10.0.2.2:8080/api.php';
  static const String physicalDeviceBaseUrl = 'http://192.168.1.202:8080/api.php';

  static const String baseUrl =
      useEmulator ? emulatorBaseUrl : physicalDeviceBaseUrl;
}
