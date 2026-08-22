class ApiConfig {
  // Cambia esta bandera para apuntar a desarrollo o producción.
  static const bool useProduction = false;
  static const bool useEmulator = false;

  static const String emulatorBaseUrl = 'http://10.0.2.2:8080/api.php';
  static const String physicalDeviceBaseUrl = 'http://192.168.1.200:8080/api.php';
  static const String productionBaseUrl = 'https://asistencias.speeddev.mx/api.php';

  static const String baseUrl =
      useProduction
          ? productionBaseUrl
          : (useEmulator ? emulatorBaseUrl : physicalDeviceBaseUrl);

  static String get serverRootUrl {
    if (baseUrl.endsWith('/api.php')) {
      return baseUrl.substring(0, baseUrl.length - '/api.php'.length) + '/';
    }
    return baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';
  }

  static Uri resolveServerUrl(String relativePath) {
    return Uri.parse(serverRootUrl).resolve(relativePath);
  }
}
