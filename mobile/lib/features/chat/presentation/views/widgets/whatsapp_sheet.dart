import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../../core/components/app_toast.dart';
import '../../../../../core/styles/app_text_styles.dart';
import '../../../data/models/whatsapp_handoff_model.dart';

/// The deeper green the design uses for the sheet, distinct from the brand
/// WhatsApp green on the small in-thread button.
const _sheetGreen = Color(0xFF25803C);

/// Shows the prefilled message before leaving for WhatsApp, so the person sees
/// exactly what is about to be sent on their behalf.
Future<void> showWhatsAppSheet(
  BuildContext context,
  WhatsAppHandoffModel handoff,
) {
  return showModalBottomSheet<void>(
    context: context,
    backgroundColor: Colors.transparent,
    isScrollControlled: true,
    builder: (_) => _WhatsAppSheet(handoff: handoff),
  );
}

class _WhatsAppSheet extends StatelessWidget {
  const _WhatsAppSheet({required this.handoff});

  final WhatsAppHandoffModel handoff;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
      ),
      padding: const EdgeInsets.fromLTRB(22, 14, 22, 34),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 42,
              height: 5,
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: const Color(0xFFE2DED4),
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: _sheetGreen,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.chat_rounded,
                  color: Colors.white,
                  size: 26,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('التواصل عبر واتساب', style: AppTextStyles.titleMd),
                    const SizedBox(height: 1),
                    Text(
                      'سيُفتح واتساب برسالة جاهزة',
                      style: AppTextStyles.caption,
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(Icons.close_rounded, size: 20),
                color: const Color(0xFF8A857A),
                style: IconButton.styleFrom(
                  backgroundColor: const Color(0xFFF4F2EC),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(11),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
            decoration: BoxDecoration(
              color: const Color(0xFFEAF6EC),
              border: Border.all(color: const Color(0xFFCDE8D3)),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Text(
              handoff.message,
              style: AppTextStyles.bodySm.copyWith(height: 1.9),
            ),
          ),
          const SizedBox(height: 10),
          Text(
            'يُسجَّل طلبك داخل المنصة برقم خاص قبل فتح واتساب لضمان المتابعة.',
            style: AppTextStyles.caption.copyWith(height: 1.6),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 54,
            child: ElevatedButton.icon(
              onPressed: () => _launch(context),
              icon: const Icon(Icons.chat_rounded, size: 22),
              label: Text('فتح واتساب', style: AppTextStyles.button),
              style: ElevatedButton.styleFrom(
                backgroundColor: _sheetGreen,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(17),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _launch(BuildContext context) async {
    final opened = await launchUrl(
      Uri.parse(handoff.url),
      mode: LaunchMode.externalApplication,
    );

    if (!context.mounted) return;

    Navigator.of(context).pop();

    // The handoff is already recorded server-side, so a device without
    // WhatsApp only loses the shortcut — the in-app thread still works.
    if (!opened) {
      AppToast.error(context, 'تعذّر فتح واتساب على هذا الجهاز.');
    }
  }
}
