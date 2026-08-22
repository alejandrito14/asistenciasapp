import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';

import '../config/api_config.dart';
import '../models/api_response.dart';

class DashboardService {
  static Future<ApiResponse<List<dynamic>>> teacherDashboard(int maestroId) async {
    final response = await _post(action: 'teacherDashboard', body: {'maestro_id': maestroId});
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    final data = (response.data as List<dynamic>? ?? <dynamic>[]);
    return ApiResponse<List<dynamic>>(success: true, message: response.message, data: data);
  }

  static Future<ApiResponse<Map<String, dynamic>>> teacherToggleSession({
    required int grupoMateriaMaestroId,
    int? usuarioId,
    double? latitud,
    double? longitud,
  }) async {
    final response = await _post(
      action: 'teacherToggleSession',
      body: {
        'grupo_materia_maestro_id': grupoMateriaMaestroId,
        if (usuarioId != null) 'usuario_id': usuarioId,
        if (latitud != null) 'latitud': latitud,
        if (longitud != null) 'longitud': longitud,
      },
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> schoolCalendarStatus({
    required String date,
  }) async {
    final response = await _post(
      action: 'schoolCalendarStatus',
      body: {'date': date},
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> studentDashboard(int alumnoId) async {
    final response = await _post(action: 'studentDashboard', body: {'alumno_id': alumnoId});
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<List<dynamic>>> studentGroupSubjects({
    required int alumnoId,
    required int grupoId,
  }) async {
    final response = await _post(
      action: 'studentGroupSubjects',
      body: {'alumno_id': alumnoId, 'grupo_id': grupoId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<List<dynamic>>> studentGroupSchedule({
    required int alumnoId,
    required int grupoId,
  }) async {
    final response = await _post(
      action: 'studentGroupSchedule',
      body: {'alumno_id': alumnoId, 'grupo_id': grupoId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> studentSubjectSchedule({
    required int alumnoId,
    required int grupoId,
    required int materiaId,
  }) async {
    final response = await _post(
      action: 'studentSubjectSchedule',
      body: {'alumno_id': alumnoId, 'grupo_id': grupoId, 'materia_id': materiaId},
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> studentRegisterAttendance({
    required int alumnoId,
    required int grupoId,
    required int materiaId,
  }) async {
    final response = await _post(
      action: 'studentRegisterAttendance',
      body: {'alumno_id': alumnoId, 'grupo_id': grupoId, 'materia_id': materiaId},
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> enrollGroup({
    required int alumnoId,
    required String pin,
  }) async {
    final response = await _post(action: 'enrollGroup', body: {'alumno_id': alumnoId, 'pin': pin});
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> teacherClassStudents({
    required int grupoMateriaMaestroId,
  }) async {
    final response = await _post(
      action: 'teacherClassStudents',
      body: {'grupo_materia_maestro_id': grupoMateriaMaestroId},
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> teacherSaveManualAttendance({
    required int grupoMateriaMaestroId,
    required List<Map<String, dynamic>> attendance,
  }) async {
    final response = await _post(
      action: 'teacherSaveManualAttendance',
      body: {
        'grupo_materia_maestro_id': grupoMateriaMaestroId,
        'attendance': attendance,
      },
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<List<dynamic>>> teacherSessionJustifications({
    required int sesionClaseId,
  }) async {
    final response = await _post(
      action: 'teacherSessionJustifications',
      body: {'sesion_clase_id': sesionClaseId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<List<dynamic>>> studentJustificationSessions({
    required int alumnoId,
  }) async {
    final response = await _post(
      action: 'studentJustificationSessions',
      body: {'alumno_id': alumnoId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<List<dynamic>>> teacherJustifications({
    required int maestroId,
  }) async {
    final response = await _post(
      action: 'teacherJustifications',
      body: {'maestro_id': maestroId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> teacherJustificationUpdate({
    required int justificationId,
    required String estatus,
  }) async {
    final response = await _post(
      action: 'teacherJustificationUpdate',
      body: {
        'justification_id': justificationId,
        'estatus': estatus,
      },
    );
    if (!response.success) {
      return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<List<dynamic>>> studentJustifications({
    required int alumnoId,
  }) async {
    final response = await _post(
      action: 'studentJustifications',
      body: {'alumno_id': alumnoId},
    );
    if (!response.success) {
      return ApiResponse<List<dynamic>>(success: false, message: response.message);
    }
    return ApiResponse<List<dynamic>>(
      success: true,
      message: response.message,
      data: (response.data as List<dynamic>?) ?? <dynamic>[],
    );
  }

  static Future<ApiResponse<Map<String, dynamic>>> studentSubmitJustification({
    required int alumnoId,
    required int sesionClaseId,
    required String motivo,
    String? filePath,
    String? fileName,
  }) async {
    if (filePath == null || filePath.isEmpty) {
      final response = await _post(
        action: 'studentSubmitJustification',
        body: {
          'alumno_id': alumnoId,
          'sesion_clase_id': sesionClaseId,
          'motivo': motivo,
        },
      );
      if (!response.success) {
        return ApiResponse<Map<String, dynamic>>(success: false, message: response.message);
      }
      return ApiResponse<Map<String, dynamic>>(
        success: true,
        message: response.message,
        data: (response.data as Map<String, dynamic>?) ?? <String, dynamic>{},
      );
    }

    final request = http.MultipartRequest('POST', Uri.parse(ApiConfig.baseUrl));
    request.fields['action'] = 'studentSubmitJustification';
    request.fields['alumno_id'] = alumnoId.toString();
    request.fields['sesion_clase_id'] = sesionClaseId.toString();
    request.fields['motivo'] = motivo;
    request.files.add(
      await http.MultipartFile.fromPath(
        'evidencia',
        filePath,
        filename: fileName,
        contentType: MediaType('application', 'octet-stream'),
      ),
    );

    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    if (response.statusCode != 200) {
      return const ApiResponse<Map<String, dynamic>>(success: false, message: 'No se pudo comunicar con el servidor.');
    }

    final decoded = jsonDecode(response.body);
    if (decoded is! Map<String, dynamic>) {
      return const ApiResponse<Map<String, dynamic>>(success: false, message: 'Respuesta inválida del servidor.');
    }

    if (decoded['success'] != true) {
      return ApiResponse<Map<String, dynamic>>(
        success: false,
        message: (decoded['message'] ?? 'Sin mensaje').toString(),
      );
    }

    return ApiResponse<Map<String, dynamic>>(
      success: true,
      message: (decoded['message'] ?? 'Justificación enviada correctamente.').toString(),
      data: (decoded['data'] as Map<String, dynamic>?) ?? <String, dynamic>{},
    );
  }

  static Future<ApiResponse<dynamic>> _post({
    required String action,
    required Map<String, dynamic> body,
  }) async {
    final uri = Uri.parse(ApiConfig.baseUrl);
    final payload = {'action': action, ...body};

    final response = await http.post(
      uri,
      headers: {'Content-Type': 'application/json; charset=UTF-8'},
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      return const ApiResponse<dynamic>(success: false, message: 'No se pudo comunicar con el servidor.');
    }

    dynamic decoded;
    try {
      decoded = jsonDecode(response.body);
    } catch (_) {
      return ApiResponse<dynamic>(
        success: false,
        message: 'El servidor respondió con un formato inválido.',
        data: response.body,
      );
    }
    if (decoded is! Map<String, dynamic>) {
      return const ApiResponse<dynamic>(success: false, message: 'Respuesta inválida del servidor.');
    }

    return ApiResponse<dynamic>(
      success: decoded['success'] == true,
      message: (() {
        final rawMessage = (decoded['message'] ?? 'Sin mensaje').toString();
        return rawMessage.trim().toUpperCase() == 'OK' ? '' : rawMessage;
      })(),
      data: decoded['data'] ?? decoded['user'] ?? decoded['alumno'] ?? decoded['group'] ?? decoded,
    );
  }
}
