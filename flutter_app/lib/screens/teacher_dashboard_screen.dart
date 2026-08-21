import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';

import '../models/auth_user.dart';
import '../services/auth_service.dart';
import '../services/dashboard_service.dart';
import 'manual_attendance_screen.dart';
import 'login_screen.dart';
import 'teacher_justifications_screen.dart';
import 'teacher_schedule_screen.dart';

class TeacherDashboardScreen extends StatefulWidget {
  final AuthUser user;

  const TeacherDashboardScreen({super.key, required this.user});

  @override
  State<TeacherDashboardScreen> createState() => _TeacherDashboardScreenState();
}

class _TeacherDashboardScreenState extends State<TeacherDashboardScreen> {
  bool _loading = true;
  String? _message;
  List<dynamic> _items = [];
  int _tabIndex = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final maestro = widget.user.maestro;
    final maestroId = (maestro?['id'] as num?)?.toInt() ?? 0;
    final result = await DashboardService.teacherDashboard(maestroId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      _message = result.message;
      _items = result.data ?? [];
    });
  }

  String _todayDayCode() {
    switch (DateTime.now().weekday) {
      case DateTime.monday:
        return 'LUNES';
      case DateTime.tuesday:
        return 'MARTES';
      case DateTime.wednesday:
        return 'MIERCOLES';
      case DateTime.thursday:
        return 'JUEVES';
      case DateTime.friday:
        return 'VIERNES';
      case DateTime.saturday:
        return 'SABADO';
      case DateTime.sunday:
      default:
        return 'DOMINGO';
    }
  }

  String _dayLabel(String code) {
    switch (code.toUpperCase()) {
      case 'LUNES':
        return 'Lunes';
      case 'MARTES':
        return 'Martes';
      case 'MIERCOLES':
        return 'Miércoles';
      case 'JUEVES':
        return 'Jueves';
      case 'VIERNES':
        return 'Viernes';
      case 'SABADO':
        return 'Sábado';
      case 'DOMINGO':
        return 'Domingo';
      default:
        return code;
    }
  }

  List<dynamic> _todayClasses() {
    final today = _todayDayCode();
    return _items.where((item) {
      final day = (item['dia_semana'] ?? '').toString().toUpperCase();
      return day == today;
    }).toList();
  }

  String _todayDateLabel() {
    const months = [
      'enero',
      'febrero',
      'marzo',
      'abril',
      'mayo',
      'junio',
      'julio',
      'agosto',
      'septiembre',
      'octubre',
      'noviembre',
      'diciembre',
    ];

    final now = DateTime.now();
    final day = now.day.toString().padLeft(2, '0');
    final month = months[now.month - 1];
    return '$day de $month de ${now.year}';
  }

  Future<void> _openManualAttendance(Map<String, dynamic> classItem) async {
    final gmmId = (classItem['id'] as num?)?.toInt() ?? 0;
    if (gmmId <= 0) return;

    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ManualAttendanceScreen(
          classInfo: Map<String, dynamic>.from(classItem),
          grupoMateriaMaestroId: gmmId,
        ),
      ),
    );
  }

  Future<void> _toggleSession(Map<String, dynamic> classItem) async {
    final gmmId = (classItem['id'] as num?)?.toInt() ?? 0;
    if (gmmId <= 0) return;

    final isOpen = (classItem['sesion_estatus'] ?? '').toString().toUpperCase() == 'ABIERTA';
    double? latitud;
    double? longitud;

    if (!isOpen) {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Necesitamos tu ubicación para abrir la sesión.')),
        );
        return;
      }

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );
      latitud = position.latitude;
      longitud = position.longitude;
    }

    final result = await DashboardService.teacherToggleSession(
      grupoMateriaMaestroId: gmmId,
      usuarioId: widget.user.id,
      latitud: latitud,
      longitud: longitud,
    );
    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message)),
    );

    if (result.success) {
      await _load();
    }
  }

  Future<void> _openScheduleView() async {
    if (_items.isEmpty) return;

    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => TeacherScheduleScreen(
          scheduleItems: _items,
        ),
      ),
    );
  }

  Future<void> _openJustifications() async {
    final maestro = widget.user.maestro;
    final maestroId = (maestro?['id'] as num?)?.toInt() ?? 0;
    if (maestroId <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No se pudo identificar al maestro.')),
      );
      return;
    }

    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => TeacherJustificationsScreen(maestroId: maestroId),
      ),
    );
  }

  Future<void> _confirmLogout() async {
    final shouldLogout = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 48,
                  height: 5,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                '¿Deseas cerrar sesión?',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              const Text(
                'Tu sesión actual se cerrará y volverás a la pantalla de acceso.',
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 52,
                      child: OutlinedButton(
                        onPressed: () => Navigator.of(context).pop(false),
                        style: OutlinedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF2E7D32),
                          side: const BorderSide(color: Color(0xFF2E7D32)),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                        child: const Text('Cancelar'),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: SizedBox(
                      height: 52,
                      child: FilledButton(
                        onPressed: () => Navigator.of(context).pop(true),
                        child: const Text('Cerrar sesión'),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );

    if (shouldLogout == true) {
      await AuthService.clearSession();
      if (!mounted) return;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final headerGradient = _tabIndex == 0
        ? const LinearGradient(colors: [Color(0xFFEF6C00), Color(0xFFFFA726)])
        : const LinearGradient(colors: [Color(0xFFEF6C00), Color(0xFFFFA726)]);
    final todayClasses = _todayClasses();
    final dashboardView = _loading
        ? const Center(child: CircularProgressIndicator())
        : RefreshIndicator(
            onRefresh: _load,
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        'Clases de hoy',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                      ),
                    ),
                    IconButton(
                      onPressed: _items.isEmpty ? null : _openScheduleView,
                      icon: const Icon(Icons.calendar_month_outlined),
                      tooltip: 'Ver horario semanal',
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  _todayDateLabel(),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.black54),
                ),
                const SizedBox(height: 2),
                Text(
                  _dayLabel(_todayDayCode()),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.black54),
                ),
                const SizedBox(height: 12),
                if (todayClasses.isEmpty)
                  Text(_message ?? 'No tienes clases programadas para hoy.')
                else
                ...todayClasses.map(
                    (item) {
                      final sessionStatus = (item['sesion_estatus'] ?? '').toString().trim().toUpperCase();
                      final isOpen = sessionStatus == 'ABIERTA';
                      final sessionIcon = isOpen ? Icons.power : Icons.power_off;
                      final sessionColor = isOpen ? const Color(0xFF2E7D32) : Colors.grey;

                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.symmetric(vertical: 6),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                                    child: const Icon(Icons.menu_book_rounded, color: Color(0xFFEF6C00)),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '${item['materia_clave']} - ${item['materia_nombre']}',
                                          style: const TextStyle(fontWeight: FontWeight.w700),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${item['grupo_nombre']} | ${item['semestre']}',
                                          style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.black54),
                                        ),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    onPressed: () => _toggleSession(Map<String, dynamic>.from(item as Map)),
                                    icon: Icon(sessionIcon, color: sessionColor),
                                    tooltip: isOpen ? 'Cerrar sesión' : 'Abrir sesión',
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                '${_dayLabel((item['dia_semana'] ?? '').toString())} ${item['hora_inicio'] != null ? item['hora_inicio'].toString().substring(0, 5) : ''}-${item['hora_fin'] != null ? item['hora_fin'].toString().substring(0, 5) : ''}',
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Aula: ${item['aula'] ?? '-'}',
                                style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.black54),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Sesión: ${isOpen ? 'ABIERTA' : (sessionStatus.isEmpty ? 'SIN INICIAR' : sessionStatus)}',
                                style: TextStyle(
                                  color: sessionColor,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              if (isOpen) ...[
                                const SizedBox(height: 12),
                                SizedBox(
                                  width: double.infinity,
                                  child: OutlinedButton.icon(
                                    onPressed: () => _openManualAttendance(Map<String, dynamic>.from(item as Map)),
                                    icon: const Icon(Icons.fact_check_outlined),
                                    label: const Text('Tomar asistencia'),
                                  ),
                                ),
                              ] else if (sessionStatus != 'SIN INICIAR') ...[
                                const SizedBox(height: 12),
                                SizedBox(
                                  width: double.infinity,
                                  child: FilledButton.icon(
                                    onPressed: () => _openManualAttendance(Map<String, dynamic>.from(item as Map)),
                                    icon: const Icon(Icons.fact_check_outlined),
                                    label: const Text('Asistencias'),
                                    style: FilledButton.styleFrom(
                                      backgroundColor: Colors.white,
                                      foregroundColor: const Color(0xFFEF6C00),
                                      side: const BorderSide(color: Color(0xFFEF6C00)),
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                if (_items.isNotEmpty && todayClasses.isEmpty) ...[
                  const SizedBox(height: 12),
                  Text(
                    'Tienes materias asignadas, pero ninguna cae en el día de hoy.',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.black54),
                  ),
                ],
              ],
            ),
          );

    final profileView = ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: const Color(0xFF2E7D32).withOpacity(0.12),
              child: const Icon(Icons.person, color: Color(0xFF2E7D32)),
            ),
            title: Text(widget.user.nombre),
            subtitle: Text(widget.user.correo),
          ),
        ),
        const SizedBox(height: 12),
        Card(
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
          child: ListTile(
            title: const Text('Rol'),
            subtitle: Text(widget.user.rol),
          ),
        ),
        if (widget.user.maestro != null) ...[
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
            child: ListTile(
              title: const Text('Teléfono'),
              subtitle: Text(widget.user.maestro?['telefono']?.toString() ?? ''),
            ),
          ),
        ],
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: _confirmLogout,
          icon: const Icon(Icons.logout),
          label: const Text('Cerrar sesión'),
        ),
      ],
    );

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Bienvenido',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
            ),
            Text(
              widget.user.nombre,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
            ),
          ],
        ),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: CircleAvatar(
              backgroundColor: Colors.white.withOpacity(0.18),
              child: const Icon(Icons.person, color: Colors.white),
            ),
          ),
        ],
        flexibleSpace: Container(
          decoration: BoxDecoration(gradient: headerGradient),
        ),
        foregroundColor: Colors.white,
      ),
      drawer: Drawer(
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Hola, ${widget.user.nombre}',
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 4),
                    Text(widget.user.correo, style: TextStyle(color: Colors.grey.shade700)),
                  ],
                ),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.dashboard_outlined),
                title: const Text('Dashboard'),
                onTap: () {
                  Navigator.pop(context);
                  setState(() => _tabIndex = 0);
                },
              ),
              ListTile(
                leading: const Icon(Icons.assignment_outlined),
                title: const Text('Justificantes'),
                onTap: () async {
                  Navigator.pop(context);
                  await _openJustifications();
                },
              ),
              ListTile(
                leading: const Icon(Icons.person_outline),
                title: const Text('Mi perfil'),
                onTap: () {
                  Navigator.pop(context);
                  setState(() => _tabIndex = 1);
                },
              ),
            ],
          ),
        ),
      ),
      body: IndexedStack(
        index: _tabIndex,
        children: [dashboardView, profileView],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tabIndex,
        onDestinationSelected: (index) {
          setState(() {
            _tabIndex = index;
          });
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Mi perfil',
          ),
        ],
      ),
    );
  }
}
