import 'package:flutter/material.dart';

class GroupScheduleScreen extends StatelessWidget {
  final String groupName;
  final List<dynamic> scheduleItems;

  const GroupScheduleScreen({
    super.key,
    required this.groupName,
    required this.scheduleItems,
  });

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

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  @override
  Widget build(BuildContext context) {
    final items = scheduleItems
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Horario del grupo'),
      ),
      body: items.isEmpty
          ? const Center(child: Text('No hay horario disponible para este grupo.'))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: const Color(0xFF2E7D32).withOpacity(0.12),
                      child: const Icon(Icons.calendar_month, color: Color(0xFF2E7D32)),
                    ),
                    title: Text(
                      groupName,
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                    subtitle: const Text('Materias organizadas por día y hora'),
                  ),
                ),
                const SizedBox(height: 16),
                ...items.map((item) {
                  final day = _dayLabel((item['dia_semana'] ?? '').toString());
                  final subject = '${item['materia_clave'] ?? ''} - ${item['materia_nombre'] ?? ''}';
                  final schedule = '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}';
                  final teacher = [
                    item['maestro_nombre']?.toString() ?? '',
                    item['apellido_paterno']?.toString() ?? '',
                    item['apellido_materno']?.toString() ?? '',
                  ].where((part) => part.trim().isNotEmpty).join(' ');

                  return Card(
                    elevation: 0,
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            day,
                            style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            subject,
                            style: const TextStyle(fontWeight: FontWeight.w700),
                          ),
                          const SizedBox(height: 4),
                          Text('Hora: $schedule'),
                          if ((item['aula'] ?? '').toString().isNotEmpty) Text('Aula: ${item['aula']}'),
                          if (teacher.isNotEmpty) Text('Maestro: $teacher'),
                        ],
                      ),
                    ),
                  );
                }),
              ],
            ),
    );
  }
}
