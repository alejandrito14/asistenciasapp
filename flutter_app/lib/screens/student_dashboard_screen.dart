import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../config/api_config.dart';
import '../models/auth_user.dart';
import '../services/auth_service.dart';
import '../services/dashboard_service.dart';
import 'group_schedule_screen.dart';
import 'justification_screen.dart';
import 'login_screen.dart';
import 'student_justifications_screen.dart';

class StudentDashboardScreen extends StatefulWidget {
  final AuthUser user;

  const StudentDashboardScreen({super.key, required this.user});

  @override
  State<StudentDashboardScreen> createState() => _StudentDashboardScreenState();
}

class _StudentDashboardScreenState extends State<StudentDashboardScreen> {
  bool _loading = true;
  String? _message;
  List<dynamic> _myGroups = [];
  List<dynamic> _subjects = [];
  int? _selectedGroupId;
  Map<String, dynamic>? _selectedGroup;
  Map<String, dynamic>? _selectedSchedule;
  int _tabIndex = 0;

  final _pinController = TextEditingController();
  int? _alumnoId;
  String? _profilePhotoPath;
  String? _localPhotoPath;
  bool _uploadingPhoto = false;

  @override
  void initState() {
    super.initState();
    _alumnoId = (widget.user.alumno?['id'] as num?)?.toInt() ?? 0;
    _profilePhotoPath = widget.user.alumno?['photo_path']?.toString();
    _load();
  }

  Future<void> _load() async {
    final result = await DashboardService.studentDashboard(_alumnoId ?? 0);
    if (!mounted) return;
    setState(() {
      _loading = false;
      _message = null;
      _myGroups = (result.data?['inscriptions'] as List<dynamic>?) ?? [];
    });
  }

  Future<void> _loadSubjects(int grupoId) async {
    final result = await DashboardService.studentGroupSubjects(
      alumnoId: _alumnoId ?? 0,
      grupoId: grupoId,
    );
    if (!mounted) return;
    Map<String, dynamic>? selected;
    for (final item in _myGroups) {
      if ((item['grupo_id'] as num?)?.toInt() == grupoId) {
        selected = item as Map<String, dynamic>;
        break;
      }
    }
    setState(() {
      _selectedGroupId = grupoId;
      _subjects = result.data ?? [];
      _selectedGroup = selected;
      _selectedSchedule = null;
      _message = null;
    });
  }

  Future<void> _openGroupSchedule() async {
    final grupoId = _selectedGroupId ?? 0;
    if (grupoId <= 0 || _alumnoId == null) return;

    final result = await DashboardService.studentGroupSchedule(
      alumnoId: _alumnoId!,
      grupoId: grupoId,
    );
    if (!mounted) return;

    if (!result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
      return;
    }

    String groupName = 'Grupo';
    for (final item in _myGroups) {
      if ((item['grupo_id'] as num?)?.toInt() == grupoId) {
        groupName = (item['grupo_nombre'] ?? 'Grupo').toString();
        break;
      }
    }

    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => GroupScheduleScreen(
          groupName: groupName,
          scheduleItems: result.data ?? <dynamic>[],
        ),
      ),
    );
  }

  Future<void> _openSubjectModal(Map<String, dynamic> subject) async {
    final grupoId = _selectedGroupId ?? 0;
    final materiaId = (subject['id'] as num?)?.toInt() ?? 0;
    if (grupoId <= 0 || materiaId <= 0 || _alumnoId == null) return;

    setState(() {
      _selectedSchedule = null;
    });

    final scheduleResult = await DashboardService.studentSubjectSchedule(
      alumnoId: _alumnoId!,
      grupoId: grupoId,
      materiaId: materiaId,
    );

    if (!mounted) return;

    setState(() {
      _selectedSchedule = scheduleResult.data;
    });

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        final schedule = _selectedSchedule;
        final sessionStatus = (schedule?['sesion_estatus'] ?? '').toString().toUpperCase();
        final attendanceState = (schedule?['estado_actual'] ?? '').toString().trim().toUpperCase();
        final attendanceRegistered = schedule?['asistencia_registrada'] == true;
        final faltaRegistered = schedule?['falta_registrada'] == true || attendanceState == 'FALTA';
        final justifiedRegistered = attendanceState == 'JUSTIFICADA' || attendanceState == 'JUSTIFICADO';
        final hasAnyRecord = attendanceRegistered || faltaRegistered || justifiedRegistered;
        final canRegister = schedule != null &&
            schedule.isNotEmpty &&
            sessionStatus == 'ABIERTA' &&
            !hasAnyRecord;

        return Padding(
          padding: EdgeInsets.only(
            left: 16,
            right: 16,
            top: 16,
            bottom: MediaQuery.of(context).viewInsets.bottom + 16,
          ),
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
                subject['nombre']?.toString() ?? 'Materia',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              if (schedule == null || schedule.isEmpty)
                const Text('No hay horario cercano o disponible para esta materia.')
              else ...[
                Card(
                  child: ListTile(
                    leading: const Icon(Icons.schedule),
                    title: Text('${schedule['dia_semana'] ?? ''}'),
                    subtitle: Text(
                      'Hora: ${_formatTime(schedule['hora_inicio'])} - ${_formatTime(schedule['hora_fin'])}\nAula: ${schedule['aula'] ?? '-'}',
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Card(
                  child: ListTile(
                    leading: const Icon(Icons.person_outline),
                    title: Text(schedule['maestro_nombre'] ?? 'Maestro'),
                    subtitle: Text('${schedule['grupo_nombre'] ?? ''} | ${schedule['semestre'] ?? ''}'),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  faltaRegistered
                      ? 'Se registró una falta para esta clase.'
                      : attendanceRegistered
                          ? 'Ya registraste tu asistencia para esta clase.'
                          : justifiedRegistered
                              ? 'La falta fue justificada para esta clase.'
                              : sessionStatus == 'ABIERTA'
                                  ? 'La sesión está abierta y ya puedes registrar asistencia.'
                                  : 'La sesión aún no está abierta por el maestro.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: faltaRegistered
                        ? const Color(0xFFB00020)
                        : attendanceRegistered
                            ? const Color(0xFF1565C0)
                            : justifiedRegistered
                                ? const Color(0xFF6A1B9A)
                                : sessionStatus == 'ABIERTA'
                                    ? const Color(0xFF2E7D32)
                                    : const Color(0xFFB00020),
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 16),
                if (!hasAnyRecord)
                  SizedBox(
                    height: 54,
                    child: ElevatedButton.icon(
                      onPressed: canRegister
                          ? () async {
                              final result = await DashboardService.studentRegisterAttendance(
                                alumnoId: _alumnoId ?? 0,
                                grupoId: grupoId,
                                materiaId: materiaId,
                              );
                              if (!context.mounted) return;
                              Navigator.of(context).pop();
                              ScaffoldMessenger.of(this.context).showSnackBar(
                                SnackBar(content: Text(result.message)),
                              );
                            }
                          : null,
                      icon: const Icon(Icons.check_circle_outline),
                      label: const Text('REGISTRAR ASISTENCIA'),
                    ),
                  ),
                const SizedBox(height: 8),
                Text(
                  faltaRegistered
                      ? 'Si crees que esta falta no corresponde, revisa el justificante.'
                      : attendanceRegistered
                          ? 'No necesitas volver a registrarla.'
                          : justifiedRegistered
                              ? 'La clase ya fue justificada.'
                              : sessionStatus == 'ABIERTA'
                                  ? 'Se registrará solo si estás dentro del día y la hora programados.'
                                  : 'Espera a que el maestro abra la sesión.',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  Future<void> _enroll() async {
    final pin = _pinController.text.trim();
    if (pin.isEmpty || (_alumnoId ?? 0) <= 0) return;

    final result = await DashboardService.enrollGroup(alumnoId: _alumnoId!, pin: pin);
    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) {
      _pinController.clear();
      await _load();
    }
  }

  Future<void> _openJustifications() async {
    if ((_alumnoId ?? 0) <= 0) return;

    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => StudentJustificationsScreen(
          alumnoId: _alumnoId ?? 0,
        ),
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

  Future<void> _confirmDeleteAccount() async {
    final shouldDelete = await showModalBottomSheet<bool>(
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
                'Eliminación de cuenta',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              const Text(
                'Al eliminar la cuenta ya no tendrá acceso en la aplicación. ¿Está seguro de realizar la acción?',
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
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFFB00020),
                          foregroundColor: Colors.white,
                        ),
                        child: const Text('Eliminar'),
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

    if (shouldDelete != true) return;

    final result = await AuthService.deleteAccount(
      userId: widget.user.id,
      correo: widget.user.correo,
      rol: widget.user.rol,
    );

    if (!mounted) return;

    if (!result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
      return;
    }

    await AuthService.clearSession();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }

  ImageProvider? _profileImageProvider() {
    if (_localPhotoPath != null && _localPhotoPath!.isNotEmpty) {
      return FileImage(File(_localPhotoPath!));
    }

    final path = _profilePhotoPath?.trim();
    if (path == null || path.isEmpty) return null;

    final imageUrl = path.startsWith('http')
        ? path
        : ApiConfig.resolveServerUrl(path).toString();
    return NetworkImage(imageUrl);
  }

  Future<void> _chooseProfilePhoto() async {
    if (_uploadingPhoto) return;

    final source = await showModalBottomSheet<ImageSource?>(
      context: context,
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.photo_camera_outlined),
                title: const Text('Tomar foto'),
                onTap: () => Navigator.of(context).pop(ImageSource.camera),
              ),
              ListTile(
                leading: const Icon(Icons.photo_library_outlined),
                title: const Text('Elegir de galería'),
                onTap: () => Navigator.of(context).pop(ImageSource.gallery),
              ),
              const SizedBox(height: 8),
            ],
          ),
        );
      },
    );

    if (source == null) return;

    final picker = ImagePicker();
    final photo = await picker.pickImage(
      source: source,
      imageQuality: 85,
      maxWidth: 1200,
    );

    if (!mounted || photo == null) return;

    setState(() {
      _uploadingPhoto = true;
      _localPhotoPath = photo.path;
    });

    final result = await AuthService.updateProfilePhoto(
      userId: widget.user.id,
      rol: widget.user.rol,
      filePath: photo.path,
      fileName: photo.name.isNotEmpty ? photo.name : 'foto_perfil.jpg',
    );

    if (!mounted) return;

    if (!result.success) {
      setState(() {
        _uploadingPhoto = false;
        _localPhotoPath = null;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
      return;
    }

    final responseData = result.data ?? <String, dynamic>{};
    final profileData = responseData['profile'];
    final photoPath = (responseData['photo_path'] ??
            (profileData is Map<String, dynamic> ? profileData['photo_path'] : null))
        ?.toString();

    if (photoPath != null && photoPath.isNotEmpty) {
      setState(() {
        _profilePhotoPath = photoPath;
        _localPhotoPath = null;
      });

      final updatedAlumno = widget.user.alumno == null
          ? null
          : {
              ...widget.user.alumno!,
              'photo_path': photoPath,
            };
      final updatedUser = widget.user.copyWith(alumno: updatedAlumno);
      await AuthService.saveSession(updatedUser);
    } else {
      setState(() {
        _localPhotoPath = null;
      });
    }

    if (!mounted) return;
    setState(() {
      _uploadingPhoto = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message)),
    );
  }

  Widget _buildProfileAvatar() {
    final imageProvider = _profileImageProvider();

    return GestureDetector(
      onTap: _chooseProfilePhoto,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          CircleAvatar(
            radius: 36,
            backgroundColor: const Color(0xFF2E7D32).withOpacity(0.12),
            backgroundImage: imageProvider,
            child: imageProvider == null
                ? const Icon(Icons.person, color: Color(0xFF2E7D32), size: 34)
                : null,
          ),
          Positioned(
            right: -2,
            bottom: -2,
            child: Container(
              padding: const EdgeInsets.all(5),
              decoration: BoxDecoration(
                color: const Color(0xFFEF6C00),
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 2),
              ),
              child: const Icon(Icons.photo_camera_rounded, size: 14, color: Colors.white),
            ),
          ),
          if (_uploadingPhoto)
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.35),
                  shape: BoxShape.circle,
                ),
                child: const Center(
                  child: SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _pinController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final headerGradient = _tabIndex == 0
        ? const LinearGradient(colors: [Color(0xFFEF6C00), Color(0xFFFFA726)])
        : const LinearGradient(colors: [Color(0xFFEF6C00), Color(0xFFFFA726)]);
    final dashboardView = _loading
        ? const Center(child: CircularProgressIndicator())
        : RefreshIndicator(
            onRefresh: _load,
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              children: [
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                  child: Padding(
                    padding: const EdgeInsets.all(18),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Inscribirte por PIN',
                          style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _pinController,
                          decoration: InputDecoration(
                            labelText: 'PIN del grupo',
                            suffixIcon: IconButton(
                              onPressed: _enroll,
                              icon: const Icon(Icons.how_to_reg),
                              tooltip: 'Inscribirme',
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),
                        FilledButton(
                          onPressed: _enroll,
                          child: const Text('Inscribirme'),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                if (_myGroups.isNotEmpty) ...[
                  Text(
                    'Mis grupos',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 8),
                  ..._myGroups.map(
                    (item) => Card(
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(20),
                        onTap: () => _loadSubjects((item['grupo_id'] as num?)?.toInt() ?? 0),
                        child: ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          leading: CircleAvatar(
                            backgroundColor: const Color(0xFF2E7D32).withOpacity(0.12),
                            child: const Icon(Icons.groups_2_rounded, color: Color(0xFF2E7D32)),
                          ),
                          title: Text(
                            item['grupo_nombre'] ?? '',
                            style: const TextStyle(fontWeight: FontWeight.w700),
                          ),
                          subtitle: Text('${item['semestre'] ?? ''} | ${item['turno'] ?? ''}\nIngreso: ${item['fecha_ingreso'] ?? ''}'),
                          trailing: const Icon(Icons.chevron_right),
                        ),
                      ),
                    ),
                  ),
                ],
                if (_selectedGroupId != null && _myGroups.isNotEmpty) ...[
                  const SizedBox(height: 24),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(
                        child: Text(
                          'Detalle del grupo',
                          style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                        ),
                      ),
                      IconButton(
                        onPressed: _openGroupSchedule,
                        icon: const Icon(Icons.calendar_month_outlined),
                        tooltip: 'Ver calendario',
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_subjects.isEmpty)
                    const Text('Este grupo todavía no tiene materias asignadas.')
                  else
                    ..._subjects.map(
                      (item) => Card(
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          leading: CircleAvatar(
                            backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                            child: const Icon(Icons.book_outlined, color: Color(0xFFEF6C00)),
                          ),
                          title: Text(
                            '${item['clave']} - ${item['nombre']}',
                            style: const TextStyle(fontWeight: FontWeight.w700),
                          ),
                          subtitle: Text(item['descripcion'] ?? ''),
                          trailing: const Icon(Icons.schedule_send),
                          onTap: () => _openSubjectModal(Map<String, dynamic>.from(item as Map)),
                        ),
                      ),
                    ),
                ],
                if (_myGroups.isEmpty) Text(_message ?? 'No hay datos disponibles.'),
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
            leading: _buildProfileAvatar(),
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
        if (widget.user.alumno != null) ...[
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
            child: ListTile(
              title: const Text('Matrícula'),
              subtitle: Text(widget.user.alumno?['matricula']?.toString() ?? ''),
            ),
          ),
        ],
        const SizedBox(height: 12),
        FilledButton.tonalIcon(
          onPressed: () async {
            final changed = await Navigator.of(context).push<bool>(
              MaterialPageRoute(
                builder: (_) => JustificationScreen(
                  alumnoId: _alumnoId ?? 0,
                ),
              ),
            );
            if (changed == true && mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Justificación enviada.')),
              );
            }
          },
          icon: const Icon(Icons.note_add_outlined),
          label: const Text('Solicitar justificante'),
        ),
        const SizedBox(height: 12),
        FilledButton.icon(
          onPressed: _confirmLogout,
          icon: const Icon(Icons.logout),
          label: const Text('Cerrar sesión'),
        ),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: _confirmDeleteAccount,
          icon: const Icon(Icons.delete_outline),
          style: OutlinedButton.styleFrom(
            backgroundColor: Colors.white,
            foregroundColor: const Color(0xFFB00020),
            side: const BorderSide(color: Color(0xFFB00020)),
            minimumSize: const Size.fromHeight(52),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          ),
          label: const Text('Eliminar cuenta'),
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
