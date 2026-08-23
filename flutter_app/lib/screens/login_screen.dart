import 'package:flutter/material.dart';

import '../services/auth_service.dart';
import '../services/device_token_service.dart';
import '../widgets/app_button.dart';
import '../widgets/app_text_field.dart';
import '../models/auth_user.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _correoController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _message;

  Future<void> _login() async {
    setState(() {
      _isLoading = true;
      _message = null;
    });

    final result = await AuthService.login(
      correo: _correoController.text.trim(),
      password: _passwordController.text,
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
      _message = result.message;
    });

    if (result.success) {
      final user = result.data;
      if (user is AuthUser) {
        await AuthService.saveSession(user);
        await DeviceTokenService.syncForUser(user);
        if (user.rol.toUpperCase() == 'MAESTRO') {
          Navigator.of(context).pushReplacementNamed('/teacher', arguments: user);
        } else if (user.rol.toUpperCase() == 'ALUMNO') {
          Navigator.of(context).pushReplacementNamed('/student', arguments: user);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Rol no soportado: ${user.rol}')),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFFFFE0B2),
              Color(0xFFF7F7F7),
              Color(0xFFE8F5E9),
            ],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 460),
                child: Card(
                  elevation: 10,
                  shadowColor: Colors.black12,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(32)),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 86,
                          height: 86,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            gradient: LinearGradient(
                              colors: [Color(0xFFEF6C00), Color(0xFF2E7D32)],
                            ),
                          ),
                          child: const Icon(Icons.school_rounded, size: 44, color: Colors.white),
                        ),
                        const SizedBox(height: 18),
                        Text(
                          'Iniciar sesión',
                          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFF1F1F1F),
                              ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Accede a tu cuenta para continuar',
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                color: Colors.black54,
                              ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 28),
                        AppTextField(
                          controller: _correoController,
                          label: 'Correo electrónico',
                          keyboardType: TextInputType.emailAddress,
                        ),
                        const SizedBox(height: 14),
                        AppTextField(
                          controller: _passwordController,
                          label: 'Contraseña',
                          obscureText: true,
                        ),
                        const SizedBox(height: 24),
                        AppButton(
                          label: 'Entrar',
                          isLoading: _isLoading,
                          onPressed: _login,
                        ),
                        const SizedBox(height: 10),
                        TextButton(
                          onPressed: () {
                            Navigator.of(context).pushNamed('register');
                          },
                          child: const Text('¿No tienes cuenta? Crear cuenta'),
                        ),
                        if (_message != null) ...[
                          const SizedBox(height: 12),
                          Text(
                            _message!,
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Color(0xFFB00020)),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
