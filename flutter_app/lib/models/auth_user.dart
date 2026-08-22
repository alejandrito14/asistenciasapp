import 'dart:convert';

class AuthUser {
  final int id;
  final String nombre;
  final String correo;
  final String rol;
  final Map<String, dynamic>? maestro;
  final Map<String, dynamic>? alumno;

  const AuthUser({
    required this.id,
    required this.nombre,
    required this.correo,
    required this.rol,
    this.maestro,
    this.alumno,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: (json['id'] ?? 0) as int,
      nombre: (json['nombre'] ?? '') as String,
      correo: (json['correo'] ?? '') as String,
      rol: (json['rol'] ?? '') as String,
      maestro: json['maestro'] is Map<String, dynamic> ? json['maestro'] as Map<String, dynamic> : null,
      alumno: json['alumno'] is Map<String, dynamic> ? json['alumno'] as Map<String, dynamic> : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nombre': nombre,
      'correo': correo,
      'rol': rol,
      'maestro': maestro,
      'alumno': alumno,
    };
  }

  AuthUser copyWith({
    int? id,
    String? nombre,
    String? correo,
    String? rol,
    Map<String, dynamic>? maestro,
    Map<String, dynamic>? alumno,
  }) {
    return AuthUser(
      id: id ?? this.id,
      nombre: nombre ?? this.nombre,
      correo: correo ?? this.correo,
      rol: rol ?? this.rol,
      maestro: maestro ?? this.maestro,
      alumno: alumno ?? this.alumno,
    );
  }

  static AuthUser? fromEncoded(String? value) {
    if (value == null || value.isEmpty) return null;
    final decoded = jsonDecode(value);
    if (decoded is Map<String, dynamic>) {
      return AuthUser.fromJson(decoded);
    }
    return null;
  }
}
