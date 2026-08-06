import 'package:flutter/material.dart';

import '../services/auth_service.dart';
import '../widgets/app_button.dart';
import '../widgets/app_text_field.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _matriculaController = TextEditingController();
  final _nombreController = TextEditingController();
  final _apellidoPaternoController = TextEditingController();
  final _apellidoMaternoController = TextEditingController();
  final _telefonoController = TextEditingController();
  final _correoController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _message;

  Future<void> _registerAlumno() async {
    setState(() {
      _isLoading = true;
      _message = null;
    });

    final result = await AuthService.registerAlumno(
      matricula: _matriculaController.text.trim(),
      nombre: _nombreController.text.trim(),
      apellidoPaterno: _apellidoPaternoController.text.trim(),
      apellidoMaterno: _apellidoMaternoController.text.trim(),
      telefono: _telefonoController.text.trim(),
      correo: _correoController.text.trim(),
      password: _passwordController.text,
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
      _message = result.message;
    });

    if (result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
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
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 520),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Material(
                        color: Colors.white,
                        elevation: 3,
                        shadowColor: Colors.black12,
                        shape: const CircleBorder(),
                        child: IconButton(
                          onPressed: () => Navigator.of(context).pop(),
                          icon: const Icon(Icons.arrow_back_ios_new_rounded),
                          color: const Color(0xFF1F1F1F),
                          tooltip: 'Regresar',
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Text(
                        'Regresar',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1F1F1F),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Card(
                    elevation: 10,
                    shadowColor: Colors.black12,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(32)),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 72,
                            height: 72,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: LinearGradient(
                                colors: [Color(0xFFEF6C00), Color(0xFF2E7D32)],
                              ),
                            ),
                            child: const Icon(Icons.person_add_alt_1_rounded, size: 38, color: Colors.white),
                          ),
                          const SizedBox(height: 18),
                          Text(
                            'Crear cuenta',
                            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                  fontWeight: FontWeight.w800,
                                  color: const Color(0xFF1F1F1F),
                                ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Regístrate para comenzar',
                            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                  color: Colors.black54,
                                ),
                          ),
                          const SizedBox(height: 24),
                          AppTextField(
                            controller: _matriculaController,
                            label: 'Matrícula',
                          ),
                          const SizedBox(height: 14),
                          AppTextField(
                            controller: _nombreController,
                            label: 'Nombre completo',
                          ),
                          const SizedBox(height: 14),
                          AppTextField(
                            controller: _apellidoPaternoController,
                            label: 'Apellido paterno',
                          ),
                          const SizedBox(height: 14),
                          AppTextField(
                            controller: _apellidoMaternoController,
                            label: 'Apellido materno',
                          ),
                          const SizedBox(height: 14),
                          AppTextField(
                            controller: _telefonoController,
                            label: 'Teléfono',
                            keyboardType: TextInputType.phone,
                          ),
                          const SizedBox(height: 14),
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
                            label: 'Registrar alumno',
                            isLoading: _isLoading,
                            onPressed: _registerAlumno,
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
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
