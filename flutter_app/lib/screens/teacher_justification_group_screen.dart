import 'package:flutter/material.dart';

import '../config/api_config.dart';
import '../services/dashboard_service.dart';

class TeacherJustificationGroupScreen extends StatefulWidget {
  final String title;
  final String subtitle;
  final List<Map<String, dynamic>> justifications;

  const TeacherJustificationGroupScreen({
    super.key,
    required this.title,
    required this.subtitle,
    required this.justifications,
  });

  @override
  State<TeacherJustificationGroupScreen> createState() => _TeacherJustificationGroupScreenState();
}

class _TeacherJustificationGroupScreenState extends State<TeacherJustificationGroupScreen> {
  late List<Map<String, dynamic>> _items;

  @override
  void initState() {
    super.initState();
    _items = List<Map<String, dynamic>>.from(widget.justifications);
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

  String _studentName(Map<String, dynamic> row) {
    return [
      row['apellido_paterno']?.toString() ?? '',
      row['apellido_materno']?.toString() ?? '',
      row['alumno_nombre']?.toString() ?? '',
    ].where((part) => part.trim().isNotEmpty).join(' ');
  }

  String _formatDate(dynamic value) {
    final text = value?.toString() ?? '';
    if (text.length >= 10) return text.substring(0, 10);
    return text;
  }

  String _evidenceUrl(String path) {
    final clean = path.trim();
    if (clean.isEmpty) return '';
    final normalized = clean.startsWith('/') ? clean.substring(1) : clean;
    return ApiConfig.baseUrl.replaceAll('api.php', '') + normalized;
  }

  Future<void> _changeStatus(Map<String, dynamic> item) async {
    final current = (item['estatus'] ?? 'PENDIENTE').toString().toUpperCase();
    final selected = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 16),
              const Text('Cambiar estatus', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18)),
              const SizedBox(height: 12),
              ...['PENDIENTE', 'APROBADO', 'RECHAZADO'].map((status) {
                return ListTile(
                  leading: Icon(Icons.circle, color: _statusColor(status), size: 14),
                  title: Text(status),
                  onTap: () => Navigator.of(context).pop(status),
                );
              }),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );

    if (selected == null || selected == current) return;

    final result = await DashboardService.teacherJustificationUpdate(
      justificationId: (item['id'] as num?)?.toInt() ?? 0,
      estatus: selected,
    );

    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(result.message)),
    );

    if (result.success) {
      setState(() {
        item['estatus'] = selected;
      });
    }
  }

  Future<void> _showDetail(Map<String, dynamic> item) async {
    final status = (item['estatus'] ?? 'PENDIENTE').toString().toUpperCase();
    final color = _statusColor(status);
    final evidencePath = (item['evidencia'] ?? '').toString();
    final evidenceUrl = _evidenceUrl(evidencePath);
    final isImage = evidenceUrl.isNotEmpty &&
        RegExp(r'\.(jpg|jpeg|png|gif|webp|bmp)$', caseSensitive: false).hasMatch(evidenceUrl);
    final sessionDate = _formatDate(item['sesion_fecha']);
    final sessionDay = (item['dia_sesion'] ?? '').toString();
    final sessionHour = [
      (item['hora_inicio'] ?? '').toString(),
      (item['hora_fin'] ?? '').toString(),
    ].map((e) => e.length >= 5 ? e.substring(0, 5) : e).join(' - ');

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
                  Text('Detalle del justificante', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
                  const SizedBox(height: 16),
                  _DetailRow(label: 'Alumno', value: _studentName(item)),
                  _DetailRow(label: 'Fecha', value: sessionDate),
                  _DetailRow(label: 'Día y hora', value: '$sessionDay $sessionHour'),
                  _DetailRow(label: 'Motivo', value: (item['motivo'] ?? '-').toString()),
                  _DetailRow(label: 'Estatus', value: status, valueColor: color),
                  const SizedBox(height: 12),
                  if (evidenceUrl.isNotEmpty) ...[
                    Text('Evidencia', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    if (isImage)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Image.network(
                          evidenceUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Text('No se pudo cargar la imagen.'),
                        ),
                      )
                    else
                      Text(evidenceUrl, style: const TextStyle(color: Colors.black54)),
                  ],
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text('Cerrar'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: FilledButton(
                          onPressed: () {
                            Navigator.of(context).pop();
                            _changeStatus(item);
                          },
                          child: const Text('Cambiar estatus'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.subtitle, style: const TextStyle(color: Colors.black54)),
                  const SizedBox(height: 4),
                  Text('${_items.length} justificantes', style: const TextStyle(fontWeight: FontWeight.w800)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          ..._items.map((item) {
            final status = (item['estatus'] ?? 'PENDIENTE').toString().toUpperCase();
            final color = _statusColor(status);
            return Card(
              elevation: 0,
              margin: const EdgeInsets.only(bottom: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              child: InkWell(
                borderRadius: BorderRadius.circular(20),
                onTap: () => _showDetail(item),
                child: ListTile(
                  contentPadding: const EdgeInsets.all(16),
                  leading: CircleAvatar(
                    backgroundColor: color.withOpacity(0.12),
                    child: Icon(Icons.assignment_outlined, color: color),
                  ),
                  title: Text(_studentName(item), style: const TextStyle(fontWeight: FontWeight.w700)),
                  subtitle: Text((item['motivo'] ?? '').toString()),
                  trailing: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(status, style: TextStyle(fontWeight: FontWeight.w800, color: color, fontSize: 12)),
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
