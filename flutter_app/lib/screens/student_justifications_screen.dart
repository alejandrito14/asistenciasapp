import 'package:flutter/material.dart';

import '../services/dashboard_service.dart';
import 'justification_screen.dart';

class StudentJustificationsScreen extends StatefulWidget {
  final int alumnoId;

  const StudentJustificationsScreen({
    super.key,
    required this.alumnoId,
  });

  @override
  State<StudentJustificationsScreen> createState() => _StudentJustificationsScreenState();
}

class _StudentJustificationsScreenState extends State<StudentJustificationsScreen> {
  bool _loading = true;
  String? _message;
  List<Map<String, dynamic>> _items = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await DashboardService.studentJustifications(alumnoId: widget.alumnoId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      _message = result.message;
      _items = (result.data ?? <dynamic>[])
          .map((item) => Map<String, dynamic>.from(item as Map))
          .toList();
    });
  }

  String _sessionDetail(Map<String, dynamic> item) {
    final date = item['sesion_fecha']?.toString() ?? '';
    final day = (item['dia_sesion'] ?? item['dia_semana'] ?? '').toString();
    final start = (item['hora_inicio'] ?? '').toString();
    final end = (item['hora_fin'] ?? '').toString();
    final pieces = <String>[];
    if (date.isNotEmpty) pieces.add(date.length >= 10 ? date.substring(0, 10) : date);
    if (day.isNotEmpty) pieces.add(day);
    if (start.isNotEmpty || end.isNotEmpty) pieces.add('${start.length >= 5 ? start.substring(0, 5) : start} - ${end.length >= 5 ? end.substring(0, 5) : end}');
    return pieces.join(' | ');
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'APROBADO':
        return const Color(0xFF2E7D32);
      case 'RECHAZADO':
        return const Color(0xFFB00020);
      default:
        return const Color(0xFFEF6C00);
    }
  }

  String _formatDate(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 10) return text.substring(0, 10);
    return text;
  }

  String _formatTime(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 5) return text.substring(0, 5);
    return text;
  }

  String _buildEvidenceUrl(String path) {
    final clean = path.trim();
    if (clean.isEmpty) return '';
    final normalized = clean.startsWith('/') ? clean.substring(1) : clean;
    return 'http://192.168.1.202:8080/$normalized';
  }

  Future<void> _showJustificationDetail(Map<String, dynamic> item) async {
    final status = (item['estatus'] ?? 'PENDIENTE').toString().toUpperCase();
    final color = _statusColor(status);
    final evidencePath = (item['evidencia'] ?? '').toString().trim();
    final evidenceUrl = _buildEvidenceUrl(evidencePath);
    final isImage = evidenceUrl.isNotEmpty &&
        RegExp(r'\.(jpg|jpeg|png|gif|webp|bmp)$', caseSensitive: false).hasMatch(evidenceUrl);
    final studentName = [
      item['apellido_paterno']?.toString() ?? '',
      item['apellido_materno']?.toString() ?? '',
      item['alumno_nombre']?.toString() ?? '',
    ].where((part) => part.trim().isNotEmpty).join(' ');
    final sessionDate = _formatDate(item['sesion_fecha']);
    final sessionDay = (item['dia_sesion'] ?? item['dia_semana'] ?? '').toString();
    final sessionTime = _formatTime(item['hora_inicio']);
    final sessionEndTime = _formatTime(item['hora_fin']);

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
                    'Detalle del justificante',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 16),
                  _DetailRow(label: 'Alumno', value: studentName),
                  _DetailRow(label: 'Grupo', value: '${item['grupo_nombre'] ?? '-'}'),
                  _DetailRow(label: 'Materia', value: '${item['materia_clave'] ?? ''} - ${item['materia_nombre'] ?? ''}'),
                  _DetailRow(label: 'Fecha de sesión', value: sessionDate),
                  _DetailRow(label: 'Día y hora', value: '$sessionDay $sessionTime - $sessionEndTime'.trim()),
                  _DetailRow(label: 'Motivo', value: '${item['motivo'] ?? '-'}'),
                  _DetailRow(label: 'Estatus', value: status, valueColor: color),
                  const SizedBox(height: 12),
                  if (evidenceUrl.isNotEmpty) ...[
                    Text(
                      'Evidencia',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 8),
                    if (isImage)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Image.network(
                          evidenceUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Text('No se pudo cargar la evidencia.'),
                        ),
                      )
                    else
                      Text(
                        evidenceUrl,
                        style: const TextStyle(color: Colors.black54),
                      ),
                  ],
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Future<void> _openRequestJustification() async {
    final changed = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => JustificationScreen(alumnoId: widget.alumnoId),
      ),
    );
    if (changed == true && mounted) {
      await _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Justificantes'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  SizedBox(
                    height: 54,
                    child: FilledButton.icon(
                      onPressed: _openRequestJustification,
                      icon: const Icon(Icons.note_add_outlined),
                      label: const Text('Solicitar justificante'),
                    ),
                  ),
                  const SizedBox(height: 16),
                  if (_items.isEmpty)
                    Card(
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(_message ?? 'No tienes justificantes solicitados todavía.'),
                      ),
                    )
                  else
                    ..._items.map((item) {
                      final status = (item['estatus'] ?? 'PENDIENTE').toString().toUpperCase();
                      final color = _statusColor(status);
                      final title = '${item['materia_clave'] ?? ''} - ${item['materia_nombre'] ?? ''}';
                      final subtitle = '${item['grupo_nombre'] ?? ''} | ${item['semestre'] ?? ''}\n${_sessionDetail(item)}';

                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: () => _showJustificationDetail(item),
                          child: ListTile(
                            contentPadding: const EdgeInsets.all(16),
                            leading: CircleAvatar(
                              backgroundColor: color.withOpacity(0.12),
                              child: Icon(Icons.assignment_outlined, color: color),
                            ),
                            title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
                            subtitle: Text(subtitle),
                            isThreeLine: true,
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  status,
                                  style: TextStyle(fontWeight: FontWeight.w800, color: color),
                                ),
                                const SizedBox(height: 4),
                                const Icon(Icons.chevron_right),
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

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;

  const _DetailRow({
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(color: Colors.black54, fontSize: 12)),
          const SizedBox(height: 4),
          Text(
            value.isEmpty ? '-' : value,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: valueColor ?? Colors.black87,
            ),
          ),
        ],
      ),
    );
  }
}
