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

  static Future<ApiResponse<Map<String, dynamic>>> registerUser({
    required String matricula,
    required String nombre,
    required String apellidoPaterno,
    required String apellidoMaterno,
    required String telefono,
    required String correo,
    required String tutorNombre,
    required String tutorApellidoPaterno,
    required String tutorApellidoMaterno,
    required String tutorTelefono,
    required String tutorCorreo,
    required String password,
    required String rol,
  }) async {
    final response = await _postFull(
      action: 'register',
      body: {
        'matricula': matricula,
        'nombre': nombre,
        'apellido_paterno': apellidoPaterno,
        'apellido_materno': apellidoMaterno,
        'telefono': telefono,
        'correo': correo,
        'tutor_nombre': tutorNombre,
        'tutor_apellido_paterno': tutorApellidoPaterno,
        'tutor_apellido_materno': tutorApellidoMaterno,
        'tutor_telefono': tutorTelefono,
        'tutor_correo': tutorCorreo,
        'password': password,
        'rol': rol,
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
      data: response.data,
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> registerAlumno({
    required String matricula,
    required String nombre,
    required String apellidoPaterno,
    required String apellidoMaterno,
    required String telefono,
    required String correo,
    required String tutorNombre,
    required String tutorApellidoPaterno,
    required String tutorApellidoMaterno,
    required String tutorTelefono,
    required String tutorCorreo,
    required String password,
  }) {
    return registerUser(
      matricula: matricula,
      nombre: nombre,
      apellidoPaterno: apellidoPaterno,
      apellidoMaterno: apellidoMaterno,
      telefono: telefono,
      correo: correo,
      tutorNombre: tutorNombre,
      tutorApellidoPaterno: tutorApellidoPaterno,
      tutorApellidoMaterno: tutorApellidoMaterno,
      tutorTelefono: tutorTelefono,
      tutorCorreo: tutorCorreo,
      password: password,
      rol: 'ALUMNO',
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> verifyRegistrationCode({
    required int userId,
    required String correo,
    required String code,
  }) async {
    final response = await _post(
      action: 'verifyRegistrationCode',
      body: {
        'user_id': userId,
        'correo': correo,
        'code': code,
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
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> resendVerificationCode({
    required int userId,
    required String correo,
  }) async {
    final response = await _post(
      action: 'resendVerificationCode',
      body: {
        'user_id': userId,
        'correo': correo,
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
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> deleteAccount({
    required int userId,
    required String correo,
    required String rol,
  }) async {
    final response = await _post(
      action: 'deleteAccount',
      body: {
        'user_id': userId,
        'correo': correo,
        'rol': rol,
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
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> registerDeviceToken({
    required int userId,
    required String deviceId,
    required String deviceToken,
  }) async {
    final response = await _post(
      action: 'registerDeviceToken',
      body: {
        'user_id': userId,
        'device_id': deviceId,
        'device_token': deviceToken,
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
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> updateProfilePhoto({
    required int userId,
    required String rol,
    required String filePath,
    required String fileName,
  }) async {
    final uri = Uri.parse(ApiConfig.baseUrl);
    final request = http.MultipartRequest('POST', uri)
      ..fields['action'] = 'updateProfilePhoto'
      ..fields['user_id'] = userId.toString()
      ..fields['rol'] = rol;

    request.files.add(
      await http.MultipartFile.fromPath(
        'photo',
        filePath,
        filename: fileName,
      ),
    );

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);

    if (response.statusCode != 200) {
      return const ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'No se pudo comunicar con el servidor.',
      );
    }

    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      return const ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'Respuesta inválida del servidor.',
      );
    }

    return ApiResponse<Map<String, dynamic>>(
      success: decoded['success'] == true,
      message: decoded['message'] ?? 'Sin mensaje',
      data: decoded,
    );
  }

  static Future<ApiResponse<dynamic>> _post({
    required String action,
    required Map<String, dynamic> body,
  }) async {
    final response = await _postRaw(action: action, body: body);

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

  static Future<ApiResponse<Map<String, dynamic>>> _postFull({
    required String action,
    required Map<String, dynamic> body,
  }) async {
    final response = await _postRaw(action: action, body: body);

    if (response.statusCode != 200) {
      return const ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'No se pudo comunicar con el servidor.',
      );
    }

    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      return const ApiResponse<Map<String, dynamic>>(
        success: false,
        message: 'Respuesta inválida del servidor.',
      );
    }

    return ApiResponse<Map<String, dynamic>>(
      success: decoded['success'] == true,
      message: decoded['message'] ?? 'Sin mensaje',
      data: decoded,
    );
  }

  static Future<http.Response> _postRaw({
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
    return response;
  }
}
