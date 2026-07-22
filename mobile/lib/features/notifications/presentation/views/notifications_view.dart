import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

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
      appBar: AppBar(
        backgroundColor: AppColors.paper,
        title: Text('الإشعارات', style: AppTextStyles.titleLg),
        actions: [
          BlocBuilder<NotificationsCubit, NotificationsState>(
            builder: (context, state) {
              if (state is! NotificationsLoaded) return const SizedBox.shrink();

              return TextButton(
                onPressed: () => context.read<NotificationsCubit>().markAllRead(),
                child: Text(
                  'تعليم الكل كمقروء',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w800,
                    color: AppColors.textStrong,
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: BlocBuilder<NotificationsCubit, NotificationsState>(
        builder: (context, state) => switch (state) {
          NotificationsLoading() => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
          NotificationsEmpty() => const _Message(text: 'لا توجد إشعارات.'),
          NotificationsFailed(:final failure) => _Message(text: failure.message),
          NotificationsLoaded(:final notifications) => RefreshIndicator(
            color: AppColors.amber,
            onRefresh: () => context.read<NotificationsCubit>().load(),
            child: ListView.separated(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
              itemCount: notifications.length,
              separatorBuilder: (_, _) => const SizedBox(height: 10),
              itemBuilder: (_, index) => _NotificationTile(
                notification: notifications[index],
                onTap: () => context.read<NotificationsCubit>().markRead(
                  notifications[index].id,
                ),
              ),
            ),
          ),
        },
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
      'application_submitted' => (Icons.person_add_alt_1_rounded, AppColors.infoFg),
      'application_decision' => (Icons.verified_rounded, AppColors.successFg),
      'job_changed' => (Icons.edit_note_rounded, AppColors.warningFg),
      _ => (Icons.notifications_rounded, AppColors.textMuted),
    };

    return Material(
      color: notification.isRead ? AppColors.surface : const Color(0xFFFFFDF6),
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: notification.isRead ? null : onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            border: Border.all(
              color: notification.isRead ? AppColors.border : AppColors.amber,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: tint.withValues(alpha: .12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, size: 21, color: tint),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(notification.title, style: AppTextStyles.titleSm),
                    if (notification.body.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Text(
                        notification.body,
                        style: AppTextStyles.caption.copyWith(
                          fontWeight: FontWeight.w500,
                          color: AppColors.textBody,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              if (!notification.isRead)
                Container(
                  width: 8,
                  height: 8,
                  margin: const EdgeInsets.only(top: 4),
                  decoration: const BoxDecoration(
                    color: AppColors.amber,
                    shape: BoxShape.circle,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
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
