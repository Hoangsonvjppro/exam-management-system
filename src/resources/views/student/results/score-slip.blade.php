<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phieu diem - {{ $section->code }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 13px;
            margin: 28px;
        }

        .header {
            margin-bottom: 18px;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .subtitle {
            margin-top: 4px;
            color: #475569;
            font-size: 12px;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 16px;
        }

        .meta-grid td {
            padding: 4px 0;
            vertical-align: top;
        }

        .meta-label {
            width: 140px;
            color: #64748b;
        }

        .block-title {
            margin: 18px 0 8px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        table.table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
        }

        .table th {
            background: #eff6ff;
            color: #1e3a8a;
            text-align: left;
            font-weight: 700;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid #bfdbfe;
            background: #f8fbff;
        }

        .summary-row {
            margin: 4px 0;
        }

        .warning {
            margin-top: 10px;
            padding: 8px 10px;
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
        }

        .footer {
            margin-top: 24px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1 class="title">PHIẾU ĐIỂM MÔN HỌC</h1>
        <!-- <div class="subtitle">He thong quan ly thi EMS</div> -->
    </div>

    <table class="meta-grid">
        <tr>
            <td class="meta-label">Sinh viên:</td>
            <td><strong>{{ $student->name }}</strong></td>
            <td class="meta-label">MSSV:</td>
            <td><strong>{{ $student->student_code ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Môn học:</td>
            <td><strong>{{ $section->subject->name ?? 'N/A' }}</strong></td>
            <td class="meta-label">Mã lớp học phần:</td>
            <td><strong>{{ $section->code }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Học kỳ:</td>
            <td>{{ $section->semester->name ?? 'N/A' }}</td>
            <td class="meta-label">Giảng viên:</td>
            <td>{{ $section->lecturer->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="block-title">Bảng điểm thành phần</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 52px;">STT</th>
                <th>Thanh phan</th>
                <th class="text-center" style="width: 90px;">Trọng số</th>
                <th class="text-center" style="width: 110px;">Điểm</th>
            </tr>
        </thead>
        <tbody>
            @forelse($section->gradeColumns as $index => $column)
            @php
            $grade = $column->studentGrades->first();
            $score = $grade && $grade->score !== null ? number_format((float) $grade->score, 2) : '-';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $column->name }}</td>
                <td class="text-center">{{ number_format((float) $column->weight, 2) }}%</td>
                <td class="text-center">{{ $score }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Chua co cot diem duoc thiet lap cho mon hoc nay.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row"><strong>Điểm tổng kết hệ 10:</strong> {{ number_format((float) $section->final_score_10, 2) }}</div>
        <div class="summary-row"><strong>Điểm tổng kết hệ 4:</strong> {{ number_format((float) $section->final_score_4, 2) }}</div>
        <div class="summary-row"><strong>Điểm chữ:</strong> {{ $section->letter_grade }}</div>
    </div>

    @if(!$section->has_all_grades)
    <div class="warning">
        Môn học hiện chưa có đầy đủ điểm thành phần. Điểm tổng kết và điểm chữ có thể chưa chính xác. Vui lòng liên hệ giảng viên để biết thêm chi tiết.
    </div>
    @endif

    <div class="footer">
        Ngày xuất phiếu: {{ $generatedAt->format('H:i d/m/Y') }}
    </div>
</body>

</html>