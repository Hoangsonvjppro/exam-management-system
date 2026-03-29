<x-app-layout>
    @section('title', 'Khiếu nại — EMS')
    @section('page-title', 'Khiếu nại')

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[28px] font-bold text-navy-900 leading-tight">Khiếu nại của tôi</h2>
                <p class="text-sm font-medium text-text-muted mt-1">Theo dõi tiến trình và kết quả xử lý các đơn khiếu nại về điểm thi.</p>
            </div>
        </div>

        <x-card padding="true">
            @if($complaints->isEmpty())
            <div class="text-center py-16 bg-surface-0 border-[0.5px] border-border-clean border-dashed rounded-[8px]">
                <svg class="w-14 h-14 text-blue-100 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="text-navy-900 font-semibold text-base mb-2">Chưa có khiếu nại nào</p>
                <p class="text-sm text-text-muted max-w-md mx-auto">
                    Bạn có thể gửi khiếu nại từ Tab <strong>"Điểm số"</strong> trong trang chi tiết lớp học phần.
                </p>
            </div>
            @else
            <div class="overflow-x-auto border border-border-clean rounded-[8px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-1 border-b border-border-clean">
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Ngày gửi</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Bài thi / Lớp</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Điểm cũ</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Điểm mới</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted text-center">Trạng thái</th>
                            <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-text-muted">Phản hồi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-clean/70">
                        @foreach($complaints as $complaint)
                        <tr class="hover:bg-surface-0 transition-colors">
                            <td class="py-3 px-4 align-top">
                                <span class="text-[13px] font-semibold text-navy-900">{{ $complaint->created_at->format('d/m/Y') }}</span>
                                <div class="text-[11px] text-text-muted mt-0.5">{{ $complaint->created_at->format('H:i') }}</div>
                            </td>
                            <td class="py-3 px-4 align-top">
                                <p class="text-[13px] font-semibold text-navy-900 leading-snug">{{ $complaint->schedule->exam->title ?? 'N/A' }}</p>
                                <p class="text-[11px] text-text-muted mt-1">{{ $complaint->section->code ?? 'N/A' }} — {{ $complaint->section->name ?? '' }}</p>
                            </td>
                            <td class="py-3 px-4 align-top text-center text-[14px] font-bold text-navy-900">
                                {{ number_format($complaint->current_score, 2) }}
                            </td>
                            <td class="py-3 px-4 align-top text-center">
                                @if($complaint->updated_score)
                                    <span class="text-[14px] font-bold text-teal-600">{{ number_format($complaint->updated_score, 2) }}</span>
                                @else
                                    <span class="text-text-muted/50">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-top text-center">
                                @php
                                    $statusConfig = match($complaint->status) {
                                        'pending'   => ['bg-yellow-50 text-yellow-700 border-yellow-200', 'Đang chờ'],
                                        'reviewing' => ['bg-blue-50 text-blue-700 border-blue-200', 'Đang xử lý'],
                                        'resolved'  => ['bg-teal-50 text-teal-700 border-teal-200', 'Được duyệt'],
                                        'rejected'  => ['bg-red-50 text-red-700 border-red-200', 'Bị từ chối'],
                                        default     => ['bg-gray-50 text-gray-500 border-gray-200', 'N/A']
                                    };
                                @endphp
                                <span class="inline-flex items-center text-[10px] font-bold uppercase rounded-[4px] px-2 py-1 border {{ $statusConfig[0] }}">
                                    {{ $statusConfig[1] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-top max-w-[250px]">
                                <div class="text-[12px] text-text-muted mb-2">
                                    <strong class="text-navy-900 font-semibold">Lý do:</strong> 
                                    <span class="break-words line-clamp-2" title="{{ $complaint->reason }}">{{ $complaint->reason }}</span>
                                </div>
                                @if($complaint->reviewer_note)
                                <div class="text-[12px] bg-blue-50/50 p-2 rounded border border-blue-100/50">
                                    <strong class="text-blue-800 font-semibold">Phản hồi ({{ $complaint->reviewer->name ?? 'GV' }}):</strong> 
                                    <span class="text-blue-900 break-words">{{ $complaint->reviewer_note }}</span>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $complaints->links() }}
            </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
