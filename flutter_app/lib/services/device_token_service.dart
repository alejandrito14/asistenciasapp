import 'dart:math';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/auth_user.dart';
import 'auth_service.dart';

class DeviceTokenService {
  static const _deviceIdKey = 'installation_device_id';
  static bool _listenerAttached = false;
  static int? _currentUserId;
  static String? _currentDeviceId;

  static Future<void> syncForUser(AuthUser user) async {
    final userId = user.id;
    if (userId <= 0) return;

    try {
      _currentUserId = userId;
      final deviceId = await _getOrCreateDeviceId();
      _currentDeviceId = deviceId;

      final messaging = FirebaseMessaging.instance;
      await messaging.setAutoInitEnabled(true);
      final permission = await messaging.requestPermission(alert: true, badge: true, sound: true);
      debugPrint('FCM: permiso notificaciones = ${permission.authorizationStatus}');
      final token = await messaging.getToken();
      if (token == null || token.isEmpty) {
        debugPrint('FCM: no se obtuvo token para el dispositivo.');
        return;
      }

      debugPrint('FCM: token obtenido = $token');

      await _registerToken(userId: userId, deviceId: deviceId, deviceToken: token);
      _attachRefreshListener();
    } catch (e) {
      debugPrint('FCM sync error: $e');
    }
  }

  static void _attachRefreshListener() {
    if (_listenerAttached) return;
    _listenerAttached = true;

    FirebaseMessaging.instance.onTokenRefresh.listen((token) async {
      final userId = _currentUserId;
      final deviceId = _currentDeviceId;
      if (userId == null || userId <= 0 || deviceId == null || deviceId.isEmpty) {
        return;
      }

      try {
        await _registerToken(userId: userId, deviceId: deviceId, deviceToken: token);
      } catch (e) {
        debugPrint('FCM token refresh error: $e');
      }
    });
  }

  static Future<String> _getOrCreateDeviceId() async {
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getString(_deviceIdKey);
    if (stored != null && stored.isNotEmpty) {
      return stored;
    }

    final newId = 'device_${DateTime.now().millisecondsSinceEpoch}_${Random().nextInt(1 << 32)}';
    await prefs.setString(_deviceIdKey, newId);
    return newId;
  }

  static Future<void> _registerToken({
    required int userId,
    required String deviceId,
    required String deviceToken,
  }) async {
    final result = await AuthService.registerDeviceToken(
      userId: userId,
      deviceId: deviceId,
      deviceToken: deviceToken,
    );

    if (!result.success) {
      debugPrint('No se pudo registrar el token del dispositivo: ${result.message}');
    } else {
      debugPrint('Token de dispositivo registrado correctamente.');
    }
  }
}
