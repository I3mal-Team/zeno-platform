import 'package:flutter/material.dart';

import '../../../../core/components/app_top_bar.dart';

/// The auth-flow header: the shared back box with the logo on the other side.
class AuthHeader extends StatelessWidget {
  const AuthHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return const AppTopBar(showLogo: true);
  }
}
