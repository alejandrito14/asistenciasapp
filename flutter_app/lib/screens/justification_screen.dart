import 'dart:io';

import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:image_picker/image_picker.dart';

import '../services/dashboard_service.dart';

class JustificationScreen extends StatefulWidget {
  final int alumnoId;

  const JustificationScreen({
    super.key,
    required this.alumnoId,
  });

  @override
  State<JustificationScreen> createState() => _JustificationScreenState();
}

class _JustificationScreenState extends State<JustificationScreen> {
  bool _loading = true;
  bool _saving = false;
  String? _message;
  List<Map<String, dynamic>> _sessions = [];
  int? _selectedSessionId;
  PlatformFile? _selectedFile;

  final _motivoController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadSessions();
  }

  Future<void> _loadSessions() async {
    final result = await DashboardService.studentJustificationSessions(alumnoId: widget.alumnoId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      _sessions = (result.data ?? <dynamic>[])
          .map((item) => Map<String, dynamic>.from(item as Map))
          .toList();
      _message = result.success ? null : result.message;
    });
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

  String _statusLabel(Map<String, dynamic> session) {
    final status = (session['estado_nombre'] ?? session['estado_actual'] ?? 'Falta').toString().trim();
    return status.isEmpty ? 'Falta' : status;
  }

  Future<void> _submit() async {
    final motivo = _motivoController.text.trim();
    final sessionId = _selectedSessionId ?? 0;
    if (motivo.isEmpty || sessionId <= 0) return;

    setState(() {
      _saving = true;
    });

    final result = await DashboardService.studentSubmitJustification(
      alumnoId: widget.alumnoId,
      sesionClaseId: sessionId,
      motivo: motivo,
      filePath: _selectedFile?.path,
      fileName: _selectedFile?.name,
    );

    if (!mounted) return;
    setState(() {
      _saving = false;
      _message = result.message;
    });

    if (result.success) {
      _motivoController.clear();
      setState(() {
        _selectedSessionId = null;
        _selectedFile = null;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result.message)),
      );
      Navigator.of(context).pop(true);
    }
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.any,
      withData: false,
    );
    if (!mounted || result == null || result.files.isEmpty) return;
    setState(() {
      _selectedFile = result.files.first;
    });
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final photo = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 85,
    );
    if (!mounted || photo == null) return;
    final photoFile = File(photo.path);
    final photoSize = await photoFile.length();

    setState(() {
      _selectedFile = PlatformFile(
        name: photo.name.isNotEmpty ? photo.name : 'foto_justificacion.jpg',
        size: photoSize,
        path: photo.path,
      );
    });
  }

  bool _isImageFile(PlatformFile file) {
    final name = file.name.toLowerCase();
    return name.endsWith('.jpg') ||
        name.endsWith('.jpeg') ||
        name.endsWith('.png') ||
        name.endsWith('.webp') ||
        name.endsWith('.gif') ||
        name.endsWith('.bmp');
  }

  @override
  void dispose() {
    _motivoController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Solicitar justificación'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    'Selecciona la falta que deseas justificar',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 12),
                  if (_sessions.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('No hay faltas pendientes para justificar.'),
                      ),
                    )
                  else
                    Column(
                      children: _sessions.map((session) {
                        final sessionId = (session['sesion_clase_id'] as num?)?.toInt() ?? 0;
                        final isSelected = _selectedSessionId == sessionId;
                        final label =
                            '${session['materia_nombre'] ?? ''} | ${session['grupo_nombre'] ?? ''}';
                        final detail =
                            '${_formatDate(session['fecha'])} | ${_dayLabel((session['dia_semana'] ?? '').toString())} ${_formatTime(session['hora_inicio'])}-${_formatTime(session['hora_fin'])}';

                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(18),
                            side: BorderSide(
                              color: isSelected ? const Color(0xFF2E7D32) : Colors.transparent,
                              width: 1.5,
                            ),
                          ),
                          child: ListTile(
                            onTap: () {
                              setState(() {
                                _selectedSessionId = sessionId;
                              });
                            },
                            leading: CircleAvatar(
                              backgroundColor: const Color(0xFF2E7D32).withOpacity(0.12),
                              child: const Icon(Icons.event_busy, color: Color(0xFF2E7D32)),
                            ),
                            title: Text(
                              label,
                              style: const TextStyle(fontWeight: FontWeight.w700),
                            ),
                            subtitle: Text(detail),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  _statusLabel(session),
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFFB00020),
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Icon(
                                  isSelected ? Icons.check_circle : Icons.radio_button_unchecked,
                                  color: isSelected ? const Color(0xFF2E7D32) : Colors.grey,
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: FilledButton.tonalIcon(
                          onPressed: _saving ? null : _pickImage,
                          icon: const Icon(Icons.photo_camera_outlined),
                          label: const Text('Tomar foto'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: FilledButton.tonalIcon(
                          onPressed: _saving ? null : _pickFile,
                          icon: const Icon(Icons.attach_file),
                          label: const Text('Adjuntar archivo'),
                        ),
                      ),
                    ],
                  ),
                  if (_selectedFile != null) ...[
                    const SizedBox(height: 8),
                    if (_selectedFile!.path != null && _isImageFile(_selectedFile!))
                      GestureDetector(
                        onTap: () {
                          showDialog(
                            context: context,
                            builder: (context) {
                              return Dialog(
                                insetPadding: const EdgeInsets.all(16),
                                child: Stack(
                                  children: [
                                    InteractiveViewer(
                                      minScale: 1,
                                      maxScale: 4,
                                      child: Image.file(
                                        File(_selectedFile!.path!),
                                        fit: BoxFit.contain,
                                      ),
                                    ),
                                    Positioned(
                                      top: 8,
                                      right: 8,
                                      child: Material(
                                        color: Colors.black54,
                                        shape: const CircleBorder(),
                                        child: IconButton(
                                          onPressed: () => Navigator.of(context).pop(),
                                          icon: const Icon(Icons.close, color: Colors.white),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          );
                        },
                        child: Card(
                          clipBehavior: Clip.antiAlias,
                          child: Stack(
                            children: [
                              AspectRatio(
                                aspectRatio: 1.2,
                                child: Image.file(
                                  File(_selectedFile!.path!),
                                  fit: BoxFit.cover,
                                ),
                              ),
                              Positioned(
                                top: 8,
                                right: 8,
                                child: Material(
                                  color: Colors.black54,
                                  shape: const CircleBorder(),
                                  child: IconButton(
                                    onPressed: _saving
                                        ? null
                                        : () {
                                            setState(() {
                                              _selectedFile = null;
                                            });
                                          },
                                    icon: const Icon(Icons.close, color: Colors.white),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    else
                      Card(
                        child: ListTile(
                          leading: const Icon(Icons.insert_drive_file_outlined),
                          title: const Text('Archivo adjunto'),
                          subtitle: Text(
                            _selectedFile!.size > 0
                                ? '${(_selectedFile!.size / 1024).toStringAsFixed(1)} KB'
                                : 'Archivo seleccionado',
                          ),
                          trailing: IconButton(
                            onPressed: _saving
                                ? null
                                : () {
                                    setState(() {
                                      _selectedFile = null;
                                    });
                                  },
                            icon: const Icon(Icons.close),
                          ),
                        ),
                      ),
                  ],
                  const SizedBox(height: 16),
                  TextField(
                    controller: _motivoController,
                    maxLines: 5,
                    decoration: const InputDecoration(
                      labelText: 'Motivo',
                      alignLabelWithHint: true,
                      hintText: 'Explica por qué debe justificarse la falta',
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (_selectedSessionId != null)
                    Card(
                      color: const Color(0xFFE8F5E9),
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Builder(
                          builder: (context) {
                            final selected = _sessions.firstWhere(
                              (item) => (item['sesion_clase_id'] as num?)?.toInt() == _selectedSessionId,
                              orElse: () => <String, dynamic>{},
                            );
                            return Text(
                              'Falta seleccionada: ${selected['materia_nombre'] ?? ''} | ${_formatDate(selected['fecha'])}',
                              style: const TextStyle(fontWeight: FontWeight.w600),
                            );
                          },
                        ),
                      ),
                    ),
                  const SizedBox(height: 12),
                  if (_message != null)
                    Text(
                      _message!,
                      style: const TextStyle(color: Color(0xFFB00020)),
                    ),
                  const SizedBox(height: 18),
                  SizedBox(
                    height: 54,
                    child: FilledButton(
                      onPressed: _saving ? null : _submit,
                      child: _saving
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Text('Enviar justificación'),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
