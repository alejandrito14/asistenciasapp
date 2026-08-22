import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';

class TeacherScheduleScreen extends StatefulWidget {
  final List<dynamic> scheduleItems;

  const TeacherScheduleScreen({
    super.key,
    required this.scheduleItems,
  });

  @override
  State<TeacherScheduleScreen> createState() => _TeacherScheduleScreenState();
}

class _TeacherScheduleScreenState extends State<TeacherScheduleScreen> {
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
  bool _loadingCalendarState = true;
  bool _isSchoolDay = true;
  Map<String, dynamic>? _blockReason;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadSchoolDayState(_selectedDate);
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

  String _formatDate(DateTime date) {
    final day = date.day.toString().padLeft(2, '0');
    final month = _monthNames[date.month - 1];
    return '$day de $month de ${date.year}';
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

  int _timeKey(dynamic value) {
    final text = value?.toString() ?? '';
    final cleaned = text.replaceAll(':', '');
    return int.tryParse(cleaned.substring(0, cleaned.length >= 4 ? 4 : cleaned.length)) ?? 9999;
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
    final items = widget.scheduleItems
        .map((item) => Map<String, dynamic>.from(item as Map))
        .where((item) => (item['dia_semana'] ?? '').toString().toUpperCase() == targetDay)
        .toList();

    items.sort((a, b) {
      final groupCompare = (a['grupo_nombre'] ?? '').toString().compareTo((b['grupo_nombre'] ?? '').toString());
      if (groupCompare != 0) return groupCompare;

      final subjectCompare = (a['materia_nombre'] ?? '').toString().compareTo((b['materia_nombre'] ?? '').toString());
      if (subjectCompare != 0) return subjectCompare;

      return _timeKey(a['hora_inicio']).compareTo(_timeKey(b['hora_inicio']));
    });

    return items;
  }

  Future<void> _loadSchoolDayState(DateTime date) async {
    setState(() {
      _loadingCalendarState = true;
      _errorMessage = null;
      _blockReason = null;
    });

    final result = await DashboardService.schoolCalendarStatus(
      date:
          '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}',
    );

    if (!mounted) return;

    if (!result.success) {
      setState(() {
        _loadingCalendarState = false;
        _errorMessage = result.message;
        _isSchoolDay = true;
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

  Future<void> _selectDate(DateTime date) async {
    final normalized = DateUtils.dateOnly(date);
    if (normalized == _selectedDate) return;

    setState(() {
      _selectedDate = normalized;
    });
    await _loadSchoolDayState(normalized);
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

  Future<void> _openItemDetail(Map<String, dynamic> item) async {
    await showModalBottomSheet<void>(
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
                item['materia_nombre']?.toString() ?? 'Horario',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              _detailRow(Icons.groups_2_rounded, 'Grupo', item['grupo_nombre']?.toString() ?? '-'),
              const SizedBox(height: 10),
              _detailRow(Icons.schedule, 'Horario', '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}'),
              const SizedBox(height: 10),
              _detailRow(Icons.location_on_outlined, 'Aula', item['aula']?.toString() ?? '-'),
              const SizedBox(height: 10),
              _detailRow(Icons.calendar_today_outlined, 'Día', _fullDayLabel((item['dia_semana'] ?? '').toString())),
              const SizedBox(height: 16),
              SizedBox(
                height: 50,
                child: FilledButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Cerrar'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, color: const Color(0xFF2E7D32)),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            '$label: $value',
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
        ),
      ],
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

  Widget _buildClassesSection(BuildContext context) {
    final items = _filteredItems();

    if (_loadingCalendarState) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.only(top: 24),
          child: CircularProgressIndicator(),
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
                _blockReason?['descripcion']?.toString() ?? 'No se muestran grupos ni materias para esta fecha.',
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
                  Icon(Icons.event_available, color: Color(0xFF2E7D32)),
                  SizedBox(width: 8),
                  Text(
                    'Sin clases para esta fecha',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                  ),
                ],
              ),
              SizedBox(height: 12),
              Text(
                'Selecciona otro día del calendario para ver los grupos y materias programados.',
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
        Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: const Color(0xFFFFF3E0),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(Icons.calendar_month_rounded, color: Color(0xFFEF6C00), size: 24),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Clases del ${_dayLabel(_dayCodeForDate(_selectedDate))} ${_selectedDate.day}',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${items.length} clase${items.length == 1 ? '' : 's'} programada${items.length == 1 ? '' : 's'}',
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
        ),
        const SizedBox(height: 12),
        ...items.asMap().entries.map((entry) {
          final index = entry.key;
          final item = entry.value;
          final accent = _accentColors[index % _accentColors.length];
          final title = (item['materia_nombre'] ?? '').toString();
          final code = (item['materia_clave'] ?? '').toString();
          final grupo = (item['grupo_nombre'] ?? '-').toString();
          final aula = (item['aula'] ?? '-').toString();
          final schedule = '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}';
          final semester = (item['semestre'] ?? '').toString();

          return Card(
            elevation: 0,
            margin: const EdgeInsets.only(bottom: 10),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            child: InkWell(
              borderRadius: BorderRadius.circular(20),
              onTap: () => _openItemDetail(item),
              child: Row(
                children: [
                  Container(
                    width: 8,
                    height: 128,
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
                                  child: Container(
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
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  title.isNotEmpty ? title : 'Materia',
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
                                const SizedBox(height: 6),
                                Text(
                                  'Grupo: $grupo${semester.isNotEmpty ? ' | Semestre: $semester' : ''}',
                                  style: const TextStyle(color: Colors.black54, fontSize: 12),
                                ),
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
        onRefresh: () => _loadSchoolDayState(_selectedDate),
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
