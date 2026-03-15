---
page: student_dashboard
---
A Student Dashboard for the Exam Management System (EMS). This is the main view for students after logging in.

**DESIGN SYSTEM (REQUIRED):**
**Visual Style:**
- Action-First Neobrutalism.
- Clean, highly contrasting aesthetics.
- Solid `#E0E1DD` background for main highlighted sections.
- Thick black borders (Neobrutalism).
- Use dark blue (`#0077B6`) for CTA buttons and highlighted shapes.
- Dark, bold drop shadows under cards and buttons.
- Use readable, sharp modern fonts.

**Components:**
1. **Cards**: Equal heights, max 2 line titles. Hover effect: show heavier shadow + `translateY(-4px)`.
2. **Buttons**: Solid dark blue (`#0077B6`) with thick black borders, white text.
3. **Badges**: Red or green/blue for status (upcoming, today, finished).
4. **Header**: White or beige background with a thick black bottom border. Nav links, plus a User Profile button/avatar on the right.
5. **Sidebar**: A thick-bordered left sidebar for quick navigation.

**Page Structure:**
1. **Header**: EMS Logo left. Search or quick stat center. User Avatar & "Đăng xuất" option on the right.
2. **Sidebar (Nav)**: Dashboard, Lịch học, Kỳ thi, Bảng điểm, Thông báo. Highlight the active tab (Dashboard) with a dark blue background.
3. **Main Content (Dashboard Overview)**:
   - **Welcome Card**: "Chào mừng sinh viên Trần Văn A!". Large brutalist box, maybe a small illustration.
   - **Quick Stats / KPI**: 4 small cards across the top (e.g. Điểm TB, Tín chỉ tích lũy, Môn đang học, Kỳ thi sắp tới).
   - **Timetable (Today)**: A block showing today's classes with room numbers and times.
   - **Upcoming Exams**: A grid or list of exams coming up in the next 7 days, prominently highlighted to urge preparation.
