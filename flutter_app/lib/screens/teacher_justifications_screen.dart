import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';
import 'teacher_justification_group_screen.dart';

class TeacherJustificationsScreen extends StatefulWidget {
  final int maestroId;

  const TeacherJustificationsScreen({
    super.key,
    required this.maestroId,
  });

  @override
  State<TeacherJustificationsScreen> createState() => _TeacherJustificationsScreenState();
}

class _TeacherJustificationsScreenState extends State<TeacherJustificationsScreen> {
  bool _loading = true;
  String? _message;
  List<Map<String, dynamic>> _groups = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await DashboardService.teacherJustifications(maestroId: widget.maestroId);
    if (!mounted) return;
    final items = (result.data ?? <dynamic>[])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();
    final grouped = <String, Map<String, dynamic>>{};

    for (final item in items) {
      final key = [
        (item['grupo_nombre'] ?? '').toString(),
        (item['materia_nombre'] ?? '').toString(),
        (item['dia_sesion'] ?? '').toString(),
        (item['hora_inicio'] ?? '').toString(),
        (item['hora_fin'] ?? '').toString(),
      ].join('|');
      grouped.putIfAbsent(key, () => {
            'grupo_nombre': item['grupo_nombre'] ?? '',
            'materia_clave': item['materia_clave'] ?? '',
            'materia_nombre': item['materia_nombre'] ?? '',
            'dia_sesion': item['dia_sesion'] ?? '',
            'sesion_fecha': item['sesion_fecha'] ?? '',
            'hora_inicio': item['hora_inicio'] ?? '',
            'hora_fin': item['hora_fin'] ?? '',
            'count': 0,
            'items': <Map<String, dynamic>>[],
          });
      grouped[key]!['count'] = (grouped[key]!['count'] as int) + 1;
      (grouped[key]!['items'] as List<Map<String, dynamic>>).add(item);
    }

    setState(() {
      _loading = false;
      _message = result.message;
      _groups = grouped.values.cast<Map<String, dynamic>>().toList();
    });
  }

  String _subtitle(Map<String, dynamic> group) {
    final date = (group['sesion_fecha'] ?? '').toString();
    final day = (group['dia_sesion'] ?? '').toString();
    final start = (group['hora_inicio'] ?? '').toString();
    final end = (group['hora_fin'] ?? '').toString();
    final parts = <String>[];
    if (date.isNotEmpty) parts.add(date.length >= 10 ? date.substring(0, 10) : date);
    if (day.isNotEmpty) parts.add(day);
    if (start.isNotEmpty || end.isNotEmpty) {
      parts.add('${start.length >= 5 ? start.substring(0, 5) : start} - ${end.length >= 5 ? end.substring(0, 5) : end}');
    }
    return parts.join(' | ');
  }

  String _title(Map<String, dynamic> group) {
    final groupName = (group['grupo_nombre'] ?? '').toString();
    final subject = (group['materia_nombre'] ?? '').toString();
    final clave = (group['materia_clave'] ?? '').toString();
    return '$groupName · $clave - $subject';
  }

  Future<void> _openGroup(Map<String, dynamic> group) async {
    final items = (group['items'] as List<Map<String, dynamic>>).cast<Map<String, dynamic>>();
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => TeacherJustificationGroupScreen(
          title: _title(group),
          subtitle: _subtitle(group),
          justifications: items,
        ),
      ),
    );
    if (mounted) {
      await _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Justificantes')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_groups.isEmpty)
                    Card(
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(_message ?? 'No hay justificantes registrados todavía.'),
                      ),
                    )
                  else
                    ..._groups.map((group) {
                      final count = (group['count'] as int?) ?? 0;
                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: ListTile(
                          onTap: () => _openGroup(group),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          leading: CircleAvatar(
                            backgroundColor: const Color(0xFFEF6C00).withOpacity(0.12),
                            child: const Icon(Icons.folder_shared_outlined, color: Color(0xFFEF6C00)),
                          ),
                          title: Text(_title(group), style: const TextStyle(fontWeight: FontWeight.w800)),
                          subtitle: Text(_subtitle(group)),
                          trailing: CircleAvatar(
                            radius: 16,
                            backgroundColor: const Color(0xFFEF6C00),
                            child: Text(
                              '$count',
                              style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
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
