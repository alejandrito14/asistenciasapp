import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';

class GroupScheduleScreen extends StatefulWidget {
  final int alumnoId;
  final int grupoId;
  final String groupName;

  const GroupScheduleScreen({
    super.key,
    required this.alumnoId,
    required this.grupoId,
    required this.groupName,
  });

  @override
  State<GroupScheduleScreen> createState() => _GroupScheduleScreenState();
}

class _GroupScheduleScreenState extends State<GroupScheduleScreen> {
  static const _monthNames = [
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

  static const _weekdayShortLabels = {
    DateTime.monday: 'LUN',
    DateTime.tuesday: 'MAR',
    DateTime.wednesday: 'MIÉ',
    DateTime.thursday: 'JUE',
    DateTime.friday: 'VIE',
    DateTime.saturday: 'SÁB',
    DateTime.sunday: 'DOM',
  };

  static const _accentColors = [
    Color(0xFFEF6C00),
    Color(0xFF2E7D32),
    Color(0xFFFFA000),
    Color(0xFF1565C0),
    Color(0xFF6A1B9A),
  ];

  DateTime _selectedDate = DateUtils.dateOnly(DateTime.now());
  bool _loading = true;
  bool _loadingCalendarState = true;
  bool _isSchoolDay = true;
  String? _message;
  String? _errorMessage;
  Map<String, dynamic>? _blockReason;
  List<Map<String, dynamic>> _items = [];

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  String _dayCodeForDate(DateTime date) {
    switch (date.weekday) {
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
        return 'Lun';
      case 'MARTES':
        return 'Mar';
      case 'MIERCOLES':
        return 'Mié';
      case 'JUEVES':
        return 'Jue';
      case 'VIERNES':
        return 'Vie';
      case 'SABADO':
        return 'Sáb';
      case 'DOMINGO':
        return 'Dom';
      default:
        return code;
    }
  }

  String _fullDayLabel(String code) {
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

  String _monthYearLabel(DateTime date) {
    final month = _monthNames[date.month - 1];
    return '${month[0].toUpperCase()}${month.substring(1)} ${date.year}';
  }

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  String _formatDate(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 10) {
      final parts = text.substring(0, 10).split('-');
      if (parts.length == 3) {
        return '${parts[2]}/${parts[1]}/${parts[0]}';
      }
    }
    return text;
  }

  DateTime _weekStart(DateTime date) {
    final normalized = DateUtils.dateOnly(date);
    return normalized.subtract(Duration(days: normalized.weekday - DateTime.monday));
  }

  List<DateTime> _weekDates() {
    final start = _weekStart(_selectedDate);
    return List<DateTime>.generate(7, (index) => start.add(Duration(days: index)));
  }

  List<Map<String, dynamic>> _filteredItems() {
    if (!_isSchoolDay) return <Map<String, dynamic>>[];

    final targetDay = _dayCodeForDate(_selectedDate);
    final items = _items
        .where((item) => (item['dia_semana'] ?? '').toString().toUpperCase() == targetDay)
        .toList();

    items.sort((a, b) {
      final subjectCompare = (a['materia_nombre'] ?? '').toString().compareTo((b['materia_nombre'] ?? '').toString());
      if (subjectCompare != 0) return subjectCompare;
      return _formatTime(a['hora_inicio']).compareTo(_formatTime(b['hora_inicio']));
    });

    return items;
  }

  List<Map<String, dynamic>> _tasksForItem(Map<String, dynamic> item) {
    final tasks = item['tasks'];
    if (tasks is! List) return <Map<String, dynamic>>[];
    return tasks.map((task) => Map<String, dynamic>.from(task as Map)).toList();
  }

  DateTime _taskSortDate(Map<String, dynamic> task) {
    final candidates = [
      task['created_at'],
      task['updated_at'],
      task['fecha_entrega'],
    ];

    for (final candidate in candidates) {
      final text = candidate?.toString() ?? '';
      if (text.isEmpty) continue;
      final parsed = DateTime.tryParse(text);
      if (parsed != null) return parsed;
    }

    final id = int.tryParse(task['id']?.toString() ?? '');
    if (id != null) {
      return DateTime.fromMillisecondsSinceEpoch(id);
    }

    return DateTime.fromMillisecondsSinceEpoch(0);
  }

  List<Map<String, dynamic>> _sortedTasks(Map<String, dynamic> item) {
    final tasks = [..._tasksForItem(item)];
    tasks.sort((a, b) => _taskSortDate(b).compareTo(_taskSortDate(a)));
    return tasks;
  }

  Map<String, dynamic>? _latestTask(Map<String, dynamic> item) {
    final tasks = _sortedTasks(item);
    if (tasks.isEmpty) return null;
    return tasks.first;
  }

  Future<void> _loadAll() async {
    setState(() {
      _loading = true;
      _message = null;
      _errorMessage = null;
    });

    await Future.wait([
      _loadCalendarState(_selectedDate),
      _loadTasks(),
    ]);

    if (!mounted) return;
    setState(() {
      _loading = false;
    });
  }

  Future<void> _loadCalendarState(DateTime date) async {
    setState(() {
      _loadingCalendarState = true;
      _blockReason = null;
      _errorMessage = null;
    });

    final result = await DashboardService.schoolCalendarStatus(
      date:
          '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}',
    );

    if (!mounted) return;

    if (!result.success) {
      setState(() {
        _loadingCalendarState = false;
        _isSchoolDay = true;
        _errorMessage = result.message;
      });
      return;
    }

    final data = result.data ?? <String, dynamic>{};
    final blockReason = data['block_reason'];

    setState(() {
      _loadingCalendarState = false;
      _isSchoolDay = data['is_school_day'] == true;
      _blockReason = blockReason is Map<String, dynamic> ? blockReason : null;
      _errorMessage = null;
    });
  }

  Future<void> _loadTasks() async {
    final result = await DashboardService.studentGroupTasks(
      alumnoId: widget.alumnoId,
      grupoId: widget.grupoId,
    );

    if (!mounted) return;

    if (!result.success) {
      setState(() {
        _message = result.message;
        _items = [];
      });
      return;
    }

    final data = result.data ?? <String, dynamic>{};
    final items = (data['items'] as List<dynamic>? ?? <dynamic>[])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();

    setState(() {
      _message = null;
      _items = items;
    });
  }

  Future<void> _selectDate(DateTime date) async {
    final normalized = DateUtils.dateOnly(date);
    if (normalized == _selectedDate) return;

    setState(() {
      _selectedDate = normalized;
    });

    await _loadCalendarState(normalized);
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 365 * 2)),
      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
      locale: const Locale('es', 'MX'),
    );
    if (picked == null) return;
    await _selectDate(picked);
  }

  Future<void> _moveWeek(int delta) async {
    await _selectDate(_selectedDate.add(Duration(days: 7 * delta)));
  }

  Future<void> _openTaskDetail(Map<String, dynamic> item) async {
    final tasks = _sortedTasks(item);
    final subject = (item['materia_nombre'] ?? '').toString();
    final code = (item['materia_clave'] ?? '').toString();
    final group = (item['grupo_nombre'] ?? '').toString();
    final teacher = [
      item['maestro_nombre']?.toString() ?? '',
      item['apellido_paterno']?.toString() ?? '',
      item['apellido_materno']?.toString() ?? '',
    ].where((part) => part.trim().isNotEmpty).join(' ');

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
                    subject.isEmpty ? 'Tareas' : subject,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    [group, teacher].where((part) => part.trim().isNotEmpty).join(' · '),
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.black54),
                  ),
                  const SizedBox(height: 8),
                  if (code.isNotEmpty)
                    Text(
                      code,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Color(0xFFEF6C00), fontWeight: FontWeight.w700),
                    ),
                  const SizedBox(height: 16),
                  _detailRow('Día', _fullDayLabel((item['dia_semana'] ?? '').toString())),
                  _detailRow('Horario', '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}'),
                  _detailRow('Aula', (item['aula'] ?? '-').toString()),
                  _detailRow('Tareas', '${tasks.length}'),
                  const SizedBox(height: 12),
                  if (tasks.isEmpty)
                    const Card(
                      elevation: 0,
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('No hay tareas para esta clase.'),
                      ),
                    )
                  else
                    ...tasks.map((task) {
                      final title = (task['titulo'] ?? '').toString();
                      final description = (task['descripcion'] ?? '').toString();
                      final dueDate = _formatDate(task['fecha_entrega']);
                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                                    child: const Icon(Icons.assignment_outlined, color: Color(0xFFEF6C00)),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      title.isEmpty ? 'Tarea' : title,
                                      style: const TextStyle(fontWeight: FontWeight.w800),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Text(
                                description.isEmpty ? 'Sin descripción.' : description,
                                style: const TextStyle(color: Colors.black87),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'Fecha de entrega: $dueDate',
                                style: const TextStyle(fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                  const SizedBox(height: 8),
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

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: const TextStyle(color: Colors.black54, fontSize: 12),
            ),
          ),
          Expanded(
            child: Text(
              value.isEmpty ? '-' : value,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  AppBar _buildAppBar(BuildContext context) {
    return AppBar(
      titleSpacing: 0,
      title: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            'Bienvenido',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
          ),
          Text(
            'Horario',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
        ],
      ),
      leading: IconButton(
        onPressed: () => Navigator.of(context).pop(),
        icon: const Icon(Icons.arrow_back_rounded),
        tooltip: 'Regresar',
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
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFEF6C00), Color(0xFFFFA726)],
          ),
        ),
      ),
      foregroundColor: Colors.white,
      elevation: 0,
    );
  }

  Widget _buildMonthSelector(BuildContext context) {
    return Row(
      children: [
        _arrowButton(
          icon: Icons.chevron_left_rounded,
          onPressed: () => _moveWeek(-1),
        ),
        Expanded(
          child: InkWell(
            borderRadius: BorderRadius.circular(999),
            onTap: _pickDate,
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    _monthYearLabel(_selectedDate),
                    style: const TextStyle(
                      color: Color(0xFFEF6C00),
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFFEF6C00)),
                ],
              ),
            ),
          ),
        ),
        _arrowButton(
          icon: Icons.chevron_right_rounded,
          onPressed: () => _moveWeek(1),
        ),
      ],
    );
  }

  Widget _arrowButton({required IconData icon, required VoidCallback onPressed}) {
    return Material(
      color: const Color(0xFFFFF3E0),
      shape: const CircleBorder(),
      child: IconButton(
        onPressed: onPressed,
        icon: Icon(icon, color: const Color(0xFFEF6C00), size: 26),
        tooltip: 'Cambiar semana',
      ),
    );
  }

  Widget _buildWeekStrip(BuildContext context) {
    final weekDates = _weekDates();
    final availableWidth = MediaQuery.of(context).size.width - 32;
    final useCompact = availableWidth < 760;
    final cardWidth = useCompact ? 72.0 : ((availableWidth - 10 * 6) / 7).clamp(68.0, 92.0).toDouble();

    final cards = weekDates.map((date) {
      final isSelected = DateUtils.isSameDay(date, _selectedDate);
      final dayCode = _dayCodeForDate(date);
      final dayShort = _weekdayShortLabels[date.weekday] ?? dayCode.substring(0, 3);
      final dayLabel = _dayLabel(dayCode);
      final dayNumber = date.day.toString();

      return GestureDetector(
        onTap: () => _selectDate(date),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          width: cardWidth,
          height: 124,
          margin: const EdgeInsets.only(right: 8),
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFFFF7A00) : Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: const [
              BoxShadow(
                color: Color(0x1A000000),
                blurRadius: 12,
                offset: Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              Text(
                dayShort,
                style: TextStyle(
                  color: isSelected ? Colors.white : const Color(0xFF111827),
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                dayNumber,
                style: TextStyle(
                  color: isSelected ? Colors.white : const Color(0xFF111827),
                  fontSize: 30,
                  fontWeight: FontWeight.w800,
                ),
              ),
              Text(
                dayLabel,
                style: TextStyle(
                  color: isSelected ? Colors.white.withOpacity(0.95) : const Color(0xFFFF7A00),
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      );
    }).toList();

    if (useCompact) {
      return SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.only(right: 4),
        child: Row(children: cards),
      );
    }

    return Row(children: cards.map((card) => Expanded(child: card)).toList());
  }

  Widget _buildSectionHeader(BuildContext context) {
    final items = _filteredItems();
    return Row(
      children: [
        Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: const Color(0xFFFFF3E0),
            borderRadius: BorderRadius.circular(16),
          ),
          child: const Icon(Icons.assignment_outlined, color: Color(0xFFEF6C00), size: 24),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Tareas de ${widget.groupName}',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 2),
              Text(
                '${items.length} clase${items.length == 1 ? '' : 's'} con tareas',
                style: const TextStyle(color: Colors.black54, fontSize: 13),
              ),
            ],
          ),
        ),
        OutlinedButton.icon(
          onPressed: _pickDate,
          icon: const Icon(Icons.tune_rounded, size: 18),
          label: const Text('Filtrar'),
          style: OutlinedButton.styleFrom(
            foregroundColor: const Color(0xFFEF6C00),
            side: const BorderSide(color: Color(0xFFF8D7BF)),
            backgroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          ),
        ),
      ],
    );
  }

  Widget _buildClassesSection(BuildContext context) {
    final items = _filteredItems();

    if (_loading || _loadingCalendarState) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.only(top: 24),
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (_errorMessage != null) {
      return Card(
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Text(_errorMessage!),
        ),
      );
    }

    if (_message != null) {
      return Card(
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Text(_message!),
        ),
      );
    }

    if (!_isSchoolDay) {
      return Card(
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                children: [
                  Icon(Icons.event_busy, color: Color(0xFFB00020)),
                  SizedBox(width: 8),
                  Text(
                    'Día inhábil',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                _blockReason?['descripcion']?.toString() ?? 'No hay tareas visibles para esta fecha.',
                style: const TextStyle(color: Colors.black54, height: 1.35),
              ),
            ],
          ),
        ),
      );
    }

    if (items.isEmpty) {
      return Card(
        elevation: 0,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: const Padding(
          padding: EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.assignment_turned_in_outlined, color: Color(0xFF2E7D32)),
                  SizedBox(width: 8),
                  Text(
                    'Sin tareas para esta fecha',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                  ),
                ],
              ),
              SizedBox(height: 12),
              Text(
                'Selecciona otro día para ver las clases con tareas registradas.',
                style: TextStyle(color: Colors.black54, height: 1.35),
              ),
            ],
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _buildSectionHeader(context),
        const SizedBox(height: 12),
        ...items.asMap().entries.map((entry) {
          final index = entry.key;
          final item = entry.value;
          final accent = _accentColors[index % _accentColors.length];
          final subject = (item['materia_nombre'] ?? '').toString();
          final code = (item['materia_clave'] ?? '').toString();
          final grupo = (item['grupo_nombre'] ?? '-').toString();
          final aula = (item['aula'] ?? '-').toString();
          final schedule = '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}';
          final teacher = [
            item['maestro_nombre']?.toString() ?? '',
            item['apellido_paterno']?.toString() ?? '',
            item['apellido_materno']?.toString() ?? '',
          ].where((part) => part.trim().isNotEmpty).join(' ');
          final tasks = _sortedTasks(item);
          final latestTask = tasks.isEmpty ? null : tasks.first;
          final remainingTasks = tasks.length > 1 ? tasks.length - 1 : 0;

          return Card(
            elevation: 0,
            margin: const EdgeInsets.only(bottom: 10),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            child: InkWell(
              borderRadius: BorderRadius.circular(20),
              onTap: () => _openTaskDetail(item),
              child: Row(
                children: [
                  Container(
                    width: 8,
                    height: 156,
                    decoration: BoxDecoration(
                      color: accent,
                      borderRadius: const BorderRadius.horizontal(left: Radius.circular(20)),
                    ),
                  ),
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 48,
                            height: 48,
                            decoration: BoxDecoration(
                              color: accent.withOpacity(0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(
                              index % 3 == 0
                                  ? Icons.menu_book_rounded
                                  : index % 3 == 1
                                      ? Icons.computer_rounded
                                      : Icons.science_rounded,
                              color: accent,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Align(
                                  alignment: Alignment.centerLeft,
                                  child: Wrap(
                                    spacing: 8,
                                    runSpacing: 6,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                        decoration: BoxDecoration(
                                          color: accent.withOpacity(0.10),
                                          borderRadius: BorderRadius.circular(999),
                                        ),
                                        child: Text(
                                          code.isNotEmpty ? code : grupo,
                                          style: TextStyle(
                                            color: accent,
                                            fontWeight: FontWeight.w700,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFF3F4F6),
                                          borderRadius: BorderRadius.circular(999),
                                        ),
                                        child: Text(
                                          '${tasks.length} tarea${tasks.length == 1 ? '' : 's'}',
                                          style: const TextStyle(
                                            color: Color(0xFF111827),
                                            fontWeight: FontWeight.w700,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  subject.isNotEmpty ? subject : 'Materia',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  children: [
                                    Icon(Icons.schedule, color: accent, size: 16),
                                    const SizedBox(width: 6),
                                    Text(
                                      schedule,
                                      style: const TextStyle(fontSize: 13),
                                    ),
                                    const SizedBox(width: 10),
                                    Container(
                                      width: 1,
                                      height: 14,
                                      color: Colors.grey.shade300,
                                    ),
                                    const SizedBox(width: 10),
                                    Icon(Icons.place_rounded, color: accent, size: 16),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Aula $aula',
                                      style: const TextStyle(fontSize: 13),
                                    ),
                                  ],
                                ),
                                if (teacher.isNotEmpty) ...[
                                  const SizedBox(height: 6),
                                  Text(
                                    'Maestro: $teacher',
                                    style: const TextStyle(color: Colors.black54, fontSize: 12),
                                  ),
                                ],
                                const SizedBox(height: 8),
                                if (tasks.isEmpty)
                                  const Text(
                                    'Sin tareas asignadas para esta clase.',
                                    style: TextStyle(color: Colors.black54, fontSize: 12),
                                  )
                                else if (latestTask != null) ...[
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Icon(Icons.brightness_1, size: 8, color: accent),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          (latestTask['titulo'] ?? '').toString().trim().isEmpty
                                              ? 'Tarea'
                                              : latestTask['titulo'].toString(),
                                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    (() {
                                      final description = (latestTask['descripcion'] ?? '').toString().trim();
                                      if (description.isEmpty) return 'Sin descripción.';
                                      return description.length > 110
                                          ? '${description.substring(0, 110)}...'
                                          : description;
                                    })(),
                                    style: const TextStyle(color: Colors.black54, fontSize: 12),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Fecha de entrega: ${_formatDate(latestTask['fecha_entrega'])}',
                                    style: const TextStyle(color: Colors.black87, fontSize: 12),
                                  ),
                                  if (remainingTasks > 0) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      '+$remainingTasks tarea${remainingTasks == 1 ? '' : 's'} más',
                                      style: TextStyle(
                                        color: accent,
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    Align(
                                      alignment: Alignment.centerLeft,
                                      child: TextButton(
                                        onPressed: () => _openTaskDetail(item),
                                        style: TextButton.styleFrom(
                                          foregroundColor: accent,
                                          padding: EdgeInsets.zero,
                                          minimumSize: const Size(0, 0),
                                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                        ),
                                        child: const Text(
                                          'Ver todas las tareas',
                                          style: TextStyle(fontWeight: FontWeight.w800),
                                        ),
                                      ),
                                    ),
                                  ],
                                ],
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Icon(Icons.chevron_right_rounded, color: Color(0xFFEF6C00), size: 28),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F7F8),
      appBar: _buildAppBar(context),
      body: RefreshIndicator(
        onRefresh: _loadAll,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 20),
          children: [
            _buildMonthSelector(context),
            const SizedBox(height: 12),
            _buildWeekStrip(context),
            const SizedBox(height: 14),
            _buildClassesSection(context),
          ],
        ),
      ),
    );
  }
}
