import 'package:flutter/material.dart';

import '../services/auth_service.dart';
import 'student_dashboard_screen.dart';

class VerifyAccountScreen extends StatefulWidget {
  final int userId;
  final String correo;
  final String password;
  final bool verificationSent;

  const VerifyAccountScreen({
    super.key,
    required this.userId,
    required this.correo,
    required this.password,
    this.verificationSent = true,
  });

  @override
  State<VerifyAccountScreen> createState() => _VerifyAccountScreenState();
}

class _VerifyAccountScreenState extends State<VerifyAccountScreen> {
  final _codeController = TextEditingController();
  bool _isLoading = false;
  bool _isResending = false;
  String? _message;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _verifyCode() async {
    final code = _codeController.text.trim();
    if (code.length != 6) {
      setState(() {
        _message = 'Ingresa el código de 6 dígitos.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _message = null;
    });

    final verifyResult = await AuthService.verifyRegistrationCode(
      userId: widget.userId,
      correo: widget.correo,
      code: code,
    );

    if (!mounted) return;

    if (!verifyResult.success) {
      setState(() {
        _isLoading = false;
        _message = verifyResult.message;
      });
      return;
    }

    final loginResult = await AuthService.login(
      correo: widget.correo,
      password: widget.password,
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (!loginResult.success || loginResult.data == null) {
      setState(() {
        _message = 'Cuenta verificada, pero no se pudo iniciar sesión automáticamente.';
      });
      return;
    }

    final user = loginResult.data!;
    await AuthService.saveSession(user);

    if (!mounted) return;

    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => StudentDashboardScreen(user: user)),
      (route) => false,
    );
  }

  Future<void> _resendCode() async {
    setState(() {
      _isResending = true;
      _message = null;
    });

    final result = await AuthService.resendVerificationCode(
      userId: widget.userId,
      correo: widget.correo,
    );

    if (!mounted) return;

    setState(() {
      _isResending = false;
      _message = result.message;
    });
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
                constraints: const BoxConstraints(maxWidth: 520),
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
                          width: 72,
                          height: 72,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            gradient: LinearGradient(
                              colors: [Color(0xFFEF6C00), Color(0xFF2E7D32)],
                            ),
                          ),
                          child: const Icon(Icons.verified_user_rounded, size: 38, color: Colors.white),
                        ),
                        const SizedBox(height: 18),
                        Text(
                          'Verifica tu cuenta',
                          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFF1F1F1F),
                              ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Enviamos un código de 6 dígitos a tu correo${widget.verificationSent ? '' : ' o puedes reenviarlo aquí'}.',
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                                color: Colors.black54,
                              ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          widget.correo,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(height: 24),
                        TextField(
                          controller: _codeController,
                          keyboardType: TextInputType.number,
                          maxLength: 6,
                          textAlign: TextAlign.center,
                          decoration: const InputDecoration(
                            labelText: 'Código de verificación',
                            counterText: '',
                          ),
                        ),
                        const SizedBox(height: 10),
                        if (_message != null) ...[
                          Text(
                            _message!,
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: _message!.toLowerCase().contains('correct') || _message!.toLowerCase().contains('enviado')
                                  ? const Color(0xFF2E7D32)
                                  : const Color(0xFFB00020),
                            ),
                          ),
                          const SizedBox(height: 10),
                        ],
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: _isLoading ? null : _verifyCode,
                            child: Text(_isLoading ? 'Verificando...' : 'Verificar código'),
                          ),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton(
                            onPressed: _isResending ? null : _resendCode,
                            child: Text(_isResending ? 'Reenviando...' : 'Reenviar código'),
                          ),
                        ),
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
