import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';

class TeacherTasksScreen extends StatefulWidget {
  final Map<String, dynamic> classInfo;
  final int grupoMateriaMaestroId;

  const TeacherTasksScreen({
    super.key,
    required this.classInfo,
    required this.grupoMateriaMaestroId,
  });

  @override
  State<TeacherTasksScreen> createState() => _TeacherTasksScreenState();
}

class _TeacherTasksScreenState extends State<TeacherTasksScreen> {
  bool _loading = true;
  String? _message;
  Map<String, dynamic> _classInfo = <String, dynamic>{};
  List<Map<String, dynamic>> _tasks = [];

  @override
  void initState() {
    super.initState();
    _classInfo = Map<String, dynamic>.from(widget.classInfo);
    _load();
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

  String _formatDateOnly(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  String _formatDateFromValue(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 10) {
      final parts = text.substring(0, 10).split('-');
      if (parts.length == 3) {
        return '${parts[2]}/${parts[1]}/${parts[0]}';
      }
    }
    return text;
  }

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  String _taskPreview(Map<String, dynamic> task) {
    final text = (task['descripcion'] ?? '').toString().trim();
    if (text.isEmpty) return 'Sin descripción';
    if (text.length <= 100) return text;
    return '${text.substring(0, 100)}...';
  }

  Future<void> _load() async {
    final result = await DashboardService.teacherClassTasks(
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
    final classData = data['class'];
    final tasks = (data['tasks'] as List<dynamic>? ?? <dynamic>[])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();

    setState(() {
      _loading = false;
      _message = null;
      _tasks = tasks;
      if (classData is Map<String, dynamic>) {
        _classInfo = Map<String, dynamic>.from(classData);
      }
    });
  }

  Future<void> _openTaskForm() async {
    final formData = await showModalBottomSheet<_TaskFormData>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return _TaskFormSheet(classTitle: _classTitle());
      },
    );

    if (formData == null) {
      return;
    }

    final result = await DashboardService.teacherCreateTask(
      grupoMateriaMaestroId: widget.grupoMateriaMaestroId,
      titulo: formData.title,
      descripcion: formData.description,
      fechaEntrega: '${formData.dueDate.year.toString().padLeft(4, '0')}-${formData.dueDate.month.toString().padLeft(2, '0')}-${formData.dueDate.day.toString().padLeft(2, '0')}',
    );

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message)),
    );

    if (result.success) {
      await _load();
    }
  }

  Future<void> _showTaskDetail(Map<String, dynamic> task) async {
    final title = (task['titulo'] ?? '').toString();
    final description = (task['descripcion'] ?? '').toString();
    final dueDate = _formatDateFromValue(task['fecha_entrega']);
    final createdAt = _formatDateFromValue(task['created_at']);

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                mainAxisSize: MainAxisSize.min,
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
                    title.isEmpty ? 'Tarea' : title,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _classTitle(),
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.black54),
                  ),
                  const SizedBox(height: 16),
                  _detailRow('Fecha de entrega', dueDate),
                  _detailRow('Creada', createdAt),
                  const SizedBox(height: 12),
                  const Text('Descripcion', style: TextStyle(fontWeight: FontWeight.w700)),
                  const SizedBox(height: 6),
                  Text(description.isEmpty ? 'Sin descripción.' : description),
                  const SizedBox(height: 18),
                  SizedBox(
                    height: 50,
                    child: FilledButton(
                      onPressed: () => Navigator.of(context).pop(),
                      child: const Text('Cerrar'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  String _classTitle() {
    final subject = (_classInfo['materia_nombre'] ?? '').toString();
    final group = (_classInfo['grupo_nombre'] ?? '').toString();
    final code = (_classInfo['materia_clave'] ?? '').toString();
    final day = _dayLabel((_classInfo['dia_semana'] ?? '').toString());
    final schedule = '${_formatTime(_classInfo['hora_inicio'])} - ${_formatTime(_classInfo['hora_fin'])}';
    return [group, code.isNotEmpty ? '$code - $subject' : subject, '$day | $schedule'].where((part) => part.trim().isNotEmpty).join(' · ');
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: Colors.black54, fontSize: 12)),
          const SizedBox(height: 4),
          Text(
            value.isEmpty ? '-' : value,
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: const Text(
          'Tareas',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
        leading: IconButton(
          onPressed: () => Navigator.of(context).pop(),
          icon: const Icon(Icons.arrow_back_rounded),
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFFEF6C00), Color(0xFFFFA726)],
            ),
          ),
        ),
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                children: [
                  Card(
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _classTitle(),
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${_tasks.length} tarea${_tasks.length == 1 ? '' : 's'}',
                            style: const TextStyle(color: Colors.black54),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    height: 52,
                    child: FilledButton.icon(
                      onPressed: _openTaskForm,
                      icon: const Icon(Icons.add_task_rounded),
                      label: const Text('Agregar Tarea'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (_message != null) ...[
                    Card(
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(_message!),
                      ),
                    ),
                  ] else if (_tasks.isEmpty)
                    Card(
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      child: const Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('Aún no hay tareas para esta clase.'),
                      ),
                    )
                  else
                    ..._tasks.map((task) {
                      final title = (task['titulo'] ?? '').toString();
                      final dueDate = _formatDateFromValue(task['fecha_entrega']);
                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: () => _showTaskDetail(task),
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                CircleAvatar(
                                  backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                                  child: const Icon(Icons.assignment_outlined, color: Color(0xFFEF6C00)),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        title.isEmpty ? 'Tarea' : title,
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        _taskPreview(task),
                                        style: const TextStyle(color: Colors.black54),
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        'Entrega: $dueDate',
                                        style: const TextStyle(fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),
                                const Icon(Icons.chevron_right_rounded, color: Color(0xFFEF6C00)),
                              ],
                            ),
                          ),
                        ),
                      );
                    }),
                ],
              ),
            ),
    );
  }
}

class _TaskFormData {
  final String title;
  final String description;
  final DateTime dueDate;

  const _TaskFormData({
    required this.title,
    required this.description,
    required this.dueDate,
  });
}

class _TaskFormSheet extends StatefulWidget {
  final String classTitle;

  const _TaskFormSheet({
    required this.classTitle,
  });

  @override
  State<_TaskFormSheet> createState() => _TaskFormSheetState();
}

class _TaskFormSheetState extends State<_TaskFormSheet> {
  final TextEditingController _titleController = TextEditingController();
  final TextEditingController _descriptionController = TextEditingController();
  DateTime _dueDate = DateTime.now();
  bool _saving = false;

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  String _formatDateOnly(DateTime date) {
    return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: EdgeInsets.only(
          left: 16,
          right: 16,
          top: 16,
          bottom: MediaQuery.of(context).viewInsets.bottom + 24,
        ),
        child: SingleChildScrollView(
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
                'Agregar Tarea',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                widget.classTitle,
                style: const TextStyle(color: Colors.black54),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Titulo',
                  hintText: 'Ej. Resolver ejercicios',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _descriptionController,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Descripcion',
                  hintText: 'Describe la tarea...',
                ),
              ),
              const SizedBox(height: 12),
              InkWell(
                borderRadius: BorderRadius.circular(18),
                onTap: () async {
                  final picked = await showDatePicker(
                    context: context,
                    initialDate: _dueDate,
                    firstDate: DateTime.now().subtract(const Duration(days: 1)),
                    lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
                    locale: const Locale('es', 'MX'),
                  );
                  if (picked == null || !mounted) return;
                  setState(() {
                    _dueDate = picked;
                  });
                },
                child: InputDecorator(
                  decoration: const InputDecoration(
                    labelText: 'Fecha de entrega',
                    suffixIcon: Icon(Icons.calendar_month_rounded),
                  ),
                  child: Text(_formatDateOnly(_dueDate)),
                ),
              ),
              const SizedBox(height: 18),
              SizedBox(
                height: 52,
                child: FilledButton(
                  onPressed: _saving
                      ? null
                      : () {
                          final title = _titleController.text.trim();
                          final description = _descriptionController.text.trim();
                          if (title.isEmpty || description.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Completa titulo y descripcion.')),
                            );
                            return;
                          }

                          setState(() {
                            _saving = true;
                          });

                          Navigator.of(context).pop(
                            _TaskFormData(
                              title: title,
                              description: description,
                              dueDate: _dueDate,
                            ),
                          );
                        },
                  child: _saving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Guardar Tarea'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
