import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../styles/app_colors.dart';
import '../styles/app_text_styles.dart';

/// A placeholder-only form field matching the design's inputs: a white rounded
/// box with the field name as the placeholder and no floating label. Grows for
/// multi-line text (the design's bio textarea).
class AppField extends StatelessWidget {
  const AppField({
    required this.controller,
    required this.hint,
    this.validator,
    this.keyboardType,
    this.inputFormatters,
    this.maxLines = 1,
    this.textDirection,
    super.key,
  });

  final TextEditingController controller;
  final String hint;
  final FormFieldValidator<String>? validator;
  final TextInputType? keyboardType;
  final List<TextInputFormatter>? inputFormatters;
  final int maxLines;
  final TextDirection? textDirection;

  @override
  Widget build(BuildContext context) {
    final multiline = maxLines > 1;

    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      textDirection: textDirection,
      style: AppTextStyles.input.copyWith(
        fontSize: 15,
        fontWeight: FontWeight.w600,
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: AppTextStyles.input.copyWith(
          fontSize: 15,
          fontWeight: FontWeight.w500,
          color: AppColors.textMuted,
        ),
        filled: true,
        fillColor: AppColors.surface,
        isDense: true,
        contentPadding: EdgeInsets.symmetric(
          horizontal: 16,
          vertical: multiline ? 14 : 15,
        ),
        enabledBorder: _border(const Color(0xFFECEAE3)),
        focusedBorder: _border(AppColors.charcoalSoft, width: 1.5),
        errorBorder: _border(AppColors.errorFg),
        focusedErrorBorder: _border(AppColors.errorFg, width: 1.5),
      ),
    );
  }

  OutlineInputBorder _border(Color color, {double width = 1}) {
    return OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide(color: color, width: width),
    );
  }
}
