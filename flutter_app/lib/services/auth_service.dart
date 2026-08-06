import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';
import '../models/api_response.dart';
import '../models/auth_user.dart';

class AuthService {
  static const _sessionKey = 'auth_user_session';

  static Future<ApiResponse<AuthUser>> login({
    required String correo,
    required String password,
  }) async {
    final response = await _post(
      action: 'login',
      body: {
        'correo': correo,
        'password': password,
      },
    );

    if (!response.success) {
      return ApiResponse<AuthUser>(
        success: false,
        message: response.message,
      );
    }

    final userJson = response.data as Map<String, dynamic>;
    return ApiResponse<AuthUser>(
      success: true,
      message: 'Login correcto',
      data: AuthUser.fromJson(userJson),
    );
  }

  static Future<void> saveSession(AuthUser user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_sessionKey, jsonEncode(user.toJson()));
  }

  static Future<AuthUser?> restoreSession() async {
    final prefs = await SharedPreferences.getInstance();
    return AuthUser.fromEncoded(prefs.getString(_sessionKey));
  }

  static Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_sessionKey);
  }

  static Future<ApiResponse<Map<String, dynamic>>> registerAlumno({
    required String matricula,
    required String nombre,
    required String apellidoPaterno,
    required String apellidoMaterno,
    required String telefono,
    required String correo,
    required String password,
  }) async {
    final response = await _post(
      action: 'registerAlumno',
      body: {
        'matricula': matricula,
        'nombre': nombre,
        'apellido_paterno': apellidoPaterno,
        'apellido_materno': apellidoMaterno,
        'telefono': telefono,
        'correo': correo,
        'password': password,
      },
    );

    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: response.message,
      );
    }

    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: response.data as Map<String, dynamic>,
    );
  }

  static Future<ApiResponse<dynamic>> _post({
    required String action,
    required Map<String, dynamic> body,
  }) async {
    final uri = Uri.parse(ApiConfig.baseUrl);
    final payload = {
      'action': action,
      ...body,
    };

    final response = await http.post(
      uri,
      headers: {
        'Content-Type': 'application/json; charset=UTF-8',
      },
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      return const ApiResponse<dynamic>(
        success: false,
        message: 'No se pudo comunicar con el servidor.',
      );
    }

    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      return const ApiResponse<dynamic>(
        success: false,
        message: 'Respuesta inválida del servidor.',
      );
    }

    return ApiResponse<dynamic>(
      success: decoded['success'] == true,
      message: decoded['message'] ?? 'Sin mensaje',
      data: decoded['user'] ?? decoded['alumno'] ?? decoded,
    );
  }
}
