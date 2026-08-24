import 'package:flutter/material.dart';

import '../../../../../core/services/service_locator.dart';
import '../../../../../core/styles/app_colors.dart';
import '../../../data/repos/jobs_repo.dart';

/// A self-contained bookmark toggle. It owns its optimistic state and talks to
/// the repository directly, so it can drop onto any job card or detail without
/// threading save/unsave through the surrounding cubit.
class SaveButton extends StatefulWidget {
  const SaveButton({
    required this.slug,
    required this.initialSaved,
    this.onDark = false,
    super.key,
  });

  final String slug;
  final bool initialSaved;
  final bool onDark;

  @override
  State<SaveButton> createState() => _SaveButtonState();
}

class _SaveButtonState extends State<SaveButton> {
  late bool _saved = widget.initialSaved;
  bool _busy = false;

  Future<void> _toggle() async {
    if (_busy) return;

    final next = !_saved;
    setState(() {
      _saved = next;
      _busy = true;
    });

    final repo = getIt<JobsRepo>();
    final result = next
        ? await repo.save(widget.slug)
        : await repo.unsave(widget.slug);
    if (!mounted) return;

    // Roll the heart back if the server rejected the change.
    result.fold((_) => setState(() => _saved = !next), (_) {});
    if (mounted) setState(() => _busy = false);
  }

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: _toggle,
        customBorder: const CircleBorder(),
        child: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: widget.onDark
                ? Colors.white.withValues(alpha: .12)
                : AppColors.surface,
            shape: BoxShape.circle,
            border: Border.all(
              color: widget.onDark
                  ? Colors.white.withValues(alpha: .2)
                  : AppColors.border,
            ),
          ),
          child: Icon(
            _saved ? Icons.favorite_rounded : Icons.favorite_border_rounded,
            size: 20,
            color: _saved
                ? AppColors.amber
                : (widget.onDark ? Colors.white : AppColors.textMuted),
          ),
        ),
      ),
    );
  }
}
