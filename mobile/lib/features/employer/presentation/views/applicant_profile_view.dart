import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/components/app_toast.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/applicant_profile_model.dart';
import '../manager/applicant_profile_cubit/applicant_profile_cubit.dart';

class ApplicantProfileView extends StatelessWidget {
  const ApplicantProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocConsumer<ApplicantProfileCubit, ApplicantProfileState>(
        listener: (context, state) {
          if (state case ApplicantProfileFailed(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) => switch (state) {
          ApplicantProfileLoaded(:final profile) => _Body(profile: profile),
          ApplicantProfileFailed() => const SizedBox.shrink(),
          _ => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
        },
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final facts = <(IconData, String, String)>[
      if (profile.age != null)
        (Icons.person_rounded, 'العمر', '${profile.age} سنة'),
      if (profile.city != null)
        (Icons.location_on_rounded, 'المدينة', profile.city!),
      if (profile.yearsOfExperience != null)
        (
          Icons.workspace_premium_rounded,
          'سنوات الخبرة',
          '${profile.yearsOfExperience} سنوات',
        ),
    ];

    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              _Header(profile: profile),
              if (facts.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 4),
                  child: Row(
                    children: [
                      for (final (index, (icon, label, value))
                          in facts.indexed) ...[
                        if (index > 0) const SizedBox(width: 11),
                        Expanded(
                          child: Entrance(
                            index: index,
                            child: _FactTile(
                              icon: icon,
                              label: label,
                              value: value,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              if (profile.skills.isNotEmpty) ...[
                const _SectionTitle('المهارات'),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [for (final s in profile.skills) _SkillChip(s)],
                  ),
                ),
              ],
              if (profile.bio != null && profile.bio!.isNotEmpty) ...[
                const _SectionTitle('نبذة مختصرة'),
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 20),
                  padding: const EdgeInsets.all(15),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    border: Border.all(color: AppColors.border),
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Text(
                    profile.bio!,
                    style: AppTextStyles.bodyMd.copyWith(height: 1.8),
                  ),
                ),
              ],
              if (profile.resumeUrl != null) ...[
                const SizedBox(height: 18),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: _ResumeButton(url: profile.resumeUrl!),
                ),
              ],
              const SizedBox(height: 18),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: _ShortlistButton(shortlisted: profile.shortlisted),
              ),
              const _SectionTitle('ملاحظة خاصة'),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: _NoteCard(
                  key: ValueKey(profile.note ?? ''),
                  initialNote: profile.note,
                ),
              ),
              if (profile.answers.isNotEmpty) ...[
                const _SectionTitle('إجابات نموذج التقديم'),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Column(
                    children: [
                      for (final answer in profile.answers)
                        _AnswerCard(answer: answer),
                    ],
                  ),
                ),
              ],
              if (profile.isAccepted) ...[
                const SizedBox(height: 16),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: _ContactCard(profile: profile),
                ),
              ],
              const SizedBox(height: 20),
            ],
          ),
        ),
        if (profile.isDecidable) const _DecisionBar(),
      ],
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(20, topInset + 14, 20, 24),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Pressable(
            onTap: () => Navigator.of(context).maybePop(),
            child: Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(13),
              ),
              child: const Icon(
                Icons.chevron_right_rounded,
                size: 24,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(height: 18),
          Row(
            children: [
              Container(
                width: 70,
                height: 70,
                alignment: Alignment.center,
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.all(Radius.circular(22)),
                  gradient: RadialGradient(
                    center: Alignment(-.3, -.4),
                    colors: [Color(0xFF3E6B54), Color(0xFF284C3D)],
                  ),
                ),
                child: Text(
                  profile.name?.characters.firstOrNull ?? 'م',
                  style: AppTextStyles.displayLg.copyWith(
                    fontSize: 30,
                    color: const Color(0xFFFFFFFF),
                  ),
                ),
              ),
              const SizedBox(width: 15),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      profile.name ?? 'مرشح',
                      style: AppTextStyles.titleLg.copyWith(
                        color: Colors.white,
                      ),
                    ),
                    if (profile.jobTitle != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.work_rounded,
                            size: 16,
                            color: AppColors.amber,
                          ),
                          const SizedBox(width: 5),
                          Flexible(
                            child: Text(
                              profile.jobTitle!,
                              style: AppTextStyles.bodySm.copyWith(
                                fontWeight: FontWeight.w800,
                                color: AppColors.amber,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              _StatusBadge(status: profile.status),
            ],
          ),
          if (profile.jobTitle != null) ...[
            const SizedBox(height: 14),
            Text.rich(
              TextSpan(
                text: 'تقدّم على وظيفة: ',
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFFC7C2B8),
                ),
                children: [
                  TextSpan(
                    text: profile.jobTitle!,
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _FactTile extends StatelessWidget {
  const _FactTile({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: AppColors.textMuted),
              const SizedBox(width: 6),
              Flexible(
                child: Text(
                  label,
                  style: AppTextStyles.caption.copyWith(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.title);

  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 18, 22, 8),
      child: Text(
        title,
        style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
      ),
    );
  }
}

class _SkillChip extends StatelessWidget {
  const _SkillChip(this.label);

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(11),
      ),
      child: Text(
        label,
        style: AppTextStyles.badge.copyWith(fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _ShortlistButton extends StatelessWidget {
  const _ShortlistButton({required this.shortlisted});

  final bool shortlisted;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: () => context.read<ApplicantProfileCubit>().toggleShortlist(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        decoration: BoxDecoration(
          color: shortlisted ? AppColors.warningBg : AppColors.surface,
          border: Border.all(
            color: shortlisted ? AppColors.amber : AppColors.border,
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(15),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              shortlisted ? Icons.star_rounded : Icons.star_outline_rounded,
              size: 21,
              color: shortlisted ? AppColors.warningFg : AppColors.textMuted,
            ),
            const SizedBox(width: 8),
            Text(
              shortlisted ? 'ضمن القائمة المختصرة' : 'أضف للقائمة المختصرة',
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w800,
                color: shortlisted ? AppColors.warningFg : AppColors.textBody,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NoteCard extends StatefulWidget {
  const _NoteCard({required this.initialNote, super.key});

  final String? initialNote;

  @override
  State<_NoteCard> createState() => _NoteCardState();
}

class _NoteCardState extends State<_NoteCard> {
  late final _controller = TextEditingController(
    text: widget.initialNote ?? '',
  );

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 6, 14, 10),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextField(
            controller: _controller,
            maxLines: 3,
            style: AppTextStyles.bodyMd,
            decoration: const InputDecoration(
              hintText: 'اكتب ملاحظاتك (لا تظهر للمتقدم)…',
              border: InputBorder.none,
            ),
          ),
          TextButton(
            onPressed: () => context.read<ApplicantProfileCubit>().saveNote(
              _controller.text.trim(),
            ),
            child: Text(
              'حفظ الملاحظة',
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w800,
                color: AppColors.charcoalSoft,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ResumeButton extends StatelessWidget {
  const _ResumeButton({required this.url});

  final String url;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: () =>
          launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.download_rounded, size: 20, color: Colors.white),
            const SizedBox(width: 9),
            Text(
              'تحميل السيرة الذاتية',
              style: AppTextStyles.button.copyWith(color: Colors.white),
            ),
          ],
        ),
      ),
    );
  }
}

class _AnswerCard extends StatelessWidget {
  const _AnswerCard({required this.answer});

  final ApplicantAnswer answer;

  Future<void> _open() async {
    final url = answer.fileUrl;
    if (url == null) return;
    await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            answer.label,
            style: AppTextStyles.caption.copyWith(
              fontWeight: FontWeight.w700,
              color: AppColors.textMuted,
            ),
          ),
          const SizedBox(height: 8),
          if (answer.fileUrl == null)
            Text(
              answer.value ?? '—',
              style: AppTextStyles.bodyMd.copyWith(
                fontWeight: FontWeight.w700,
                height: 1.7,
              ),
            )
          else if (answer.isImage)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: InkWell(
                onTap: _open,
                child: Image.network(
                  answer.fileUrl!,
                  height: 170,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => Container(
                    height: 90,
                    color: AppColors.paper,
                    alignment: Alignment.center,
                    child: const Icon(
                      Icons.broken_image_outlined,
                      color: AppColors.textMuted,
                    ),
                  ),
                ),
              ),
            )
          else
            Pressable(
              onTap: _open,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 11,
                ),
                decoration: BoxDecoration(
                  color: AppColors.warningBg,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.download_rounded,
                      size: 19,
                      color: AppColors.warningFg,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'عرض الملف',
                      style: AppTextStyles.bodySm.copyWith(
                        fontWeight: FontWeight.w800,
                        color: AppColors.warningFg,
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _ContactCard extends StatelessWidget {
  const _ContactCard({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final channel = switch (profile.contactChannel) {
      'whatsapp' => 'واتساب',
      'both' => 'داخل التطبيق أو واتساب',
      _ => 'داخل التطبيق',
    };

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.successBg,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle_rounded, color: AppColors.successFg),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'تم القبول · التواصل عبر $channel',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1F5C36),
                  ),
                ),
                if (profile.candidatePhone != null) ...[
                  const SizedBox(height: 3),
                  Text(
                    profile.candidatePhone!,
                    textDirection: TextDirection.ltr,
                    style: AppTextStyles.titleSm.copyWith(
                      color: const Color(0xFF1F5C36),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final (label, fg, bg) = switch (status) {
      'accepted' => ('مقبول', AppColors.successFg, AppColors.successBg),
      'rejected' => ('مرفوض', AppColors.errorFg, AppColors.errorBg),
      'withdrawn' => ('منسحب', AppColors.neutralFg, AppColors.neutralBg),
      'review' => ('قيد المراجعة', AppColors.warningFg, AppColors.warningBg),
      _ => ('جديد', AppColors.warningFg, AppColors.warningBg),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 12,
          fontWeight: FontWeight.w800,
          color: fg,
        ),
      ),
    );
  }
}

class _DecisionBar extends StatelessWidget {
  const _DecisionBar();

  @override
  Widget build(BuildContext context) {
    final cubit = context.read<ApplicantProfileCubit>();

    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Pressable(
              onTap: cubit.reject,
              child: Container(
                width: 56,
                height: 54,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  border: Border.all(
                    color: const Color(0xFFF6DADA),
                    width: 1.5,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.close_rounded,
                  color: AppColors.errorFg,
                ),
              ),
            ),
            const SizedBox(width: 11),
            Expanded(
              child: Pressable(
                onTap: cubit.accept,
                child: Container(
                  height: 54,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: AppColors.successFg,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'قبول المرشّح',
                        style: AppTextStyles.button.copyWith(
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(width: 8),
                      const Icon(
                        Icons.check_circle_rounded,
                        size: 22,
                        color: Colors.white,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
