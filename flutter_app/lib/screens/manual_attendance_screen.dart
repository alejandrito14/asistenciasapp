import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';

class ManualAttendanceScreen extends StatefulWidget {
  final Map<String, dynamic> classInfo;
  final int grupoMateriaMaestroId;

  const ManualAttendanceScreen({
    super.key,
    required this.classInfo,
    required this.grupoMateriaMaestroId,
  });

  @override
  State<ManualAttendanceScreen> createState() => _ManualAttendanceScreenState();
}

class _ManualAttendanceScreenState extends State<ManualAttendanceScreen> {
  bool _loading = true;
  bool _saving = false;
  String? _message;
  List<Map<String, dynamic>> _students = [];
  List<Map<String, dynamic>> _states = [];
  final Map<int, int> _stateMap = {};
  final Map<int, TextEditingController> _obsMap = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await DashboardService.teacherClassStudents(
      grupoMateriaMaestroId: widget.grupoMateriaMaestroId,
    );
    if (!mounted) return;
    if (!result.success) {
      setState(() {
        _loading = false;
        _message = result.message;
      });
      return;
    }

    final data = result.data ?? <String, dynamic>{};
    final students = (data['students'] as List<dynamic>? ?? <dynamic>[])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();
    final states = (data['states'] as List<dynamic>? ?? <dynamic>[])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();

    _obsMap.clear();
    _stateMap.clear();

    for (final student in students) {
      final alumnoId = (student['alumno_id'] as num?)?.toInt() ?? 0;
      final estadoActual = (student['estado_actual'] ?? '').toString().toUpperCase();
      final matched = states.where((state) => (state['nombre'] ?? '').toString().toUpperCase() == estadoActual).toList();
      final present = states.where((state) => (state['nombre'] ?? '').toString().toUpperCase() == 'PRESENTE').toList();
      final fallbackId = matched.isNotEmpty
          ? (matched.first['id'] as num?)?.toInt() ?? 0
          : (present.isNotEmpty ? (present.first['id'] as num?)?.toInt() ?? 0 : (states.isNotEmpty ? (states.first['id'] as num?)?.toInt() ?? 0 : 0));

      _stateMap[alumnoId] = fallbackId;
      _obsMap[alumnoId] = TextEditingController(text: student['observaciones']?.toString() ?? '');
    }

    setState(() {
      _loading = false;
      _students = students;
      _states = states;
      _message = null;
    });
  }

  String _formatFullName(Map<String, dynamic> student) {
    final parts = [
      student['apellido_paterno']?.toString() ?? '',
      student['apellido_materno']?.toString() ?? '',
      student['nombre']?.toString() ?? '',
    ].where((part) => part.trim().isNotEmpty).toList();
    return parts.join(' ');
  }

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
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

  Future<void> _save() async {
    setState(() {
      _saving = true;
    });

    final payload = _students
        .map((student) {
          final alumnoId = (student['alumno_id'] as num?)?.toInt() ?? 0;
          return {
            'alumno_id': alumnoId,
            'estado_id': _stateMap[alumnoId] ?? 0,
            'observaciones': _obsMap[alumnoId]?.text.trim() ?? '',
          };
        })
        .where((row) => (row['alumno_id'] as int) > 0 && (row['estado_id'] as int) > 0)
        .toList();

    final result = await DashboardService.teacherSaveManualAttendance(
      grupoMateriaMaestroId: widget.grupoMateriaMaestroId,
      attendance: payload,
    );

    if (!mounted) return;
    setState(() {
      _saving = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) {
      Navigator.of(context).pop(true);
    }
  }

  @override
  void dispose() {
    for (final controller in _obsMap.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final classInfo = widget.classInfo;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tomar asistencia'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Card(
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                        child: const Icon(Icons.class_, color: Color(0xFFEF6C00)),
                      ),
                      title: Text(
                        classInfo['materia_nombre']?.toString() ?? 'Clase',
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                      subtitle: Text(
                        '${classInfo['grupo_nombre'] ?? ''} | ${classInfo['semestre'] ?? ''}\n${_dayLabel((classInfo['dia_semana'] ?? '').toString())} ${_formatTime(classInfo['hora_inicio'])}-${_formatTime(classInfo['hora_fin'])}  Aula: ${classInfo['aula'] ?? '-'}',
                      ),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.only(left: 20, right: 20, bottom: 8),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Fecha: ${DateTime.now().day.toString().padLeft(2, '0')}/${DateTime.now().month.toString().padLeft(2, '0')}/${DateTime.now().year}',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.black54),
                    ),
                  ),
                ),
                if (_message != null) Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Text(_message!),
                ),
                Expanded(
                  child: _students.isEmpty
                      ? const Center(child: Text('No hay alumnos inscritos en este grupo.'))
                      : Scrollbar(
                          child: ListView(
                            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                            children: [
                              Row(
                                children: const [
                                  Expanded(flex: 5, child: Text('Alumno', style: TextStyle(fontWeight: FontWeight.w700))),
                                  SizedBox(width: 12),
                                  SizedBox(width: 130, child: Center(child: Text('Asistencia', style: TextStyle(fontWeight: FontWeight.w700)))),
                                ],
                              ),
                              const SizedBox(height: 8),
                              ..._students.map((student) {
                                final alumnoId = (student['alumno_id'] as num?)?.toInt() ?? 0;
                                final selectedStateId = _stateMap[alumnoId];
                                final hasSelectedState = selectedStateId != null &&
                                    _states.any((state) => (state['id'] as num?)?.toInt() == selectedStateId);
                                final dropdownValue = hasSelectedState ? selectedStateId : null;
                                return Card(
                                  elevation: 0,
                                  margin: const EdgeInsets.symmetric(vertical: 6),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    child: Row(
                                      children: [
                                        Expanded(
                                          flex: 5,
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(_formatFullName(student), style: const TextStyle(fontWeight: FontWeight.w700)),
                                              const SizedBox(height: 4),
                                              Text(
                                                student['matricula']?.toString() ?? '',
                                                style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.black54),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        SizedBox(
                                          width: 130,
                                          child: _states.isEmpty
                                              ? const Text('Sin estados')
                                              : DropdownButtonFormField<int>(
                                                  value: dropdownValue,
                                                  isExpanded: true,
                                                  decoration: const InputDecoration(
                                                    isDense: true,
                                                    contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                                                  ),
                                                  items: _states
                                                      .map(
                                                        (state) => DropdownMenuItem<int>(
                                                          value: (state['id'] as num?)?.toInt() ?? 0,
                                                          child: Text(state['nombre']?.toString() ?? ''),
                                                        ),
                                                      )
                                                      .toList(),
                                                  onChanged: (value) {
                                                    setState(() {
                                                      _stateMap[alumnoId] = value ?? 0;
                                                    });
                                                  },
                                                ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              }),
                              const SizedBox(height: 16),
                              SizedBox(
                                height: 54,
                                child: FilledButton.icon(
                                  onPressed: _saving ? null : _save,
                                  icon: _saving
                                      ? const SizedBox(
                                          width: 18,
                                          height: 18,
                                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                        )
                                      : const Icon(Icons.save),
                                  label: Text(_saving ? 'Guardando...' : 'Guardar asistencia'),
                                ),
                              ),
                            ],
                          ),
                        ),
                ),
              ],
            ),
    );
  }
}
