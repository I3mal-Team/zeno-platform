import 'dart:math';

/// A random RFC-4122 v4 UUID — used as the idempotency key when sending a
/// message so a retried send never duplicates it. Avoids a package dependency.
String generateUuidV4() {
  final random = Random();
  String hex(int length) => List.generate(
    length,
    (_) => random.nextInt(16).toRadixString(16),
  ).join();

  final variant = (8 + random.nextInt(4)).toRadixString(16);

  return '${hex(8)}-${hex(4)}-4${hex(3)}-$variant${hex(3)}-${hex(12)}';
}
