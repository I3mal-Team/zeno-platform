import 'package:flutter/material.dart';

import '../../../../../core/motion/motion.dart';
import '../../../../../core/styles/app_colors.dart';
import '../../../../../core/styles/app_shadows.dart';
import '../../../../../core/styles/app_text_styles.dart';
import '../../../../../core/styles/category_visuals.dart';
import '../../../data/models/job_model.dart';

class JobCard extends StatelessWidget {
  const JobCard({required this.job, required this.onTap, super.key});

  final JobModel job;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(job.categoryCode);
    final place = [
      job.district,
      job.city,
    ].where((e) => e != null && e.isNotEmpty).join(' · ');

    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(15),
          decoration: BoxDecoration(
            color: AppColors.surface,
            border: Border.all(color: const Color(0xFFF0EDE6)),
            borderRadius: BorderRadius.circular(22),
            boxShadow: AppShadows.card,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 54,
                    height: 54,
                    decoration: BoxDecoration(
                      color: tint,
                      borderRadius: BorderRadius.circular(17),
                    ),
                    child: Icon(
                      CategoryVisuals.icon(job.categoryCode),
                      color: Colors.white,
                      size: 27,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                job.title,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: AppTextStyles.titleMd,
                              ),
                            ),
                            if (job.organizationVerified) ...[
                              const SizedBox(width: 5),
                              const Icon(
                                Icons.verified_rounded,
                                size: 16,
                                color: AppColors.verified,
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          [
                            job.organizationName,
                            place,
                          ].where((e) => e != null && e.isNotEmpty).join(' · '),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: AppTextStyles.caption.copyWith(
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF8A857A),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 13),
              Wrap(
                spacing: 7,
                runSpacing: 7,
                children: [
                  if (job.workType != null)
                    _Chip(icon: Icons.schedule_rounded, label: job.workType!),
                  if (place.isNotEmpty)
                    _Chip(icon: Icons.place_rounded, label: place),
                  if (job.category != null)
                    _Chip(
                      icon: CategoryVisuals.icon(job.categoryCode),
                      label: job.category!.name,
                      accent: true,
                    ),
                ],
              ),
              const SizedBox(height: 13),
              const Divider(height: 1, color: Color(0xFFEDF1EC)),
              const SizedBox(height: 12),
              Row(
                children: [
                  Text(
                    job.salary.formatted,
                    style: AppTextStyles.titleLg.copyWith(fontSize: 19),
                  ),
                  const SizedBox(width: 5),
                  Text(
                    'ريال${job.salary.unit != null ? ' / ${job.salary.unit}' : ''}',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w700,
                      color: AppColors.textMuted,
                    ),
                  ),
                  const Spacer(),
                  Text(
                    'التفاصيل',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.textStrong,
                    ),
                  ),
                  const Icon(
                    Icons.chevron_left_rounded,
                    size: 18,
                    color: Color(0xFFC9C4B9),
                  ),
                ],
              ),
            ],
          ),
        ),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({required this.icon, required this.label, this.accent = false});

  final IconData icon;
  final String label;
  final bool accent;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: accent ? AppColors.warningBg : const Color(0xFFF4F6F3),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 15,
            color: accent ? AppColors.warningFg : const Color(0xFF56524A),
          ),
          const SizedBox(width: 5),
          Text(
            label,
            style: AppTextStyles.caption.copyWith(
              fontSize: 12,
              fontWeight: accent ? FontWeight.w800 : FontWeight.w700,
              color: accent ? AppColors.warningFg : const Color(0xFF56524A),
            ),
          ),
        ],
      ),
    );
  }
}
