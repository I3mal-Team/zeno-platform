import 'package:equatable/equatable.dart';

/// One shape for every list endpoint, so no feature reimplements paging.
class PaginatedResponse<T> extends Equatable {
  const PaginatedResponse({
    required this.items,
    required this.currentPage,
    required this.perPage,
    required this.hasMore,
    this.total,
    this.lastPage,
  });

  factory PaginatedResponse.fromEnvelope(
    List<dynamic> data,
    Map<String, dynamic>? meta,
    T Function(Map<String, dynamic>) parse,
  ) {
    final pagination = meta?['pagination'] as Map<String, dynamic>?;

    return PaginatedResponse(
      items: data.map((e) => parse(e as Map<String, dynamic>)).toList(),
      currentPage: pagination?['current_page'] as int? ?? 1,
      perPage: pagination?['per_page'] as int? ?? data.length,
      total: pagination?['total'] as int?,
      lastPage: pagination?['last_page'] as int?,
      hasMore: pagination?['has_more'] as bool? ?? false,
    );
  }

  final List<T> items;
  final int currentPage;
  final int perPage;
  final int? total;
  final int? lastPage;
  final bool hasMore;

  bool get isEmpty => items.isEmpty;

  @override
  List<Object?> get props => [items, currentPage, perPage, total, hasMore];
}
