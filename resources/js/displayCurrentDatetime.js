export function formatDate(date) {
    return new Intl.DateTimeFormat('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'short',
        timeZone: 'Asia/Tokyo',
    }).format(date);
}

export function formatTime(date) {
    return new Intl.DateTimeFormat('ja-JP', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Asia/Tokyo',
    }).format(date);
}

export function displayCurrentDatetime() {
    const date = document.getElementById('date');
    const time = document.getElementById('time');

    if (date) {
        date.textContent = formatDate(new Date());
    }

    if (time) {
        time.textContent = formatTime(new Date());
    }
}

if (typeof document !== 'undefined') {
    displayCurrentDatetime();
    setInterval(displayCurrentDatetime, 1000);
}
