import 'package:flutter/material.dart';

class TeacherScheduleScreen extends StatelessWidget {
  final List<dynamic> scheduleItems;

  const TeacherScheduleScreen({
    super.key,
    required this.scheduleItems,
  });

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Horario semanal')),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: scheduleItems.length,
        itemBuilder: (context, index) {
          final item = Map<String, dynamic>.from(scheduleItems[index] as Map);
          return Card(
            elevation: 0,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            child: ListTile(
              contentPadding: const EdgeInsets.all(16),
              leading: const CircleAvatar(
                backgroundColor: Color(0xFFEF6C00),
                child: Icon(Icons.calendar_month, color: Colors.white),
              ),
              title: Text(
                '${item['materia_clave'] ?? ''} - ${item['materia_nombre'] ?? ''}',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
              subtitle: Text(
                '${item['grupo_nombre'] ?? '-'} | ${item['dia_semana'] ?? '-'}\n'
                '${_formatTime(item['hora_inicio'])} - ${_formatTime(item['hora_fin'])}',
              ),
            ),
          );
        },
      ),
    );
  }
}
