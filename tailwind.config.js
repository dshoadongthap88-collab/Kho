import animate from 'tailwindcss-animate'

/** @type {import('tailwindcss').Config} */
export default {
    // Quét mọi nơi có thể chứa tên class. Các component Livewire cũng gán chuỗi
    // class trong PHP (vd: $statusColor = 'bg-rose-50 text-rose-700') nên phải
    // quét cả app/ — thiếu chỗ nào là class đó bị loại khỏi CSS build ra.
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
        './app/Http/**/*.php',
        './app/View/**/*.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        // Cac class animate-in / slide-in-from-* / fade-in / zoom-in da co san
        // trong markup nhung truoc gio khong chay vi plugin chua duoc cai.
        animate,
    ],
}
