import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';

/// Lets a parent drive the boxes programmatically — used by the dev auto-fill.
class OtpBoxesController extends ChangeNotifier {
  String _pending = '';
  String get pending => _pending;

  void fill(String code) {
    _pending = code;
    notifyListeners();
  }
}

/// The four separate code boxes from the design, with auto-advance on entry and
/// backspace-to-previous. Reports the concatenated code through [onChanged].
class OtpBoxes extends StatefulWidget {
  const OtpBoxes({
    required this.length,
    required this.onChanged,
    this.controller,
    super.key,
  });

  final int length;
  final ValueChanged<String> onChanged;
  final OtpBoxesController? controller;

  @override
  State<OtpBoxes> createState() => _OtpBoxesState();
}

class _OtpBoxesState extends State<OtpBoxes> {
  late final List<TextEditingController> _fields = List.generate(
    widget.length,
    (_) => TextEditingController(),
  );
  late final List<FocusNode> _nodes = List.generate(
    widget.length,
    (_) => FocusNode(),
  );

  @override
  void initState() {
    super.initState();
    widget.controller?.addListener(_applyExternalFill);
  }

  @override
  void dispose() {
    widget.controller?.removeListener(_applyExternalFill);
    for (final field in _fields) {
      field.dispose();
    }
    for (final node in _nodes) {
      node.dispose();
    }
    super.dispose();
  }

  void _applyExternalFill() {
    final code = widget.controller?.pending ?? '';
    for (var i = 0; i < widget.length; i++) {
      _fields[i].text = i < code.length ? code[i] : '';
    }
    FocusScope.of(context).unfocus();
    widget.onChanged(code);
  }

  void _onDigit(int index, String value) {
    if (value.isNotEmpty && index < widget.length - 1) {
      _nodes[index + 1].requestFocus();
    }
    widget.onChanged(_fields.map((f) => f.text).join());
  }

  KeyEventResult _onKey(int index, FocusNode node, KeyEvent event) {
    if (event is KeyDownEvent &&
        event.logicalKey == LogicalKeyboardKey.backspace &&
        _fields[index].text.isEmpty &&
        index > 0) {
      _nodes[index - 1].requestFocus();
      _fields[index - 1].clear();
      widget.onChanged(_fields.map((f) => f.text).join());
      return KeyEventResult.handled;
    }
    return KeyEventResult.ignored;
  }

  @override
  Widget build(BuildContext context) {
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          for (var i = 0; i < widget.length; i++) ...[
            if (i > 0) const SizedBox(width: 11),
            SizedBox(
              width: 54,
              height: 60,
              child: Focus(
                onKeyEvent: (node, event) => _onKey(i, node, event),
                child: TextField(
                  controller: _fields[i],
                  focusNode: _nodes[i],
                  autofocus: i == 0,
                  keyboardType: TextInputType.number,
                  textAlign: TextAlign.center,
                  maxLength: 1,
                  style: AppTextStyles.displayLg.copyWith(
                    fontSize: 26,
                    fontWeight: FontWeight.w800,
                  ),
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  onChanged: (value) => _onDigit(i, value),
                  decoration: InputDecoration(
                    counterText: '',
                    filled: true,
                    fillColor: AppColors.surface,
                    contentPadding: EdgeInsets.zero,
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(18),
                      borderSide: const BorderSide(color: Color(0xFFE5EAE6)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(18),
                      borderSide: const BorderSide(
                        color: AppColors.charcoalSoft,
                        width: 1.5,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
