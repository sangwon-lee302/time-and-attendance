import { formatDate, formatTime } from './displayCurrentDatetime';

describe('formatDate', () => {
    it('should format the date correctly', () => {
        const date = new Date('2026-05-01T00:00:00Z');
        expect(formatDate(date)).toBe('2026年5月1日(金)');
    });

    it('should format the date in Japanese locale', () => {
        const date = new Date('2026-04-30T15:30:00Z');
        expect(formatDate(date)).toBe('2026年5月1日(金)');
    });
});

describe('formatTime', () => {
    it('should format the time correctly', () => {
        const date = new Date('2026-05-01T00:00:00Z');
        expect(formatTime(date)).toBe('09:00');
    });

    it('should format the time in Japanese locale', () => {
        const date = new Date('2026-05-01T15:30:00Z');
        expect(formatTime(date)).toBe('00:30');
    });
});
