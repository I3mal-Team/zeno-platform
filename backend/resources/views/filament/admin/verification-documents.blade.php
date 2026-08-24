<div style="display:flex;flex-direction:column;gap:8px">
    @forelse ($documents as $document)
        <a href="{{ $document->getUrl() }}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;gap:10px;border:1px solid rgb(228 228 231);border-radius:10px;padding:12px 14px;text-decoration:none">
            <span style="font-size:14px;font-weight:600;flex:1">{{ $document->file_name }}</span>
            <span style="font-size:12px;opacity:.6">{{ round($document->size / 1024) }} KB</span>
        </a>
    @empty
        <div style="font-size:14px;opacity:.7">لا توجد مستندات مرفقة.</div>
    @endforelse
</div>
