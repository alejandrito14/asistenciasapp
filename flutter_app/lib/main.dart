import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'models/auth_user.dart';
import 'services/auth_service.dart';
import 'screens/login_screen.dart';
import 'screens/student_dashboard_screen.dart';
import 'screens/teacher_dashboard_screen.dart';
import 'screens/register_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
  } catch (_) {
    // Si Firebase no termina de configurarse, la app aún puede abrir.
  }
  runApp(const AppAsistenciasApp());
}

class AppAsistenciasApp extends StatelessWidget {
  const AppAsistenciasApp({super.key});

  @override
  Widget build(BuildContext context) {
      return MaterialApp(
      title: 'ArpiaCheck',
      debugShowCheckedModeBanner: false,
      locale: const Locale('es', 'MX'),
      supportedLocales: const [
        Locale('es', 'MX'),
        Locale('es', 'ES'),
        Locale('en', 'US'),
      ],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFEF6C00),
          primary: const Color(0xFFEF6C00),
          secondary: const Color(0xFF2E7D32),
        ),
        scaffoldBackgroundColor: const Color(0xFFF7F7F7),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          centerTitle: false,
          backgroundColor: Colors.transparent,
          elevation: 0,
          scrolledUnderElevation: 0,
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: const BorderSide(color: Color(0xFFE0E0E0)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: const BorderSide(color: Color(0xFFE0E0E0)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(18),
            borderSide: const BorderSide(color: Color(0xFFEF6C00), width: 1.5),
          ),
        ),
        filledButtonTheme: FilledButtonThemeData(
          style: FilledButton.styleFrom(
            backgroundColor: const Color(0xFFEF6C00),
            foregroundColor: Colors.white,
            minimumSize: const Size.fromHeight(52),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFEF6C00),
            foregroundColor: Colors.white,
            minimumSize: const Size.fromHeight(52),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          ),
        ),
      ),
      home: const SessionGate(),
      routes: {
        'register': (context) => const RegisterScreen(),
      },
      onGenerateRoute: (settings) {
        if (settings.name == '/teacher') {
          final user = settings.arguments as AuthUser;
          return MaterialPageRoute(builder: (_) => TeacherDashboardScreen(user: user));
        }
        if (settings.name == '/student') {
          final user = settings.arguments as AuthUser;
          return MaterialPageRoute(builder: (_) => StudentDashboardScreen(user: user));
        }
        return null;
      },
    );
  }
}

class SessionGate extends StatelessWidget {
  const SessionGate({super.key});

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<AuthUser?>(
      future: AuthService.restoreSession(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }

        final user = snapshot.data;
        if (user != null) {
          if (user.rol.toUpperCase() == 'MAESTRO') {
            return TeacherDashboardScreen(user: user);
          }
          if (user.rol.toUpperCase() == 'ALUMNO') {
            return StudentDashboardScreen(user: user);
          }
        }

        return const LoginScreen();
      },
    );
  }
}
