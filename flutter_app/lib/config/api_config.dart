class ApiConfig {
  // Cambia esta bandera para apuntar a desarrollo o producción.
  static const bool useProduction = true;
  static const bool useEmulator = false;

  static const String emulatorBaseUrl = 'http://10.0.2.2:8080/api.php';
  static const String physicalDeviceBaseUrl = 'http://192.168.1.202:8080/api.php';
  static const String productionBaseUrl = 'https://asistencias.speeddev.mx/api.php';

  static const String baseUrl =
      useProduction
          ? productionBaseUrl
          : (useEmulator ? emulatorBaseUrl : physicalDeviceBaseUrl);
}
