import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/notification_model.dart';
import '../manager/notifications_cubit/notifications_cubit.dart';

class NotificationsView extends StatelessWidget {
  const NotificationsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<NotificationsCubit, NotificationsState>(
        builder: (context, state) {
          final unread = state is NotificationsLoaded
              ? state.notifications.where((n) => !n.isRead).length
              : 0;

          return Column(
            children: [
              _Header(
                unread: unread,
                onMarkAll: state is NotificationsLoaded && unread > 0
                    ? () => context.read<NotificationsCubit>().markAllRead()
                    : null,
              ),
              Expanded(
                child: switch (state) {
                  NotificationsLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  NotificationsEmpty() => const _Message(
                    text: 'لا توجد إشعارات.',
                  ),
                  NotificationsFailed(:final failure) => _Message(
                    text: failure.message,
                  ),
                  NotificationsLoaded(:final notifications) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<NotificationsCubit>().load(),
                    child: ListView.separated(
                      padding: const EdgeInsets.fromLTRB(18, 16, 18, 24),
                      itemCount: notifications.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 11),
                      itemBuilder: (_, index) => Entrance(
                        index: index,
                        child: _NotificationTile(
                          notification: notifications[index],
                          onTap: () => context
                              .read<NotificationsCubit>()
                              .markRead(notifications[index].id),
                        ),
                      ),
                    ),
                  ),
                },
              ),
            ],
          );
        },
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.unread, required this.onMarkAll});

  final int unread;
  final VoidCallback? onMarkAll;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(20, topInset + 14, 20, 22),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Row(
        children: [
          const _DarkBackBox(),
          const SizedBox(width: 13),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'الإشعارات',
                  style: AppTextStyles.titleLg.copyWith(color: Colors.white),
                ),
                const SizedBox(height: 2),
                Text(
                  '$unread إشعارات غير مقروءة',
                  style: AppTextStyles.bodySm.copyWith(
                    color: const Color(0xFFC7C2B8),
                  ),
                ),
              ],
            ),
          ),
          if (onMarkAll != null)
            GestureDetector(
              onTap: onMarkAll,
              child: Text(
                'قراءة الكل',
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w800,
                  color: AppColors.amber,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _DarkBackBox extends StatelessWidget {
  const _DarkBackBox();

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: () => Navigator.of(context).maybePop(),
      child: Container(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: .1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Icon(
          Icons.chevron_right_rounded,
          size: 23,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _NotificationTile extends StatelessWidget {
  const _NotificationTile({required this.notification, required this.onTap});

  final NotificationModel notification;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final (icon, tint) = switch (notification.type) {
      'application_submitted' => (
        Icons.person_add_alt_1_rounded,
        AppColors.infoFg,
      ),
      'application_decision' => (Icons.verified_rounded, AppColors.successFg),
      'job_changed' => (Icons.edit_note_rounded, AppColors.warningFg),
      'job_alert' => (Icons.notifications_active_rounded, AppColors.amber),
      _ => (Icons.notifications_rounded, AppColors.textMuted),
    };

    return Pressable(
      onTap: notification.isRead ? null : onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: notification.isRead
              ? AppColors.surface
              : const Color(0xFFFFFDF6),
          border: Border.all(
            color: notification.isRead ? AppColors.border : AppColors.amber,
          ),
          borderRadius: BorderRadius.circular(18),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                color: tint.withValues(alpha: .12),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, size: 24, color: tint),
            ),
            const SizedBox(width: 13),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(
                        child: Text(
                          notification.title,
                          style: AppTextStyles.titleSm.copyWith(
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      if (!notification.isRead) ...[
                        const SizedBox(width: 7),
                        Container(
                          width: 7,
                          height: 7,
                          decoration: const BoxDecoration(
                            color: AppColors.amber,
                            shape: BoxShape.circle,
                          ),
                        ),
                      ],
                    ],
                  ),
                  if (notification.body.isNotEmpty) ...[
                    const SizedBox(height: 3),
                    Text(
                      notification.body,
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w500,
                        color: AppColors.textBody,
                        height: 1.6,
                      ),
                    ),
                  ],
                  if (_relativeTime(notification.createdAt).isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      _relativeTime(notification.createdAt),
                      style: AppTextStyles.caption.copyWith(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFFB0AB9F),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

String _relativeTime(String? iso) {
  if (iso == null) return '';
  final time = DateTime.tryParse(iso);
  if (time == null) return '';

  final diff = DateTime.now().difference(time);
  if (diff.inMinutes < 1) return 'الآن';
  if (diff.inMinutes < 60) return 'منذ ${diff.inMinutes} دقيقة';
  if (diff.inHours < 24) return 'منذ ${diff.inHours} ساعة';
  return 'منذ ${diff.inDays} يوم';
}

class _Message extends StatelessWidget {
  const _Message({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.notifications_off_rounded,
            size: 46,
            color: AppColors.textMuted,
          ),
          const SizedBox(height: 14),
          Text(
            text,
            style: AppTextStyles.bodyMd.copyWith(color: AppColors.textBody),
          ),
        ],
      ),
    );
  }
}
