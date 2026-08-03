import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../../core/components/app_field.dart';
import '../../../../../core/components/app_select_field.dart';
import '../../../../../core/components/app_toast.dart';
import '../../../../../core/styles/app_colors.dart';
import '../../../../../core/styles/app_text_styles.dart';
import '../../../../jobs/data/models/application_field_model.dart';

/// What the candidate filled in: scalar answers and picked upload files, both
/// keyed by field key, ready for [ApplicationsRepo.apply].
typedef ApplyFormResult = ({Map<String, String> answers, Map<String, File> files});

/// A bottom sheet that renders a job's employer-authored application form and
/// returns the collected answers, or null if the candidate backs out.
class ApplyFormSheet extends StatefulWidget {
  const ApplyFormSheet({required this.fields, super.key});

  final List<ApplicationFieldModel> fields;

  static Future<ApplyFormResult?> show(
    BuildContext context, {
    required List<ApplicationFieldModel> fields,
  }) {
    return showModalBottomSheet<ApplyFormResult>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => ApplyFormSheet(fields: fields),
    );
  }

  @override
  State<ApplyFormSheet> createState() => _ApplyFormSheetState();
}

class _ApplyFormSheetState extends State<ApplyFormSheet> {
  final _formKey = GlobalKey<FormState>();
  final _controllers = <String, TextEditingController>{};
  final _selected = <String, String?>{};
  final _files = <String, File?>{};
  bool _submitted = false;

  @override
  void initState() {
    super.initState();
    for (final field in widget.fields) {
      if (field.type == 'text' || field.type == 'number') {
        _controllers[field.key] = TextEditingController();
      }
    }
  }

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pick(ApplicationFieldModel field) async {
    final result = await FilePicker.platform.pickFiles(
      type: field.isImage ? FileType.image : FileType.custom,
      allowedExtensions: field.isImage
          ? null
          : const ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'],
    );
    final path = result?.files.single.path;
    if (path != null) setState(() => _files[field.key] = File(path));
  }

  void _submit() {
    setState(() => _submitted = true);

    final formOk = _formKey.currentState?.validate() ?? false;
    final uploadsOk = widget.fields
        .where((f) => f.isUpload && f.required)
        .every((f) => _files[f.key] != null);

    if (!formOk || !uploadsOk) {
      AppToast.error(context, 'أكمل الحقول المطلوبة.');
      return;
    }

    final answers = <String, String>{};
    for (final field in widget.fields) {
      if (field.type == 'select') {
        final value = _selected[field.key];
        if (value != null) answers[field.key] = value;
      } else if (!field.isUpload) {
        final value = _controllers[field.key]?.text.trim() ?? '';
        if (value.isNotEmpty) answers[field.key] = value;
      }
    }

    final files = <String, File>{
      for (final entry in _files.entries)
        if (entry.value != null) entry.key: entry.value!,
    };

    Navigator.of(context).pop((answers: answers, files: files));
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.viewInsetsOf(context).bottom),
      child: DraggableScrollableSheet(
        initialChildSize: .82,
        minChildSize: .5,
        maxChildSize: .95,
        expand: false,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            color: AppColors.paper,
            borderRadius: BorderRadius.vertical(top: Radius.circular(26)),
          ),
          child: Column(
            children: [
              const SizedBox(height: 10),
              Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 14, 20, 6),
                child: Row(
                  children: [
                    const Icon(Icons.assignment_rounded, color: AppColors.charcoalSoft),
                    const SizedBox(width: 9),
                    Text('نموذج التقديم', style: AppTextStyles.titleMd),
                  ],
                ),
              ),
              Expanded(
                child: Form(
                  key: _formKey,
                  child: ListView(
                    controller: scrollController,
                    padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                    children: [
                      for (final field in widget.fields) ...[
                        _FieldLabel(label: field.label, required: field.required),
                        _buildField(field),
                        const SizedBox(height: 16),
                      ],
                    ],
                  ),
                ),
              ),
              _SubmitBar(onSubmit: _submit),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildField(ApplicationFieldModel field) {
    switch (field.type) {
      case 'select':
        return AppSelectField<String>(
          hint: 'اختر…',
          value: _selected[field.key],
          options: [for (final o in field.options) (value: o, label: o)],
          onChanged: (v) => setState(() => _selected[field.key] = v),
        );
      case 'number':
        return AppField(
          controller: _controllers[field.key]!,
          hint: 'أدخل رقماً',
          keyboardType: TextInputType.number,
          textDirection: TextDirection.rtl,
          inputFormatters: [FilteringTextInputFormatter.digitsOnly],
          validator: (v) => field.required && (v ?? '').trim().isEmpty
              ? 'هذا الحقل مطلوب'
              : null,
        );
      case 'file':
      case 'image':
        return _UploadField(
          field: field,
          file: _files[field.key],
          showError: _submitted && field.required && _files[field.key] == null,
          onPick: () => _pick(field),
        );
      default:
        return AppField(
          controller: _controllers[field.key]!,
          hint: 'اكتب إجابتك',
          maxLines: 3,
          validator: (v) => field.required && (v ?? '').trim().isEmpty
              ? 'هذا الحقل مطلوب'
              : null,
        );
    }
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel({required this.label, required this.required});

  final String label;
  final bool required;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text.rich(
        TextSpan(
          text: label,
          style: AppTextStyles.titleSm.copyWith(
            fontSize: 13.5,
            fontWeight: FontWeight.w800,
          ),
          children: [
            if (required)
              TextSpan(
                text: ' *',
                style: AppTextStyles.titleSm.copyWith(color: AppColors.errorFg),
              ),
          ],
        ),
      ),
    );
  }
}

class _UploadField extends StatelessWidget {
  const _UploadField({
    required this.field,
    required this.file,
    required this.showError,
    required this.onPick,
  });

  final ApplicationFieldModel field;
  final File? file;
  final bool showError;
  final VoidCallback onPick;

  @override
  Widget build(BuildContext context) {
    final name = file?.path.split('/').last;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        InkWell(
          onTap: onPick,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(
                color: showError ? AppColors.errorFg : AppColors.border,
                width: 1.5,
              ),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                Icon(
                  file != null
                      ? Icons.check_circle_rounded
                      : (field.isImage
                            ? Icons.image_outlined
                            : Icons.attach_file_rounded),
                  size: 20,
                  color: file != null ? AppColors.successFg : AppColors.textMuted,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    name ?? (field.isImage ? 'اختر صورة' : 'اختر ملفاً'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: AppTextStyles.bodySm.copyWith(
                      fontWeight: FontWeight.w700,
                      color: file != null ? AppColors.textStrong : AppColors.textMuted,
                    ),
                  ),
                ),
                if (file != null)
                  Text(
                    'تغيير',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.charcoalSoft,
                    ),
                  ),
              ],
            ),
          ),
        ),
        if (showError)
          Padding(
            padding: const EdgeInsets.only(top: 6, right: 4),
            child: Text(
              'هذا الحقل مطلوب',
              style: AppTextStyles.caption.copyWith(color: AppColors.errorFg),
            ),
          ),
      ],
    );
  }
}

class _SubmitBar extends StatelessWidget {
  const _SubmitBar({required this.onSubmit});

  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 50,
          width: double.infinity,
          child: FilledButton(
            onPressed: onSubmit,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.amber,
              foregroundColor: AppColors.charcoalSoft,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(17),
              ),
              elevation: 0,
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('إرسال الطلب', style: AppTextStyles.button),
                const SizedBox(width: 8),
                const Icon(Icons.send_rounded, size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
