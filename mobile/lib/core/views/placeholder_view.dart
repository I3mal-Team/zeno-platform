import 'package:flutter/material.dart';

import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';

/// Stands in for screens not built yet so routing can be wired and walked
/// before any feature exists.
class PlaceholderView extends StatelessWidget {
  const PlaceholderView({required this.title, super.key});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(title, style: AppTextStyles.titleMd)),
      body: Padding(
        padding: const EdgeInsets.all(AppDimensions.screenPadding),
        child: Center(child: Text(title, style: AppTextStyles.displayLg)),
      ),
    );
  }
}
